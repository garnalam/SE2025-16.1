<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    attempt: Object,
});

const scorePercent = ((props.attempt.score / props.attempt.post.max_points) * 100).toFixed(0);
const passed = scorePercent >= 50; 
</script>

<template>
    <AppLayout title="Analysis Report">
        <div class="py-12 flex items-center justify-center min-h-[80vh]">
            <div class="max-w-2xl w-full mx-auto px-4">
                
                <div class="relative bg-[#0f172a] border rounded-3xl overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.5)] p-1"
                     :class="passed ? 'border-emerald-500/30 shadow-emerald-500/10' : 'border-rose-500/30 shadow-rose-500/10'">
                    
                    <!-- Inner Container -->
                    <div class="bg-slate-900/90 rounded-[20px] p-8 md:p-12 text-center relative overflow-hidden backdrop-blur-xl">
                        
                        <!-- Dynamic Background -->
                        <div class="absolute inset-0 opacity-20 pointer-events-none">
                            <div class="absolute top-[-50%] left-[-50%] w-[200%] h-[200%] animate-spin-slow"
                                 :class="passed ? 'bg-[conic-gradient(from_0deg,transparent_0deg,#10b981_360deg)]' : 'bg-[conic-gradient(from_0deg,transparent_0deg,#e11d48_360deg)]'">
                            </div>
                        </div>
                        <div class="absolute inset-[1px] bg-slate-900 rounded-[19px]"></div>

                        <!-- Content -->
                        <div class="relative z-10">
                            <div class="mb-6 inline-flex items-center gap-2 px-3 py-1 rounded-full border bg-slate-950/50 backdrop-blur"
                                 :class="passed ? 'border-emerald-500/50 text-emerald-400' : 'border-rose-500/50 text-rose-400'">
                                <span class="w-2 h-2 rounded-full animate-pulse" :class="passed ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-widest">
                                    {{ passed ? 'Mission Successful' : 'Mission Failed' }}
                                </span>
                            </div>

                            <h2 class="text-sm font-mono text-slate-400 uppercase tracking-[0.3em] mb-8">
                                Assessment Report: {{ attempt.post.title }}
                            </h2>
                            
                            <!-- Score Circle -->
                            <div class="relative w-48 h-48 mx-auto mb-10 flex items-center justify-center">
                                <!-- Outer Ring -->
                                <svg class="absolute inset-0 w-full h-full transform -rotate-90">
                                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-800" />
                                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="8" fill="transparent" 
                                            :class="passed ? 'text-emerald-500' : 'text-rose-500'"
                                            :stroke-dasharray="2 * Math.PI * 88"
                                            :stroke-dashoffset="2 * Math.PI * 88 * (1 - scorePercent / 100)"
                                            stroke-linecap="round"
                                            class="transition-all duration-1000 ease-out drop-shadow-[0_0_10px_currentColor]" />
                                </svg>
                                
                                <div class="flex flex-col items-center">
                                    <span class="text-5xl font-black text-white font-exo">{{ Number(attempt.score).toFixed(1) }}</span>
                                    <span class="text-xs text-slate-500 font-mono uppercase tracking-widest mt-1">/ {{ attempt.post.max_points }} PTS</span>
                                </div>
                            </div>

                            <p class="text-4xl font-black mb-10 font-exo tracking-tight"
                               :class="passed ? 'text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400' : 'text-rose-500'">
                                {{ scorePercent }}% <span class="text-lg font-mono text-slate-500 tracking-normal align-middle">EFFICIENCY</span>
                            </p>

                            <div class="flex justify-center gap-4">
                                <Link :href="route('topics.show', attempt.post.topic_id)"
                                      class="group relative inline-flex items-center justify-center px-8 py-3 font-bold text-white transition-all duration-200 bg-slate-800 font-exo rounded-xl hover:bg-slate-700 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 border border-slate-700">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                    Return to Feed
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 10s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>