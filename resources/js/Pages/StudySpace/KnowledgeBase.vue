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
    <AppLayout title="AI Guru Hub">
        <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
                <div>
                    <h2 class="text-4xl font-black text-white tracking-tighter font-exo mb-2">
                        NEURAL <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">HUB</span>
                    </h2>
                    <p class="text-slate-400 font-mono text-sm max-w-lg">
                        Manage your learning sessions. Interact with the AI Guru to summarize documents, ask questions, and expand your knowledge base.
                    </p>
                </div>
                
                <button @click="createNewSession" 
                    class="group relative flex items-center gap-3 bg-slate-800 hover:bg-slate-700 text-white px-8 py-4 rounded-2xl font-bold border border-slate-600 hover:border-purple-500 transition-all duration-300 shadow-[0_0_20px_rgba(0,0,0,0.3)] hover:shadow-[0_0_30px_rgba(168,85,247,0.2)] overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-600/20 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    <span class="text-2xl font-light text-purple-400 relative z-10 group-hover:scale-110 transition">+</span> 
                    <span class="uppercase tracking-widest text-xs relative z-10">Initialize Session</span>
                </button>
            </div>

            <div class="bg-[#0f172a]/80 backdrop-blur-2xl border border-white/5 rounded-[40px] p-8 shadow-2xl min-h-[600px] relative overflow-hidden">
                <!-- Background Decor -->
                <div class="absolute -top-20 -left-20 w-96 h-96 bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-600/10 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="flex items-center gap-4 mb-8 relative z-10">
                    <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent flex-1"></div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[0.3em] font-mono">Active Memory Banks</h3>
                    <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent flex-1"></div>
                </div>
                
                <div v-if="sessions.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <Link v-for="session in sessions" :key="session.id" :href="route('knowledge-base.show', session.id)"
                        class="group block p-6 bg-slate-900 border border-slate-800 rounded-3xl hover:border-purple-500/50 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_10px_40px_-10px_rgba(147,51,234,0.15)] relative overflow-hidden">
                        
                        <!-- Glow -->
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div class="p-3 bg-slate-800 rounded-2xl group-hover:bg-purple-900/20 group-hover:text-purple-400 transition-colors text-slate-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                            </div>
                            <span class="text-[10px] font-mono text-slate-600 bg-slate-950 px-2 py-1 rounded-lg border border-slate-800 group-hover:border-purple-500/30 transition">
                                ID: {{ session.id.toString().padStart(4, '0') }}
                            </span>
                        </div>
                        
                        <h5 class="mb-2 text-lg font-bold text-slate-200 group-hover:text-white truncate font-exo relative z-10">
                            {{ session.title || 'Untitled Session' }}
                        </h5>
                        
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-800 group-hover:border-slate-700 transition relative z-10">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <p class="text-slate-500 text-xs font-mono">
                                Last Active: {{ new Date(session.updated_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </Link>
                </div>

                <div v-else class="flex flex-col items-center justify-center h-96 text-center border-2 border-dashed border-slate-800 rounded-3xl bg-slate-900/30 relative z-10">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(0,0,0,0.5)]">
                        <svg class="w-10 h-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    </div>
                    <h3 class="text-white font-bold font-exo text-lg mb-2">Memory Banks Empty</h3>
                    <p class="text-slate-500 text-sm font-mono max-w-xs mx-auto mb-8">Initialize a new neural link to begin data processing and chat.</p>
                    <button @click="createNewSession" class="text-purple-400 hover:text-white font-bold text-xs uppercase tracking-widest border-b border-purple-500 hover:border-white transition pb-1">
                        Start First Session
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>