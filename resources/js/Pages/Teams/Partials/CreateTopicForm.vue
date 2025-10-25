<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue'; // Giả sử bạn có component này
import TextArea from '@/Components/TextArea.vue';   // Component bạn đã sửa ở các bước trước

const props = defineProps({
    team: Object,
});

const form = useForm({
    name: '',
    description: '',
});

const createTopic = () => {
    form.post(route('topics.store', props.team), {
        errorBag: 'createTopic',
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <FormSection @submitted="createTopic">
        <template #title>
            Tạo Chủ đề mới
        </template>

        <template #description>
            Tạo một kênh thảo luận (forum topic) mới cho các thành viên trong lớp.
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <label for="name">Tên Chủ đề</label>
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <label for="description">Mô tả (Không bắt buộc)</label>
                <TextArea
                    id="description"
                    v-model="form.description"
                    class="mt-1 block w-full"
                    rows="3"
                />
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Đã tạo.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Tạo
            </PrimaryButton>
        </template>
    </FormSection>
</template>