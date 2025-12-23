<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, PlayCircle } from 'lucide-vue-next';

const props = defineProps({
    canRegister: Boolean,
});
</script>

<template>
    <section class="relative pt-32 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="space-y-10">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur-md">
                        <span class="flex h-2 w-2 animate-pulse rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-medium uppercase tracking-[0.4em] text-indigo-200/80">Smart Classroom </span>
                    </div>

                    <header class="space-y-6">
                        <p class="text-xs uppercase tracking-[0.45em] text-indigo-200/80"></p>
                        <h1 class="text-5xl font-black leading-tight md:text-6xl xl:text-7xl">
                            <span class="block bg-gradient-to-br from-white via-slate-200 to-slate-500 bg-clip-text text-transparent">Smart Classroom</span>
                            <span class="block bg-gradient-to-r from-indigo-400 via-purple-500 to-fuchsia-500 bg-clip-text text-transparent">Quản lý lớp học thật dễ dàng</span>
                        </h1>
                        <p class="max-w-xl text-lg leading-relaxed text-slate-300/90">
                            Quản lý lớp học tiện lợi dành cho giáo viên, trải nghiệm hứng thú dành cho học sinh và độ tiện ích và đáng tin cậy đến từ AI. Tất cả hòa quyện trong dự án Smart Classroom.
                        </p>
                    </header>

                    <div class="flex flex-wrap items-center gap-4">
                        <Link
                            v-if="props.canRegister"
                            :href="route('register')"
                            class="group relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-fuchsia-500 px-8 py-4 text-sm font-semibold uppercase tracking-[0.35em] text-white shadow-[0_0_45px_-15px_rgba(168,85,247,0.9)]"
                        >
                            <span class="absolute inset-0 -translate-x-full bg-white/25 skew-x-[25deg] transition duration-700 group-hover:translate-x-full"></span>
                            <span class="relative flex items-center gap-2">
                                Bắt đầu ngay
                                <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </span>
                        </Link>
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-sm font-medium uppercase tracking-[0.35em] text-slate-200 transition hover:bg-white/10"
                        >
                            <PlayCircle class="h-5 w-5" />
                            Xem mô phỏng
                        </button>
                    </div>

                    <dl class="grid grid-cols-3 gap-6 border-t border-white/10 pt-6 text-center">
                        <div class="space-y-2">
                            <dt class="text-xs uppercase tracking-[0.4em] text-slate-500">Điểm danh</dt>
                            <dd class="text-3xl font-bold text-white">Dễ dàng</dd>
                            <p class="text-[13px] text-slate-500">QR Code + Mã điểm danh</p>
                        </div>
                        <div class="space-y-2">
                            <dt class="text-xs uppercase tracking-[0.4em] text-slate-500">AI Hỗ Trợ</dt>
                            <dd class="text-3xl font-bold text-white">Song hành</dd>
                            <p class="text-[13px] text-slate-500">Soạn đề & chấm tự luận</p>
                        </div>
                        <div class="space-y-2">
                            <dt class="text-xs uppercase tracking-[0.4em] text-slate-500">Tương tác</dt>
                            <dd class="text-3xl font-bold text-white">24/7</dd>
                            <p class="text-[13px] text-slate-500">Hoạt động như một diễn đàn</p>
                        </div>
                    </dl>
                </div>

                <div class="relative">
                    <div class="absolute -left-6 top-6 h-[420px] w-[420px] rounded-[36px] bg-gradient-to-br from-indigo-500/25 via-purple-500/25 to-fuchsia-500/20 blur-[120px]"></div>
                    <div class="relative overflow-hidden rounded-[38px] border border-white/10 bg-white/6 backdrop-blur-2xl shadow-[0_35px_120px_-30px_rgba(56,189,248,0.45)]">
                        <div class="flex h-14 items-center justify-between border-b border-white/10 bg-white/10 px-6">
                            <div class="flex items-center gap-2 text-xs font-mono uppercase tracking-[0.35em] text-slate-300">
                                Attendance Matrix
                            </div>
                            <div class="flex gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-400/60"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400/60"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/60"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-3 p-6">
                            <template v-for="col in 5" :key="`col-${col}`">
                                <template v-for="row in 5" :key="`cell-${col}-${row}`">
                                    <div
                                        class="relative flex aspect-square flex-col items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/5"
                                    >
                                        <div class="h-10 w-10 rounded-full bg-slate-800"></div>
                                        <div class="h-1.5 w-12 rounded-full bg-slate-700/80"></div>
                                        <span
                                            class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full shadow-[0_0_10px_rgba(74,222,128,0.7)]"
                                            :class="(col + row) % 3 === 0 ? 'bg-rose-500 shadow-[0_0_10px_rgba(244,114,182,0.8)]' : 'bg-emerald-400'"
                                        ></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
