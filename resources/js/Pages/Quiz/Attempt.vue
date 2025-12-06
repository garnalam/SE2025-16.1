<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted, onUnmounted } from 'vue';
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
                Swal.fire('Lỗi', 'Không thể vào chế độ toàn màn hình. Hãy thử lại!', 'error');
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
            </div>
        </div>
    </AppLayout>
</template>