<script setup>
import { BarChart3, BrainCircuit, Lock, MessageSquare, QrCode } from 'lucide-vue-next';

const cards = [
    {
        key: 'attendance',
        span: 'md:col-span-2',
        icon: QrCode,
        title: 'Real-time Attendance Matrix',
        description: 'Điểm danh QR và ma trận kính mờ cập nhật tức thời, chỉnh sửa chỉ bằng một cú chạm.',
        gradient: 'from-emerald-500/25 via-teal-400/15 to-transparent',
    },
    {
        key: 'feed',
        span: 'md:row-span-2',
        icon: MessageSquare,
        title: 'Class Feed như mạng xã hội',
        description: 'Topics phân tầng, bình luận động, bảng tin như một mạng xã hội chuyên sâu về giáo dục.',
        gradient: 'from-indigo-500/20 via-purple-500/15 to-transparent',
    },
    {
        key: 'ai-grader',
        span: '',
        icon: BrainCircuit,
        title: 'AI Grader',
        description: 'Soạn đề tự động và chấm tự luận với phản hồi mềm mại đúng phong cách giảng dạy.',
        gradient: 'from-rose-500/25 via-fuchsia-500/15 to-transparent',
    },
    {
        key: 'secure-exam',
        span: '',
        icon: Lock,
        title: 'Secure Exam Shield',
        description: 'Chống gian lận đa kênh, xác thực hai lớp và ghi nhận vi phạm theo thời gian thực.',
        gradient: 'from-amber-500/25 via-orange-500/15 to-transparent',
    },
    {
        key: 'dashboard',
        span: '',
        icon: BarChart3,
        title: 'Dashboard cá nhân hóa',
        description: 'Biểu đồ tiến bộ AI-driven với khuyến nghị dành riêng cho từng lớp học.',
        gradient: 'from-sky-500/25 via-cyan-500/15 to-transparent',
    },
];
</script>

<template>
    <section class="relative py-28">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(79,70,229,0.18),transparent_55%)]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="mx-auto mb-16 max-w-3xl space-y-4 text-center">
                <p class="text-xs uppercase tracking-[0.45em] text-slate-500">Tính năng cốt lõi</p>
                <h2 class="text-4xl md:text-5xl font-bold text-white">Các tính năng chính của dự án Smart Classroom</h2>
                <p class="text-lg text-slate-300/90">
                    Từ điểm danh 4.0, Class Feed tương tác đến AI chấm điểm thông minh, tất cả đều được đóng gói trong sản phẩm này.
                </p>
            </header>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <article
                    v-for="card in cards"
                    :key="card.key"
                    :class="[
                        'relative overflow-hidden rounded-[26px] border border-white/12 bg-white/6 p-8 backdrop-blur-2xl transition hover:bg-white/10 shadow-[0_35px_120px_-45px_rgba(79,70,229,0.7)]',
                        card.span,
                    ]"
                >
                    <div :class="['pointer-events-none absolute inset-0 opacity-80 blur-3xl', `bg-gradient-to-br ${card.gradient}`]"></div>
                    <div class="relative z-10 space-y-6">
                        <component :is="card.icon" class="h-12 w-12 text-white/80" />
                        <div class="space-y-3">
                            <h3 class="text-2xl font-semibold text-white">{{ card.title }}</h3>
                            <p class="text-sm leading-relaxed text-slate-200/80">{{ card.description }}</p>
                        </div>
                        <div class="h-[1px] w-full bg-gradient-to-r from-white/0 via-white/30 to-white/0"></div>
                        <button class="inline-flex items-center gap-2 text-sm font-medium text-white/80 transition hover:text-white">
                            Khám phá mô phỏng
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
