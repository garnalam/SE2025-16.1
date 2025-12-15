<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CreatePostForm from '@/Pages/Teams/Partials/CreatePostForm.vue';
import PollDisplay from '@/Pages/Topics/Partials/PollDisplay.vue';
import CommentSection from '@/Pages/Topics/Partials/CommentSection.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AssignmentView from '@/Pages/Teams/Partials/AssignmentView.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import EditPostModal from '@/Pages/Topics/Partials/EditPostModal.vue';

const props = defineProps({
    team: Object,
    topic: Object,
    posts: Array,
    permissions: Object,
    authUserId: Number, 
    userSubmissions: Object,
    userQuizAttempts: Object,
});

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

const editingPost = ref(null);
const openEditModal = (post) => { editingPost.value = post; };
const closeEditModal = () => { editingPost.value = null; };

const deleteForm = useForm({});
const confirmDeletePost = (postId) => {
    if (confirm('Bạn đã chắc chắn xóa bài viết chưa ? ')) {
        deleteForm.delete(route('posts.destroy', postId), { preserveScroll: true });
    }
};

onMounted(() => {
    const userId = usePage().props.auth.user.id;
    if (window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                if (['new_comment', 'reply_comment'].includes(notification.type)) {
                    router.reload(); 
                } else {
                    router.reload({ only: ['posts'] }); 
                }
            });
    }
});

onUnmounted(() => {
    const userId = usePage().props.auth.user.id;
    if (window.Echo) window.Echo.leave(`App.Models.User.${userId}`);
});
</script>

