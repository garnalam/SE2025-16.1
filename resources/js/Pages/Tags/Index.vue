<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    tags: Array,
});

const createForm = useForm({
    name: '',
});

const updateForm = useForm({
    name: '',
});

// Dùng ref để theo dõi ID của thẻ đang được sửa
const editingId = ref(null);

// Hàm để vào chế độ Sửa
const editTag = (tag) => {
    editingId.value = tag.id;
    updateForm.name = tag.name;
};

// Hàm để Hủy Sửa
const cancelEdit = () => {
    editingId.value = null;
    updateForm.reset();
};

// Hàm gửi form Tạo mới
const submitCreate = () => {
    createForm.post(route('tags.store'), {
        onSuccess: () => createForm.reset(),
        preserveScroll: true,
    });
};

// Hàm gửi form Cập nhật
const submitUpdate = (id) => {
    updateForm.put(route('tags.update', id), {
        onSuccess: () => cancelEdit(),
        preserveScroll: true,
    });
};

// Hàm Xóa
const deleteTag = (id) => {
    if (confirm('Bạn có chắc muốn xóa thẻ này? Nó sẽ bị gỡ khỏi tất cả các câu hỏi.')) {
        useForm({}).delete(route('tags.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Quản lý Thẻ">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quản lý Thẻ (Tags)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="p-6 bg-white shadow-xl sm:rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Tạo Thẻ mới</h3>
                    <form @submit.prevent="submitCreate" class="flex space-x-4">
                        <div class="flex-1">
                            <TextInput
                                v-model="createForm.name"
                                type="text"
                                class="w-full"
                                placeholder="Tên thẻ (ví dụ: Giữa kì, Chương 1, Dễ)"
                            />
                            <InputError :message="createForm.errors.name" class="mt-2" />
                        </div>
                        <PrimaryButton :disabled="createForm.processing">
                            Tạo mới
                        </PrimaryButton>
                    </form>
                </div>

                <div class="p-6 bg-white shadow-xl sm:rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Danh sách Thẻ</h3>
                    <ul class="divide-y divide-gray-200">
                        <li v-if="tags.length === 0" class="py-3 text-gray-500">
                            Bạn chưa tạo thẻ nào.
                        </li>
                        
                        <li v-for="tag in tags" :key="tag.id" class="py-3">
                            
                            <div v-if="editingId !== tag.id" class="flex justify-between items-center">
                                <span class="text-gray-900">{{ tag.name }}</span>
                                <div class="space-x-2">
                                    <button @click="editTag(tag)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Sửa</button>
                                    <button @click="deleteTag(tag.id)" class="text-sm font-medium text-red-600 hover:text-red-900">Xóa</button>
                                </div>
                            </div>
                            
                            <form v-else @submit.prevent="submitUpdate(tag.id)" class="space-y-4">
                                <TextInput
                                    v-model="updateForm.name"
                                    type="text"
                                    class="w-full"
                                />
                                <InputError :message="updateForm.errors.name" class="mt-2" />
                                <div class="flex justify-end space-x-2">
                                    <SecondaryButton @click="cancelEdit">Hủy</SecondaryButton>
                                    <PrimaryButton :disabled="updateForm.processing">Lưu</PrimaryButton>
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>