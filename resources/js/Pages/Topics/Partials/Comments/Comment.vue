<script setup>
import { ref } from 'vue';
import CommentForm from './CommentForm.vue'; // Import form vừa tạo

const props = defineProps({
    comment: Object,
    postId: Number,
});

// Trạng thái để ẩn/hiện form trả lời
const showingReplyForm = ref(false);

const toggleReplyForm = () => {
    showingReplyForm.value = !showingReplyForm.value;
};
</script>

<template>
    <div class="flex space-x-3">
        <img class="h-8 w-8 rounded-full object-cover" :src="comment.user.profile_photo_url" :alt="comment.user.name">
        
        <div class="flex-1">
            <div>
                <span class="font-semibold text-sm text-gray-900">{{ comment.user.name }}</span>
                <p class="text-gray-700 whitespace-pre-wrap">{{ comment.body }}</p>
            </div>
            
            <div class="mt-1 text-sm">
                <button @click="toggleReplyForm" class="text-gray-500 hover:text-indigo-600 font-medium">
                    {{ showingReplyForm ? 'Hủy' : 'Trả lời' }}
                </button>
            </div>

            <div v-if="showingReplyForm" class="mt-2">
                <CommentForm
                    :postId="postId"
                    :parentId="comment.id"
                    :onSuccess="() => showingReplyForm = false" 
                />
            </div>

            <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 space-y-4">
                <Comment
                    v-for="reply in comment.replies"
                    :key="reply.id"
                    :comment="reply"
                    :postId="postId"
                />
            </div>
        </div>
    </div>
</template>