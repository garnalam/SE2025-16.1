<script setup>
import { ref, computed } from 'vue';
import CommentForm from './Comments/CommentForm.vue'; // Form (Bước 8.4)
import Comment from './Comments/Comment.vue'; // Hiển thị 1 comment (Bước 8.4)
import { useForm } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    post: Object,
    topic: Object,
    authUserId: Number,
});

// Trạng thái ẩn/hiện toàn bộ khu vực bình luận
const commentsVisible = ref(false);

// === LOGIC PHÂN QUYỀN (QUAN TRỌNG) ===

// 1. Kiểm tra xem user có phải là chủ bài đăng không
const isPostOwner = computed(() => props.post.user_id === props.authUserId);

// 2. Kiểm tra xem user có phải là Giáo viên không
// (Dựa vào $page.props.auth.user.role)
const isTeacher = computed(() => {
    // $page là global prop của Inertia, không cần defineProps
    return useForm().page.props.auth.user.role === 'teacher'; 
});

// 3. Quyền được phép bình luận
const canComment = computed(() => {
    // Nếu topic bị khóa, chỉ GV được bình luận
    if (props.topic.is_locked) {
        return isTeacher.value;
    }
    // Nếu topic không khóa, kiểm tra xem bài đăng có cho phép bình luận không
    return props.post.are_comments_enabled;
});

// === LOGIC TẮT/MỞ BÌNH LUẬN ===
const toggleCommentForm = useForm({});
const toggleComments = () => {
    toggleCommentForm.patch(route('posts.toggleComments', props.post), {
        preserveScroll: true,
    });
};

</script>

<template>
    <div class="border-t border-gray-200 pt-4 mt-4">
        
        <div class="flex justify-between items-center">
            <h4 class="text-sm font-semibold text-gray-700">
                Bình luận ({{ post.parent_comments.length }})
            </h4>
            <button @click="commentsVisible = !commentsVisible" class="text-sm text-indigo-600 hover:text-indigo-800">
                {{ commentsVisible ? 'Ẩn bình luận' : 'Hiện bình luận' }}
            </button>
        </div>

        <div v-if="commentsVisible" class="mt-3">

            <div v-if="canComment" class="flex space-x-3">
                <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                <div class="flex-1">
                    <CommentForm :postId="post.id" />
                </div>
            </div>

            <div v-else class="text-center text-gray-500 text-sm p-4 bg-gray-50 rounded-md">
                <span v-if="!post.are_comments_enabled">
                    Chủ bài đăng đã tắt tính năng bình luận.
                </span>
                <span v-else-if="topic.is_locked">
                    🔒 Chủ đề này đã bị khóa. Chỉ giáo viên mới có thể bình luận.
                </span>
            </div>

            <div v-if="isPostOwner" class="mt-4 flex justify-end">
                <SecondaryButton @click="toggleComments">
                    {{ post.are_comments_enabled ? 'Tắt bình luận' : 'Mở bình luận' }}
                </SecondaryButton>
            </div>


            <div class="mt-4 space-y-4">
                <Comment
                    v-for="comment in post.parent_comments"
                    :key="comment.id"
                    :comment="comment"
                    :postId="post.id"
                />
            </div>
        </div>
    </div>
</template>