<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    team: Object,
});

const form = useForm({
    content: '',
});

const createPost = () => {
    form.post(route('posts.store', props.team), {
        errorBag: 'createPost',
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <FormSection @submitted="createPost">
        <template #title>
            Đăng bài mới
        </template>

        <template #description>
            Tạo một thông báo hoặc bài giảng mới cho tất cả thành viên trong lớp.
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <label for="content">Nội dung</label>
                <TextArea
                    id="content"
                    v-model="form.content"
                    class="mt-1 block w-full"
                    rows="5"
                />
                <InputError :message="form.errors.content" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Đã đăng.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Đăng bài
            </PrimaryButton>
        </template>
    </FormSection>
</template>