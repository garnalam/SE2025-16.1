<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3'; // <--- Import Link
import CommentForm from './CommentForm.vue'; 

const props = defineProps({
    comment: Object,
    postId: Number,
});

const showingReplyForm = ref(false);
const toggleReplyForm = () => { showingReplyForm.value = !showingReplyForm.value; };
</script>

<template>
    <div class="flex gap-3 group">
        <div class="flex flex-col items-center">
             <Link :href="route('profile.public', comment.user.id)" class="group-hover:ring-2 ring-cyan-500/50 rounded-lg transition-all">
                 <img class="h-8 w-8 rounded-lg object-cover border border-slate-600 hover:border-cyan-500 transition-colors" :src="comment.user.profile_photo_url" :alt="comment.user.name">
             </Link>
             <div class="w-px h-full bg-slate-800 mt-2 group-last:hidden"></div>
        </div>
        
        <div class="flex-1 pb-4">
            <div class="bg-[#1e293b] border border-white/5 rounded-r-xl rounded-bl-xl p-3 relative hover:border-white/10 transition-colors">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <Link :href="route('profile.public', comment.user.id)" class="font-bold text-xs text-slate-200 font-exo hover:text-cyan-400 hover:underline transition">
                        {{ comment.user.name }}
                    </Link>
                    
                    <div v-if="comment.user.role === 'student'" class="flex items-center gap-1">
                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                            LVL {{ comment.user.level || 1 }}
                        </span>
                        
                         <div v-if="comment.user.badges && comment.user.badges.length > 0" class="flex -space-x-1 ml-1">
                            <div v-for="badge in comment.user.badges" :key="badge.id" :title="badge.name" class="w-4 h-4 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center">
                                <img v-if="badge.icon_path" :src="'/storage/' + badge.icon_path" class="w-3 h-3 object-contain">
                                <div v-else class="w-2 h-2 rounded-full bg-yellow-500"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-slate-300 text-sm whitespace-pre-wrap leading-relaxed">{{ comment.body }}</p>
                
                <div class="mt-2 flex items-center gap-3">
                    <button @click="toggleReplyForm" class="text-[10px] font-bold text-slate-500 hover:text-cyan-400 transition uppercase tracking-wider">
                        {{ showingReplyForm ? 'Cancel' : 'Reply' }}
                    </button>
                    <span class="text-[10px] text-slate-600 font-mono">{{ new Date(comment.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                </div>
            </div>

            <div v-if="showingReplyForm" class="mt-2 pl-2">
                <CommentForm :postId="postId" :parentId="comment.id" :onSuccess="() => showingReplyForm = false" />
            </div>

            <div v-if="comment.replies && comment.replies.length > 0" class="mt-3 space-y-3">
                <Comment v-for="reply in comment.replies" :key="reply.id" :comment="reply" :postId="postId" />
            </div>
        </div>
    </div>
</template>