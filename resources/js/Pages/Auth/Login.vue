<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <template #promo>
            <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white p-8 text-slate-700 shadow-[0_40px_140px_-90px_rgba(15,23,42,0.35)]">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-10 top-12 h-32 w-32 rounded-full bg-gradient-to-br from-sky-200 via-white to-transparent blur-3xl"></div>
                    <div class="absolute right-6 top-12 h-20 w-20 rounded-full bg-gradient-to-br from-indigo-200 via-cyan-100 to-transparent blur-3xl"></div>
                    <div class="absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent opacity-70"></div>
                </div>
                <div class="relative space-y-5">
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Lớp học tương tác</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Cổng truy cập của bạn</h2>
                    <p class="text-sm text-slate-600">
                        Bảng điều khiển tập trung, lịch học đồng bộ và thông báo tức thời giúp bạn kết nối với hành trình học tập chỉ trong vài giây.
                    </p>
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-sky-300"></span>
                            <span>Điểm danh realtime và nhắc việc tự động.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-sky-300"></span>
                            <span>Biểu đồ tiến độ cá nhân hóa.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-sky-300"></span>
                            <span>Diễn đàn tương tác đa chiều.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-600">
            {{ status }}
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="space-y-2">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold uppercase tracking-[0.32em] text-slate-500 transition hover:text-slate-800"
                    >
                        Quên mật khẩu?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between rounded-2xl border border-slate-200/70 bg-white px-5 py-4 text-sm text-slate-600 shadow-sm">
                <Checkbox v-model:checked="form.remember" name="remember">Ghi nhớ đăng nhập</Checkbox>
                <span class="text-xs text-slate-500">Bạn chỉ nên sử dụng trên thiết bị riêng tư.</span>
            </div>

            <PrimaryButton class="w-full justify-center" :class="{ 'opacity-60': form.processing }" :disabled="form.processing">
                Đăng nhập
            </PrimaryButton>
        </form>
    </AuthenticationCard>
</template>
