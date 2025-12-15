<script setup>
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
});

const createTeam = () => {
    form.post(route('teams.store'), {
        errorBag: 'createTeam',
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="createTeam">
        <template #title>
            <span class="text-white font-exo uppercase tracking-wide">Mô tả lớp học</span>
        </template>

        <template #description>
            <span class="text-slate-400">Thêm thông tin cho lớp học của bạn</span>
        </template>

        <template #form>
            <div class="col-span-6">
                <InputLabel value="Người sáng lập" />

                <div class="flex items-center mt-2 p-3 rounded-xl bg-slate-800/50 border border-white/5">
                    <img class="object-cover size-12 rounded-lg border border-indigo-500/30" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">

                    <div class="ms-4 leading-tight">
                        <div class="text-white font-bold font-exo">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm text-slate-400 font-mono">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Tên lớp học" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full mt-1 bg-slate-900 border-slate-700 text-white placeholder-slate-600 focus:ring-cyan-500 focus:border-cyan-500"
                    placeholder="ví dụ : Lớp Toán 10A1"
                    autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Tạo lớp học
            </PrimaryButton>
        </template>
    </FormSection>
</template>