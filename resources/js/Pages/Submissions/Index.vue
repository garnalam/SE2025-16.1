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
const aiStatusText = ref('');

// 1. Gửi yêu cầu chấm bài
const triggerAiAnalysis = () => {
    if (!selectedSubmissionData.value?.submission) return;
    
    isAiProcessing.value = true;
    aiStatusText.value = 'UPLOADING DATA PACKETS...';
    
    router.post(route('submissions.ai-grade', selectedSubmissionData.value.submission.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            aiStatusText.value = 'PROCESSING NEURAL NET...';
            startPolling();
        },
        onError: () => {
            isAiProcessing.value = false;
            aiStatusText.value = 'CONNECTION FAILED';
        }
    });
};

// 2. Hàm kiểm tra kết quả thông minh
const startPolling = () => {
    if (pollInterval.value) clearInterval(pollInterval.value);

    let attempts = 0;
    const maxAttempts = 15; 
    
    setTimeout(() => {
        pollInterval.value = setInterval(() => {
            attempts++;
            aiStatusText.value = `SCANNING RESULT [ATTEMPT ${attempts}]...`;
            
            router.reload({
                only: ['submissions'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    const currentStudentId = selectedSubmissionData.value.student.id;
                    const updatedData = props.submissions.find(s => s.student.id === currentStudentId);
                    
                    if (updatedData?.submission?.ai_suggested_grade) {
                        selectedSubmissionData.value = updatedData; 
                        isAiProcessing.value = false; 
                        clearInterval(pollInterval.value); 
                    } 
                    else if (attempts >= maxAttempts) {
                        isAiProcessing.value = false;
                        aiStatusText.value = 'TIMEOUT: MANUAL OVERRIDE REQUIRED';
                        clearInterval(pollInterval.value);
                    }
                }
            });
        }, 3000); 
    }, 5000); 
};

const closeModal = () => {
    if (pollInterval.value) clearInterval(pollInterval.value);
    isAiProcessing.value = false;
    showGradeModal.value = false;
    form.reset();
    selectedSubmissionData.value = null;
};

const applyAiGrade = () => {
    if (selectedSubmissionData.value?.submission?.ai_suggested_grade) {
        form.grade = selectedSubmissionData.value.submission.ai_suggested_grade;
    }
};

const applyAiFeedback = () => {
    if (selectedSubmissionData.value?.submission?.ai_suggested_feedback) {
        form.feedback = selectedSubmissionData.value.submission.ai_suggested_feedback;
    }
};
</script>

