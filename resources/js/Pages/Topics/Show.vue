<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import CreatePostForm from '@/Pages/Teams/Partials/CreatePostForm.vue';
import { Link, useForm } from '@inertiajs/vue3'; 
import { computed } from 'vue'; 
import PollDisplay from '@/Pages/Topics/Partials/PollDisplay.vue';
import CommentSection from '@/Pages/Topics/Partials/CommentSection.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    team: Object,
    topic: Object,
    posts: Array,
    permissions: Object,
    authUserId: Number, 
});

// TÍNH TOÁN CÁC BIẾN MỚI
const canManageTopics = computed(() => props.permissions.canManageTopics);
const canCreatePosts = computed(() => props.permissions.canCreatePosts);

// Ẩn form nếu: (chủ đề bị khóa VÀ user không phải là GV)
const showCreatePostForm = computed(() => {
    // Nếu topic bị khóa VÀ user không phải là người quản lý (GV)
    if (props.topic.is_locked && !canManageTopics.value) {
        return false;
    }
    // Hoặc nếu user không có quyền đăng bài (không phải thành viên)
    if (!canCreatePosts.value) {
        return false;
    }
    // Mọi trường hợp khác (GV, hoặc topic không khóa) -> hiển thị
    return true;
});

// Form để gọi route 'toggleLock'
const lockForm = useForm({});
const toggleLock = () => {
    lockForm.patch(route('topics.toggleLock', props.topic), {
        preserveScroll: true,
    });
};

// HÀM FORMAT NGÀY (Lấy từ file Show.vue trước đó của bạn)
const formatMyDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes} ${day}/${month}/${year}`;
};
</script>

<template>
    <AppLayout :title="topic.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <Link :href="route('teams.feed', team)" class="text-indigo-600 hover:text-indigo-800">
                        {{ team.name }}
                    </Link>
                    <span class="text-gray-500 mx-2">/</span>
                    {{ topic.name }}
                    <span v-if="topic.is_locked" title="Chủ đề này đã bị khóa" class="ml-2">🔒</span>
                </h2>
                
                <div v-if="canManageTopics">
                    <SecondaryButton @click="toggleLock" :class="{ 'opacity-25': lockForm.processing }" :disabled="lockForm.processing">
                        {{ topic.is_locked ? '🔓 Mở khóa Chủ đề' : '🔒 Khóa Chủ đề' }}
                    </secondarybutton>
                </div>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div v-if="showCreatePostForm">
                    <!-- ===== THÊM PROP can-manage-topics VÀO DÒNG NÀY ===== -->
                    <CreatePostForm 
                        :team="team" 
                        :topic="topic" 
                        :can-manage-topics="canManageTopics" 
                    />
                    <!-- ==================================================== -->
                    <SectionBorder />
                </div>
                <div v-else-if="topic.is_locked && canCreatePosts" 
                     class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-md shadow-sm">
                    <p class="font-bold">🔒 Chủ đề đã bị khóa</p>
                    <p>Chỉ giáo viên mới có thể đăng bài trong chủ đề này.</p>
                </div>


                <div class="mt-10 sm:mt-0">
                    <h3 class="text-lg font-medium text-gray-900">
                        Bài đăng trong chủ đề
                    </h3>
                    <p v-if="topic.description" class="text-sm text-gray-600 mb-4">{{ topic.description }}</p>

                    <div class="mt-4 space-y-4">
    
                        <div v-if="posts.length > 0" class="space-y-4">
                            
                            <!-- Vòng lặp v-for (Đã cập nhật ở lần trước) -->
                            <div v-for="post in posts" :key="post.id" class="bg-white shadow-sm rounded-lg p-4">
                                
                                <div class="flex items-center mb-3">
                                    <img class="h-8 w-8 rounded-full object-cover" :src="post.user.profile_photo_url" :alt="post.user.name">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900">{{ post.user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ formatMyDate(post.created_at) }}</div>
                                    </div>
                                </div>

                                <div class="content-container space-y-2">
                                    
                                    <p v-if="post.post_type === 'text'" class="text-gray-700 whitespace-pre-wrap">
                                        {{ post.content }}
                                    </p>

                                    <PollDisplay 
                                        v-else-if="post.post_type === 'poll'"
                                        :post="post"
                                        :authUserId="authUserId"
                                    />

                                    <div v-else-if="post.post_type === 'material'" class="space-y-2">
                                        <h3 class="font-bold text-lg text-indigo-700">📚 Tài liệu mới</h3>
                                        <p class="whitespace-pre-wrap">{{ post.content }}</p> 
                                        <div v-if="post.attachments && post.attachments.length > 0">
                                            <strong>File đính kèm:</strong>
                                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                                <li v-for="file in post.attachments" :key="file.id">
                                                    <a 
                                                        :href="'/storage/' + file.path" 
                                                        target="_blank" 
                                                        class="text-blue-600 hover:underline hover:text-blue-800"
                                                    >
                                                        {{ file.original_name }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div v-else-if="post.post_type === 'assignment'" class="space-y-2">
                                        <h3 class="font-bold text-lg text-green-700">🧑‍💻 Bài tập: {{ post.title }}</h3>
                                        
                                        <p class="whitespace-pre-wrap">{{ post.content }}</p> 

                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-700 border-t pt-2 mt-2">
                                            <strong v-if="post.max_points">
                                                Điểm tối đa: {{ post.max_points }}
                                            </strong>
                                            <strong v-if="post.due_date" class="text-red-600">
                                                Hạn nộp: {{ formatMyDate(post.due_date) }}
                                            </strong>
                                        </div>

                                        <div v-if="post.attachments && post.attachments.length > 0" class="mt-2">
                                            <strong>File đính kèm:</strong>
                                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                                <li v-for="file in post.attachments" :key="file.id">
                                                    <a 
                                                        :href="'/storage/' + file.path"
                                                        target="_blank" 
                                                        class="text-blue-600 hover:underline hover:text-blue-800"
                                                    >
                                                        {{ file.original_name }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <CommentSection
                                    :post="post"
                                    :topic="topic"
                                    :authUserId="authUserId"
                                />
                            </div>
                        </div>
                    
                        <div v-else class="text-center text-gray-500 py-6">
                            Chưa có bài đăng nào trong chủ đề này.
                            <span v-if="showCreatePostForm">Hãy là người đầu tiên!</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

