<script setup>
import { ref, watch,reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import VueMultiselect from 'vue-multiselect'; // <-- 1. Import thư viện
import Pagination from '@/Components/Pagination.vue'; // (Giả sử bạn có component này)
// Import thêm components của Jetstream để làm Modal đẹp
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
<<<<<<< Updated upstream
import axios from 'axios'; // Dùng axios để gọi API AI mà không reload trang
// 2. Nhận props mới từ controller
=======
import axios from 'axios'; 
import MathRender from '@/Components/MathRender.vue'; // <--- THÊM DÒNG NÀY

>>>>>>> Stashed changes
const props = defineProps({
    questions: Object, // Đây là object phân trang
    subjects: Array,
    tags: Array,
    filters: Object, // Các filter đã áp dụng
});

// Form helper để xóa (Như cũ)
const deleteForm = useForm({});
function deleteQuestion(questionId) {
    if (confirm('Bạn có chắc muốn xóa câu hỏi này?')) {
        deleteForm.delete(route('questions.destroy', questionId), {
            preserveScroll: true,
        });
    }
}

// 3. Form cho Bộ lọc
const filterForm = ref({
    subject: props.filters.subject || null,
    tags: props.filters.tags || [],
    search: props.filters.search || '',
});

// 4. Hàm watch để tự động lọc khi form thay đổi
watch(filterForm, (newFilters) => {
    router.get(route('questions.index'), newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

// 5. Hàm reset bộ lọc
function resetFilters() {
    filterForm.value = {
        subject: null,
        tags: [],
        search: '',
    };
}
// ===== [NEW] LOGIC AI GENERATOR =====
const showAiModal = ref(false);
const aiStep = ref(1); // 1: Upload, 2: Review
const isGenerating = ref(false);

const aiForm = reactive({
    documents: [], // Đổi từ null sang mảng rỗng
    subject_id: null,
    tags: [],
    number_of_questions: 5,
    custom_instructions: '', // <-- Trường mới
});

const generatedQuestions = ref([]); // Chứa danh sách câu hỏi AI trả về

// Hàm xử lý chọn file
const handleFileUpload = (event) => {
    // Chuyển FileList thành Array
    aiForm.documents = Array.from(event.target.files);
};

// Gửi file lên server để AI xử lý
const generateQuestions = async () => {
    // 1. Kiểm tra dữ liệu đầu vào
    if (aiForm.documents.length === 0 || !aiForm.subject_id) {
<<<<<<< Updated upstream
        alert('Vui lòng chọn ít nhất 1 file và môn học!');
=======
        alert('Vui lòng chọn Môn học và Tải lên ít nhất 1 tài liệu.');
>>>>>>> Stashed changes
        return;
    }

    isGenerating.value = true;
    const formData = new FormData();
<<<<<<< Updated upstream
    
    // Duyệt mảng file và append từng cái vào formData
    aiForm.documents.forEach((file, index) => {
        formData.append(`documents[${index}]`, file);
    });
    
    formData.append('number_of_questions', aiForm.number_of_questions);
    formData.append('subject_id', aiForm.subject_id); // Gửi thêm cái này nếu cần validate backend chặt chẽ
    
    // Gửi yêu cầu riêng
=======

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
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
        alert('Lỗi: ' + (error.response?.data?.error || 'Không thể tạo câu hỏi.'));
=======
        // Hiển thị lỗi chi tiết từ Backend trả về
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            // Lấy lỗi đầu tiên để hiển thị alert
            const firstError = Object.values(errors)[0][0];
            alert('Lỗi dữ liệu: ' + firstError);
        } else {
            alert('Lỗi hệ thống: ' + (error.response?.data?.message || 'Không thể tạo câu hỏi.'));
        }
>>>>>>> Stashed changes
    } finally {
        isGenerating.value = false;
    }
};

// Lưu câu hỏi sau khi duyệt
const saveBulkForm = useForm({
    questions: [],
    subject_id: null,
    tags: []
});

const saveGeneratedQuestions = () => {
    saveBulkForm.questions = generatedQuestions.value;
    saveBulkForm.subject_id = aiForm.subject_id;
    saveBulkForm.tags = aiForm.tags.map(t => t.id); // Lấy ID của tags

    saveBulkForm.post(route('questions.store-bulk'), {
        onSuccess: () => {
            showAiModal.value = false;
            aiStep.value = 1;
            generatedQuestions.value = [];
            aiForm.document = null;
        }
    });
};
</script>

<template>
    <AppLayout title="Ngân hàng câu hỏi">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ngân hàng câu hỏi của tôi
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap gap-4 items-center">
                    <Link :href="route('questions.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        + Tạo câu hỏi mới
                    </Link>
                    
                    <button 
                        @click="showAiModal = true"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 animate-pulse"
                    >
                        ✨ Tạo bằng AI (Gemini)
                    </button>
                    <Link :href="route('subjects.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">+ Quản lý Môn học</Link>
                    <Link :href="route('tags.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">+ Quản lý Thẻ (Tags)</Link>
                    <Link :href="route('questions.import.create')" class="inline-flex items-center px-4 py-2 bg-green-100 border border-green-300 rounded-md font-semibold text-xs text-green-700 uppercase tracking-widest shadow-sm hover:bg-green-200">+ Import từ File</Link>
                </div>

                <DialogModal :show="showAiModal" @close="showAiModal = false" maxWidth="4xl">
                    <template #title>
                        <span class="text-purple-700">✨ Tạo câu hỏi tự động với Gemini AI</span>
                    </template>

                    <template #content>
    <div v-if="aiStep === 1">
        <div class="space-y-4">
            <div>
                <InputLabel value="1. Chọn Môn học (Bắt buộc)" />
                <select v-model="aiForm.subject_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option :value="null">-- Chọn môn --</option>
                    <option v-for="sub in props.subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                </select>
            </div>

            <div>
                <InputLabel value="2. Gắn Thẻ (Tùy chọn)" />
                <VueMultiselect
                    v-model="aiForm.tags"
                    :options="props.tags"
                    track-by="id"
                    label="name"
                    :multiple="true"
                    placeholder="Chọn thẻ"
                    class="mt-1"
                />
            </div>

            <div>
                <InputLabel value="3. Tài liệu nguồn (Chọn nhiều file)" />
                <input 
                    type="file" 
                    multiple @change="handleFileUpload" 
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100" 
                />
                <p class="text-xs text-gray-500 mt-1">
                    Hỗ trợ PDF, Word, TXT (Max 10MB). 
                    <span v-if="aiForm.documents.length > 0" class="font-bold text-green-600">
                        Đã chọn {{ aiForm.documents.length }} file.
                    </span>
                </p>
            </div>
            
            <div>
                <InputLabel value="4. Yêu cầu riêng cho AI (Tùy chọn)" />
                <textarea 
                    v-model="aiForm.custom_instructions" 
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 text-sm"
                    rows="2"
                    placeholder="Ví dụ: Chỉ lấy kiến thức chương 1, tạo câu hỏi khó, tránh hỏi về ngày tháng..."
                ></textarea>
            </div>

            <div>
                <InputLabel value="5. Số lượng câu hỏi muốn tạo" />
                <input type="number" v-model="aiForm.number_of_questions" min="1" max="20" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20 mt-1" />
            </div>
        </div>
        
        <div v-if="isGenerating" class="mt-6 text-center">
            <svg class="animate-spin h-8 w-8 text-purple-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-600 font-medium">
                Gemini đang đọc {{ aiForm.documents.length }} tài liệu và suy nghĩ... (Có thể mất 10-20s)
            </span>
        </div>
    </div>

    <div v-else>
        <div class="mb-4 bg-green-50 p-3 rounded-md border border-green-200 text-green-800">
            ✅ AI đã tạo xong! Vui lòng kiểm tra kỹ nội dung trước khi lưu.
        </div>
        <div class="max-h-96 overflow-y-auto space-y-6 pr-2">
            <div v-for="(q, index) in generatedQuestions" :key="index" class="border p-4 rounded-lg bg-gray-50 relative">
                <button @click="generatedQuestions.splice(index, 1)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-xs font-bold">XÓA</button>
                
                <label class="block text-xs font-bold text-gray-700 mb-1">Câu hỏi {{ index + 1 }}:</label>
                <textarea v-model="q.question_text" class="w-full border-gray-300 rounded-md shadow-sm text-sm" rows="2"></textarea>
                
                <div class="mt-2 space-y-2">
                    <div v-for="(opt, optIndex) in q.options" :key="optIndex" class="flex items-center gap-2">
                        <input type="radio" :name="'correct_' + index" :checked="opt.is_correct" @change="() => { q.options.forEach(o => o.is_correct = false); opt.is_correct = true; }" />
                        <input type="text" v-model="opt.text" class="flex-1 border-gray-300 rounded-md shadow-sm text-xs py-1" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

                    <template #footer>
                        <SecondaryButton @click="showAiModal = false" :disabled="isGenerating || saveBulkForm.processing">
                            Hủy
                        </SecondaryButton>

                        <PrimaryButton 
                            v-if="aiStep === 1" 
                            class="ml-3 bg-purple-600 hover:bg-purple-700" 
                            @click="generateQuestions"
                            :disabled="isGenerating"
                        >
                            {{ isGenerating ? 'Đang xử lý...' : 'Tạo câu hỏi' }}
                        </PrimaryButton>

                        <PrimaryButton 
                            v-if="aiStep === 2" 
                            class="ml-3" 
                            @click="saveGeneratedQuestions"
                            :disabled="saveBulkForm.processing"
                        >
                            {{ saveBulkForm.processing ? 'Đang lưu...' : 'Lưu tất cả vào Ngân hàng' }}
                        </PrimaryButton>
                    </template>
                </DialogModal>
                <div class="bg-white p-4 shadow-md rounded-lg mb-6">
                   <h3 class="text-lg font-semibold mb-3">Tìm & Lọc</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Môn học</label>
                            <VueMultiselect
                                v-model="filterForm.subject"
                                :options="props.subjects.map(s => s.id)"
                                :custom-label="id => props.subjects.find(s => s.id === id)?.name"
                                placeholder="Chọn môn học"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Thẻ</label>
                            <VueMultiselect
                                v-model="filterForm.tags"
                                :options="props.tags.map(t => t.id)"
                                :custom-label="id => props.tags.find(t => t.id === id)?.name"
                                :multiple="true"
                                placeholder="Chọn thẻ"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nội dung</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Tìm theo nội dung câu hỏi..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            />
                        </div>
                    </div>
                    <button @click="resetFilters" class="mt-4 text-sm text-blue-600 hover:text-blue-800">
                        Xóa bộ lọc
                    </button>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <li v-if="questions.data.length === 0" class="p-6 text-gray-500 text-center">
                            Không tìm thấy câu hỏi nào.
                        </li>
                        
                        <li v-for="question in questions.data" :key="question.id" class="p-6">
                            <div class="mb-2 flex flex-wrap gap-2 items-center">
                                <span v-if="question.subject" class="px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                    {{ question.subject.name }}
                                </span>
                                <span v-for="tag in question.tags" :key="tag.id" class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">
                                    {{ tag.name }}
                                </span>
                            </div>

<<<<<<< Updated upstream
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ question.question_text }}</p>
                                    <img 
                                        v-if="question.image_path" 
                                        :src="'/storage/' + question.image_path" 
                                        class="mt-2 w-full max-w-xs rounded-md border"
                                    >
                                    <ul class="mt-2 list-disc list-inside">
                                        <li
                                            v-for="option in question.options" :key="option.id"
                                            :class="{ 'font-bold text-green-600': option.is_correct }"
                                        >
                                            {{ option.option_text }}
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="flex-shrink-0 ml-4">
                                    <Link
                                        :href="route('questions.edit', question.id)"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        Sửa
                                    </Link>
                                    <button
                                        @click="deleteQuestion(question.id)"
                                        class="ml-4 text-sm font-medium text-red-600 hover:text-red-900"
                                    >
                                        Xóa
                                    </button>
=======
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
>>>>>>> Stashed changes
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <Pagination :links="questions.links" class="mt-6" />
            </div>
        </div>
    </AppLayout>
</template>