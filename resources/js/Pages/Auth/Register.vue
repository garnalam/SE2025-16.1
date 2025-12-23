<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BrainCircuit,
    GraduationCap,
    Loader2,
    Lock,
    Mail,
    Sparkles,
    User,
    Zap,
} from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
    terms: false,
});

const roles = [
    {
        key: 'teacher',
        title: 'Giáo viên quyền lực',
        description: 'Quản trị lớp, sử dụng AI hỗ trợ chấm điểm',
        accent: 'from-indigo-500 via-purple-500 to-fuchsia-500',
    },
    {
        key: 'student',
        title: 'Học sinh chủ động',
        description: 'Không gian tự học cá nhân, điểm danh QR',
        accent: 'from-sky-400 via-cyan-500 to-emerald-400',
    },
];

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Đăng ký | Smart Classroom" />

    <div class="relative min-h-screen bg-slate-950 text-white overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-25 mix-blend-soft-light"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.08)_1px,transparent_1px)] bg-[size:28px_28px]"></div>
            <div class="absolute -top-24 right-[-18%] h-[460px] w-[460px] rounded-full bg-gradient-to-br from-indigo-500/25 via-purple-500/20 to-fuchsia-500/15 blur-[180px]"></div>
            <div class="absolute bottom-[-35%] left-[-15%] h-[520px] w-[520px] rounded-full bg-gradient-to-tr from-sky-400/20 via-cyan-500/15 to-emerald-400/10 blur-[190px]"></div>
        </div>

        <header class="relative z-10 flex items-center justify-between px-6 py-6 sm:px-10">
            <Link :href="route('welcome')" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 shadow-[0_18px_60px_-25px_rgba(129,140,248,0.8)]">
                    <Zap class="w-6 h-6 text-white" />
                </span>
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Smart Classroom</p>
                    <p class="text-lg font-semibold">Nền tảng lớp học trực tuyến thế hệ mới</p>
                </div>
            </Link>
            <Link
                :href="route('login')"
                class="hidden rounded-full border border-white/15 px-5 py-2 text-sm font-medium text-slate-300 transition hover:text-white md:block"
            >
                Đăng nhập
            </Link>
        </header>

        <main class="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr] lg:pb-20 lg:pt-0">
            <section class="space-y-8">
                <div class="space-y-6 max-w-xl">
                    <p class="text-xs uppercase tracking-[0.45em] text-fuchsia-200/80">Chọn vai trò của bạn</p>
                    <h1 class="text-4xl font-bold leading-tight sm:text-5xl">
                        Thiết lập hồ sơ cho giáo viên quyền lực hoặc học sinh chủ động
                    </h1>
                    <p class="text-base text-slate-300/90 leading-relaxed">
                        Smart Classroom thiết kế trải nghiệm riêng biệt cho từng vai trò. Chọn đúng nhóm của bạn để nhận quyền năng tương ứng, từ quản lý lớp học đến bảng tin kết nối.
                    </p>
                </div>

                <div class="grid gap-3">
                    <article
                        v-for="role in roles"
                        :key="role.key"
                        class="group relative overflow-hidden rounded-[22px] border border-white/12 bg-white/6 p-5 backdrop-blur-2xl transition"
                        :class="form.role === role.key ? 'ring-2 ring-offset-2 ring-offset-slate-950 ring-white/40 shadow-[0_35px_120px_-40px_rgba(129,140,248,0.7)]' : 'hover:border-white/25'"
                        @click="form.role = role.key"
                    >
                        <div :class="['absolute inset-0 opacity-70 blur-3xl', `bg-gradient-to-br ${role.accent}`]"></div>
                        <div class="relative z-10 flex items-start justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-slate-300/80">{{ role.key === 'teacher' ? 'Teacher Mode' : 'Student Mode' }}</p>
                                <h2 class="mt-2 text-xl font-semibold text-white">{{ role.title }}</h2>
                                <p class="mt-3 text-sm text-slate-200/80 leading-relaxed">{{ role.description }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white">
                                <span v-if="form.role === role.key">✓</span>
                                <span v-else>○</span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="relative">
                <div class="absolute inset-0 rounded-[38px] border border-white/10 bg-white/8 blur-3xl"></div>
                <div class="relative rounded-[32px] border border-white/15 bg-white/10 p-8 backdrop-blur-3xl shadow-[0_45px_120px_-35px_rgba(217,70,239,0.45)]">
                    <header class="mb-8 space-y-3 text-center">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs uppercase tracking-[0.35em] text-slate-300">
                            <Sparkles class="h-4 w-4 text-indigo-200" />
                            Smart Classroom Onboarding
                        </div>
                        <h2 class="text-3xl font-semibold text-white">Tạo tài khoản chỉ trong 60 giây</h2>
                        <p class="text-sm text-slate-400">Trải nghiệm sản phẩm Smart Classroom ngay sau khi hoàn tất.</p>
                    </header>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium text-slate-200">Họ và tên</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                                    <User class="h-5 w-5" />
                                </span>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-fuchsia-400/70 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/60"
                                    placeholder="Nguyễn Văn A"
                                />
                            </div>
                            <p v-if="form.errors.name" class="text-sm text-rose-300">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium text-slate-200">Địa chỉ email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                                    <Mail class="h-5 w-5" />
                                </span>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-fuchsia-400/70 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/60"
                                    placeholder="name@example.com"
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-sm text-rose-300">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium text-slate-200">Mật khẩu</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                                    <Lock class="h-5 w-5" />
                                </span>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-fuchsia-400/70 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/60"
                                    placeholder="••••••••"
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-sm text-rose-300">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-medium text-slate-200">Xác nhận mật khẩu</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                                    <Lock class="h-5 w-5" />
                                </span>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-fuchsia-400/70 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/60"
                                    placeholder="••••••••"
                                />
                            </div>
                        </div>

                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="flex items-start gap-3 text-sm text-slate-400">
                            <input
                                id="terms"
                                v-model="form.terms"
                                type="checkbox"
                                required
                                class="mt-1 h-4 w-4 rounded border-white/10 bg-slate-900/60 text-fuchsia-500 focus:ring-fuchsia-500"
                            />
                            <label for="terms" class="leading-relaxed">
                                Tôi đồng ý với
                                <a target="_blank" :href="route('terms.show')" class="text-fuchsia-300 underline-offset-4 hover:underline">Điều khoản dịch vụ</a>
                                và
                                <a target="_blank" :href="route('policy.show')" class="text-fuchsia-300 underline-offset-4 hover:underline">Chính sách bảo mật</a>.
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-fuchsia-500 p-[3px] text-sm font-semibold"
                        >
                            <span class="absolute inset-0 translate-x-[-120%] bg-white/25 skew-x-[25deg] transition duration-700 group-hover:translate-x-[120%]"></span>
                            <span class="relative flex w-full items-center justify-center gap-2 rounded-[17px] bg-slate-950/80 py-3 text-white">
                                <Loader2 v-if="form.processing" class="h-5 w-5 animate-spin" />
                                <span v-else>Đăng ký tài khoản</span>
                                <ArrowRight v-if="!form.processing" class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                            </span>
                        </button>
                    </form>

                    <footer class="mt-8 space-y-3 text-center text-sm text-slate-400">
                        <p>
                            Đã có tài khoản?
                            <Link :href="route('login')" class="font-medium text-indigo-300 transition hover:text-indigo-200">
                                Đăng nhập ngay
                            </Link>
                        </p>
                        <p class="flex items-center justify-center gap-2 text-xs uppercase tracking-[0.35em] text-slate-500">
                            <GraduationCap class="h-4 w-4" /> Smart Classroom - SE2025-16.1
                        </p>
                    </footer>
                </div>
            </section>
        </main>
    </div>
</template>
