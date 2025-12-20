<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
<<<<<<< Updated upstream
import { ref, onMounted, onUnmounted } from 'vue';
=======
import MathRender from '@/Components/MathRender.vue'; // <--- Đã import MathRender
import { ref, onMounted, onUnmounted, computed } from 'vue';
>>>>>>> Stashed changes
import axios from 'axios';
import Swal from 'sweetalert2';
import { computed } from 'vue'; // Import thêm computed
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
const isQuizStarted = ref(false); // <--- BIẾN MỚI: Đánh dấu đã chính thức làm bài chưa

const enterFullscreen = () => {
    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen()
            .then(() => {
                isFullscreen.value = true;
                // Chỉ khi vào fullscreen thành công mới tính là bắt đầu làm bài
                // Thêm delay nhỏ 100ms để trình duyệt ổn định trạng thái
                setTimeout(() => {
                    isQuizStarted.value = true; 
                }, 100);
            })
            .catch(err => {
                console.error("Lỗi fullscreen:", err);
<<<<<<< Updated upstream
                Swal.fire('Lỗi', 'Không thể vào chế độ toàn màn hình. Hãy thử lại!', 'error');
=======
>>>>>>> Stashed changes
            });
    }
};

const handleViolation = async (type) => {
    // QUAN TRỌNG: Nếu chưa bắt đầu làm bài thì không tính lỗi
    if (!isQuizStarted.value) return; 

    try {
        const response = await axios.post(route('quiz.log-violation', props.attempt.id), { type });
        
        if (response.data.status === 'terminated') {
            await Swal.fire({
                icon: 'error',
                title: 'ĐÌNH CHỈ THI!',
                text: response.data.message,
                allowOutsideClick: false,
                confirmButtonText: 'Xem kết quả'
            });
            window.location.href = response.data.redirect_url;
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'CẢNH BÁO!',
                text: `Phát hiện rời màn hình. Vi phạm: ${response.data.violation_count}/3`,
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
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
    // Nếu đang trong trạng thái làm bài mà bị mất fullscreen -> Báo lỗi
    if (!document.fullscreenElement) {
        isFullscreen.value = false;
        if (isQuizStarted.value) {
            handleViolation('exit_fullscreen');
        }
    } else {
        // Nếu quay lại fullscreen -> Cập nhật trạng thái
        isFullscreen.value = true;
    }
};

onMounted(() => {
    if (isProctored.value) {
    // Chặn chuột phải, copy paste
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('copy', e => e.preventDefault());
    
    // Lắng nghe sự kiện
    document.addEventListener('visibilitychange', onVisibilityChange);
    document.addEventListener('fullscreenchange', onFullscreenChange);

    // Modal bắt buộc fullscreen
    Swal.fire({
        title: 'Bắt đầu làm bài',
        text: "Hệ thống yêu cầu chế độ toàn màn hình. Vi phạm 3 lần sẽ tự động nộp bài.",
        icon: 'info',
        confirmButtonText: 'Bắt đầu ngay',
        allowOutsideClick: false,
        allowEscapeKey: false
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
<<<<<<< Updated upstream
<AppLayout :title="'Câu ' + questionNumber" :class="{ 'select-none': isProctored }">
            
        <div v-if="isProctored && isQuizStarted && !isFullscreen" class="fixed inset-0 bg-gray-900 z-[9999] flex flex-col items-center justify-center text-white text-center p-4">
            <h2 class="text-3xl font-bold mb-4 text-red-500 animate-pulse">⚠️ CẢNH BÁO</h2>
            <p class="mb-6 text-xl">Bạn đã thoát chế độ toàn màn hình!</p>
            <button @click="enterFullscreen" class="px-8 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition">
                QUAY LẠI BÀI THI
            </button>
        </div>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 flex justify-between">
                        <h2 class="text-2xl font-semibold">Câu {{ questionNumber }} / {{ totalQuestions }}</h2>
                        <span v-if="isProctored && attempt.violation_count > 0" class="text-red-500 font-bold">Lỗi: {{ attempt.violation_count }}/3</span>
=======
    <AppLayout :title="'Question ' + questionNumber" :class="{ 'select-none': isProctored }">
        
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
                
                <div class="px-8 py-6 bg-slate-900/80 border-b border-indigo-500/20 flex flex-col md:flex-row justify-between items-center relative z-10 backdrop-blur-md">
                    <div>
                        <h2 class="text-2xl font-black text-white font-exo uppercase tracking-wide">
                            Sequence <span class="text-cyan-400">{{ String(questionNumber).padStart(2, '0') }}</span> / {{ String(totalQuestions).padStart(2, '0') }}
                        </h2>
                        <div class="h-1 w-full bg-slate-800 mt-2 rounded-full overflow-hidden">
                            <div class="h-full bg-cyan-500 shadow-[0_0_10px_cyan]" :style="{ width: (questionNumber / totalQuestions) * 100 + '%' }"></div>
                        </div>
>>>>>>> Stashed changes
                    </div>
                    
                    <form @submit.prevent="saveAndNext">
                        <div class="p-6">
                            <p class="text-lg font-medium text-gray-900 mb-6">{{ question.question_text }}</p>
                            <div class="space-y-4">
                                <label v-for="option in question.options" :key="option.id" 
                                    class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                    :class="{ 'border-indigo-600 bg-indigo-50': form.option_id === option.id }">
                                    <input type="radio" :value="option.id" v-model="form.option_id" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-4 text-gray-700">{{ option.option_text }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50 border-t flex justify-between">
                            <a v-if="questionNumber > 1" :href="route('quiz.question.show', { attempt: attempt.id, questionNumber: questionNumber - 1 })" class="text-gray-600 hover:text-gray-900">&larr; Câu trước</a>
                            <span v-else></span>
                            <PrimaryButton :disabled="form.processing || !form.option_id">
                                {{ questionNumber < totalQuestions ? 'Câu tiếp theo' : 'Đến trang nộp bài' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
<<<<<<< Updated upstream
=======
                
                <form @submit.prevent="saveAndNext" class="flex-1 flex flex-col relative z-10">
                    <div class="p-8 flex-1 overflow-y-auto">
                        <div class="mb-8">
                            <div class="text-xl md:text-2xl font-bold text-slate-200 font-exo leading-relaxed">
                                <MathRender :content="question.question_text" />
                            </div>
                            <div v-if="question.image_url" class="mt-6 rounded-xl overflow-hidden border border-slate-700 bg-black/50 inline-block max-w-full">
                                <img :src="question.image_url" class="max-h-[400px] object-contain">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <label v-for="option in question.options" :key="option.id" 
                                class="relative group cursor-pointer"
                            >
                                <input type="radio" :value="option.id" v-model="form.option_id" class="peer sr-only">
                                
                                <div class="flex items-center p-5 rounded-xl border-2 transition-all duration-300 relative overflow-hidden"
                                    :class="form.option_id === option.id 
                                        ? 'bg-indigo-600/20 border-cyan-500 shadow-[0_0_20px_rgba(6,182,212,0.2)]' 
                                        : 'bg-slate-900 border-slate-700 hover:border-slate-500 hover:bg-slate-800'">
                                    
                                    <div class="w-8 h-8 flex items-center justify-center mr-4 transition-all duration-300 shrink-0"
                                         :class="form.option_id === option.id ? 'text-cyan-400 scale-110' : 'text-slate-600'">
                                        <svg viewBox="0 0 24 24" fill="none" class="w-full h-full" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2l8.66 5v10L12 22 3.34 17V7L12 2z" />
                                            <circle v-if="form.option_id === option.id" cx="12" cy="12" r="4" fill="currentColor" />
                                        </svg>
                                    </div>

                                    <div class="text-base font-medium font-sans transition-colors duration-300 flex-1"
                                          :class="form.option_id === option.id ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'">
                                        <MathRender :content="option.option_text" />
                                    </div>

                                    <div v-if="form.option_id === option.id" class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-transparent pointer-events-none"></div>
                                </div>
                            </label>
                        </div>
                    </div>

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

                <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-indigo-900/10 to-transparent pointer-events-none"></div>
>>>>>>> Stashed changes
            </div>
        </div>
    </AppLayout>
</template>