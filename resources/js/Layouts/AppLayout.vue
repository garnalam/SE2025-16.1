<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import JoinClassroomModal from '@/Components/JoinClassroomModal.vue';
import NotificationBell from '@/Components/NotificationBell.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const showingUserMenu = ref(false);
const showJoinModal = ref(false);

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const allTeams = computed(() => page.props.auth.user.all_teams || []);
const currentTeam = computed(() => page.props.auth.user.current_team || null);
const currentTeamId = computed(() => page.props.auth.user.current_team_id || null);
const canCreateTeams = computed(() => page.props.jetstream.canCreateTeams);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const openJoinModal = () => {
    showJoinModal.value = true;
};
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-[#020617] flex font-sans text-slate-300 selection:bg-cyan-500 selection:text-white relative overflow-hidden">
            
            <!-- BACKGROUND AMBIENCE (Global) -->
            <div class="fixed inset-0 z-0 pointer-events-none">
                <div class="absolute top-[-20%] left-[-10%] w-[60vw] h-[60vw] bg-indigo-600/10 rounded-full blur-[120px] mix-blend-screen animate-float"></div>
                <div class="absolute bottom-[-20%] right-[-10%] w-[50vw] h-[50vw] bg-cyan-600/10 rounded-full blur-[100px] mix-blend-screen animate-float delay-2000"></div>
                <!-- Grid Overlay -->
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
            </div>

            <!-- DESKTOP FLOATING SIDEBAR -->
            <aside class="hidden md:flex flex-col w-72 h-[96vh] fixed left-4 top-[2vh] z-50 transition-all duration-300">
                <!-- Glass Container with Double Border effect -->
                <div class="h-full flex flex-col bg-slate-900/40 backdrop-blur-2xl border border-white/10 rounded-3xl shadow-[0_0_40px_-10px_rgba(0,0,0,0.5)] relative overflow-hidden ring-1 ring-white/5">
                    
                    <!-- Sidebar Header -->
                    <div class="space-y-2">
    <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 font-mono">Các tính năng chính</p>
    
    <Link :href="route('dashboard')" 
        :class="route().current('dashboard') 
            ? 'bg-indigo-600/20 text-white shadow-[0_0_20px_rgba(79,70,229,0.3)] border-indigo-500/50' 
            : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
        class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-300 border backdrop-blur-sm group">
        <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('dashboard') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        Bảng điều khiển
    </Link>

    <div class="px-4 py-2 text-slate-400 hover:text-white rounded-xl transition-all cursor-pointer group">
        <NotificationBell class="w-full text-left" />
    </div>
</div>

                    <!-- Navigation -->
                    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-8 custom-scrollbar">
                        
                        <!-- Group 1: CORE -->
                        <div class="space-y-2">
    <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 font-mono">Góc Học Tập</p>
    
    <Link :href="route('study.documents')" 
        :class="route().current('study.documents') 
            ? 'bg-purple-600/20 text-white shadow-[0_0_20px_rgba(147,51,234,0.3)] border-purple-500/50' 
            : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
        class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-300 border backdrop-blur-sm group">
        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        Kho Tài Liệu & AI
    </Link>

    <Link :href="route('study.mistakes')" 
        :class="route().current('study.mistakes') 
            ? 'bg-rose-600/20 text-white shadow-[0_0_20px_rgba(225,29,72,0.3)] border-rose-500/50' 
            : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
        class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-300 border backdrop-blur-sm group">
        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        Sửa Lỗi Sai
    </Link>
</div>

                        <!-- Group 2: ACADEMIC -->
                        <div class="space-y-2">
                            <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3 font-mono">Khu vực lớp học</p>

                            <Link v-if="currentTeam" :href="route('teams.feed', currentTeam)"
                                 :class="route().current('teams.feed') 
                                    ? 'bg-cyan-600/20 text-white shadow-[0_0_20px_rgba(6,182,212,0.3)] border-cyan-500/50' 
                                    : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
                                 class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all duration-300 border backdrop-blur-sm group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('teams.feed') ? 'text-cyan-400' : 'text-slate-500 group-hover:text-cyan-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                <span class="truncate">{{ currentTeam ? currentTeam.name : 'Select Class' }}</span>
                            </Link>

                            <button v-if="userRole === 'student'" @click="openJoinModal"
                                 class="w-full flex items-center px-4 py-3 text-sm font-bold text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 border border-transparent hover:border-emerald-500/30 rounded-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                Tham gia lớp học
                            </button>

                            <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                <Link v-if="currentTeam" :href="route('teams.show', currentTeam)"
                                    :class="route().current('teams.show') 
                                        ? 'bg-white/10 text-white border-white/20' 
                                        : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
                                    class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border group">
                                    <svg class="mr-3 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Cài đặt lớp học
                                </Link>

                                <Link :href="route('questions.index')"
                                    :class="route().current('questions.index') 
                                        ? 'bg-white/10 text-white border-white/20' 
                                        : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
                                    class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 border group">
                                    <svg class="mr-3 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Ngân hàng câu hỏi
                                </Link>
                            </template>
                        </div>
                       
                        <!-- Group 3: SWITCHER -->
                        <div v-if="allTeams.length > 0" class="space-y-2 pt-4 border-t border-white/5">
                            <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 font-mono">Lớp học của tôi </p>
                            <div class="px-2 space-y-1 max-h-40 overflow-y-auto custom-scrollbar">
                                <template v-for="team in allTeams" :key="team.id">
                                    <form @submit.prevent="switchToTeam(team)">
                                        <button class="w-full text-left px-3 py-2 rounded-lg text-xs transition-all flex items-center justify-between group border border-transparent"
                                            :class="team.id == currentTeamId 
                                                ? 'bg-slate-800 text-cyan-300 shadow-inner border-slate-700' 
                                                : 'text-slate-500 hover:text-white hover:bg-white/5'">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div class="w-2 h-2 rounded-full" :class="team.id == currentTeamId ? 'bg-cyan-400 shadow-[0_0_8px_cyan]' : 'bg-slate-700'"></div>
                                                <span class="truncate font-medium">{{ team.name }}</span>
                                            </div>
                                        </button>
                                    </form>
                                </template>
                            </div>
                             <Link v-if="canCreateTeams" :href="route('teams.create')" class="block mx-2 mt-2 text-center py-2 text-[10px] uppercase font-bold border border-dashed border-slate-600 rounded-lg text-slate-500 hover:text-indigo-400 hover:border-indigo-500 hover:bg-indigo-500/5 transition duration-200">
                                + Tạo Lớp Học Mới
                            </Link>
                        </div>
                    </nav>

                    <!-- PLAYER CARD PROFILE -->
                    <div class="p-4 bg-gradient-to-t from-slate-900 to-transparent relative">
                         <!-- XP Bar -->
                        <div v-if="userRole === 'student'" class="mb-4 relative group">
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-[10px] font-black text-cyan-400 tracking-wider">LEVEL {{ $page.props.auth.user.level }}</span>
                                <span class="text-[9px] font-mono text-slate-400">{{ $page.props.auth.user.xp }} XP</span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden border border-white/5 relative">
                                <div class="h-full bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 shadow-[0_0_15px_rgba(6,182,212,0.6)] relative z-10" :style="{ width: $page.props.auth.user.xp_progress + '%' }"></div>
                            </div>
                        </div>

                        <!-- User Info & Dropdown -->
                        <div class="relative">
                            <transition 
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95 translate-y-2"
                                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                                leave-to-class="transform opacity-0 scale-95 translate-y-2"
                            >
                                <div v-show="showingUserMenu" 
                                     class="absolute bottom-[calc(100%+10px)] left-0 w-full rounded-2xl bg-[#0f172a] border border-slate-700 shadow-[0_0_30px_rgba(0,0,0,0.8)] overflow-hidden z-50 ring-1 ring-white/10 p-2">
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-3 py-2 text-xs text-slate-300 hover:bg-white/10 rounded-xl transition">
                                        <div class="p-1.5 bg-slate-800 rounded-lg"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                        <span>Cài đặt hồ sơ</span>
                                    </Link>
                                    <form @submit.prevent="logout">
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs text-rose-400 hover:bg-rose-500/10 rounded-xl transition mt-1">
                                            <div class="p-1.5 bg-rose-500/10 rounded-lg"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg></div>
                                            <span>Đăng xuất</span>
                                        </button>
                                    </form>
                                </div>
                            </transition>

                            <button @click="showingUserMenu = !showingUserMenu" 
                                    class="flex items-center gap-3 w-full group focus:outline-none p-2 rounded-2xl hover:bg-white/5 transition duration-300 border border-white/5 hover:border-white/10 bg-slate-800/50 backdrop-blur-md">
                                <div class="relative">
                                    <img class="h-10 w-10 rounded-xl object-cover border-2 border-slate-700 group-hover:border-indigo-400 transition" 
                                         :src="$page.props.auth.user.profile_photo_url" 
                                         :alt="$page.props.auth.user.name">
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse"></div>
                                </div>
                                
                                <div class="flex-1 text-left overflow-hidden">
                                    <p class="text-sm font-bold text-slate-200 group-hover:text-white transition truncate font-exo">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="text-[9px] text-cyan-500 uppercase font-mono tracking-wider truncate">
                                        {{ userRole }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 md:ml-[304px] h-screen overflow-y-auto relative z-10 scroll-smooth custom-scrollbar">
                
                <!-- MOBILE HEADER -->
                <div class="md:hidden sticky top-0 z-40 bg-slate-900/90 backdrop-blur-xl border-b border-white/10 px-4 py-3 flex justify-between items-center shadow-2xl">
                    <div class="flex items-center gap-3">
                         <ApplicationMark class="h-8 w-auto text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]" />
                         <span class="font-black text-white text-lg tracking-tight">LMS<span class="text-cyan-400">.AI</span></span>
                    </div>
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2 text-slate-400 hover:text-white bg-white/5 rounded-lg border border-white/10">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- MOBILE MENU OVERLAY -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden bg-slate-900 border-b border-white/10 relative z-40 shadow-2xl">
                     <div class="p-4 space-y-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                            Class: {{ currentTeam.name }}
                        </ResponsiveNavLink>
                        <template v-if="userRole === 'teacher' || userRole === 'admin'">
                            <ResponsiveNavLink v-if="currentTeam" :href="route('teams.show', currentTeam)" :active="route().current('teams.show')">Settings</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('questions.index')" :active="route().current('questions.index')">Question Bank</ResponsiveNavLink>
                        </template>
                        <ResponsiveNavLink :href="route('study.mistakes')" :active="route().current('knowledge-base.*')" class="text-purple-400">
    🤖 AI Assistant
</ResponsiveNavLink>
                        <div class="border-t border-slate-800 my-2 pt-2"></div>
                         <form method="POST" @submit.prevent="logout">
                            <ResponsiveNavLink as="button" class="text-rose-400">Log Out</ResponsiveNavLink>
                        </form>
                    </div>
                </div>

                <!-- CONTENT WRAPPER -->
                <div class="relative min-h-full p-4 md:p-8">
                    <slot />
                </div>
            </main>

            <JoinClassroomModal :show="showJoinModal" @close="showJoinModal = false" />
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=JetBrains+Mono:wght@100..800&display=swap');

.font-exo { font-family: 'Exo 2', sans-serif; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.5); }

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}
.animate-float { animation: float 10s infinite ease-in-out; }
</style>