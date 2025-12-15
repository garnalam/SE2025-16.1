<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BackgroundEffects from '@/Components/Welcome/BackgroundEffects.vue';
import HeroSection from '@/Components/Welcome/HeroSection.vue';
import RoleSimulation from '@/Components/Welcome/RoleSimulation.vue';
import BentoGrid from '@/Components/Welcome/BentoGrid.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Smart Classroom" />

    <div class="relative min-h-screen bg-slate-950 text-white">
        <BackgroundEffects />

        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/60 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 text-white shadow-[0_20px_60px_-25px_rgba(129,140,248,0.9)] font-bold">
                            SC
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-[0.4em] text-slate-500">Smart Classroom</p>
                            <p class="text-xl font-semibold leading-tight text-white">Nền tảng lớp học trực tuyến thế hệ mới</p>
                        </div>
                    </div>

                    <nav v-if="props.canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="hidden md:flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-2 text-sm font-medium text-slate-100 transition hover:bg-white/10"
                        >
                            Vào bảng điều khiển
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="rounded-full px-5 py-2 text-sm font-medium text-slate-300 transition hover:text-white"
                        >
                            Đăng nhập
                        </Link>
                        <Link
                            v-if="props.canRegister"
                            :href="route('register')"
                            class="group relative overflow-hidden rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-fuchsia-500 px-6 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_-25px_rgba(129,140,248,0.9)]"
                        >
                            <span class="absolute inset-0 -translate-x-full bg-white/25 blur-sm transition group-hover:translate-x-full"></span>
                            <span class="relative">Tạo tài khoản</span>
                        </Link>
                    </nav>
                </div>
            </div>
        </header>

        <main class="relative z-10 pt-24">
            <HeroSection :can-register="props.canRegister" />
            <RoleSimulation />
            <BentoGrid />
        </main>

        <footer class="relative z-10 border-t border-white/10 bg-slate-950/60 py-12 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto flex flex-col gap-6 px-4 text-sm text-slate-400 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <p>© {{ new Date().getFullYear() }} Smart Classroom. Developed By Group SE2025-16.1 </p>
                <div class="flex items-center gap-6">
                    <a href="#" class="transition hover:text-white">Chính sách bảo mật</a>
                    <a href="#" class="transition hover:text-white">Điều khoản sử dụng</a>
                    <a href="#" class="transition hover:text-white">Liên hệ đội ngũ</a>
                </div>
            </div>
        </footer>
    </div>
</template>
