<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
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
});

// (Các hàm addOption, removeOption G-IỐNG-HỆT file cũ)
function addOption() { form.options.push({ text: '' }); }
function removeOption(index) { if (form.options.length > 2) { form.options.splice(index, 1); } }

function submitForm() {
    // Chuyển đổi object thành ID trước khi gửi
    const payload = {
        ...form.data(),
        subject_id: form.subject_id ? form.subject_id.id : null,
        tags: form.tags.map(tag => tag.id),
    };

    useForm(payload).put(route('questions.update', props.question.id));
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
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Cập nhật</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>