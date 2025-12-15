<script setup>
import { useForm } from '@inertiajs/vue3';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    postId: Number,
    parentId: { type: Number, default: null },
    onSuccess: { type: Function, default: () => {} },
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
            props.onSuccess();
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitComment" class="relative">
        <TextArea
            v-model="form.body"
            rows="1"
            class="w-full bg-slate-900 border-slate-700 text-white text-sm focus:border-cyan-500 focus:ring-cyan-500/20 rounded-lg pr-12 min-h-[40px] py-2"
            :placeholder="parentId ? 'Trả lời phản hồi...' : 'Trả lời bình luận...'"
            required
        />
        <button 
            type="submit" 
            :disabled="form.processing"
            class="absolute right-1 top-1 bottom-1 px-3 bg-cyan-600 hover:bg-cyan-500 text-white rounded-md flex items-center justify-center transition disabled:opacity-50"
        >
            <svg class="w-4 h-4 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
        </button>
        <InputError :message="form.errors.body" class="mt-1" />
    </form>
</template>