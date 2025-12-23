<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link, router } from '@inertiajs/vue3'; // Thêm router
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VueMultiselect from 'vue-multiselect';

const props = defineProps({
    question: Object,
    subjects: Array,
    tags: Array,
});

// --- SỬA ĐOẠN NÀY ---
// 1. Tạo biến an toàn cho options, nếu không có thì gán mảng rỗng []
const questionOptions = props.question.options || []; 

// 2. Dùng biến an toàn đó để tìm index
const correctIndex = questionOptions.findIndex(option => option.is_correct);

const form = useForm({
    question_text: props.question.question_text,
    // 3. Dùng biến an toàn đó để map dữ liệu
    options: questionOptions.length > 0 
        ? questionOptions.map(option => ({ text: option.option_text })) 
        : [{ text: '' }, { text: '' }], // Nếu chưa có option nào, tạo sẵn 2 ô trống
    correct_option: correctIndex,
    subject_id: props.question.subject, 
    tags: props.question.tags,
    image: null, // Thêm trường này để chứa file ảnh mới nếu người dùng chọn
});

function getImagePreview(file) {
    return file ? URL.createObjectURL(file) : null;
}

function addOption() { form.options.push({ text: '' }); }
function removeOption(index) { if (form.options.length > 2) { form.options.splice(index, 1); } }

function submitForm() {
    const optionTexts = form.options.map(o => o.text.trim());
    const uniqueTexts = new Set(optionTexts);
    
    if (uniqueTexts.size !== optionTexts.length) {
        form.setError('options', 'Các đáp án không được giống nhau.');
        return;
    }
    const payload = {
        _method: 'PUT', 
        question_text: form.question_text,
        correct_option: form.correct_option,
        subject_id: form.subject_id ? form.subject_id.id : null,
        tags: form.tags.map(tag => tag.id),
        options: form.options,
        image: form.image, // File ảnh mới (nếu có)
    };

    router.post(route('questions.update', props.question.id), payload, {
        forceFormData: true, // Crucial for file uploads
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Handle success
        },
        onError: (errors) => {
            // Map errors back to the form object if needed for display
            form.errors = errors;
        }
    });

}
</script>

<template>
    <AppLayout title="Chỉnh sửa câu hỏi">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('questions.index')" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                    Chỉnh sửa câu hỏi: <span class="text-cyan-400">{{ question.id }}</span>
                </h2>
            </div>
        </template>

        <div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0f172a] border border-indigo-500/30 rounded-3xl p-8 shadow-[0_0_50px_rgba(99,102,241,0.1)] relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <form @submit.prevent="submitForm" class="space-y-6 relative z-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Môn học" />
                            <VueMultiselect
                                v-model="form.subject_id"
                                :options="props.subjects"
                                label="name"
                                track-by="id"
                                placeholder="Chọn môn học"
                                class="custom-multiselect mt-1"
                            />
                            <InputError :message="form.errors.subject_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Nhãn" />
                            <VueMultiselect
                                v-model="form.tags"
                                :options="props.tags"
                                :multiple="true"
                                label="name"
                                track-by="id"
                                placeholder="Chọn nhãn"
                                class="custom-multiselect mt-1"
                            />
                            <InputError :message="form.errors.tags" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <InputLabel value="Ảnh minh họa (Tùy chọn)" />
                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-700 border-dashed rounded-xl hover:border-indigo-500 transition-colors bg-slate-900/50 group">
                            <div class="space-y-1 text-center">
                                
                                <svg v-if="!question.image_url && !form.image" class="mx-auto h-12 w-12 text-slate-500 group-hover:text-indigo-400 transition" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>

                                <img v-else-if="form.image" :src="getImagePreview(form.image)" class="mx-auto h-40 rounded object-contain border border-indigo-500/50">
                                
                                <img v-else :src="question.image_url" class="mx-auto h-40 rounded object-contain border border-slate-600">
                                
                                <div class="flex text-sm text-slate-400 justify-center mt-2">
                                    <label class="relative cursor-pointer rounded-md font-medium text-indigo-400 hover:text-indigo-300 focus-within:outline-none">
                                        <span>{{ form.image ? 'Chọn ảnh khác' : (question.image_url ? 'Thay đổi ảnh' : 'Tải ảnh lên') }}</span>
                                        <input type="file" @change="form.image = $event.target.files[0]" accept="image/png, image/jpeg" class="sr-only" />
                                    </label>
                                </div>
                                <p v-if="question.image_url && !form.image" class="text-[10px] text-slate-500">Ảnh hiện tại đang được sử dụng</p>
                            </div>
                        </div>
                        <InputError :message="form.errors.image" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Nội dung câu hỏi" />
                        <textarea v-model="form.question_text" class="w-full bg-slate-900 border-slate-700 text-white rounded-xl text-sm font-mono mt-1 focus:border-indigo-500 focus:ring-indigo-500/20 p-4 h-32"></textarea>
                        <InputError :message="form.errors.question_text" class="mt-2" />
                    </div>

                    <div class="bg-slate-900/50 p-6 rounded-xl border border-white/5">
                        <div class="flex justify-between items-center mb-4">
                            <InputLabel value="Tạo phương án" />
                            <button type="button" @click="addOption" class="text-[10px] font-bold text-indigo-400 hover:text-white uppercase tracking-wider transition border border-indigo-500/30 px-2 py-1 rounded bg-indigo-900/20 hover:bg-indigo-600">
                                + Thêm phương án
                            </button>
                        </div>
                        
                        <div class="space-y-3">
    <div v-for="(option, index) in form.options" :key="index" class="flex items-center gap-3 group">
        
        <label class="relative cursor-pointer">
            <input type="radio" :value="index" v-model="form.correct_option" class="peer sr-only" />
            
            <div class="w-6 h-6 rounded-full border-2 border-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition flex items-center justify-center hover:border-emerald-500/50">
                <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </label>
        <TextInput v-model="option.text" type="text" class="flex-1 text-sm bg-slate-950" :placeholder="'Phương án ' + (index + 1)" />
        
        <button type="button" @click="removeOption(index)" class="text-slate-600 hover:text-rose-500 transition p-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
</div>
                        <InputError :message="form.errors.options" class="mt-2" />
                        <InputError :message="form.errors.correct_option" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-white/10">
                        <Link :href="route('questions.index')" class="text-xs font-bold text-slate-500 hover:text-white uppercase tracking-widest mr-6 transition">Hủy</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"></PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Reuse styles from Index/Create */
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
    background-color: #6366f1; /* Indigo */
    color: white;
}
.custom-multiselect .multiselect__option--selected {
    background-color: #334155;
    color: white;
}
.custom-multiselect .multiselect__tag {
    background-color: #6366f1;
    color: white;
    font-weight: bold;
}
.custom-multiselect .multiselect__placeholder {
    color: #64748b;
}
</style>