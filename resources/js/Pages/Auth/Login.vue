<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BrainCircuit,
    CheckCircle2,
    Loader2,
    Lock,
    Mail,
    QrCode,
    Sparkles,
    Zap,
} from 'lucide-vue-next';

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
    form
        .transform((data) => ({
            ...data,
            remember: form.remember ? 'on' : '',
        }))
        .post(route('login'), {
            onFinish: () => form.reset('password'),
        });
};
</script>

<template>
    <Head title="Đăng nhập | Smart Classroom" />

    <div class="relative min-h-screen bg-slate-950 text-white overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-25 mix-blend-soft-light"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.08)_1px,transparent_1px)] bg-[size:28px_28px]"></div>
            <div class="absolute -top-20 -left-32 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-indigo-500/30 via-purple-500/25 to-fuchsia-500/20 blur-[160px]"></div>
            <div class="absolute -bottom-40 right-[-20%] h-[520px] w-[520px] rounded-full bg-gradient-to-tr from-sky-400/20 via-cyan-500/15 to-emerald-400/10 blur-[180px]"></div>
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
                :href="route('register')"
                class="hidden rounded-full border border-white/15 px-5 py-2 text-sm font-medium text-slate-300 transition hover:text-white md:block"
            >
                Tạo tài khoản
            </Link>
        </header>

        <main class="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr] lg:pb-20 lg:pt-0">
            <section class="space-y-12">
                <div class="space-y-7">
                    <p class="text-xs uppercase tracking-[0.45em] text-indigo-200/80">Giải pháp cho giáo viên</p>
                    <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl">
                        Quản lý lớp học chưa bao giờ dễ dàng đến thế với Smart Classroom
                    </h1>
                    <p class="max-w-xl text-base leading-relaxed text-slate-300/90">
                        Smart Classroom giúp giáo viên điểm danh, giao nhiệm vụ và kích hoạt trợ lý AI soạn đề,chấm đề. Tất cả được đồng bộ theo thời gian thực với lớp học.
                    </p>
                </div>

                <dl class="grid gap-4 rounded-3xl border border-white/10 bg-white/6 p-6 backdrop-blur-2xl shadow-[0_32px_95px_-35px_rgba(30,64,175,0.45)]">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500/30 via-purple-500/30 to-fuchsia-500/30 border border-white/10">
                            <QrCode class="h-6 w-6 text-indigo-200" />
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.35em] text-indigo-200/80">Attendance Matrix</dt>
                            <dd class="text-sm text-slate-200/90">Điểm danh cập nhật tức thời, chỉnh sửa bằng một cú chạm.</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500/25 via-fuchsia-500/25 to-purple-500/25 border border-white/10">
                            <BrainCircuit class="h-6 w-6 text-rose-200" />
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.35em] text-rose-200/80">AI Soạn đề</dt>
                            <dd class="text-sm text-slate-200/90">Nhập yêu cầu, nhận đề thi hoàn chỉnh chỉ sau vài giây.</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-400/25 via-cyan-500/25 to-emerald-400/25 border border-white/10">
                            <CheckCircle2 class="h-6 w-6 text-emerald-200" />
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.35em] text-emerald-200/80">Báo cáo AI</dt>
                            <dd class="text-sm text-slate-200/90">Nhận phân tích bất thường, nhắc nhở học viên thiếu chuyên cần.</dd>
                        </div>
                    </div>
                </dl>
            </section>

            <section class="relative">
                <div class="absolute inset-0 rounded-[38px] border border-white/10 bg-white/8 blur-3xl"></div>
                <div class="relative rounded-[32px] border border-white/15 bg-white/8 p-8 backdrop-blur-3xl shadow-[0_45px_120px_-35px_rgba(79,70,229,0.6)]">
                    <header class="mb-8 space-y-3 text-center">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs uppercase tracking-[0.35em] text-slate-300">
                            <Sparkles class="h-4 w-4 text-fuchsia-300" />
                            Smart Classroom Portal
                        </div>
                        <h2 class="text-3xl font-semibold text-white">Đăng nhập để đồng bộ lớp học</h2>
                        <p class="text-sm text-slate-400">Quyền truy cập dành riêng cho giáo viên và học sinh của nền tảng Smart Classroom.</p>
                    </header>

                    <div v-if="status" class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/15 p-4 text-sm font-medium text-emerald-100">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
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
                                    autofocus
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-indigo-400/70 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
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
                                    class="w-full rounded-2xl border border-white/12 bg-slate-950/40 py-3 pl-12 pr-4 text-sm text-white placeholder-slate-500 transition focus:border-indigo-400/70 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                                    placeholder="••••••••"
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-sm text-rose-300">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-between text-sm text-slate-400">
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="h-4 w-4 rounded border-white/10 bg-slate-900/60 text-indigo-500 focus:ring-indigo-500"
                                />
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-indigo-300 transition hover:text-indigo-200"
                            >
                                Quên mật khẩu?
                            </Link>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-fuchsia-500 p-[3px] text-sm font-semibold"
                        >
                            <span class="absolute inset-0 translate-x-[-120%] bg-white/25 skew-x-[25deg] transition duration-700 group-hover:translate-x-[120%]"></span>
                            <span class="relative flex w-full items-center justify-center gap-2 rounded-[17px] bg-slate-950/80 py-3 text-white">
                                <Loader2 v-if="form.processing" class="h-5 w-5 animate-spin" />
                                <span v-else>Đăng nhập</span>
                                <ArrowRight v-if="!form.processing" class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                            </span>
                        </button>
                    </form>

                    <footer class="mt-8 space-y-4 text-center text-sm text-slate-400">
                        <p>
                            Chưa có tài khoản?
                            <Link :href="route('register')" class="font-medium text-indigo-300 transition hover:text-indigo-200">
                                Đăng ký ngay
                            </Link>
                        </p>
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Smart Classroom By SE2025-16.1</p>
                    </footer>
                </div>
            </section>
        </main>
    </div>
</template>
