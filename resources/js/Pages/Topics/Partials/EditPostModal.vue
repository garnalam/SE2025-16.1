<script setup>
import { useForm } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue'; // Giả sử bạn có component này
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    post: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    content: '',
});

// Khi prop 'post' thay đổi (khi modal mở),
// hãy điền dữ liệu của post đó vào form
watch(() => props.post, (newPost) => {
    if (newPost) {
        form.title = newPost.title;
        form.content = newPost.content;
    }
});

const submitUpdate = () => {
    if (!props.post) return;

    form.put(route('posts.update', props.post.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => { /* Lỗi sẽ tự hiển thị */ },
    });
};

const closeModal = () => {
    emit('close');
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <DialogModal :show="show" @close="closeModal">
        <template #title>
            Sửa bài đăng
        </template>

        <template #content>
            <form @submit.prevent="submitUpdate" id="edit-post-form">
                <div class="space-y-4">
                    
                    <!-- Chỉ hiển thị Title nếu là Assignment -->
                    <div v-if="post && post.post_type === 'assignment'">
                        <InputLabel for="edit_title" value="Tiêu đề Bài tập" />
                        <TextInput
                            id="edit_title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="edit_content" value="Nội dung" />
                        <!-- 
                          Bạn có thể thay <TextArea> bằng Tiptap/Editor
                          nếu bạn dùng trình soạn thảo văn bản
                        -->
                        <TextArea
                            id="edit_content"
                            v-model="form.content"
                            class="mt-1 block w-full h-40"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.content" />
                    </div>

                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Hủy bỏ
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                form="edit-post-form"
                type="submit"
            >
                Lưu thay đổi
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
