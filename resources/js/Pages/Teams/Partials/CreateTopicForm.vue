<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({ team: Object });

const form = useForm({ name: '', description: '' });

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
            <span class="text-white font-exo uppercase tracking-wide">Tạo Topic Cho Lớp Học</span>
        </template>
        <template #description>
            <span class="text-slate-400">Tạo ra các Topic theo chủ đề để quản lý lớp học</span>
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Tên Topic" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full bg-slate-900 border-slate-700 text-white placeholder-slate-600 focus:ring-cyan-500 focus:border-cyan-500"
                    placeholder="ví dụ : Thông Báo Chung"
                    autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="description" value="Mô tả Topic (Không bắt buộc)" />
                <TextArea
                    id="description"
                    v-model="form.description"
                    class="mt-1 block w-full bg-slate-900 border-slate-700 text-white placeholder-slate-600 focus:ring-cyan-500 focus:border-cyan-500"
                    rows="3"
                />
                <InputError :message="form.errors.description" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3 text-emerald-400">
                CHANNEL ESTABLISHED.
            </ActionMessage>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Initialize
            </PrimaryButton>
        </template>
    </FormSection>
</template>