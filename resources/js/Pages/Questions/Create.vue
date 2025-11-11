<script setup>
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
}
</script>

<template>
    <AppLayout title="Tạo Câu hỏi">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tạo câu hỏi mới
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
                        <div class="mb-6">
                            <InputLabel for="image" value="Ảnh đính kèm (Tùy chọn)" />
                            <input 
                                type="file" 
                                @input="form.image = $event.target.files[0]"
                                accept="image/png, image/jpeg, image/jpg"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <InputError :message="form.errors.image" class="mt-2" />

                            <div v-if="form.image" class="mt-2">
                                <img :src="URL.createObjectURL(form.image)" class="w-48 h-auto rounded-md border">
                            </div>
                        </div>

                        <div class="mt-6">
                            <InputLabel for="question_text" value="Nội dung câu hỏi" />
                            <textarea id="question_text" v-model="form.question_text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" rows="4"></textarea>
                            <InputError :message="form.errors.question_text" class="mt-2" />
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
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Lưu câu hỏi</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>