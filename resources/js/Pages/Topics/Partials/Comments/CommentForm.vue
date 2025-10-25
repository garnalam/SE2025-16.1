<script setup>
import { useForm } from '@inertiajs/vue3';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    postId: Number,
    parentId: { // Dùng cho replies, nếu không có thì là bình luận gốc
        type: Number,
        default: null,
    },
    // Hàm này sẽ được gọi khi submit thành công (để đóng form reply)
    onSuccess: {
        type: Function,
        default: () => {},
    },
});

const form = useForm({
    body: '',
    post_id: props.postId,
    parent_id: props.parentId,
});

const submitComment = () => {
    form.post(route('comments.store', props.postId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            props.onSuccess(); // Gọi hàm callback (nếu có)
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitComment">
        <div class="mt-2">
            <TextArea
                v-model="form.body"
                rows="2"
                class="w-full"
                :placeholder="parentId ? 'Viết trả lời...' : 'Viết bình luận...'"
                required
            />
            <InputError :message="form.errors.body" class="mt-1" />
        </div>
        <div class="mt-2 flex justify-end">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Gửi
            </PrimaryButton>
        </div>
    </form>
</template>