<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    flashcardSet: Object
});

const cards = ref([]);
const currentIndex = ref(0);
const isFlipped = ref(false);
const isLoading = ref(false);

const loadCards = async () => {
    if (!props.flashcardSet) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(route('memory-shards.flashcards.get', { setId: props.flashcardSet.id }));
        cards.value = response.data;
        currentIndex.value = 0;
        isFlipped.value = false;
    } catch (error) {
        console.error("Translink Error:", error);
    } finally {
        isLoading.value = false;
    }
};

const shuffleCards = () => {
    let array = [...cards.value];
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    cards.value = array;
    currentIndex.value = 0;
    isFlipped.value = false;
};

watch(() => props.flashcardSet, loadCards, { immediate: true });

const currentCard = computed(() => cards.value[currentIndex.value]);

const nextCard = () => {
    isFlipped.value = false;
    setTimeout(() => {
        if (currentIndex.value < cards.value.length - 1) {
            currentIndex.value++;
        } else {
            currentIndex.value = 0;
        }
    }, 150);
};

const prevCard = () => {
    isFlipped.value = false;
    setTimeout(() => {
        if (currentIndex.value > 0) currentIndex.value--;
    }, 150);
};
</script>

<template>
    <div class="h-full flex flex-col bg-[#020617] w-full relative overflow-y-auto custom-scrollbar font-sans p-4 md:p-6">
        
        <!-- Ambient Background FX -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-1/4 -right-1/4 w-[600px] h-[600px] bg-cyan-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-1/4 -left-1/4 w-[600px] h-[600px] bg-purple-500/5 rounded-full blur-[120px]"></div>
        </div>

        <!-- TACTICAL OVERLAY BUTTONS -->
        <div class="sticky top-0 right-0 flex justify-end gap-3 z-50 mb-4">
            <button @click="shuffleCards" class="group px-4 py-2 bg-slate-900/80 hover:bg-cyan-600/20 border border-white/10 hover:border-cyan-500/50 rounded-full transition-all duration-300 flex items-center gap-2 backdrop-blur-xl shadow-xl scale-90 md:scale-100">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-cyan-400 transition-colors">Xáo bộ thẻ</span>
                <svg class="w-3 h-3 text-cyan-500 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </button>
            <button @click="loadCards" class="group px-4 py-2 bg-slate-900/80 hover:bg-emerald-600/20 border border-white/10 hover:border-emerald-500/50 rounded-full transition-all duration-300 flex items-center gap-2 backdrop-blur-xl shadow-xl scale-90 md:scale-100">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 group-hover:text-emerald-400 transition-colors">Tải lại bộ thẻ</span>
                <svg class="w-3 h-3 text-emerald-500 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </button>
        </div>

        <!-- LOADING & EMPTY STATES -->
        <div v-if="isLoading" class="flex-1 flex flex-col items-center justify-center gap-6">
            <div class="relative w-20 h-20 flex items-center justify-center">
                <div class="absolute inset-0 border-4 border-cyan-500/10 rounded-full"></div>
                <div class="absolute inset-0 border-t-4 border-cyan-500 rounded-full animate-spin"></div>
            </div>
        </div>

        <div v-else-if="cards.length === 0" class="flex-1 flex flex-col items-center justify-center text-center py-20">
            <div class="w-16 h-16 bg-slate-900 border border-dashed border-slate-700 rounded-full flex items-center justify-center mb-6 mx-auto">
                <span class="text-2xl opacity-20">📂</span>
            </div>
            <h3 class="text-lg font-black text-white uppercase tracking-widest mb-1 font-exo">Registry Empty</h3>
            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-tighter">No data nodes initialized.</p>
        </div>

        <!-- MAIN VIEWER CONTENT -->
        <div v-else class="flex-1 w-full max-w-4xl mx-auto flex flex-col items-center justify-center animate-fade-in-up pb-6">
            
            <!-- TOP CARD META -->
            <div class="w-full max-w-[550px] flex justify-between items-end mb-6 px-2">
                <div class="space-y-1">
                    <span class="block text-[9px] font-black text-cyan-500 uppercase tracking-widest font-mono">{{ props.flashcardSet.title }}</span>
                    <!-- <span class="block text-[7px] text-slate-600 font-mono uppercase tracking-[0.2em]">Protocol v6.1_Stable</span> -->
                </div>
                <div class="px-3 py-1 bg-slate-900 border border-white/5 rounded-full shadow-lg">
                    <span class="text-[9px] font-black font-mono text-slate-500">
                        <span class="text-white">{{ currentIndex + 1 }}</span> <span class="mx-0.5 opacity-20">/</span> {{ cards.length }}
                    </span>
                </div>
            </div>

            <!-- THE CORE CARD CONTAINER (Flexible Height) -->
            <div 
                class="w-full max-w-[600px] h-[35vh] min-h-[300px] max-h-[450px] cursor-pointer perspective-2000 group relative mb-8"
                @click="isFlipped = !isFlipped"
            >
                <div class="absolute inset-x-10 inset-y-10 bg-cyan-500/10 blur-[80px] opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                <div 
                    class="relative w-full h-full duration-[800ms] preserve-3d transition-transform ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                    :class="{ 'rotate-y-180': isFlipped }"
                >
                    <!-- FRONT: Query Side -->
                    <div class="absolute inset-0 backface-hidden bg-[#0a0f1d] border border-white/10 rounded-[2rem] shadow-2xl overflow-hidden flex flex-col ring-1 ring-white/5">
                        <div class="absolute top-4 left-8 flex items-center gap-2 opacity-30">
                            <div class="w-1 h-1 rounded-full bg-cyan-500"></div>
                            <span class="text-[8px] font-black text-cyan-500 uppercase tracking-[0.4em] font-mono">Input_Phase</span>
                        </div>

                        <div class="flex-1 flex flex-col items-center justify-center px-8 py-10 text-center overflow-hidden">
                            <div class="w-full max-h-full overflow-y-auto custom-scrollbar-minimal px-2">
                                <h3 class="text-xl md:text-2xl font-black text-white font-exo leading-tight tracking-tight whitespace-pre-line group-hover:text-cyan-100 transition-colors">
                                    {{ currentCard.front_content }}
                                </h3>
                            </div>
                        </div>

                        <div class="h-10 bg-white/5 border-t border-white/5 flex items-center justify-center shrink-0">
                            <span class="text-[7px] font-black text-slate-500 uppercase tracking-[0.5em] animate-pulse">Chạm vào thẻ để lật</span>
                        </div>
                    </div>

                    <!-- BACK: Result Side -->
                    <div class="absolute inset-0 backface-hidden rotate-y-180 bg-[#0d1626] border-2 border-cyan-500/30 rounded-[2rem] shadow-[0_0_50px_rgba(6,182,212,0.15)] overflow-hidden flex flex-col">
                        <div class="absolute top-4 left-8 flex items-center gap-2">
                            <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]"></div>
                            <span class="text-[8px] font-black text-emerald-400 uppercase tracking-[0.4em] font-mono">Resolution_Active</span>
                        </div>

                        <div class="flex-1 flex flex-col items-center justify-center px-8 py-10 text-center bg-[radial-gradient(circle_at_center,rgba(6,182,212,0.05)_0%,transparent_100%)] overflow-hidden">
                            <div class="w-full max-h-full overflow-y-auto custom-scrollbar-minimal px-2">
                                <h3 class="text-lg md:text-xl text-cyan-50 font-bold leading-relaxed whitespace-pre-line font-exo tracking-tight">
                                    {{ currentCard.back_content }}
                                </h3>
                            </div>
                        </div>

                        <div class="h-10 bg-cyan-500/10 border-t border-cyan-500/20 flex items-center justify-center shrink-0">
                            <span class="text-[7px] font-black text-emerald-400 uppercase tracking-[0.5em] font-mono">Đã lật</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPACT NAVIGATION CONTROLS -->
            <div class="flex items-center gap-6 md:gap-10">
                <button @click="prevCard" :disabled="currentIndex === 0" 
                    class="w-12 h-12 rounded-full bg-slate-900 border border-white/5 flex items-center justify-center text-slate-500 hover:text-cyan-400 hover:border-cyan-500/50 transition-all duration-300 disabled:opacity-5 shadow-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                </button>
                
                <button @click="isFlipped = !isFlipped" 
                    class="group relative px-8 h-12 rounded-full bg-white text-black font-black uppercase tracking-[0.3em] text-[9px] transition-all duration-300 hover:scale-105 active:scale-95 shadow-[0_10px_30px_rgba(255,255,255,0.2)] flex items-center gap-3 overflow-hidden">
                    <div class="absolute inset-0 bg-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <span class="relative z-10 flex items-center gap-2 group-hover:text-black">
                        <svg class="w-3.5 h-3.5 group-hover:rotate-90 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        Flip
                    </span>
                </button>

                <button @click="nextCard" 
                    class="w-12 h-12 rounded-full bg-slate-900 border border-white/5 flex items-center justify-center text-slate-500 hover:text-cyan-400 hover:border-cyan-500/50 transition-all duration-300 shadow-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
            
            <!-- PROGRESS DOTS -->
            <div class="mt-8 w-full max-w-[300px] flex gap-1.5 justify-center opacity-50">
                <div v-for="n in Math.min(cards.length, 15)" :key="n" 
                    class="h-0.5 flex-1 rounded-full transition-all duration-500"
                    :class="n-1 <= currentIndex ? 'bg-cyan-500' : 'bg-slate-800'"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-2000 { perspective: 2000px; }
.preserve-3d { transform-style: preserve-3d; }
.backface-hidden { backface-visibility: hidden; }
.rotate-y-180 { transform: rotateY(180deg); }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }

.custom-scrollbar-minimal::-webkit-scrollbar { width: 3px; }
.custom-scrollbar-minimal::-webkit-scrollbar-thumb { background: rgba(34, 211, 238, 0.2); border-radius: 10px; }

.animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>