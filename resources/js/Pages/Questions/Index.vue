<script setup>
import { ref, watch, reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import VueMultiselect from 'vue-multiselect'; 
import Pagination from '@/Components/Pagination.vue'; 
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios'; 
import MathRender from '@/Components/MathRender.vue'; // <--- THÊM DÒNG NÀY

const props = defineProps({
    questions: Object,
    subjects: Array,
    tags: Array,
    filters: Object,
});

const deleteForm = useForm({});
function deleteQuestion(questionId) {
    if (confirm('Xác nhận xóa bài viết: Bạn đã chắc chắn chưa ?')) {
        deleteForm.delete(route('questions.destroy', questionId), {
            preserveScroll: true,
        });
    }
}

const filterForm = ref({
    subject: props.filters.subject || null,
    tags: props.filters.tags || [],
    search: props.filters.search || '',
});

watch(filterForm, (newFilters) => {
    router.get(route('questions.index'), newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

function resetFilters() {
    filterForm.value = { subject: null, tags: [], search: '' };
}

// ===== LOGIC AI GENERATOR =====
const showAiModal = ref(false);
const aiStep = ref(1); 
const isGenerating = ref(false);

const aiForm = reactive({
    documents: [], 
    subject_id: null,
    tags: [],
    number_of_questions: 5,
    custom_instructions: '', 
});

const generatedQuestions = ref([]); 

const handleFileUpload = (event) => {
    aiForm.documents = Array.from(event.target.files);
};

const generateQuestions = async () => {
    // 1. Kiểm tra dữ liệu đầu vào
    if (aiForm.documents.length === 0 || !aiForm.subject_id) {
        alert('Vui lòng chọn Môn học và Tải lên ít nhất 1 tài liệu.');
        return;
    }

    isGenerating.value = true;
    const formData = new FormData();

    // 2. Gắn file tài liệu
    aiForm.documents.forEach((file, index) => {
        formData.append(`documents[${index}]`, file);
    });

    // 3. Gắn các trường cơ bản
    formData.append('number_of_questions', aiForm.number_of_questions);
    formData.append('subject_id', aiForm.subject_id);

    // [FIX 1] GẮN DỮ LIỆU TAGS (QUAN TRỌNG)
    // Vì tags là mảng object, ta cần map lấy ID hoặc gửi từng cái
    aiForm.tags.forEach((tag, index) => {
        formData.append(`tags[${index}]`, tag.id); // Chỉ gửi ID của tag
    });

    // [FIX 2] GẮN CUSTOM INSTRUCTIONS
    if (aiForm.custom_instructions) {
        formData.append('custom_instructions', aiForm.custom_instructions);
    }
    
    // [FIX 3 - TÙY CHỌN] NẾU BACKEND CẦN 'TOPIC', BẠN PHẢI THÊM NÓ VÀO FORM
    // formData.append('topic', 'Chủ đề gì đó...'); 

    try {
        const response = await axios.post(route('questions.generate-ai'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        generatedQuestions.value = response.data.questions;
        aiStep.value = 2; 
    } catch (error) {
        console.error(error);
        // Hiển thị lỗi chi tiết từ Backend trả về
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            // Lấy lỗi đầu tiên để hiển thị alert
            const firstError = Object.values(errors)[0][0];
            alert('Lỗi dữ liệu: ' + firstError);
        } else {
            alert('Lỗi hệ thống: ' + (error.response?.data?.message || 'Không thể tạo câu hỏi.'));
        }
    } finally {
        isGenerating.value = false;
    }
};

const saveBulkForm = useForm({
    questions: [],
    subject_id: null,
    tags: []
});

const saveGeneratedQuestions = () => {
    saveBulkForm.questions = generatedQuestions.value;
    saveBulkForm.subject_id = aiForm.subject_id;
    saveBulkForm.tags = aiForm.tags.map(t => t.id); 

    saveBulkForm.post(route('questions.store-bulk'), {
        onSuccess: () => {
            showAiModal.value = false;
            aiStep.value = 1;
            generatedQuestions.value = [];
            aiForm.documents = [];
        }
    });
};
</script>

<template>
    <AppLayout title="Data Bank">
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/10 border border-indigo-500/30 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <div>
                    <div class="text-[10px] font-mono text-indigo-400 uppercase tracking-widest">Repository</div>
                    <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                        Question Bank
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Toolbar -->
            <div class="mb-8 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <Link :href="route('questions.create')" class="group relative px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold uppercase tracking-widest text-xs rounded-xl overflow-hidden transition-all shadow-[0_0_15px_rgba(6,182,212,0.3)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)]">
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Tạo câu hỏi thủ công
                        </span>
                    </Link>
                    
                    <button 
                        @click="showAiModal = true"
                        class="group relative px-6 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold uppercase tracking-widest text-xs rounded-xl overflow-hidden transition-all shadow-[0_0_15px_rgba(147,51,234,0.3)] hover:shadow-[0_0_25px_rgba(147,51,234,0.5)]"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Tạo câu hỏi bằng AI
                        </span>
                        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                    </button>

                    <Link :href="route('questions.import.create')" class="px-4 py-2.5 bg-emerald-600/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-600 hover:text-white font-bold uppercase tracking-widest text-xs rounded-xl transition-all">
                        Nhập dữ liệu từ file Excel/CSV
                    </Link>
                </div>

                <div class="flex gap-2">
                    <Link :href="route('subjects.index')" class="px-3 py-2 bg-slate-800 border border-slate-700 text-slate-400 hover:text-white rounded-lg text-[10px] uppercase font-bold tracking-wider transition">Quản lý môn học</Link>
                    <Link :href="route('tags.index')" class="px-3 py-2 bg-slate-800 border border-slate-700 text-slate-400 hover:text-white rounded-lg text-[10px] uppercase font-bold tracking-wider transition">Quản lý nhãn</Link>
                </div>
            </div>

            <!-- Search Console -->
            <div class="bg-[#0f172a] border border-slate-700 rounded-2xl p-5 mb-8 shadow-xl relative">
                <!-- Decoration Container (Clipped) -->
                <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-cyan-500/10 rounded-full blur-2xl"></div>
                </div>
                
                <!-- Content (Visible Overflow for Dropdowns) -->
                <div class="relative z-10">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 font-mono flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Thanh tìm kiếm
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <VueMultiselect
                                v-model="filterForm.subject"
                                :options="props.subjects.map(s => s.id)"
                                :custom-label="id => props.subjects.find(s => s.id === id)?.name"
                                placeholder="Chọn Môn Học"
                                class="custom-multiselect"
                            />
                        </div>
                        
                        <div>
                            <VueMultiselect
                                v-model="filterForm.tags"
                                :options="props.tags.map(t => t.id)"
                                :custom-label="id => props.tags.find(t => t.id === id)?.name"
                                :multiple="true"
                                placeholder="Chọn Nhãn"
                                class="custom-multiselect"
                            />
                        </div>
                        
                        <div class="md:col-span-2 flex gap-2">
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Nhập nội dung câu hỏi trắc nghiệm..."
                                class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm focus:border-cyan-500 focus:ring-cyan-500/20"
                            />
                            <button @click="resetFilters" class="px-3 py-2 border border-slate-600 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Grid -->
            <div class="grid grid-cols-1 gap-4">
                <div v-if="questions.data.length === 0" class="p-12 text-center border border-dashed border-slate-700 rounded-2xl bg-slate-900/30">
                    <p class="text-slate-500 font-mono text-sm">>> NO DATA NODES FOUND.</p>
                </div>
                
                <div v-for="question in questions.data" :key="question.id" 
                     class="group bg-slate-900/80 border border-slate-800 hover:border-indigo-500/50 rounded-xl p-5 transition-all duration-200 hover:shadow-[0_0_20px_rgba(99,102,241,0.1)] relative overflow-hidden">
                    
                    <!-- Hover Highlight -->
                    <div class="absolute left-0 top-0 w-1 h-full bg-indigo-500 opacity-0 group-hover:opacity-100 transition duration-200"></div>

                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="mb-3 flex flex-wrap gap-2 items-center">
                                <span v-if="question.subject" class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-slate-800 text-indigo-400 border border-slate-700 rounded">
                                    {{ question.subject.name }}
                                </span>
                                <span v-for="tag in question.tags" :key="tag.id" class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700 rounded">
                                    {{ tag.name }}
                                </span>
                            </div>

                            <div class="mb-3 font-bold text-slate-200 font-exo text-sm">
                                <MathRender :content="question.question_text" />
                            </div>
                            
                            <img v-if="question.image_url" :src="question.image_url" class="mb-3 w-32 h-auto rounded border border-slate-700 object-contain bg-black/20">                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div v-for="option in question.options" :key="option.id"
                                    class="flex items-center gap-2 text-xs font-mono p-2 rounded"
                                    :class="option.is_correct ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-slate-950 border border-slate-800 text-slate-500'">
                                    <div class="w-1.5 h-1.5 rounded-full shrink-0" :class="option.is_correct ? 'bg-emerald-500' : 'bg-slate-700'"></div>
                                    
                                    <MathRender :content="option.option_text" /> 
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2 opacity-50 group-hover:opacity-100 transition">
                            <Link :href="route('questions.edit', question.id)" class="p-2 bg-slate-800 hover:bg-cyan-600 text-slate-400 hover:text-white rounded border border-slate-700 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </Link>
                            <button @click="deleteQuestion(question.id)" class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded border border-slate-700 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <Pagination :links="questions.links" class="mt-8" />
        </div>

        <!-- AI GENERATOR MODAL -->
        <DialogModal :show="showAiModal" @close="showAiModal = false" maxWidth="4xl">
            <template #title>
                <div class="flex items-center gap-2 text-purple-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span class="font-exo font-bold uppercase tracking-wide">TẠO CÂU HỎI BẰNG AI</span>
                </div>
            </template>

            <template #content>
                <div v-if="aiStep === 1" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <InputLabel value="Môn học (Bắt buộc)" />
                            <select v-model="aiForm.subject_id" class="bg-slate-900 border-slate-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 w-full mt-1 font-mono">
                                <option :value="null">-- Chọn Môn Học --</option>
                                <option v-for="sub in props.subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Nhãn" />
                            <VueMultiselect
                                v-model="aiForm.tags"
                                :options="props.tags"
                                track-by="id"
                                label="name"
                                :multiple="true"
                                placeholder="Chọn nhãn"
                                class="custom-multiselect mt-1"
                            />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Thêm tài liệu (PDF, DOCX, TXT)" />
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-700 border-dashed rounded-xl hover:border-purple-500 transition-colors bg-slate-900/50">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-slate-400">
                                    <label class="relative cursor-pointer bg-slate-800 rounded-md font-medium text-purple-400 hover:text-purple-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500 px-2">
                                        <span>Upload files</span>
                                        <input type="file" multiple @change="handleFileUpload" class="sr-only" />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-slate-500">
                                    Files selected: <span class="text-white font-bold">{{ aiForm.documents.length }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <InputLabel value="Thêm yêu cầu cụ thể" />
                        <textarea v-model="aiForm.custom_instructions" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm font-mono mt-1 focus:border-purple-500 focus:ring-purple-500/20" rows="2" placeholder="// Ví dụ: Thêm cho tôi những câu hỏi có độ khó cao"></textarea>
                    </div>

                    <div>
                        <InputLabel value="Số lượng câu hỏi" />
                        <div class="flex items-center gap-4">
                            <input type="range" v-model="aiForm.number_of_questions" min="1" max="50" class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-500">
                            <span class="text-purple-400 font-bold font-mono text-lg w-8">{{ aiForm.number_of_questions }}</span>
                        </div>
                    </div>
                    
                    <div v-if="isGenerating" class="flex flex-col items-center justify-center py-8">
                        <div class="relative w-16 h-16">
                            <div class="absolute inset-0 border-t-2 border-purple-500 rounded-full animate-spin"></div>
                            <div class="absolute inset-2 border-r-2 border-indigo-500 rounded-full animate-spin-reverse"></div>
                        </div>
                        <p class="text-purple-400 font-mono text-xs mt-4 animate-pulse">ANALYZING DATA STREAMS...</p>
                    </div>
                </div>

                <div v-else class="h-[60vh] flex flex-col">
                    <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-emerald-400 text-xs font-mono flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        GENERATION COMPLETE. REVIEW OUTPUT.
                    </div>
                    
                    <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                        <div v-for="(q, index) in generatedQuestions" :key="index" class="bg-slate-900 border border-slate-700 p-4 rounded-xl relative group">
                            <button @click="generatedQuestions.splice(index, 1)" class="absolute top-2 right-2 text-slate-600 hover:text-rose-500 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Query {{ index + 1 }}</label>
                            <textarea v-model="q.question_text" class="w-full bg-slate-950 border-slate-800 rounded-lg text-sm text-slate-200 mb-3" rows="2"></textarea>
                            
                            <div class="space-y-2">
                                <div v-for="(opt, optIndex) in q.options" :key="optIndex" class="flex items-center gap-3">
                                    <input type="radio" :name="'correct_' + index" :checked="opt.is_correct" @change="() => { q.options.forEach(o => o.is_correct = false); opt.is_correct = true; }" class="bg-slate-900 border-slate-700 text-purple-600 focus:ring-purple-500" />
                                    <input type="text" v-model="opt.text" class="flex-1 bg-slate-950 border-slate-800 rounded text-xs text-slate-300 py-1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showAiModal = false" :disabled="isGenerating || saveBulkForm.processing">Hủy</SecondaryButton>

                <PrimaryButton v-if="aiStep === 1" class="ml-3 !bg-purple-600 hover:!bg-purple-500" @click="generateQuestions" :disabled="isGenerating">
                    {{ isGenerating ? 'Đang tạo câu hỏi...' : 'Tạo câu hỏi' }}
                </PrimaryButton>

                <PrimaryButton v-if="aiStep === 2" class="ml-3 !bg-emerald-600 hover:!bg-emerald-500" @click="saveGeneratedQuestions" :disabled="saveBulkForm.processing">
                    {{ saveBulkForm.processing ? 'Đang lưu...' : 'Lưu vào ngân hàng câu hỏi' }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<style>
/* Override VueMultiselect for Dark Mode */
.custom-multiselect .multiselect__tags {
    background-color: #0f172a;
    border-color: #334155;
    color: white;
    border-radius: 0.5rem;
}
.custom-multiselect .multiselect__input, .custom-multiselect .multiselect__single {
    background-color: #0f172a;
    color: white;
}
.custom-multiselect .multiselect__content-wrapper {
    background-color: #1e293b;
    border-color: #334155;
}
.custom-multiselect .multiselect__option {
    background-color: #1e293b;
    color: #cbd5e1;
}
.custom-multiselect .multiselect__option--highlight {
    background-color: #06b6d4; /* Cyan */
    color: white;
}
.custom-multiselect .multiselect__option--selected {
    background-color: #334155;
    color: white;
}
.custom-multiselect .multiselect__tag {
    background-color: #06b6d4;
    color: #0f172a;
    font-weight: bold;
}
.custom-multiselect .multiselect__tag-icon:hover {
    background-color: #0891b2;
}

/* Custom Scrollbar for Multiselect */
.custom-multiselect .multiselect__content-wrapper::-webkit-scrollbar {
    width: 6px;
}
.custom-multiselect .multiselect__content-wrapper::-webkit-scrollbar-track {
    background: #0f172a;
}
.custom-multiselect .multiselect__content-wrapper::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 3px;
}
.custom-multiselect .multiselect__content-wrapper::-webkit-scrollbar-thumb:hover {
    background: #06b6d4;
}
</style>