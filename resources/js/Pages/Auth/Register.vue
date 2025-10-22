<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const roles = [
    { key: 'teacher', label: 'Giáo viên', description: 'Tạo lớp, giao bài, chấm điểm và theo dõi tiến độ học sinh.' },
    { key: 'student', label: 'Học sinh', description: 'Nhận bài tập, theo dõi lịch học, tương tác cùng lớp.' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: roles[1].key,
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <template #promo>
            <div class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white p-8 text-slate-700 shadow-[0_40px_140px_-90px_rgba(15,23,42,0.35)]">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-14 top-16 h-40 w-40 rounded-full bg-gradient-to-br from-violet-200 via-sky-100 to-transparent blur-3xl"></div>
                    <div class="absolute right-0 top-8 h-24 w-24 rounded-full bg-gradient-to-br from-sky-200 via-indigo-100 to-transparent blur-3xl"></div>
                    <div class="absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent opacity-70"></div>
                </div>
                <div class="relative space-y-5">
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-500">Chào người mới</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Tạo tài khoản dễ dàng</h2>
                    <p class="text-sm text-slate-600">
                        Khởi động hành trình cùng “Lớp học tương tác” với bảng điều khiển đa vai trò, học liệu phong phú và phân tích sâu sát được tinh chỉnh cho từng người dùng.
                    </p>
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-violet-300"></span>
                            <span>Đồng bộ lịch học và nhắc nhở thông minh.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-violet-300"></span>
                            <span>Kho mẫu timeline, rubrics, diễn đàn sẵn dùng.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 size-1.5 rounded-full bg-violet-300"></span>
                            <span>Phân quyền linh hoạt cho giáo viên, học sinh, phụ huynh.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="space-y-2">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Họ và tên"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3 md:space-y-2">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Tối thiểu 8 ký tự"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="space-y-3 md:space-y-2">
                    <InputLabel for="password_confirmation" value="Confirm Password" />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Nhập lại mật khẩu"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-300 bg-white px-6 py-5 text-sm text-slate-700 shadow-md">
                    <InputLabel for="role">Bạn là</InputLabel>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <button
                            v-for="option in roles"
                            :key="option.key"
                            type="button"
                            class="group flex w-full flex-col items-start gap-2 rounded-2xl border px-4 py-4 text-left transition"
                            :class="form.role === option.key ? 'border-sky-500 bg-sky-50 shadow-[0_20px_60px_-35px_rgba(14,165,233,0.6)]' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                            @click="form.role = option.key"
                        >
                            <span class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                <span
                                    class="flex size-5 items-center justify-center rounded-full border"
                                    :class="form.role === option.key ? 'border-sky-500 bg-sky-500 text-white' : 'border-slate-300 bg-white text-slate-400'"
                                >
                                    <svg v-if="form.role === option.key" class="size-3" viewBox="0 0 20 20" fill="none">
                                        <path d="M5 10.5L8.5 14L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                {{ option.label }}
                            </span>
                            <span class="text-xs text-slate-500">{{ option.description }}</span>
                        </button>
                    </div>
                    <InputError class="mt-3" :message="form.errors.role" />
                </div>

                <div class="rounded-2xl border border-slate-300 bg-white px-6 py-5 text-sm text-slate-700 shadow-md" v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required>
                        Tôi đồng ý với
                        <Link target="_blank" :href="route('terms.show')" class="text-sky-600 underline-offset-4 hover:underline">
                            Điều khoản dịch vụ
                        </Link>
                        và
                        <Link target="_blank" :href="route('policy.show')" class="text-sky-600 underline-offset-4 hover:underline">
                            Chính sách bảo mật
                        </Link>
                    </Checkbox>
                    <InputError class="mt-3" :message="form.errors.terms" />
                </div>
            </div>

            <div class="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Link :href="route('login')" class="text-xs font-semibold uppercase tracking-[0.32em] text-slate-500 transition hover:text-slate-800">
                    Đã có tài khoản? Đăng nhập
                </Link>

                <PrimaryButton :class="{ 'opacity-60': form.processing }" :disabled="form.processing">
                    Tạo tài khoản
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
