<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VueMultiselect from 'vue-multiselect';

// Controller đã gửi subjects và tags
const props = defineProps({
    subjects: Array,
    tags: Array,
});

const form = useForm({
    file: null,
    subject_id: null,
    tags: [],
});

function submitForm() {
    // Chuyển đổi object (từ VueMultiselect) thành ID
    const payload = {
        ...form.data(),
        subject_id: form.subject_id ? form.subject_id.id : null,
        tags: form.tags.map(tag => tag.id),
    };

    // Gửi form (đã chuyển đổi)
    useForm(payload).post(route('questions.import.store'), {
        // Đặt lại form sau khi thành công
        onSuccess: () => form.reset('file'),
    });
}
</script>

<template>
    <AppLayout title="Import Câu hỏi">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Import Câu hỏi từ File
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">

                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-bold text-blue-800">Hướng dẫn Import</h4>
                        <ol class="list-decimal list-inside text-sm text-blue-700 mt-2 space-y-1">
                            <li>Chỉ hỗ trợ file Excel (.xlsx) hoặc .csv.</li>
                            <li>Vui lòng tải file mẫu để đảm bảo đúng định dạng cột.</li>
                            <li>Các câu hỏi import sẽ được tự động gán Môn học và Thẻ bạn chọn bên dưới.</li>
                        </ol>
                        <a 
                            :href="route('questions.import.template')" 
                            class="mt-4 inline-block text-sm font-bold text-green-600 hover:text-green-800"
                        >
                            ⬇️ Tải file Excel mẫu (.xlsx)
                        </a>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="mb-6">
                            <InputLabel for="subject" value="1. Chọn Môn học (Bắt buộc)" class="font-bold" />
                            <VueMultiselect
                                id="subject"
                                v-model="form.subject_id"
                                :options="props.subjects"
                                label="name" track-by="id"
                                placeholder="Chọn 1 môn học"
                            />
                            <InputError :message="form.errors.subject_id" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="tags" value="2. Chọn Thẻ (Tùy chọn)" class="font-bold" />
                            <VueMultiselect
                                id="tags" v-model="form.tags"
                                :options="props.tags"
                                :multiple="true"
                                label="name" track-by="id"
                                placeholder="Chọn 1 hoặc nhiều thẻ"
                            />
                            <InputError :message="form.errors.tags" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="file" value="3. Tải lên File của bạn" class="font-bold" />
                            <input 
                                type="file" 
                                @input="form.file = $event.target.files[0]" 
                                class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                            />
                            <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-2">
                                {{ form.progress.percentage }}%
                            </progress>
                            <InputError :message="form.errors.file" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <Link :href="route('questions.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Hủy
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Bắt đầu Import
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>