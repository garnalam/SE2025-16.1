<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({ sessions: Array });

const form = useForm({});

const createNewSession = () => {
    form.post(route('knowledge-base.create'));
};
</script>

<template>
    <AppLayout title="Knowledge Base">
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight font-exo">
                            TRỢ LÝ <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">AI GURU</span>
                        </h2>
                        <p class="text-slate-400 mt-1">Quản lý các phiên học tập và tra cứu tài liệu</p>
                    </div>
                    
                    <button @click="createNewSession" 
                        class="group flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white px-6 py-3 rounded-xl font-bold shadow-[0_0_20px_rgba(147,51,234,0.3)] transition-all duration-300 transform hover:-translate-y-1">
                        <span class="text-xl font-bold">+</span> 
                        <span>Bắt đầu phiên mới</span>
                    </button>
                </div>

                <div class="bg-slate-900/50 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl min-h-[500px]">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-6 font-mono">Lịch sử trò chuyện</h3>
                    
                    <div v-if="sessions.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link v-for="session in sessions" :key="session.id" :href="route('knowledge-base.show', session.id)"
                            class="group block p-6 bg-slate-800/50 border border-white/5 rounded-2xl hover:bg-slate-800 hover:border-purple-500/50 transition-all duration-300 relative overflow-hidden">
                            
                            <div class="absolute inset-0 bg-purple-600/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            
                            <h5 class="mb-2 text-lg font-bold text-slate-200 group-hover:text-white truncate font-exo relative z-10">
                                {{ session.title }}
                            </h5>
                            <p class="text-slate-500 text-xs font-mono relative z-10">
                                {{ new Date(session.created_at).toLocaleString('vi-VN') }}
                            </p>
                            
                            <div class="absolute bottom-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                                <svg class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-slate-700/50 rounded-2xl bg-slate-800/20">
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4 ring-1 ring-white/10">
                            <span class="text-3xl">🤖</span>
                        </div>
                        <h3 class="text-slate-300 font-bold mb-2">Chưa có cuộc trò chuyện nào</h3>
                        <p class="text-slate-500 text-sm max-w-xs mx-auto mb-6">Hãy khởi tạo một phiên làm việc mới để bắt đầu hỏi đáp hoặc tóm tắt tài liệu.</p>
                        <button @click="createNewSession" class="text-purple-400 hover:text-purple-300 font-bold text-sm hover:underline">
                            + Tạo ngay bây giờ
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>