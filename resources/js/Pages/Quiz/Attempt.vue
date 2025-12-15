<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    attempt: Object,
    question: Object,
    questionNumber: Number,
    totalQuestions: Number,
    previousAnswerOptionId: Number,
});

const isProctored = computed(() => props.attempt.post.is_proctored == 1);

// --- LOGIC LÀM BÀI ---
const form = useForm({
    option_id: props.previousAnswerOptionId,
});

function saveAndNext() {
    form.post(route('quiz.question.save', { 
        attempt: props.attempt.id, 
        questionNumber: props.questionNumber 
    }), { preserveScroll: true });
}

// --- LOGIC CHỐNG GIAN LẬN ---
const isFullscreen = ref(false);
const isQuizStarted = ref(false); 

const enterFullscreen = () => {
    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen()
            .then(() => {
                isFullscreen.value = true;
                setTimeout(() => {
                    isQuizStarted.value = true; 
                }, 100);
            })
            .catch(err => {
                console.error("Lỗi fullscreen:", err);
                Swal.fire({
                    title: 'System Error',
                    text: 'Fullscreen initialization failed.',
                    icon: 'error',
                    background: '#0f172a',
                    color: '#fff'
                });
            });
    }
};

const handleViolation = async (type) => {
    if (!isQuizStarted.value) return; 

    try {
        const response = await axios.post(route('quiz.log-violation', props.attempt.id), { type });
        
        if (response.data.status === 'terminated') {
            await Swal.fire({
                icon: 'error',
                title: 'SESSION TERMINATED',
                text: response.data.message,
                allowOutsideClick: false,
                confirmButtonText: 'View Report',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#e11d48'
            });
            window.location.href = response.data.redirect_url;
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'SECURITY ALERT',
                text: `Focus lost. Violation detected: ${response.data.violation_count}/3`,
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false,
                background: '#0f172a',
                color: '#fbbf24'
            });
        }
    } catch (error) {
        console.error(error);
    }
};

const onVisibilityChange = () => { 
    if (document.hidden) handleViolation('tab_switch'); 
};

const onFullscreenChange = () => {
    if (!document.fullscreenElement) {
        isFullscreen.value = false;
        if (isQuizStarted.value) {
            handleViolation('exit_fullscreen');
        }
    } else {
        isFullscreen.value = true;
    }
};

onMounted(() => {
    if (isProctored.value) {
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('visibilitychange', onVisibilityChange);
        document.addEventListener('fullscreenchange', onFullscreenChange);

        Swal.fire({
            title: 'SECURE EXAM ENVIRONMENT',
            text: "Fullscreen mode required. 3 violations will result in automatic termination.",
            icon: 'info',
            confirmButtonText: 'INITIALIZE',
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#0ea5e9'
        }).then((result) => {
            if (result.isConfirmed) {
                enterFullscreen();
            }
        });
    }
});

onUnmounted(() => {
    if (isProctored.value) {
        document.removeEventListener('visibilitychange', onVisibilityChange);
        document.removeEventListener('fullscreenchange', onFullscreenChange);
    }
});
</script>

