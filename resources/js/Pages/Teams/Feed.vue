<script setup>
import { ref, computed } from 'vue'; // <-- THÊM ref, computed
import { Link, useForm } from '@inertiajs/vue3'; // <-- THÊM useForm
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import CreateTopicForm from '@/Pages/Teams/Partials/CreateTopicForm.vue';

// IMPORT CHO MODAL SỬA/XÓA
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    team: Object,
    permissions: Object,
    topics: Array, 
});

// Quyền Giáo viên
const canCreateTopics = computed(() => props.permissions.canCreateTopics);

// === LOGIC CHO MODAL SỬA/XÓA ===
const confirmingTopicDeletion = ref(false);
const topicToUpdate = ref(null); // Lưu topic đang được SỬA
const topicToDelete = ref(null); // Lưu topic đang được XÓA

// Form Sửa (Update)
const updateTopicForm = useForm({
    name: '',
    description: '',
});

// Form Xóa (Delete)
const deleteTopicForm = useForm({});

// Mở Modal Sửa
const openUpdateModal = (topic) => {
    topicToUpdate.value = topic; // Đặt topic đang sửa
    updateTopicForm.name = topic.name;
    updateTopicForm.description = topic.description;
};

// Gửi form Sửa
const updateTopic = () => {
    updateTopicForm.put(route('topics.update', topicToUpdate.value), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

// Mở Modal Xác nhận Xóa
const openDeleteModal = (topic) => {
    topicToDelete.value = topic; // Đặt topic đang xóa
    confirmingTopicDeletion.value = true;
};

// Gửi form Xóa
const deleteTopic = () => {
    deleteTopicForm.delete(route('topics.destroy', topicToDelete.value), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

// Đóng cả 2 Modal
const closeModal = () => {
    topicToUpdate.value = null;
    topicToDelete.value = null;
    confirmingTopicDeletion.value = false;
    updateTopicForm.reset();
};
</script>

<template>
    <AppLayout :title="team.name">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ team.name }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                
                <div v-if="canCreateTopics">
                    <CreateTopicForm :team="team" />
                    <SectionBorder />
                </div>

                <div class="mt-10 sm:mt-0">
                    <h3 class="text-lg font-medium text-gray-900">
                        Diễn đàn lớp học
                    </h3>
                    
                    <div class="mt-4 space-y-4">
                        <div v-if="topics.length > 0">
                            
                            <div v-for="topic in topics" :key="topic.id" 
                                 class="bg-white shadow-sm rounded-lg p-4 flex justify-between items-start transition hover:bg-gray-50">
                                
                                <Link :href="route('topics.show', topic.id)" class="block flex-1 mr-4">
                                    <div class="flex items-center mb-2">
                                        <div> 
                                            <div class="font-medium text-lg text-gray-900">
                                                <span v-if="topic.is_locked" title="Đã khóa">🔒 </span>
                                                {{ topic.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Tạo bởi: {{ topic.user.name }}
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ topic.description }}</p>
                                </Link>

                                <div v-if="canCreateTopics" class="flex-shrink-0 space-x-2">
                                    <SecondaryButton @click="openUpdateModal(topic)">
                                        Sửa
                                    </SecondaryButton>
                                    <DangerButton @click="openDeleteModal(topic)">
                                        Xóa
                                    </DangerButton>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center text-gray-500 py-6">
                            Lớp học này chưa có chủ đề thảo luận nào. <br>
                            <span v-if="canCreateTopics">Hãy tạo chủ đề đầu tiên!</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <DialogModal :show="topicToUpdate != null" @close="closeModal">
            <template #title>
                Sửa Chủ đề
            </template>

            <template #content>
                <div class="col-span-6 sm:col-span-4">
                    <label for="name">Tên Chủ đề</label>
                    <TextInput
                        id="name"
                        v-model="updateTopicForm.name"
                        type="text"
                        class="mt-1 block w-full"
                        autofocus
                    />
                    <InputError :message="updateTopicForm.errors.name" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4 mt-4">
                    <label for="description">Mô tả</label>
                    <TextArea
                        id="description"
                        v-model="updateTopicForm.description"
                        class="mt-1 block w-full"
                        rows="3"
                    />
                    <InputError :message="updateTopicForm.errors.description" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Hủy
                </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': updateTopicForm.processing }"
                    :disabled="updateTopicForm.processing"
                    @click="updateTopic"
                >
                    Lưu
                </PrimaryButton>
            </template>
        </DialogModal>

        <DialogModal :show="confirmingTopicDeletion" @close="closeModal">
            <template #title>
                Xóa Chủ đề
            </template>

            <template #content>
                Bạn có chắc chắn muốn xóa chủ đề: "{{ topicToDelete ? topicToDelete.name : '' }}"?
                <br>
                Tất cả các bài đăng bên trong chủ đề này cũng sẽ bị xóa vĩnh viễn.
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Hủy
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': deleteTopicForm.processing }"
                    :disabled="deleteTopicForm.processing"
                    @click="deleteTopic"
                >
                    Xóa
                </DangerButton>
            </template>
        </DialogModal>

    </AppLayout>
</template>