<template>
    <AppLayout :title="'GRADING: ' + post.title">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-500/10 border border-indigo-500/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono text-indigo-400 uppercase tracking-widest">Grading Protocol</div>
                        <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo truncate max-w-md">
                            {{ post.title }}
                        </h2>
                    </div>
                </div>
                
                <Link :href="route('topics.show', post.topic_id)" class="group flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-300 hover:text-white uppercase tracking-wider transition">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Return to Feed
                </Link>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Data Grid Container -->
            <div class="bg-[#0f172a] border border-slate-800 rounded-3xl overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.3)] relative">
                <!-- Top Decoration -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/80 border-b border-slate-700 text-[10px] font-mono text-slate-500 uppercase tracking-[0.1em]">
                                <th class="px-6 py-4">Học sinh</th>
                                <th class="px-6 py-4">Trạng thái nộp bài</th>
                                <th class="px-6 py-4">Thời gian nộp</th>
                                <th class="px-6 py-4">Điểm</th>
                                <th class="px-6 py-4 text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            <tr v-for="data in submissions" :key="data.student.id" class="group hover:bg-indigo-900/10 transition-colors duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="relative">
                                            <img class="h-10 w-10 rounded-lg object-cover border border-slate-700 group-hover:border-indigo-400 transition" :src="data.student.profile_photo_url">
                                            <div v-if="data.submission" class="absolute -bottom-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="font-bold text-slate-200 group-hover:text-white font-exo">{{ data.student.name }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono">{{ data.student.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="data.status === 'Graded'" class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span> Graded
                                    </span>
                                    <span v-else-if="data.status === 'Submitted'" class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 font-mono uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span> Đang chờ chấm
                                    </span>
                                    <span v-else class="text-[10px] text-slate-600 font-mono uppercase">Chưa nộp </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="data.submission" class="text-sm text-slate-400 font-mono">
                                        {{ new Date(data.submission.submitted_at).toLocaleString('vi-VN') }}
                                        <div v-if="isSubmissionLate(data.submission)" class="mt-1 inline-block text-[9px] text-rose-500 font-bold border border-rose-500/30 px-1 rounded uppercase tracking-wide">
                                            ⚠️ Nộp trễ
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-600 text-xs">--</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="data.submission?.grade !== null && data.submission?.grade !== undefined" class="font-black text-white font-exo text-lg">
                                        {{ data.submission.grade }} <span class="text-xs text-slate-500 font-normal">/ {{ post.max_points }}</span>
                                    </div>
                                    <span v-else class="text-slate-600 text-xs italic">-- / {{ post.max_points }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="openGradeModal(data)" 
                                        :disabled="!data.submission"
                                        class="px-4 py-2 bg-slate-800 hover:bg-cyan-600 text-slate-300 hover:text-white rounded-lg text-xs font-bold uppercase tracking-widest transition-all disabled:opacity-20 disabled:cursor-not-allowed border border-slate-700 hover:border-cyan-400 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
                                    >
                                        {{ data.status === 'Graded' ? 'Cập nhật điểm' : 'Chấm điểm' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- GRADING CONSOLE MODAL -->
        <Modal :show="showGradeModal" @close="closeModal" maxWidth="5xl"> 
            <div class="flex flex-col h-[85vh] md:h-auto bg-[#0f172a] text-slate-200 overflow-hidden relative">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-900 border-b border-white/5 flex justify-between items-center z-10">
                    <div class="flex items-center gap-3">
                        <img :src="selectedSubmissionData?.student.profile_photo_url" class="h-8 w-8 rounded bg-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white font-exo uppercase tracking-wide">
                                Khu vực chấm bài: <span class="text-cyan-400">{{ selectedSubmissionData?.student.name }}</span>
                            </h3>
                            <div class="text-[9px] text-slate-500 font-mono">
                                ID: {{ selectedSubmissionData?.student.id }} // SESSION_ACTIVE
                            </div>
                        </div>
                    </div>
                    <button @click="closeModal" class="text-slate-500 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-0 md:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
                        
                        <!-- LEFT COL: SUBMISSION CONTENT -->
                        <div class="flex flex-col gap-6">
                            <!-- Content Viewer -->
                            <div class="bg-black/40 border border-slate-700 rounded-xl flex-1 flex flex-col min-h-[300px]">
                                <div class="px-4 py-2 bg-slate-800/50 border-b border-slate-700 text-[10px] font-mono text-slate-400 uppercase tracking-widest flex justify-between">
                                    <span>Lời nhắn</span>
                                    <span>TEXT_STREAM</span>
                                </div>
                                <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                                    <p class="text-sm text-slate-300 whitespace-pre-wrap font-mono leading-relaxed">
                                        {{ selectedSubmissionData?.submission?.content || '>> Không có nội dung đính kèm.' }}
                                    </p>
                                    
                                    <div v-if="selectedSubmissionData?.submission?.files.length > 0" class="mt-6 pt-4 border-t border-slate-800">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-2">File đính kèm</p>
                                        <div class="space-y-2">
                                            <a v-for="file in selectedSubmissionData.submission.files" :key="file.id" 
                                               :href="route('submissions.downloadFile', file.id)" target="_blank"
                                               class="flex items-center gap-3 p-2 bg-slate-900 border border-slate-700 hover:border-cyan-500 rounded group transition">
                                                <div class="p-1.5 bg-slate-800 text-cyan-500 rounded">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                </div>
                                                <span class="text-xs text-slate-300 group-hover:text-white font-mono">{{ file.original_name }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Grading Form -->
                            <form @submit.prevent="submitGrade" class="bg-slate-900 border border-slate-700 rounded-xl p-5 shadow-lg relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                                <h4 class="font-bold text-white font-exo uppercase tracking-wide mb-4 text-xs">Chấm điểm</h4>
                                
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <InputLabel value="Điểm" class="!text-slate-400" />
                                        <div class="relative">
                                            <TextInput v-model="form.grade" type="number" step="0.1" class="w-full font-mono font-bold text-emerald-400 border-slate-600 bg-slate-800" :max="post.max_points" />
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-mono">/ {{ post.max_points }}</span>
                                        </div>
                                        <InputError :message="form.errors.grade" />
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <InputLabel value="Nhận xét" class="!text-slate-400" />
                                    <TextArea v-model="form.feedback" rows="3" class="w-full bg-slate-800 border-slate-600 text-slate-200 text-xs" placeholder="// Viết nhận xét..." />
                                    <InputError :message="form.errors.feedback" />
                                </div>
                                <div class="flex justify-end gap-3">
                                    <SecondaryButton @click="closeModal" class="!text-xs">Hủy</SecondaryButton>
                                    <PrimaryButton :disabled="form.processing" :class="{ 'opacity-50': form.processing }" class="!text-xs !bg-indigo-600 hover:!bg-indigo-500">
                                        Hoàn thành chấm điểm
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- RIGHT COL: AI GEMINI ANALYSIS -->
                        <div class="flex flex-col h-full">
                            <div class="bg-purple-900/10 border border-purple-500/30 rounded-xl p-1 flex-1 flex flex-col relative overflow-hidden">
                                <!-- Animated Background -->
                                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl animate-pulse"></div>

                                <!-- Header -->
                                <div class="flex justify-between items-center p-4 border-b border-purple-500/20 relative z-10">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a1 1 0 011 1v8h8a1 1 0 010 2h-8v8a1 1 0 01-2 0v-8H3a1 1 0 010-2h8V3a1 1 0 011-1z"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-purple-300 font-exo uppercase tracking-wide text-sm">Gợi ý chấm bài bằng AI</h3>
                                            <p class="text-[9px] text-purple-400/60 font-mono"></p>
                                        </div>
                                    </div>
                                    
                                    <button 
                                        @click="triggerAiAnalysis" 
                                        :disabled="isAiProcessing"
                                        class="group relative px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-[10px] font-bold uppercase tracking-widest rounded transition-all disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden shadow-[0_0_15px_rgba(147,51,234,0.3)]"
                                    >
                                        <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300"></div>
                                        <span class="relative z-10 flex items-center gap-2">
                                            <svg v-if="isAiProcessing" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span v-if="isAiProcessing">Đang chấm bài...</span>
                                            <span v-else>Chấm bài</span>
                                        </span>
                                    </button>
                                </div>

                                <!-- Content Area -->
                                <div class="flex-1 p-4 overflow-y-auto relative z-10 custom-scrollbar">
                                    
                                    <!-- Idle State -->
                                    <div v-if="!selectedSubmissionData?.submission?.ai_suggested_grade && !isAiProcessing" class="h-full flex flex-col items-center justify-center text-purple-400/40 space-y-4">
                                        <div class="w-16 h-16 border border-dashed border-purple-500/30 rounded-full flex items-center justify-center">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        </div>
                                        <p class="text-xs font-mono text-center max-w-[200px]">Hỗ trợ chấm bài bằng file pdf, word, ảnh chụp từ điện thoại (với góc chụp đủ đẹp)</p>
                                    </div>

                                    <!-- Loading State -->
                                    <div v-else-if="isAiProcessing && !selectedSubmissionData?.submission?.ai_suggested_grade" class="h-full flex flex-col items-center justify-center space-y-4">
                                        <div class="relative w-20 h-20">
                                            <div class="absolute inset-0 border-t-2 border-purple-500 rounded-full animate-spin"></div>
                                            <div class="absolute inset-2 border-r-2 border-indigo-500 rounded-full animate-spin-reverse"></div>
                                            <div class="absolute inset-0 flex items-center justify-center text-purple-500 text-xs font-mono animate-pulse">AI</div>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs font-bold text-purple-400 font-mono animate-pulse">{{ aiStatusText }}</p>
                                            <p class="text-[9px] text-purple-500/50 mt-1 uppercase tracking-widest">Est. Time: 10-15s</p>
                                        </div>
                                    </div>

                                    <!-- Result State -->
                                    <div v-else-if="selectedSubmissionData?.submission?.ai_suggested_grade" class="space-y-4 animate-fade-in-up">
                                        
                                        <!-- Grade Card -->
                                        <div class="bg-slate-900/80 border border-purple-500/40 p-4 rounded-xl flex items-center justify-between shadow-[0_0_20px_rgba(147,51,234,0.1)]">
                                            <div>
                                                <div class="text-[10px] font-bold text-purple-400 uppercase tracking-widest mb-1">Calculated Score</div>
                                                <div class="text-4xl font-black text-white font-exo">
                                                    {{ selectedSubmissionData.submission.ai_suggested_grade }}
                                                    <span class="text-sm font-normal text-slate-500">/ {{ post.max_points }}</span>
                                                </div>
                                            </div>
                                            <button @click="applyAiGrade" class="flex flex-col items-center gap-1 group">
                                                <div class="p-2 bg-purple-600 group-hover:bg-purple-500 text-white rounded-lg transition shadow-lg">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                                </div>
                                                <span class="text-[8px] font-bold uppercase text-purple-400 group-hover:text-purple-300">Apply</span>
                                            </button>
                                        </div>

                                        <!-- Feedback Card -->
                                        <div class="bg-slate-900/80 border border-purple-500/40 p-4 rounded-xl shadow-[0_0_20px_rgba(147,51,234,0.1)]">
                                            <div class="flex justify-between items-center mb-3 pb-2 border-b border-purple-500/20">
                                                <span class="text-[10px] font-bold text-purple-400 uppercase tracking-widest">Detailed Analysis</span>
                                                <button @click="applyAiFeedback" class="text-[9px] font-bold uppercase text-indigo-400 hover:text-white flex items-center gap-1 transition">
                                                    <span>Apply Text</span>
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                                </button>
                                            </div>
                                            <div class="text-xs text-slate-300 whitespace-pre-wrap leading-relaxed font-mono">
                                                {{ selectedSubmissionData.submission.ai_suggested_feedback }}
                                            </div>
                                        </div>

                                        <div class="text-center pt-2">
                                            <button @click="triggerAiAnalysis" class="text-[9px] text-purple-500/60 hover:text-purple-400 uppercase tracking-widest hover:underline transition">
                                                Re-Initialize Analysis
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.animate-spin-reverse {
    animation: spin-reverse 1.5s linear infinite;
}
@keyframes spin-reverse {
    from { transform: rotate(360deg); }
    to { transform: rotate(0deg); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>