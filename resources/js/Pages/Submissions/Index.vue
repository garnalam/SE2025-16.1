<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    post: Object,
    submissions: Array,
});

const selectedSubmissionData = ref(null);
const showGradeModal = ref(false);

const form = useForm({
    grade: '',
    feedback: '',
});

const isSubmissionLate = (submission) => {
    if (!submission || !props.post.due_date) return false;
    const submissionTime = new Date(submission.submitted_at);
    const dueDate = new Date(props.post.due_date);
    return submissionTime > dueDate;
};

const openGradeModal = (submissionData) => {
    selectedSubmissionData.value = submissionData;
    const submission = submissionData.submission;
    
    form.grade = submission?.grade ?? '';
    form.feedback = submission?.feedback ?? '';
    
    showGradeModal.value = true;
    isAiProcessing.value = false;
};

const submitGrade = () => {
    if (!selectedSubmissionData.value?.submission) return;
    form.put(route('submissions.grade', selectedSubmissionData.value.submission.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const isAiProcessing = ref(false);
const pollInterval = ref(null);

// 1. Gửi yêu cầu chấm bài
const triggerAiAnalysis = () => {
    if (!selectedSubmissionData.value?.submission) return;
    
    isAiProcessing.value = true;
    
    // Gọi API để Backend đẩy Job vào hàng đợi
    router.post(route('submissions.ai-grade', selectedSubmissionData.value.submission.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Gửi thành công -> Bắt đầu chờ kết quả
            startPolling();
        },
        onError: () => {
            isAiProcessing.value = false;
            alert("Lỗi kết nối server.");
        }
    });
};

// 2. Hàm kiểm tra kết quả thông minh
const startPolling = () => {
    if (pollInterval.value) clearInterval(pollInterval.value);

    let attempts = 0;
    const maxAttempts = 15; // Thử tối đa 15 lần
    
    // Đợi 5 giây đầu tiên rồi mới bắt đầu hỏi (để đỡ spam server lúc đầu)
    setTimeout(() => {
        pollInterval.value = setInterval(() => {
            attempts++;
            
            // Reload lại danh sách bài nộp để lấy dữ liệu mới nhất từ DB
            router.reload({
                only: ['submissions'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    // Tìm lại bài đang mở trong modal
                    const currentStudentId = selectedSubmissionData.value.student.id;
                    const updatedData = props.submissions.find(s => s.student.id === currentStudentId);
                    
                    // Kiểm tra xem đã có điểm AI chưa (cột ai_suggested_grade khác null)
                    if (updatedData?.submission?.ai_suggested_grade) {
                        // => ĐÃ CÓ KẾT QUẢ!
                        selectedSubmissionData.value = updatedData; // Cập nhật vào Modal
                        isAiProcessing.value = false; // Tắt loading
                        clearInterval(pollInterval.value); // Dừng hỏi
                    } 
                    else if (attempts >= maxAttempts) {
                        // => QUÁ LÂU (Khoảng 45s) MÀ CHƯA CÓ
                        isAiProcessing.value = false;
                        clearInterval(pollInterval.value);
                        // Không alert lỗi, cứ để user bấm lại nếu muốn
                    }
                }
            });
        }, 3000); // Cứ 3 giây hỏi 1 lần
    }, 5000); // Delay 5s ban đầu
};

// Dọn dẹp khi đóng modal
const closeModal = () => {
    if (pollInterval.value) clearInterval(pollInterval.value);
    isAiProcessing.value = false;
    showGradeModal.value = false;
    form.reset();
    selectedSubmissionData.value = null;
};

// 3. Áp dụng điểm từ AI vào Form
const applyAiGrade = () => {
    if (selectedSubmissionData.value?.submission?.ai_suggested_grade) {
        form.grade = selectedSubmissionData.value.submission.ai_suggested_grade;
    }
};

// 4. Áp dụng Feedback từ AI vào Form
const applyAiFeedback = () => {
    if (selectedSubmissionData.value?.submission?.ai_suggested_feedback) {
        // Nối thêm vào feedback cũ hoặc ghi đè (ở đây mình chọn ghi đè cho gọn)
        form.feedback = selectedSubmissionData.value.submission.ai_suggested_feedback;
    }
};

</script>

<template>
    <AppLayout :title="'Chấm bài: ' + post.title">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chấm bài: {{ post.title }}
            </h2>
            <Link :href="route('topics.show', post.topic_id)" class="text-sm text-indigo-600 hover:text-indigo-800">
                &larr; Quay lại chủ đề
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Học sinh</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nộp lúc</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Điểm</th>
                                    <th class="px-6 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="data in submissions" :key="data.student.id">
                                    <td class="px-6 py-4 flex items-center">
                                        <img class="h-8 w-8 rounded-full mr-3" :src="data.student.profile_photo_url">
                                        {{ data.student.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="{'bg-green-100 text-green-800': data.status === 'Graded', 'bg-yellow-100': data.status === 'Submitted'}" class="px-2 py-1 rounded text-xs font-bold">
                                            {{ data.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div v-if="data.submission">
                                            {{ new Date(data.submission.submitted_at).toLocaleString('vi-VN') }}
                                            <span v-if="isSubmissionLate(data.submission)" class="ml-2 text-red-600 font-bold text-xs bg-red-100 px-2 py-0.5 rounded">MUỘN</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-bold">
                                        {{ data.submission?.grade ?? '--' }} / {{ post.max_points }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <PrimaryButton @click="openGradeModal(data)" :disabled="!data.submission" size="sm">
                                            Chấm bài
                                        </PrimaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showGradeModal" @close="closeModal" maxWidth="4xl"> <div class="p-6">
                
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Chấm bài: {{ selectedSubmissionData?.student.name }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-3 rounded border h-64 overflow-y-auto">
                            <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Nội dung bài làm:</p>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap">
                                {{ selectedSubmissionData?.submission?.content || '(Không có nội dung text)' }}
                            </p>
                            
                            <div v-if="selectedSubmissionData?.submission?.files.length > 0" class="mt-4 pt-2 border-t">
                                <p class="text-xs font-bold text-gray-500 mb-1">Files đính kèm:</p>
                                <ul class="list-disc list-inside">
                                    <li v-for="file in selectedSubmissionData.submission.files" :key="file.id" class="text-sm">
                                        <a :href="route('submissions.downloadFile', file.id)" class="text-blue-600 hover:underline" target="_blank">
                                            📄 {{ file.original_name }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <form @submit.prevent="submitGrade" class="bg-white border rounded p-4 shadow-sm">
                            <h4 class="font-bold text-indigo-700 mb-3">✍️ Giáo viên chấm (Quyết định)</h4>
                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <InputLabel value="Điểm số" />
                                    <div class="flex items-center">
                                        <TextInput v-model="form.grade" type="number" step="0.1" class="w-full" :max="post.max_points" />
                                        <span class="ml-2 text-gray-500">/ {{ post.max_points }}</span>
                                    </div>
                                    <InputError :message="form.errors.grade" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <InputLabel value="Nhận xét" />
                                <TextArea v-model="form.feedback" rows="4" class="w-full" placeholder="Nhập lời phê..." />
                                <InputError :message="form.errors.feedback" />
                            </div>
                            <div class="text-right">
                                <SecondaryButton @click="closeModal" class="mr-2">Hủy</SecondaryButton>
                                <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }">Lưu kết quả</PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 flex flex-col h-full">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-2">🤖</span>
                                <h3 class="font-bold text-purple-800">Trợ lý AI Gemini</h3>
                            </div>
                            
                            <button 
                                @click="triggerAiAnalysis" 
                                :disabled="isAiProcessing"
                                class="px-3 py-1 bg-purple-600 text-white text-xs font-bold rounded shadow hover:bg-purple-700 disabled:opacity-50 transition"
                            >
                                <span v-if="isAiProcessing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Đang phân tích...
                                </span>
                                <span v-else>✨ Phân tích ngay</span>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto pr-1">
                            <div v-if="!selectedSubmissionData?.submission?.ai_suggested_grade && !isAiProcessing" class="text-center text-gray-500 py-10 italic">
                                <p>Nhấn nút "Phân tích ngay" để AI đọc bài và gợi ý điểm số.</p>
                                <p class="text-xs mt-2">(Hỗ trợ cả File Word, PDF và Ảnh scan)</p>
                            </div>

                            <div v-else-if="isAiProcessing && !selectedSubmissionData?.submission?.ai_suggested_grade" class="text-center text-purple-600 py-10">
                                <p class="animate-pulse">Đang gửi đề bài và bài làm sang Google Gemini...</p>
                                <p class="text-xs mt-2 text-gray-500">Quá trình này có thể mất 10-15 giây.</p>
                            </div>

                            <div v-else-if="selectedSubmissionData?.submission?.ai_suggested_grade">
                                
                                <div class="bg-white p-3 rounded border border-purple-100 shadow-sm mb-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-bold text-gray-500 uppercase">Điểm đề xuất</span>
                                        <button @click="applyAiGrade" class="text-xs text-blue-600 font-semibold hover:underline cursor-pointer">
                                            ⬇ Áp dụng
                                        </button>
                                    </div>
                                    <div class="text-3xl font-black text-purple-700">
                                        {{ selectedSubmissionData.submission.ai_suggested_grade }}
                                        <span class="text-sm font-normal text-gray-400">/ {{ post.max_points }}</span>
                                    </div>
                                </div>

                                <div class="bg-white p-3 rounded border border-purple-100 shadow-sm">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase">Nhận xét chi tiết</span>
                                        <button @click="applyAiFeedback" class="text-xs text-blue-600 font-semibold hover:underline cursor-pointer">
                                            ⬇ Áp dụng
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                                        {{ selectedSubmissionData.submission.ai_suggested_feedback }}
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <button @click="reloadSubmissionData" class="text-xs text-gray-400 underline hover:text-gray-600">
                                        Làm mới dữ liệu (nếu thấy lỗi)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </Modal>
    </AppLayout>
</template>