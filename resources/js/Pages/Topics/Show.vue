<script setup>
import { ref, computed } from 'vue'; // <-- THÊM ref
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import CreatePostForm from '@/Pages/Teams/Partials/CreatePostForm.vue';
import PollDisplay from '@/Pages/Topics/Partials/PollDisplay.vue';
import CommentSection from '@/Pages/Topics/Partials/CommentSection.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AssignmentView from '@/Pages/Teams/Partials/AssignmentView.vue';
import Dropdown from '@/Components/Dropdown.vue'; // <-- THÊM
import DropdownLink from '@/Components/DropdownLink.vue'; // <-- THÊM
import EditPostModal from '@/Pages/Topics/Partials/EditPostModal.vue'; // <-- THÊM MODAL SỬA
import { onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
const props = defineProps({
    team: Object,
    topic: Object,
    posts: Array, // posts bây giờ đã có 'can' và 'created_at_formatted'
    permissions: Object,
    authUserId: Number, 
    userSubmissions: Object,
    userQuizAttempts: Object,
});

// (Logic 'showCreatePostForm' và 'toggleLock' của bạn giữ nguyên)
const canManageTopics = computed(() => props.permissions.canManageTopics);
const canCreatePosts = computed(() => props.permissions.canCreatePosts);
const showCreatePostForm = computed(() => {
    if (props.topic.is_locked && !canManageTopics.value) return false;
    if (!canCreatePosts.value) return false;
    return true;
});
const lockForm = useForm({});
const toggleLock = () => {
    lockForm.patch(route('topics.toggleLock', props.topic), {
        preserveScroll: true,
    });
};

// --- LOGIC MỚI CHO SỬA/XÓA ---

// Biến trạng thái để mở modal sửa
const editingPost = ref(null);

const openEditModal = (post) => {
    editingPost.value = post;
};

const closeEditModal = () => {
    editingPost.value = null;
};

// Form và hàm Xóa (Đã có)
const deleteForm = useForm({});
const confirmDeletePost = (postId) => {
    if (confirm('Bạn có chắc chắn muốn xóa bài đăng này không?')) {
        deleteForm.delete(route('posts.destroy', postId), {
            preserveScroll: true,
        });
    }
};
onMounted(() => {
    // Lấy ID của user đang đăng nhập
    const userId = usePage().props.auth.user.id;

    console.log("Bảng tin đang chờ bài mới...");

    // Lắng nghe đúng cái kênh mà cái chuông đang nghe
    Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log("Nhận tín hiệu có bài mới -> Tải lại nội dung ngay!", notification);
            
            // 3. THỰC HIỆN RELOAD DỮ LIỆU NGẦM (Không f5 trình duyệt)
            // 'only' giúp chỉ tải lại biến 'posts' cho nhẹ (nếu props bài viết của bạn tên là 'posts')
            // Nếu không chắc props tên gì, bạn cứ dùng router.reload() là được.
            if (notification.type === 'new_comment') {
            // Reload lại để hiện bình luận mới
            // (Nếu bạn tách comment ra props riêng thì reload props đó, không thì reload hết)
            router.reload(); 
        }
            if (['new_comment', 'reply_comment'].includes(notification.type)) {
            router.reload(); 
        }
            router.reload({ only: ['posts'] }); 
        });
});

// (Tùy chọn) Hủy lắng nghe khi rời trang để tránh lỗi
onUnmounted(() => {
    const userId = usePage().props.auth.user.id;
    Echo.leave(`App.Models.User.${userId}`);
});
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
                
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="showCreatePostForm">
                    <CreatePostForm 
                        :team="team" 
                        :topic="topic" 
                        :can-manage-topics="canManageTopics" 
                    />
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
                    
                    <div class="mt-4 space-y-4">
                        <div v-if="posts.length > 0" class="space-y-4">
                            
                            <div v-for="post in posts" :key="post.id" class="bg-white shadow-sm rounded-lg">
                                <div class="p-4 sm:p-6">
                                    
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center">
                                            <img class="h-8 w-8 rounded-full object-cover" :src="post.user.profile_photo_url" :alt="post.user.name">
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">{{ post.user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ post.created_at_formatted }}</div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="post.can && (post.can.update || post.can.delete)" class="relative">
                                            <Dropdown align="right" width="48">
                                                <template #trigger>
                                                    <button class="p-2 rounded-full text-gray-400 hover:text-gray-600 focus:outline-none">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                                        </svg>
                                                    </button>
                                                </template>
                                                <template #content>
                                                    <DropdownLink as="button" v-if="post.can.update" @click="openEditModal(post)">
                                                        Sửa bài đăng
                                                    </DropdownLink>
                                                    <DropdownLink as="button" v-if="post.can.delete" @click="confirmDeletePost(post.id)" class="text-red-600 hover:bg-red-50">
                                                        Xóa bài đăng
                                                    </DropdownLink>
                                                </template>
                                            </Dropdown>
                                        </div>
                                    </div>

                                    <div class="content-container space-y-2">
                                        
                                        <AssignmentView
                                            v-if="post.post_type === 'assignment'"
                                            :post="post"
                                            :can-manage-topics="canManageTopics"
                                            :user-submission="userSubmissions[post.id]"
                                        />

                                        <div v-else-if="post.post_type === 'quiz'" class="space-y-3">
                                            <h3 class="font-bold text-lg text-purple-700">✏️ Bài kiểm tra trắc nghiệm</h3>
                                            <p class="whitespace-pre-wrap">{{ post.content }}</p>

                                            <div v-if="post.can && post.can.update">
                                                <Link
                                                    :href="route('post.quiz.manage', post.id)"
                                                    class="inline-block px-4 py-2 bg-indigo-100 text-indigo-700 text-sm font-medium rounded-md hover:bg-indigo-200 transition duration-150"
                                                >
                                                    Quản lý câu hỏi
                                                </Link>
                                            </div>

                                            <div v-if="post.can && !post.can.update">

                                                <div v-if="props.userQuizAttempts[post.id]" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                                    <p class="font-semibold text-green-800">Bạn đã hoàn thành:</p>
                                                    <p class="text-2xl font-bold text-green-700">
                                                        {{ Number(props.userQuizAttempts[post.id].score).toFixed(2) }} / {{ post.max_points || 100 }} điểm
                                                    </p>
                                                    <Link 
                                                        :href="route('quiz.results', props.userQuizAttempts[post.id].id)" 
                                                        class="text-sm text-green-600 hover:text-green-800 hover:underline"
                                                    >
                                                        Xem lại kết quả
                                                    </Link>
                                                </div>

                                                <Link
                                                    v-else
                                                    :href="route('quiz.start', post.id)"
                                                    method="post"
                                                    as="button"
                                                    class="inline-block mt-4 px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition duration-150"
                                                >
                                                    Bắt đầu làm bài
                                                </Link>
                                            </div>
                                        </div>
                                        <p v-else-if="post.post_type === 'text'" class="text-gray-700 whitespace-pre-wrap">
                                            {{ post.content }}
                                        </p>
                                        <p v-else-if="post.post_type === 'text'" class="text-gray-700 whitespace-pre-wrap">
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
                                    </div>

                                    <CommentSection
                                        :post="post"
                                        :topic="topic"
                                        :authUserId="authUserId"
                                    />
                                </div>
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

        <EditPostModal
            :show="editingPost !== null"
            :post="editingPost"
            @close="closeEditModal"
        />

    </AppLayout>
</template>