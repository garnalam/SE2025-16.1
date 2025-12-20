<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { marked } from 'marked';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({ total_sessions: 0, mistakes_count: 0, win_rate: 0, recent_history: [] })
    },
    // [NEW] Nhận thông tin Lớp học hiện tại
    current_team: {
        type: Object,
        default: null
    }
});

const gameState = ref('lobby'); 
const currentSessionId = ref(null);
const questions = ref([]);
const currentIndex = ref(0);
const currentQuestion = computed(() => questions.value[currentIndex.value]);
const selectedOptionId = ref(null); 
const correctOptionId = ref(null); 
const isSubmitting = ref(false);    
const sessionResult = ref(null);

const getRankTheme = (rank) => {
    const map = { 'S': 'text-yellow-400 border-yellow-400', 'A': 'text-purple-400 border-purple-400', 'B': 'text-cyan-400 border-cyan-400', 'F': 'text-slate-500 border-slate-700' };
    return map[rank] || map['F'];
};

const calculateHistoryPercentage = (scoreStr) => {
    try {
        if (!scoreStr) return 0;
        const [correct, total] = scoreStr.split('/').map(Number);
        return (!total || total === 0) ? 0 : (correct / total) * 100;
    } catch (e) { return 0; }
};

const startGame = async (mode) => {
    gameState.value = 'loading';
    try {
        const response = await axios.post(route('gym.start'), { 
            mode: mode,
            // [FIX] Gửi team_id khi bắt đầu
            team_id: props.current_team?.id 
        });
        
        questions.value = response.data.questions;
        currentSessionId.value = response.data.session_id;
        currentIndex.value = 0;
        resetRound();
        gameState.value = 'playing';
    } catch (error) {
        if (error.response?.status === 404) {
            alert(error.response.data.message);
        } else {
            alert('Lỗi hệ thống: ' + (error.response?.data?.message || 'Không thể kết nối.'));
        }
        gameState.value = 'lobby';
    }
};

const selectOption = async (optionId) => {
    if (isSubmitting.value || selectedOptionId.value) return; 
    
    correctOptionId.value = null; 
    selectedOptionId.value = optionId;
    isSubmitting.value = true;

    try {
        const response = await axios.post(route('gym.submit'), {
            session_id: currentSessionId.value,
            question_id: currentQuestion.value.id,
            selected_option_id: optionId,
            time_taken: 5 
        });

        correctOptionId.value = response.data.correct_option_id;

        setTimeout(() => {
            if (currentIndex.value < questions.value.length - 1) {
                currentIndex.value++;
                resetRound();
            } else {
                finishGame();
            }
        }, 1500);
    } catch (error) {
        alert("Lỗi kết nối.");
        isSubmitting.value = false;
        selectedOptionId.value = null;
    }
};

const resetRound = () => {
    selectedOptionId.value = null;
    correctOptionId.value = null;
    isSubmitting.value = false;
};

const finishGame = async () => {
    gameState.value = 'loading';
    try {
        const response = await axios.post(route('gym.finish'), { session_id: currentSessionId.value });
        sessionResult.value = response.data;
        gameState.value = 'summary';
    } catch (error) {
        gameState.value = 'lobby';
    }
};
</script>