<template>
    <AppLayout :title="topic.name">
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <Link :href="route('teams.feed', team)" class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-cyan-400 hover:border-cyan-500 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-mono uppercase tracking-widest text-slate-500">
                            <span>{{ team.name }}</span>
                            <span>//</span>
                            <span>Channel_ID: {{ topic.id }}</span>
                        </div>
                        <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo flex items-center gap-2">
                            {{ topic.name }}
                            <span v-if="topic.is_locked" class="px-2 py-0.5 rounded bg-rose-500/20 border border-rose-500/40 text-rose-500 text-[10px] animate-pulse">LOCKED</span>
                        </h2>
                    </div>
                </div>
                
                <div v-if="canManageTopics">
                    <button @click="toggleLock" 
                        class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-all border"
                        :class="topic.is_locked 
                            ? 'bg-emerald-600/10 text-emerald-400 border-emerald-500/50 hover:bg-emerald-600 hover:text-white' 
                            : 'bg-rose-600/10 text-rose-400 border-rose-500/50 hover:bg-rose-600 hover:text-white'"
                        :disabled="lockForm.processing">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="topic.is_locked" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        {{ topic.is_locked ? 'Unlock Channel' : 'Lock Channel' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="text-sm font-mono font-bold">{{ $page.props.flash.success }}</span>
            </div>

            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0">
                <div v-if="showCreatePostForm" class="mb-10">
                    <CreatePostForm :team="team" :topic="topic" :can-manage-topics="canManageTopics" />
                </div>
            </transition>

<div v-if="topic.is_locked && canCreatePosts" class="mb-8 p-4 bg-amber-500/10 border-l-4 border-amber-500 rounded-r-lg">
    <div class="flex items-center gap-3">
        <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <p class="text-amber-400 font-bold uppercase tracking-wider text-xs">Security Protocol Active</p>
            <p class="text-slate-400 text-sm">Channel is read-only for cadets. Only officers may transmit.</p>
        </div>
    </div>
</div>

            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-px bg-slate-800 flex-1"></div>
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em]">Newfeed Lớp học</span>
                    <div class="h-px bg-slate-800 flex-1"></div>
                </div>

                <div v-if="posts.length > 0" class="space-y-6">
                    <article v-for="post in posts" :key="post.id" 
                        class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden shadow-lg hover:border-white/10 transition-colors duration-300 relative group">
                        
                        <!-- Glow Effect based on Post Type -->
                        <div class="absolute top-0 left-0 w-1 h-full" 
                            :class="{
                                'bg-emerald-500': post.post_type === 'assignment',
                                'bg-purple-500': post.post_type === 'quiz',
                                'bg-cyan-500': post.post_type === 'material',
                                'bg-pink-500': post.post_type === 'poll',
                                'bg-indigo-500': post.post_type === 'text'
                            }">
                        </div>

                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4 pl-2">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img class="h-10 w-10 rounded-lg object-cover border border-slate-700" :src="post.user.profile_photo_url" :alt="post.user.name">
                                        <div v-if="post.user.id === team.user_id" class="absolute -top-1 -right-1 bg-indigo-500 text-white text-[8px] font-bold px-1 rounded shadow-sm">CDR</div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-200 font-exo">{{ post.user.name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono flex items-center gap-2">
                                            <span>{{ post.created_at_formatted }}</span>
                                            <span class="text-slate-700">|</span>
                                            <span class="uppercase tracking-wider" 
                                                :class="{
                                                    'text-emerald-400': post.post_type === 'assignment',
                                                    'text-purple-400': post.post_type === 'quiz',
                                                    'text-cyan-400': post.post_type === 'material',
                                                    'text-pink-400': post.post_type === 'poll',
                                                    'text-indigo-400': post.post_type === 'text'
                                                }">{{ post.post_type }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="post.can && (post.can.update || post.can.delete)" class="relative z-10">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button class="p-1.5 rounded-lg text-slate-500 hover:text-white hover:bg-white/10 transition">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden shadow-xl">
                                                <button v-if="post.can.update" @click="openEditModal(post)" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-indigo-600 hover:text-white transition flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                    Modify
                                                </button>
                                                <button v-if="post.can.delete" @click="confirmDeletePost(post.id)" class="w-full text-left px-4 py-2 text-sm text-rose-400 hover:bg-rose-600 hover:text-white transition flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    Purge
                                                </button>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="pl-2 md:pl-14 space-y-4">
                                
                                <!-- Assignment Type -->
                                <AssignmentView
                                    v-if="post.post_type === 'assignment'"
                                    :post="post"
                                    :can-manage-topics="canManageTopics"
                                    :user-submission="userSubmissions[post.id]"
                                />

                                <!-- Quiz Type -->
                                <div v-else-if="post.post_type === 'quiz'" class="p-5 bg-purple-900/10 border border-purple-500/20 rounded-xl relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-2 opacity-10"><svg class="w-24 h-24 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></div>
                                    <h3 class="font-bold text-lg text-purple-400 mb-2 font-exo flex items-center gap-2">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></span> BÀI KIỂM TRA
                                    </h3>
                                    <p class="text-slate-300 text-sm mb-4">{{ post.content || 'Assessment module initialized.' }}</p>

                                    <div v-if="post.can && post.can.update">
                                        <Link :href="route('post.quiz.manage', post.id)" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-lg uppercase tracking-widest transition shadow-[0_0_15px_rgba(147,51,234,0.3)]">
                                            Quản lý câu hỏi
                                        </Link>
                                    </div>
                                    <div v-else>
                                        <div v-if="props.userQuizAttempts[post.id]" class="p-3 bg-purple-500/10 border border-purple-500/30 rounded-lg flex items-center justify-between">
                                            <div>
                                                <span class="block text-[10px] text-purple-300 uppercase tracking-widest">Score Recorded</span>
                                                <span class="text-2xl font-black text-white font-mono">{{ Number(props.userQuizAttempts[post.id].score).toFixed(1) }}<span class="text-xs text-slate-500">/{{ post.max_points || 100 }}</span></span>
                                            </div>
                                            <Link :href="route('quiz.results', props.userQuizAttempts[post.id].id)" class="text-xs text-purple-400 hover:text-white underline">View Analytics</Link>
                                        </div>
                                        <Link v-else :href="route('quiz.start', post.id)" method="post" as="button" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold rounded-lg shadow-lg hover:shadow-purple-500/30 hover:scale-105 transition">
                                            Start Assessment
                                        </Link>
                                    </div>
                                </div>

                                <!-- Standard Text -->
                                <div v-else-if="post.post_type === 'text'" class="text-slate-300 whitespace-pre-wrap leading-relaxed text-sm">
                                    {{ post.content }}
                                </div>

                                <!-- Poll -->
                                <PollDisplay v-else-if="post.post_type === 'poll'" :post="post" :authUserId="authUserId" />

                                <!-- Material -->
                                <div v-else-if="post.post_type === 'material'" class="p-4 bg-cyan-900/10 border border-cyan-500/20 rounded-xl">
                                    <h3 class="font-bold text-sm text-cyan-400 mb-2 font-mono uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        Data Payload
                                    </h3>
                                    <p class="text-slate-300 text-sm mb-3">{{ post.content }}</p>
                                    
                                    <div v-if="post.attachments && post.attachments.length > 0" class="space-y-2">
                                        <div v-for="file in post.attachments" :key="file.id" class="flex items-center justify-between p-2 bg-slate-900 border border-slate-700 rounded hover:border-cyan-500/50 transition group/file">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div class="p-1.5 bg-slate-800 rounded text-cyan-500">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                </div>
                                                <span class="text-xs text-slate-300 truncate font-mono">{{ file.original_name }}</span>
                                            </div>
                                            <a :href="'/storage/' + file.path" target="_blank" class="px-3 py-1 bg-cyan-600/20 text-cyan-400 text-[10px] font-bold uppercase rounded hover:bg-cyan-600 hover:text-white transition">Download</a>
                                        </div>
                                    </div>
                                </div>

                                <CommentSection :post="post" :topic="topic" :authUserId="authUserId" />
                            </div>
                        </div>
                    </article>
                </div>
                
                <div v-else class="text-center py-20 bg-white/5 border border-dashed border-slate-700 rounded-2xl">
                    <div class="inline-block p-4 bg-slate-900 rounded-full mb-4 shadow-lg">
                        <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                    </div>
                    <p class="text-slate-400 font-mono text-sm">No signals detected on this frequency.</p>
                </div>
            </div>
        </div>

        <EditPostModal :show="editingPost !== null" :post="editingPost" @close="closeEditModal" />
    </AppLayout>
</template>