<template>
    <AppLayout :title="'Question ' + questionNumber" :class="{ 'select-none': isProctored }">
        
        <!-- Fullscreen Warning Overlay -->
        <div v-if="isProctored && isQuizStarted && !isFullscreen" class="fixed inset-0 bg-[#020617] z-[9999] flex flex-col items-center justify-center text-white text-center p-4">
            <div class="relative mb-8">
                <div class="absolute inset-0 bg-red-500 blur-3xl opacity-20 animate-pulse"></div>
                <svg class="w-32 h-32 text-red-500 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h2 class="text-4xl font-black font-exo mb-4 text-red-500 tracking-widest uppercase">Security Breach</h2>
            <p class="mb-8 text-xl font-mono text-slate-400">Fullscreen mode disengaged. Return immediately.</p>
            <button @click="enterFullscreen" class="px-10 py-4 bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl shadow-[0_0_30px_rgba(220,38,38,0.5)] transition uppercase tracking-widest text-lg font-exo">
                Re-Engage System
            </button>
        </div>

        <div class="py-12 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0f172a] border border-indigo-500/30 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.5)] relative overflow-hidden flex flex-col min-h-[600px]">
                
                <!-- HUD Header -->
                <div class="px-8 py-6 bg-slate-900/80 border-b border-indigo-500/20 flex flex-col md:flex-row justify-between items-center relative z-10 backdrop-blur-md">
                    <div>
                        <h2 class="text-2xl font-black text-white font-exo uppercase tracking-wide">
                            Sequence <span class="text-cyan-400">{{ String(questionNumber).padStart(2, '0') }}</span> / {{ String(totalQuestions).padStart(2, '0') }}
                        </h2>
                        <div class="h-1 w-full bg-slate-800 mt-2 rounded-full overflow-hidden">
                            <div class="h-full bg-cyan-500 shadow-[0_0_10px_cyan]" :style="{ width: (questionNumber / totalQuestions) * 100 + '%' }"></div>
                        </div>
                    </div>
                    
                    <div v-if="isProctored" class="mt-4 md:mt-0 flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-[10px] text-slate-500 font-mono uppercase tracking-widest">Security Status</div>
                            <div class="text-xs font-bold font-mono" :class="attempt.violation_count > 0 ? 'text-red-500' : 'text-emerald-500'">
                                {{ attempt.violation_count > 0 ? 'COMPROMISED' : 'SECURE' }}
                            </div>
                        </div>
                        <div class="px-3 py-1.5 rounded border bg-slate-950 font-mono text-sm font-bold" 
                             :class="attempt.violation_count > 0 ? 'border-red-500/50 text-red-500' : 'border-emerald-500/50 text-emerald-500'">
                            Errors: {{ attempt.violation_count }}/3
                        </div>
                    </div>
                </div>
                
                <form @submit.prevent="saveAndNext" class="flex-1 flex flex-col relative z-10">
                    <div class="p-8 flex-1 overflow-y-auto">
                        <!-- Question Text -->
                        <div class="mb-8">
                            <p class="text-xl md:text-2xl font-bold text-slate-200 font-exo leading-relaxed">
                                {{ question.question_text }}
                            </p>
                            <div v-if="question.image_path" class="mt-6 rounded-xl overflow-hidden border border-slate-700 bg-black/50 inline-block max-w-full">
                                <img :src="'/storage/' + question.image_path" class="max-h-[400px] object-contain">
                            </div>
                        </div>

                        <!-- Options Grid -->
                        <div class="grid grid-cols-1 gap-4">
                            <label v-for="option in question.options" :key="option.id" 
                                class="relative group cursor-pointer"
                            >
                                <input type="radio" :value="option.id" v-model="form.option_id" class="peer sr-only">
                                
                                <div class="flex items-center p-5 rounded-xl border-2 transition-all duration-300 relative overflow-hidden"
                                    :class="form.option_id === option.id 
                                        ? 'bg-indigo-600/20 border-cyan-500 shadow-[0_0_20px_rgba(6,182,212,0.2)]' 
                                        : 'bg-slate-900 border-slate-700 hover:border-slate-500 hover:bg-slate-800'">
                                    
                                    <!-- Hexagon Marker -->
                                    <div class="w-8 h-8 flex items-center justify-center mr-4 transition-all duration-300"
                                         :class="form.option_id === option.id ? 'text-cyan-400 scale-110' : 'text-slate-600'">
                                        <svg viewBox="0 0 24 24" fill="none" class="w-full h-full" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2l8.66 5v10L12 22 3.34 17V7L12 2z" />
                                            <circle v-if="form.option_id === option.id" cx="12" cy="12" r="4" fill="currentColor" />
                                        </svg>
                                    </div>

                                    <span class="text-base font-medium font-sans transition-colors duration-300"
                                          :class="form.option_id === option.id ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'">
                                        {{ option.option_text }}
                                    </span>

                                    <!-- Active Scanline -->
                                    <div v-if="form.option_id === option.id" class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-transparent pointer-events-none"></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-6 bg-slate-900/80 border-t border-white/5 flex justify-between items-center backdrop-blur-md">
                        <a v-if="questionNumber > 1" 
                           :href="route('quiz.question.show', { attempt: attempt.id, questionNumber: questionNumber - 1 })" 
                           class="flex items-center gap-2 text-slate-500 hover:text-cyan-400 transition uppercase font-bold text-xs tracking-widest group">
                            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            Previous Node
                        </a>
                        <div v-else></div>

                        <PrimaryButton :disabled="form.processing || !form.option_id" class="!px-8 !py-3 !text-sm !bg-gradient-to-r !from-cyan-600 !to-blue-600 hover:!from-cyan-500 hover:!to-blue-500">
                            {{ questionNumber < totalQuestions ? 'Next Sequence' : 'Finalize Data' }}
                            <svg v-if="questionNumber < totalQuestions" class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Background Detail -->
                <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-indigo-900/10 to-transparent pointer-events-none"></div>
            </div>
        </div>
    </AppLayout>
</template>