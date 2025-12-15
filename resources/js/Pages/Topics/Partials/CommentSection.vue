<script setup>
import { ref, computed } from 'vue';
import CommentForm from './Comments/CommentForm.vue';
import Comment from './Comments/Comment.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    post: Object,
    topic: Object,
    authUserId: Number,
});

const commentsVisible = ref(false);
const page = usePage();

const isPostOwner = computed(() => props.post.user_id === props.authUserId);
const isTeacher = computed(() => page.props.auth.user.role === 'teacher');

const canComment = computed(() => {
    if (props.topic.is_locked) return isTeacher.value;
    return props.post.are_comments_enabled;
});

const toggleCommentForm = useForm({});
const toggleComments = () => {
    toggleCommentForm.patch(route('posts.toggleComments', props.post), { preserveScroll: true });
};
</script>

<template>
    <div class="border-t border-slate-800 pt-4 mt-4">
        
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-xs font-bold text-slate-500 font-mono uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                Tất cả bình luận ({{ post.parent_comments.length }})
            </h4>
            <button @click="commentsVisible = !commentsVisible" class="text-[10px] text-cyan-400 hover:text-white uppercase font-bold tracking-wider transition border border-cyan-500/20 px-2 py-1 rounded bg-cyan-900/10 hover:bg-cyan-600">
                {{ commentsVisible ? 'Thu gọn' : 'Mở rộng' }}
            </button>
        </div>

        <div v-if="commentsVisible" class="space-y-4">

            <div v-if="canComment" class="flex gap-3 bg-slate-900/50 p-3 rounded-xl border border-white/5">
                <img class="h-8 w-8 rounded-lg object-cover border border-slate-700" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                <div class="flex-1">
                    <CommentForm :postId="post.id" />
                </div>
            </div>

            <div v-else class="text-center text-rose-400 text-xs font-mono p-3 bg-rose-900/10 border border-rose-500/20 rounded-xl">
                <span v-if="!post.are_comments_enabled">>> Bài viết đã được tắt bình luận.</span>
                <span v-else-if="topic.is_locked">>> CHANNEL LOCKED. OFFICER ACCESS ONLY.</span>
            </div>

            <div v-if="isPostOwner" class="flex justify-end">
                <button @click="toggleComments" class="text-[9px] text-slate-500 hover:text-white uppercase tracking-widest underline decoration-slate-700 hover:decoration-white transition">
                    {{ post.are_comments_enabled ? 'Tắt bình luận' : 'Bật bình luận' }}
                </button>
            </div>

            <div class="space-y-3 pl-2 border-l border-slate-800 ml-2">
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