<template>
    <AppLayout title="Simulation Gym">
        <div class="max-w-[1400px] mx-auto py-6 px-4 sm:px-6 lg:px-8 min-h-screen text-slate-300">
            
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-6">
                <div>
                    <h1 class="text-4xl font-black text-white font-exo tracking-tighter uppercase flex items-center gap-4">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-500 to-indigo-600">Simulation</span> Gym
                    </h1>
                    
                    <div class="mt-2 flex items-center gap-2">
                        <p class="text-slate-500 font-mono text-xs uppercase tracking-[0.2em]">> Khu vực ôn tập của học sinh</p>
                        <span v-if="current_team" class="bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded text-[10px] font-bold border border-indigo-500/30 font-exo uppercase">
                            TEAM: {{ current_team.name }}
                        </span>
                        <span v-else class="bg-slate-700/50 text-slate-400 px-2 py-0.5 rounded text-[10px] font-bold border border-slate-600 font-exo uppercase">
                            GLOBAL ACCESS
                        </span>
                    </div>
                </div>
                
                <div v-if="gameState === 'playing'" class="font-mono text-cyan-400 text-lg font-bold">
                    Q: {{ currentIndex + 1 }} / {{ questions.length }}
                </div>
                 <div v-else class="grid grid-cols-3 gap-1 bg-slate-900/50 border border-slate-800 p-1 rounded-2xl backdrop-blur-xl">
                    <div class="px-5 py-3 text-center border-r border-slate-800">
                        <div class="text-[9px] text-slate-500 font-black uppercase font-mono tracking-widest mb-1">Sessions</div>
                        <div class="text-lg font-black text-white font-exo">{{ stats.total_sessions }}</div>
                    </div>
                    <div class="px-5 py-3 text-center border-r border-slate-800">
                        <div class="text-[9px] text-slate-500 font-black uppercase font-mono tracking-widest mb-1">Mistakes</div>
                        <div class="text-lg font-black text-rose-500 font-exo">{{ stats.mistakes_count }}</div>
                    </div>
                    <div class="px-5 py-3 text-center">
                        <div class="text-[9px] text-slate-500 font-black uppercase font-mono tracking-widest mb-1">Win Rate</div>
                        <div class="text-lg font-black text-emerald-400 font-exo">{{ stats.win_rate }}%</div>
                    </div>
                </div>
            </div>

            <div v-if="gameState === 'lobby'" class="animate-fade-in-up">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div @click="startGame('quick')" class="group relative bg-[#0f172a] border border-slate-800 hover:border-cyan-500/50 rounded-[2rem] p-8 cursor-pointer transition-all duration-300 hover:-translate-y-2 shadow-2xl">
                        <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 text-cyan-400 group-hover:bg-cyan-500 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white font-exo mb-2 uppercase">Quick Drill</h3>
                        <p class="text-sm text-slate-400 mb-6">Luyện tập 10 câu ngẫu nhiên <span v-if="current_team">từ lớp <span class="text-cyan-400 font-bold">{{ current_team.name }}</span></span>.</p>
                    </div>

                    <div @click="startGame('mistake')" class="group relative bg-[#0f172a] border border-slate-800 hover:border-rose-500/50 rounded-[2rem] p-8 cursor-pointer transition-all duration-300 hover:-translate-y-2 shadow-2xl">
                        <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 text-rose-500 group-hover:bg-rose-500 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white font-exo mb-2 uppercase">Shadow Boxing</h3>
                        <p class="text-sm text-slate-400 mb-6">Ôn lại các lỗi sai <span v-if="current_team">trong lớp <span class="text-rose-400 font-bold">{{ current_team.name }}</span></span>.</p>
                    </div>

                    <div @click="startGame('survival')" class="group relative bg-[#0f172a] border border-slate-800 hover:border-purple-500/50 rounded-[2rem] p-8 cursor-pointer transition-all duration-300 hover:-translate-y-2 shadow-2xl">
                        <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-all">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0 1 18 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white font-exo mb-2 uppercase">Survival Mode</h3>
                        <p class="text-sm text-slate-400 mb-6">Chế độ sinh tồn. 30 câu hỏi liên tục.</p>
                    </div>
                </div>

                <div class="bg-slate-900/30 border border-slate-800 rounded-[2.5rem] p-8 shadow-inner overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-[100px]"></div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[0.3em] font-mono mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Lịch sử luyện tập
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-mono text-slate-500 uppercase tracking-widest border-b border-slate-800/50">
                                    <th class="px-6 py-4">Mode</th>
                                    <th class="px-6 py-4">Score</th>
                                    <th class="px-6 py-4">XP</th>
                                    <th class="px-6 py-4 text-right">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/30">
                                <tr v-for="(log, index) in stats.recent_history" :key="index" class="group hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-300 uppercase">{{ log.mode }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 h-1 bg-slate-800 rounded-full overflow-hidden">
                                                <div class="h-full bg-cyan-500 shadow-[0_0_10px_cyan]" :style="{ width: calculateHistoryPercentage(log.score) + '%' }"></div>
                                            </div>
                                            <span class="font-mono text-xs text-cyan-400 font-bold">{{ log.score }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-purple-400">+{{ log.xp }} XP</td>
                                    <td class="px-6 py-4 text-right text-slate-600 font-mono text-[10px]">{{ log.date }}</td>
                                </tr>
                                <tr v-if="stats.recent_history.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-600 italic font-mono text-xs">>> NO DATA.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="gameState === 'loading'" class="flex flex-col items-center justify-center py-32">
                <div class="w-16 h-16 border-4 border-slate-800 border-t-cyan-500 rounded-full animate-spin mb-6"></div>
                <div class="text-cyan-400 font-mono text-sm tracking-widest animate-pulse">
                    SYNCING DATA <span v-if="current_team">FOR {{ current_team.name }}</span>...
                </div>
            </div>

            <div v-if="gameState === 'playing'" class="max-w-4xl mx-auto animate-fade-in">
                <div class="w-full h-1 bg-slate-800 rounded-full mb-8 overflow-hidden">
                    <div class="h-full bg-cyan-500 transition-all duration-300" :style="{ width: ((currentIndex + 1) / questions.length) * 100 + '%' }"></div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] p-10 shadow-2xl relative overflow-hidden">
                    <div class="mb-10 relative z-10">
                        <span class="text-[10px] font-mono font-bold text-cyan-500 uppercase tracking-[0.3em] block mb-4">Question Data:</span>
                        <h2 class="text-2xl md:text-3xl font-bold text-white font-exo leading-tight">{{ currentQuestion.question_text }}</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 relative z-10">
                        <button v-for="opt in currentQuestion.options" :key="opt.id"
                            @click="selectOption(opt.id)"
                            :disabled="isSubmitting"
                            class="group relative w-full text-left p-6 rounded-2xl border-2 transition-all duration-200 flex items-center gap-5 overflow-hidden"
                            :class="{
                                'bg-slate-950/50 border-slate-800 hover:border-cyan-500/50 hover:bg-slate-900': !selectedOptionId,
                                'bg-cyan-500/20 border-cyan-500 text-white': selectedOptionId === opt.id && correctOptionId === null,
                                'bg-emerald-500/20 border-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.2)]': selectedOptionId === opt.id && correctOptionId === opt.id,
                                'bg-rose-500/20 border-rose-500 text-white': selectedOptionId === opt.id && correctOptionId !== null && correctOptionId !== opt.id,
                                'bg-emerald-500/10 border-emerald-500/50 text-emerald-300': selectedOptionId && selectedOptionId !== opt.id && correctOptionId === opt.id,
                                'opacity-40 grayscale': selectedOptionId && selectedOptionId !== opt.id && correctOptionId !== opt.id
                            }"
                        >
                            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-950 font-black font-mono border border-slate-700 text-slate-500">
                                {{ opt.id }}
                            </div>
                            <span class="flex-1 font-bold text-lg" :class="!selectedOptionId ? 'text-slate-300 group-hover:text-white' : ''">
                                {{ opt.text }}
                            </span>
                            
                            <div v-if="selectedOptionId === opt.id">
                                <span v-if="correctOptionId === null" class="text-cyan-400 animate-spin text-xl">⏳</span>
                                <span v-else-if="correctOptionId === opt.id" class="text-emerald-400 text-2xl">✓</span>
                                <span v-else class="text-rose-400 text-2xl">✕</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="gameState === 'summary' && sessionResult" class="flex flex-col items-center justify-center py-10 animate-fade-in-up">
                <div class="bg-[#0f172a] border border-slate-800 rounded-[3rem] p-10 max-w-2xl w-full text-center relative overflow-hidden">
                    <div class="inline-block relative mb-8">
                        <div class="w-40 h-40 rounded-full border-4 flex items-center justify-center text-7xl font-black font-exo bg-slate-900" :class="getRankTheme(sessionResult.summary.rank)">
                            {{ sessionResult.summary.rank }}
                        </div>
                    </div>
                    <h2 class="text-3xl font-black text-white mb-2 font-exo uppercase">Hoàn thành</h2>
                    <div class="text-slate-400 font-mono text-sm mb-8">
                        Kết quả: <span class="text-white font-bold">{{ sessionResult.summary.correct }}/{{ sessionResult.summary.total }}</span> câu
                        <span class="mx-2">|</span> XP: <span class="text-purple-400 font-bold">+{{ sessionResult.summary.xp_earned }}</span>
                    </div>

                    <div class="bg-indigo-900/20 border border-indigo-500/30 rounded-2xl p-6 text-left mb-8">
                        <div class="flex gap-4">
                            <div class="text-3xl">🤖</div>
                            <div>
                                <h3 class="text-indigo-300 font-bold text-xs uppercase tracking-widest mb-2 font-mono">Góc nhìn AI Coach</h3>
                                <div class="prose prose-invert prose-sm text-slate-200" v-html="marked.parse(sessionResult.ai_analysis)"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 mb-8 max-h-64 overflow-y-auto custom-scrollbar text-left pr-2">
                         <div v-for="(item, idx) in sessionResult.details" :key="idx" class="p-4 rounded-xl border flex items-center justify-between" :class="item.is_correct ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-rose-500/5 border-rose-500/20'">
                            <div>
                                <p class="text-sm font-bold text-white mb-1">{{ item.question_text }}</p>
                                <div class="text-xs font-mono"><span class="text-slate-500">Đáp án đúng: </span><span class="text-emerald-400">{{ item.correct_answer }}</span></div>
                            </div>
                            <span class="text-xs font-bold px-2 py-1 rounded" :class="item.is_correct ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">{{ item.is_correct ? 'ĐÚNG' : 'SAI' }}</span>
                         </div>
                    </div>

                    <div class="flex gap-4 justify-center">
                        <button @click="gameState = 'lobby'" class="px-8 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl">Thoát</button>
                        <button @click="startGame('quick')" class="px-8 py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg">Luyện tập lại</button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.font-exo { font-family: 'Exo 2', sans-serif; }
.animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>