<script setup>
    import { ref } from 'vue';
    import CommentForm from './CommentForm.vue'; 
    
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
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-sm text-gray-900">{{ comment.user.name }}</span>
                    
                    <div v-if="comment.user.role === 'student'" class="flex items-center gap-1 select-none">
                        
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm" title="Cấp độ hiện tại">
                            LVL {{ comment.user.level }}
                        </span>
    
                        <div v-if="comment.user.badges && comment.user.badges.length > 0" class="flex -space-x-3 ml-2 items-center">
                        <div 
                            v-for="badge in comment.user.badges" 
                            :key="badge.id" 
                            :title="badge.name + ': ' + badge.description"
                            class="relative z-0 hover:z-10 transition-transform hover:scale-110 group cursor-help"
                        >
                            <img 
                                v-if="badge.icon_path && badge.icon_path !== 'badges/default.png'" 
                                :src="'/storage/' + badge.icon_path" 
                                class="w-12 h-12 rounded-full border-2 border-white bg-white object-contain shadow-sm" 
                                :alt="badge.name"
                            >
                            
                            <div v-else class="w-8 h-8 rounded-full border-2 border-white bg-yellow-100 flex items-center justify-center text-yellow-600 shadow-sm">
                                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                 </svg>
                            </div>
                        </div>
                    </div>
                    </div>
                    </div>
                
                <p class="text-gray-700 whitespace-pre-wrap mt-0.5">{{ comment.body }}</p>
                
                <div class="mt-1 text-sm">
                    <button @click="toggleReplyForm" class="text-xs font-medium text-gray-500 hover:text-indigo-600 transition">
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
    
                <div v-if="comment.replies && comment.replies.length > 0" class="mt-3 pl-3 border-l-2 border-gray-100 space-y-3">
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