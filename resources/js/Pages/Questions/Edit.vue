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

const correctIndex = props.question.options.findIndex(option => option.is_correct);

const form = useForm({
    question_text: props.question.question_text,
    options: props.question.options.map(option => ({ text: option.option_text })),
    correct_option: correctIndex,
    // Pre-fill Subject và Tags (đây là các object)
    subject_id: props.question.subject, 
    tags: props.question.tags,
    image: null, // Thêm trường này để chứa file ảnh mới nếu người dùng chọn
});

<<<<<<< Updated upstream
// (Các hàm addOption, removeOption G-IỐNG-HỆT file cũ)
=======
function getImagePreview(file) {
    return file ? URL.createObjectURL(file) : null;
}

>>>>>>> Stashed changes
function addOption() { form.options.push({ text: '' }); }
function removeOption(index) { if (form.options.length > 2) { form.options.splice(index, 1); } }

function submitForm() {
<<<<<<< Updated upstream
    // Chuyển đổi object thành ID trước khi gửi
=======
    const optionTexts = form.options.map(o => o.text.trim());
    const uniqueTexts = new Set(optionTexts);
    
    if (uniqueTexts.size !== optionTexts.length) {
        form.setError('options', 'Các đáp án không được giống nhau.');
        return;
    }
>>>>>>> Stashed changes
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
    <AppLayout title="Sửa Câu hỏi">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chỉnh sửa câu hỏi
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submitForm"> 
                        
                        <div class="mb-6">
                            <InputLabel for="subject" value="Môn học" class="font-bold" />
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

                        <div class="mb-6">
                            <InputLabel for="tags" value="Thẻ (Phân loại)" class="font-bold" />
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

<<<<<<< Updated upstream
                        <div class="mt-6">
                            <InputLabel for="question_text" value="Nội dung câu hỏi" />
                            <textarea id="question_text" v-model="form.question_text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" rows="4"></textarea>
                            <InputError :message="form.errors.question_text" class="mt-2" />
=======
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
>>>>>>> Stashed changes
                        </div>
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
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Cập nhật</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>