<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { marked } from 'marked';
import axios from 'axios';

const props = defineProps({
    attempt: Object,
    mistakes: Array,
});

const selectedMistake = ref(null);
const aiExplanation = ref('');
const isLoading = ref(false);

const selectMistake = (mistake) => {
    selectedMistake.value = mistake;
    aiExplanation.value = ''; 
};

const askAI = async () => {
    if (!selectedMistake.value) return;

    isLoading.value = true;
    try {
        const response = await axios.post(route('study.analyze-mistake'), {
            question_text: selectedMistake.value.question_text,
            student_answer: selectedMistake.value.student_answer,
            correct_answer: selectedMistake.value.correct_answer,
            subject: selectedMistake.value.subject,
        });
        aiExplanation.value = marked.parse(response.data.explanation);
    } catch (error) {
        aiExplanation.value = "⚠️ SYSTEM ERROR: Connection to AI Core timed out.";
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <AppLayout title="Glitch Log Analysis">
        <div class="flex h-[calc(100vh-80px)] max-w-[1800px] mx-auto p-4 gap-6">
            
            <!-- LEFT: ERROR REGISTRY -->
            <div class="w-1/3 min-w-[350px] flex flex-col bg-[#0f172a] border border-red-500/30 rounded-3xl overflow-hidden shadow-[0_0_30px_rgba(225,29,72,0.1)]">
                <div class="p-6 border-b border-red-500/20 bg-red-950/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-20 animate-pulse">
                        <svg class="w-16 h-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-black text-red-500 text-xl font-exo uppercase tracking-widest relative z-10">Glitch Log</h3>
                    <p class="text-xs text-red-400/60 font-mono mt-1 relative z-10">{{ mistakes.length }} ANOMALIES DETECTED</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    <div v-if="mistakes.length === 0" class="h-full flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-slate-800 rounded-2xl">
                        <div class="text-emerald-500 font-bold font-exo text-lg">SYSTEM CLEAN</div>
                        <p class="text-slate-500 text-xs font-mono mt-2">No errors found in this sector. Good work.</p>
                    </div>

                    <div v-for="(mistake, index) in mistakes" :key="index" 
                        @click="selectMistake(mistake)"
                        class="group cursor-pointer p-4 rounded-xl border transition-all duration-300 relative overflow-hidden"
                        :class="selectedMistake === mistake 
                            ? 'bg-red-900/20 border-red-500 shadow-[inset_0_0_20px_rgba(220,38,38,0.2)]' 
                            : 'bg-slate-900/50 border-slate-800 hover:border-red-500/50 hover:bg-slate-900'">
                        
                        <!-- Glitch line decoration -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300" :class="selectedMistake === mistake ? 'bg-red-500' : 'bg-slate-700 group-hover:bg-red-500/50'"></div>

                        <div class="pl-3">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 bg-slate-950 px-1.5 py-0.5 rounded border border-slate-800">
                                    {{ mistake.subject || 'UNKNOWN_PROTOCOL' }}
                                </span>
                                <span class="text-[9px] font-mono text-red-500">ERR_CODE_{{ index + 1 }}</span>
                            </div>
                            <div class="text-sm font-bold text-slate-200 group-hover:text-white line-clamp-2 font-mono mb-2">
                                {{ mistake.question_text }}
                            </div>
                            <div class="text-[10px] font-mono flex items-center gap-2">
                                <span class="text-red-400">INPUT: {{ mistake.student_answer }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: ANALYSIS CORE -->
            <div class="flex-1 flex flex-col bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative">
                <!-- Grid Overlay -->
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10 pointer-events-none"></div>
                
                <div v-if="!selectedMistake" class="flex-1 flex flex-col items-center justify-center text-slate-600 opacity-60">
                    <div class="w-24 h-24 border border-dashed border-slate-700 rounded-full flex items-center justify-center mb-6 animate-spin-slow">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <p class="font-mono text-sm uppercase tracking-widest">Select an anomaly to initiate analysis.</p>
                </div>

                <div v-else class="flex flex-col h-full relative z-10">
                    <!-- Error Detail Header -->
                    <div class="p-8 border-b border-slate-800 bg-slate-950/50">
                        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">Original Query Data</h4>
                        <p class="text-xl font-medium text-white font-exo leading-relaxed mb-6">
                            {{ selectedMistake.question_text }}
                        </p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-red-900/10 border border-red-500/20 rounded-xl">
                                <span class="block text-[9px] font-bold text-red-500 uppercase tracking-widest mb-1">Your Input (Incorrect)</span>
                                <span class="text-sm font-mono text-red-300">{{ selectedMistake.student_answer }}</span>
                            </div>
                            <div class="p-4 bg-emerald-900/10 border border-emerald-500/20 rounded-xl">
                                <span class="block text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Expected Output (Correct)</span>
                                <span class="text-sm font-mono text-emerald-300">{{ selectedMistake.correct_answer }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- AI Analysis Section -->
                    <div class="flex-1 bg-slate-900 p-8 overflow-y-auto custom-scrollbar flex flex-col">
                        <div v-if="!aiExplanation" class="flex-1 flex items-center justify-center">
                            <button @click="askAI" :disabled="isLoading"
                                class="group relative px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_50%,transparent_75%)] bg-[length:250%_250%,100%_100%] animate-[shimmer_2s_infinite] pointer-events-none" v-if="isLoading"></div>
                                <span class="flex items-center gap-3 relative z-10 uppercase tracking-wider text-sm">
                                    <span v-if="isLoading" class="animate-spin">⚙️</span>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ isLoading ? 'Processing Logic...' : 'Run Diagnostics (AI)' }}
                                </span>
                            </button>
                        </div>

                        <div v-else class="flex-1 space-y-4">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                <h4 class="text-indigo-400 font-bold font-exo uppercase tracking-widest text-sm">Diagnostics Report</h4>
                            </div>
                            <div class="prose prose-invert prose-sm max-w-none font-sans bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50 leading-relaxed shadow-inner" v-html="aiExplanation"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>