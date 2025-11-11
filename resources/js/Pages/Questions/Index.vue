<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import VueMultiselect from 'vue-multiselect'; // <-- 1. Import thư viện
import Pagination from '@/Components/Pagination.vue'; // (Giả sử bạn có component này)

// 2. Nhận props mới từ controller
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
                    <Link
                        :href="route('questions.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        + Tạo câu hỏi mới
                    </Link>

                    <Link
                        :href="route('subjects.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                    >
                        + Quản lý Môn học
                    </Link>
                    <Link
                        :href="route('tags.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50"
                    >
                        + Quản lý Thẻ (Tags)
                    </Link>
                    <Link
                        :href="route('questions.import.create')"
                        class="inline-flex items-center px-4 py-2 bg-green-100 border border-green-300 rounded-md font-semibold text-xs text-green-700 uppercase tracking-widest shadow-sm hover:bg-green-200"
                    >
                        + Import từ File
                    </Link>
                </div>

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