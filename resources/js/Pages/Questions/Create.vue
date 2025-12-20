<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VueMultiselect from 'vue-multiselect';

// Controller đã gửi 'subjects' và 'tags'
const props = defineProps({
    subjects: Array,
    tags: Array,
});

const submit = () => {
    // --- Duplicate Check ---
    const optionTexts = form.options.map(o => o.text.trim());
    const uniqueTexts = new Set(optionTexts);
    
    if (uniqueTexts.size !== optionTexts.length) {
        form.setError('options', 'Các đáp án không được giống nhau.');
        return;
    }
    // -----------------------

    form.transform((data) => ({
        ...data,
        tags: data.tags.map(t => t.id),
        subject_id: data.subject_id?.id
    })).post(route('questions.store'), {
        forceFormData: true
    });
};

const form = useForm({
    question_text: '',
    options: [ { text: '' }, { text: '' }, { text: '' }, { text: '' } ],
    correct_option: null, 
    subject_id: null, // Sẽ là ID
    tags: [],         // Sẽ là mảng các ID
    image: null,
});

// (Các hàm addOption, removeOption G-IỐNG-HỆT file cũ)
function addOption() { form.options.push({ text: '' }); }
function removeOption(index) { if (form.options.length > 2) { form.options.splice(index, 1); } }

<<<<<<< Updated upstream
// Hàm submit
function submitForm() {
    // Tạo một payload thủ công
    const payload = {
        question_text: form.question_text,
        options: form.options,
        correct_option: form.correct_option,
        image: form.image, // Đây là File object

        // Chuyển đổi object thành ID (cho backend)
        subject_id: form.subject_id ? form.subject_id.id : null,
        tags: form.tags.map(tag => tag.id),
    };

    // Gửi thủ công bằng router.post
    // Inertia sẽ tự động xử lý 'multipart/form-data'
    router.post(route('questions.store'), payload, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Reset VueMultiselect về rỗng
            form.subject_id = null;
            form.tags = [];
        },
        // Hiển thị lỗi validation (nếu có)
        onError: (errors) => {
            form.errors = errors;
        },
    });
=======
// --- FIX: Helper method to safely create object URL ---
function getImagePreview(file) {
    return file ? URL.createObjectURL(file) : null;
>>>>>>> Stashed changes
}
</script>

<template>
    <AppLayout title="Tạo Câu hỏi">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tạo câu hỏi mới
            </h2>
        </template>
<<<<<<< Updated upstream
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-6">
                            <InputLabel for="subject" value="Môn học" class="font-bold" />
=======

        <div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0f172a] border border-cyan-500/30 rounded-3xl p-8 shadow-[0_0_50px_rgba(6,182,212,0.1)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-px h-full bg-gradient-to-b from-transparent via-cyan-500/50 to-transparent"></div>

                <form @submit.prevent="submit" class="space-y-6 relative z-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Môn học" />
>>>>>>> Stashed changes
                            <VueMultiselect
                                id="subject"
                                v-model="form.subject_id"
                                :options="props.subjects"
                                label="name"
                                track-by="id"
                                placeholder="Chọn 1 môn học"
                            />
                            <InputError :message="form.errors.subject_id" class="mt-2" />
                        </div>

<<<<<<< Updated upstream
                        <div class="mb-6">
                            <InputLabel for="tags" value="Thẻ (Phân loại)" class="font-bold" />
=======
                        <div>
                            <InputLabel value="Nhãn" />
>>>>>>> Stashed changes
                            <VueMultiselect
                                id="tags"
                                v-model="form.tags"
                                :options="props.tags"
                                :multiple="true"
                                label="name"
                                track-by="id"
                                placeholder="Chọn thẻ (vd: Giữa kì, Chương 1)"
                            />
                            <InputError :message="form.errors.tags" class="mt-2" />
                        </div>
                        <div class="mb-6">
                            <InputLabel for="image" value="Ảnh đính kèm (Tùy chọn)" />
                            <input 
                                type="file" 
                                @input="form.image = $event.target.files[0]"
                                accept="image/png, image/jpeg, image/jpg"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <InputError :message="form.errors.image" class="mt-2" />

<<<<<<< Updated upstream
                            <div v-if="form.image" class="mt-2">
                                <img :src="URL.createObjectURL(form.image)" class="w-48 h-auto rounded-md border">
=======
                    <div>
                        <InputLabel value="Ảnh minh họa câu hỏi (Không bắt buộc)" />
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-700 border-dashed rounded-xl hover:border-cyan-500 transition-colors bg-slate-900/50 group">
                            <div class="space-y-1 text-center">
                                <svg v-if="!form.image" class="mx-auto h-12 w-12 text-slate-500 group-hover:text-cyan-400 transition" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <img v-else :src="getImagePreview(form.image)" class="mx-auto h-32 rounded object-contain border border-cyan-500/50">
                                
                                <div class="flex text-sm text-slate-400 justify-center">
                                    <label class="relative cursor-pointer rounded-md font-medium text-cyan-400 hover:text-cyan-300 focus-within:outline-none">
                                        <span>{{ form.image ? 'Change File' : 'Upload Image' }}</span>
                                        <input type="file" @input="form.image = $event.target.files[0]" accept="image/png, image/jpeg" class="sr-only" />
                                    </label>
                                </div>
>>>>>>> Stashed changes
                            </div>
                        </div>

                        <div class="mt-6">
                            <InputLabel for="question_text" value="Nội dung câu hỏi" />
                            <textarea id="question_text" v-model="form.question_text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" rows="4"></textarea>
                            <InputError :message="form.errors.question_text" class="mt-2" />
                        </div>
<<<<<<< Updated upstream
                        <div class="mt-6">
                            <InputLabel value="Các lựa chọn trả lời" class="mb-2" />
                            <div v-for="(option, index) in form.options" :key="index" class="flex items-center space-x-2 mb-2">
                                <input type="radio" :value="index" v-model="form.correct_option" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300" />
                                <TextInput v-model="option.text" type="text" class="flex-1" :placeholder="'Lựa chọn ' + (index + 1)" />
                                <button type="button" @click="removeOption(index)" class="text-red-500 hover:text-red-700" title="Xóa lựa chọn">&times;</button>
                            </div>
                            <InputError :message="form.errors.options" class="mt-2" />
                            <InputError :message="form.errors.correct_option" class="mt-2" />
                        </div>
                        <div class="mt-4">
                             <button type="button" @click="addOption" class="text-sm text-blue-600 hover:text-blue-800">+ Thêm lựa chọn</button>
                        </div>
                        <div class="flex items-center justify-end mt-8">
                            <Link :href="route('questions.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Hủy</Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Lưu câu hỏi</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
=======
                        
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
                        <Link :href="route('questions.index')" class="text-xs font-bold text-slate-500 hover:text-white uppercase tracking-widest mr-6 transition">Abort</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Tạo câu hỏi</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Multiselect styling remains the same */
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
    background-color: #06b6d4;
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
.custom-multiselect .multiselect__placeholder {
    color: #64748b;
}
</style>
>>>>>>> Stashed changes
