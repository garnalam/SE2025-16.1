<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    attempt: Object,
    answeredCount: Number,
    totalQuestions: Number,
});
</script>

<template>
    <AppLayout title="Upload Confirmation">
        <div class="py-12 flex items-center justify-center min-h-[80vh]">
            <div class="max-w-xl w-full mx-auto px-4">
                
                <div class="bg-[#0f172a] border border-amber-500/30 rounded-3xl overflow-hidden shadow-[0_0_50px_rgba(245,158,11,0.15)] relative">
                    
                    <!-- Warning Stripe Header -->
                    <div class="bg-[repeating-linear-gradient(45deg,#451a03,#451a03_10px,#0f172a_10px,#0f172a_20px)] h-4 border-b border-amber-500/50"></div>
                    
                    <div class="p-8 md:p-10 text-center relative z-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/50 mb-6 animate-pulse">
                            <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>

                        <h2 class="text-2xl font-black text-white font-exo uppercase tracking-wide mb-2">Finalize Transmission?</h2>
                        <p class="text-sm text-slate-400 font-mono mb-8">Confirm data upload to central server.</p>

                        <div class="bg-slate-900/80 rounded-xl p-6 border border-slate-700 mb-8">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-slate-500 uppercase tracking-widest font-bold">Progress</span>
                                <span class="text-xl font-bold font-mono" :class="answeredCount < totalQuestions ? 'text-amber-500' : 'text-emerald-500'">
                                    {{ answeredCount }} / {{ totalQuestions }}
                                </span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full transition-all duration-1000" 
                                     :class="answeredCount < totalQuestions ? 'bg-amber-500' : 'bg-emerald-500'"
                                     :style="{ width: (answeredCount / totalQuestions) * 100 + '%' }"></div>
                            </div>
                            <p v-if="answeredCount < totalQuestions" class="text-[10px] text-amber-500 mt-2 font-mono font-bold text-left">
                                >> WARNING: INCOMPLETE DATA PACKETS DETECTED.
                            </p>
                            <p v-else class="text-[10px] text-emerald-500 mt-2 font-mono font-bold text-left">
                                >> ALL SYSTEMS READY.
                            </p>
                        </div>
                        
                        <p class="text-xs text-slate-500 mb-8 max-w-xs mx-auto leading-relaxed">
                            This action is irreversible. Modifying data after transmission is not permitted by protocol.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <Link
                                :href="route('quiz.question.show', { attempt: attempt.id, questionNumber: totalQuestions })"
                                class="px-6 py-3 text-slate-400 bg-slate-800/50 hover:bg-slate-800 hover:text-white rounded-xl border border-slate-700 font-bold uppercase tracking-wider text-xs transition"
                            >
                                Return
                            </Link>
                            
                            <Link
                                :href="route('quiz.finish', attempt.id)"
                                method="post"
                                as="button"
                                class="group relative px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold uppercase tracking-widest text-xs rounded-xl overflow-hidden transition-all shadow-[0_0_20px_rgba(16,185,129,0.4)]"
                            >
                                <span class="relative z-10 flex items-center gap-2">
                                    Transmit Data
                                    <svg class="w-4 h-4 group-hover:translate-y-[-2px] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>