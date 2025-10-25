<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import CreatePostForm from '@/Pages/Teams/Partials/CreatePostForm.vue';
import { Link, useForm } from '@inertiajs/vue3'; // <-- THÊM useForm
import { computed } from 'vue'; // <-- THÊM computed
import PollDisplay from '@/Pages/Topics/Partials/PollDisplay.vue';
import CommentSection from '@/Pages/Topics/Partials/CommentSection.vue';
// THÊM CÁC IMPORT NÀY:
import SecondaryButton from '@/Components/SecondaryButton.vue';
// InputError đã có sẵn trong Jetstream (thường là vậy)
// import InputError from '@/Components/InputError.vue'; 

const props = defineProps({
    team: Object,
    topic: Object,
    posts: Array,
    permissions: Object,
    authUserId: Number, // <-- Thêm prop này (đã thêm ở Bước 7.5)
});
console.log('--- DEBUG LỖI KHÓA CHỦ ĐỀ ---');
console.log('1. Giá trị is_locked (mong đợi false):', props.topic.is_locked, typeof props.topic.is_locked);
console.log('2. Giá trị canManageTopics (mong đợi true):', props.permissions.canManageTopics, typeof props.permissions.canManageTopics);
// TÍNH TOÁN CÁC BIẾN MỚI
// Quyền này sẽ được truyền từ controller ở bước 6.6
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
                    <CreatePostForm :team="team" :topic="topic" />
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
                            
                            <div v-for="post in posts" :key="post.id" class="bg-white shadow-sm rounded-lg p-4">
                                
                                <div class="flex items-center mb-3">
                                    <img class="h-8 w-8 rounded-full object-cover" :src="post.user.profile_photo_url" :alt="post.user.name">
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900">{{ post.user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ new Date(post.created_at).toLocaleString() }}</div>
                                    </div>
                                </div>

                                <p v-if="post.post_type === 'text'" class="text-gray-700 whitespace-pre-wrap">
                                    {{ post.content }}
                                </p>

                                <PollDisplay 
                                    v-else-if="post.post_type === 'poll'"
                                    :post="post"
                                    :authUserId="authUserId"
                                />

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