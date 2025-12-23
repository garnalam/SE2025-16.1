<script setup>
import { useForm } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    post: Object,
});

const emit = defineEmits(['close']);
const form = useForm({ title: '', content: '' });

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
            <span class="text-white font-exo uppercase tracking-wide">Chỉnh sửa bài viết</span>
        </template>

        <template #content>
            <form @submit.prevent="submitUpdate" id="edit-post-form" class="space-y-4">
                <div v-if="post && post.post_type === 'assignment'">
                    <InputLabel for="edit_title" value="Tiêu đề bài viết" />
                    <TextInput id="edit_title" v-model="form.title" type="text" class="mt-1 block w-full bg-slate-900 border-slate-700 text-white" required />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div>
                    <InputLabel for="edit_content" value="Nội dung bài viết" />
                    <TextArea id="edit_content" v-model="form.content" class="mt-1 block w-full h-40 bg-slate-900 border-slate-700 text-white font-sans text-sm" required />
                    <InputError class="mt-2" :message="form.errors.content" />
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">Đóng</SecondaryButton>
            <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" form="edit-post-form" type="submit">
                Xác nhận thay đổi
            </PrimaryButton>
        </template>
    </DialogModal>
</template>