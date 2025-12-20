<script setup>
    import { ref, computed } from 'vue';
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import ApplicationMark from '@/Components/ApplicationMark.vue';
    import Banner from '@/Components/Banner.vue';
    import Dropdown from '@/Components/Dropdown.vue';
    import DropdownLink from '@/Components/DropdownLink.vue';
    import NavLink from '@/Components/NavLink.vue';
    import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
    import JoinClassroomModal from '@/Components/JoinClassroomModal.vue';
    import NotificationBell from '@/Components/NotificationBell.vue';
    
    defineProps({
        title: String,
    });
<<<<<<< Updated upstream
    
    const showingNavigationDropdown = ref(false);
    
    // Lấy thông tin người dùng và vai trò từ $page props
    const page = usePage();
    const userRole = computed(() => page.props.auth.user.role);
    const allTeams = computed(() => page.props.auth.user.all_teams || []);
    const currentTeam = computed(() => page.props.auth.user.current_team || null);
    const currentTeamId = computed(() => page.props.auth.user.current_team_id || null);
    
    // Lấy config của Jetstream
    const hasTeamFeatures = computed(() => page.props.jetstream.hasTeamFeatures);
    const canCreateTeams = computed(() => page.props.jetstream.canCreateTeams);
    
    // Hàm chuyển Team (Lớp học)
    const switchToTeam = (team) => {
        router.put(route('current-team.update'), {
            team_id: team.id,
        }, {
            preserveState: false,
        });
    };
    
    // Hàm Đăng xuất
    const logout = () => {
        router.post(route('logout'));
    };
    
    // Biến trạng thái để điều khiển Modal "Tham gia Lớp"
    const showJoinModal = ref(false);
    
    // Hàm MỞ Modal "Tham gia Lớp"
    const openJoinModal = () => {
        showJoinModal.value = true;
    };
    </script>
    
    <template>
        <div>
            <Head :title="title" />
    
            <Banner />
    
            <div class="min-h-screen bg-gray-100">
                <nav class="bg-white border-b border-gray-100">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <div class="shrink-0 flex items-center">
                                    <Link :href="route('dashboard')">
                                        <ApplicationMark class="block h-9 w-auto" />
                                    </Link>
                                </div>
    
                                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                        Dashboard
                                    </NavLink>
                                    
                                    <NavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                                        Lớp học hiện tại
                                    </NavLink>
                                    
                                    <NavLink 
                                        v-if="userRole === 'teacher'" 
                                        :href="route('questions.index')" 
                                        :active="route().current('questions.index')"
                                    >
                                        Ngân hàng câu hỏi
                                    </NavLink>
                                    <NavLink 
                                        v-if="$page.props.auth.user.current_team && $page.props.jetstream.hasTeamFeatures"
                                        :href="route('gradebook.index', $page.props.auth.user.current_team.id)" 
                                        :active="route().current('gradebook.*')"
                                    >
                                        Sổ Điểm
                                    </NavLink>
                                    <button 
                                        v-if="userRole === 'student'"
                                        @click="openJoinModal" 
                                        type="button" 
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="ml-1">Tham gia Lớp</span>
                                    </button>
                                </div>
                            </div>
    
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                
                                <div v-if="userRole === 'student'" class="hidden sm:flex flex-col items-end mr-4 border-r pr-4 border-gray-200 h-10 justify-center">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            LVL {{ $page.props.auth.user.level }}
                                        </span>
                                        <span class="text-xs font-semibold text-indigo-600">
                                            {{ $page.props.auth.user.xp }} XP
                                        </span>
                                    </div>
    
                                    <div class="w-32 h-1.5 bg-gray-200 rounded-full mt-1 overflow-hidden relative" title="Kinh nghiệm để lên cấp tiếp theo">
                                        <div 
                                            class="h-full bg-indigo-500 rounded-full transition-all duration-500 ease-out shadow-sm"
                                            :style="{ width: $page.props.auth.user.xp_progress + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-[9px] text-gray-400 mt-0.5">
                                        {{ $page.props.auth.user.xp_progress }}% to next level
                                    </span>
                                </div>
                                <NotificationBell class="mr-4" />
                                
                                <div class="ms-3 relative" v-if="hasTeamFeatures">
                                    <Dropdown align="right" width="60">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    {{ currentTeam ? currentTeam.name : 'Chọn Lớp' }}
    
                                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
    
                                        <template #content>
                                            <div class="w-60">
                                                
                                                <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Quản lý Lớp học (GV)
                                                    </div>
    
                                                    <DropdownLink v-if="currentTeam" :href="route('teams.show', currentTeam)">
                                                        Cài đặt Lớp học
                                                    </DropdownLink>
    
                                                    <div class="border-t border-gray-200" />
                                                    <DropdownLink :href="route('questions.index')">
                                                        Ngân hàng câu hỏi
                                                    </DropdownLink>
                                                    <div class="border-t border-gray-200" />
                                                    
                                                    <DropdownLink v-if="canCreateTeams" :href="route('teams.create')">
                                                        Tạo Lớp học mới
                                                    </DropdownLink>
                                                </template>
                                                
                                                <template v-if="userRole === 'student'">
                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Lớp học (HS)
                                                    </div>
    
                                                    <DropdownLink as="button" @click="openJoinModal">
                                                        Tham gia Lớp học
                                                    </DropdownLink>
                                                </template>
    
                                                <template v-if="allTeams.length > 0">
                                                    <div class="border-t border-gray-200" />
    
                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Chuyển Lớp học
                                                    </div>
    
                                                    <template v-for="team in allTeams" :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg v-if="team.id == currentTeamId" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>
                                
                                <div class="ms-3 relative">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                            </button>
    
                                            <span v-else class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    {{ $page.props.auth.user.name }}
    
                                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
    
                                        <template #content>
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Quản lý Tài khoản
                                            </div>
    
                                            <DropdownLink :href="route('profile.show')">
                                                Hồ sơ
                                            </DropdownLink>
    
                                            <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                                API Tokens
                                            </DropdownLink>
    
                                            <div class="border-t border-gray-200" />
    
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button">
                                                    Đăng xuất
                                                </DropdownLink>
                                            </form>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
    
                            <div class="-me-2 flex items-center sm:hidden">
                                <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
    
                    <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                        <div class="pt-2 pb-3 space-y-1">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </ResponsiveNavLink>
                            
                            <ResponsiveNavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                                Lớp học hiện tại
                            </ResponsiveNavLink>
    
                            <ResponsiveNavLink 
                                v-if="userRole === 'teacher'" 
                                :href="route('questions.index')" 
                                :active="route().current('questions.index')"
                            >
                                Ngân hàng câu hỏi
                            </ResponsiveNavLink>
                            
                            <ResponsiveNavLink 
                                v-if="currentTeam && hasTeamFeatures"
                                :href="route('gradebook.index', currentTeam.id)" 
                                :active="route().current('gradebook.*')"
                            >
                                Sổ Điểm
                            </ResponsiveNavLink>
                            <ResponsiveNavLink 
                                v-if="userRole === 'student'"
                                as="button"
                                @click="openJoinModal"
                            >
                                Tham gia Lớp học mới
                            </ResponsiveNavLink>
                        </div>
    
                        <div class="pt-4 pb-1 border-t border-gray-200">
                            <div class="flex items-center px-4">
                                <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                    <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                </div>
    
                                <div>
                                    <div class="font-medium text-base text-gray-800">
                                        {{ $page.props.auth.user.name }}
                                    </div>
                                    <div class="font-medium text-sm text-gray-500">
                                        {{ $page.props.auth.user.email }}
                                    </div>
                                </div>
                            </div>
    
                            <div v-if="userRole === 'student'" class="mt-3 px-4 py-2 bg-gray-50 border-t border-b border-gray-100">
                                <div class="flex justify-between text-xs font-semibold text-gray-600 mb-1">
                                    <span>LEVEL {{ $page.props.auth.user.level }}</span>
                                    <span>{{ $page.props.auth.user.xp }} XP</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500" :style="{ width: $page.props.auth.user.xp_progress + '%' }"></div>
                                </div>
                                <div class="text-right text-[10px] text-gray-400 mt-1">
                                    {{ $page.props.auth.user.xp_progress }}% to next level
                                </div>
                            </div>
                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                    Hồ sơ
                                </ResponsiveNavLink>
    
                                <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                    API Tokens
                                </ResponsiveNavLink>
    
                                <form method="POST" @submit.prevent="logout">
                                    <ResponsiveNavLink as="button">
                                        Đăng xuất
                                    </ResponsiveNavLink>
                                </form>
    
                                <div class="border-t border-gray-200 mt-2 pt-2">
                                    <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Quản lý Lớp học (GV)
                                        </div>
                                        <ResponsiveNavLink v-if="currentTeam" :href="route('teams.show', currentTeam)" :active="route().current('teams.show')">
                                            Cài đặt Lớp học
                                        </ResponsiveNavLink>
                                        <ResponsiveNavLink v-if="canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">
                                            Tạo Lớp học mới
                                        </ResponsiveNavLink>
                                    </template>
    
                                    <template v-if="userRole === 'student'">
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Lớp học (HS)
                                        </div>
                                        <ResponsiveNavLink as="button" @click="openJoinModal">
                                            Tham gia Lớp học
                                        </ResponsiveNavLink>
                                    </template>
    
                                    <template v-if="allTeams.length > 0">
                                        <div class="border-t border-gray-200 mt-2 pt-2" />
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Chuyển Lớp học
                                        </div>
                                        <template v-for="team in allTeams" :key="team.id">
                                            <form @submit.prevent="switchToTeam(team)">
                                                <ResponsiveNavLink as="button">
                                                    <div class="flex items-center">
                                                        <svg v-if="team.id == currentTeamId" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div>{{ team.name }}</div>
                                                    </div>
                                                </ResponsiveNavLink>
                                            </form>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
    
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>
    
                <main>
=======
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
            
            <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[-20%] left-[-10%] w-[70vw] h-[70vw] bg-indigo-900/20 rounded-full blur-[120px] mix-blend-screen animate-pulse-slow"></div>
                <div class="absolute bottom-[-20%] right-[-10%] w-[60vw] h-[60vw] bg-cyan-900/10 rounded-full blur-[100px] mix-blend-screen animate-float delay-1000"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.8)_2px,transparent_2px),linear-gradient(90deg,rgba(15,23,42,0.8)_2px,transparent_2px)] bg-[size:40px_40px] opacity-20"></div>
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10 brightness-100 contrast-150 mix-blend-overlay"></div>
            </div>

            <aside class="hidden md:flex flex-col w-72 h-[96vh] fixed left-4 top-[2vh] z-50 transition-all duration-300">
                <div class="h-full flex flex-col bg-[#0f172a]/70 backdrop-blur-2xl border border-white/5 rounded-3xl shadow-[0_0_40px_-10px_rgba(0,0,0,0.5)] relative overflow-hidden ring-1 ring-white/5 group/sidebar">
                    
                    <div class="h-24 flex items-center px-6 border-b border-white/5 bg-gradient-to-r from-white/5 to-transparent shrink-0">
                        <Link :href="route('dashboard')" class="flex items-center gap-4 group w-full">
                            <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-[0_0_20px_rgba(6,182,212,0.4)] group-hover:scale-110 transition-transform duration-300">
                                <ApplicationMark class="h-6 w-auto text-white" />
                                <div class="absolute inset-0 bg-white/20 blur-md rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-black text-lg text-white tracking-wide uppercase font-exo group-hover:text-cyan-400 transition-colors">Smart<span class="text-cyan-400 group-hover:text-white">Classroom</span></span>
                                <span class="text-[10px] text-slate-400 font-mono tracking-widest uppercase flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                </span>
                            </div>
                        </Link>
                    </div>

                    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-8 custom-scrollbar">
                        
                        <div class="space-y-2">
                            <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 font-mono opacity-80">Trung tâm điều khiển</p>
                            
                            <Link :href="route('dashboard')" 
                                :class="route().current('dashboard') ? 'bg-gradient-to-r from-indigo-500/20 to-transparent border-l-2 border-indigo-500 text-white shadow-[0_0_20px_rgba(99,102,241,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('dashboard') ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Dashboard
                            </Link>

                             <div class="px-4 py-2 text-slate-400 hover:text-white rounded-xl transition-all cursor-pointer group hover:bg-white/5">
                                <NotificationBell class="w-full text-left" />
                            </div>
                        </div>

                        <div v-if="userRole === 'student'" class="space-y-2">
                            <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 font-mono opacity-80">Góc Học Tập</p>
                            
                            <Link :href="route('study.documents')" 
                                :class="route().current('study.documents') ? 'bg-gradient-to-r from-purple-500/20 to-transparent border-l-2 border-purple-500 text-white shadow-[0_0_20px_rgba(168,85,247,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('study.documents') ? 'text-purple-400' : 'text-slate-500 group-hover:text-purple-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Kho Tài Liệu & AI
                            </Link>

                            <Link :href="route('study.mistakes')" 
                                :class="route().current('study.mistakes') ? 'bg-gradient-to-r from-rose-500/20 to-transparent border-l-2 border-rose-500 text-white shadow-[0_0_20px_rgba(244,63,94,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('study.mistakes') ? 'text-rose-400' : 'text-slate-500 group-hover:text-rose-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Sửa Lỗi Sai
                            </Link>
                            <Link v-if="currentTeam":href="route('memory-shards.index', { teamId: currentTeam?.id })" 
                                :class="route().current('memory-shards.*') ? 'bg-gradient-to-r from-teal-500/20 to-transparent border-l-2 border-teal-500 text-white shadow-[0_0_20px_rgba(20,184,166,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('memory-shards.*') ? 'text-teal-400' : 'text-slate-500 group-hover:text-teal-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Góc học tập (Memory)
                            </Link>

                            <Link :href="route('gym.index', { team_id: currentTeam?.id })" 
                                :class="route().current('gym.index') ? 'bg-gradient-to-r from-orange-500/20 to-transparent border-l-2 border-orange-500 text-white shadow-[0_0_20px_rgba(249,115,22,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('gym.index') ? 'text-orange-400' : 'text-slate-500 group-hover:text-orange-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <div class="flex-1">
                                    Luyện Đề (Gym)
                                    <div v-if="currentTeam" class="text-[9px] font-mono text-orange-400 mt-0.5 font-normal tracking-wide">
                                        LỚP: {{ currentTeam.name.substring(0, 10) }}...
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 font-mono opacity-80">Lớp Học</p>

                            <Link v-if="currentTeam" :href="route('teams.feed', currentTeam)"
                                 :class="route().current('teams.feed') ? 'bg-gradient-to-r from-cyan-500/20 to-transparent border-l-2 border-cyan-500 text-white shadow-[0_0_20px_rgba(6,182,212,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                 class="flex items-center px-4 py-3 text-sm font-bold rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 transition-transform group-hover:scale-110 duration-300" :class="route().current('teams.feed') ? 'text-cyan-400' : 'text-slate-500 group-hover:text-cyan-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                <span class="truncate">{{ currentTeam ? currentTeam.name : 'Select Class' }}</span>
                            </Link>

                            <button v-if="userRole === 'student'" @click="openJoinModal"
                                 class="w-full flex items-center px-4 py-3 text-sm font-bold text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 border-l-2 border-transparent hover:border-emerald-500 rounded-r-xl transition-all duration-300 group">
                                <svg class="mr-3 h-5 w-5 text-slate-500 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                Tham gia lớp mới
                            </button>

                            <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                <Link v-if="currentTeam" :href="route('teams.show', currentTeam)"
                                    :class="route().current('teams.show') ? 'bg-white/10 text-white border-l-2 border-white' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                    class="flex items-center px-4 py-3 text-sm font-medium rounded-r-xl transition-all duration-200 group">
                                    <svg class="mr-3 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Cài đặt lớp
                                </Link>

                                <Link :href="route('questions.index')"
                                    :class="route().current('questions.index') ? 'bg-white/10 text-white border-l-2 border-white' : 'text-slate-400 hover:text-white hover:bg-white/5 border-l-2 border-transparent'"
                                    class="flex items-center px-4 py-3 text-sm font-medium rounded-r-xl transition-all duration-200 group">
                                    <svg class="mr-3 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Ngân hàng câu hỏi
                                </Link>
                            </template>
                        </div>

<div class="space-y-2 pt-4 border-t border-white/5">
    <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 font-mono opacity-80">Danh sách lớp</p>
    
    <div v-if="allTeams.length > 0" class="px-2 space-y-1 max-h-32 overflow-y-auto custom-scrollbar">
        <template v-for="team in allTeams" :key="team.id">
            <form @submit.prevent="switchToTeam(team)">
                <button class="w-full text-left px-3 py-2 rounded-lg text-xs transition-all flex items-center justify-between group border border-transparent"
                    :class="team.id == currentTeamId ? 'bg-slate-800 text-cyan-300 shadow-inner border-slate-700' : 'text-slate-500 hover:text-white hover:bg-white/5'">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-2 h-2 rounded-full" :class="team.id == currentTeamId ? 'bg-cyan-400 shadow-[0_0_8px_cyan]' : 'bg-slate-700'"></div>
                        <span class="truncate font-medium">{{ team.name }}</span>
                    </div>
                </button>
            </form>
        </template>
    </div>

    <div v-else class="px-4 py-2 text-xs text-slate-600 italic">
        Bạn chưa tham gia lớp học nào.
    </div>

    <Link v-if="canCreateTeams" :href="route('teams.create')" class="block mx-2 mt-2 text-center py-2 text-[10px] uppercase font-bold border border-dashed border-slate-600 rounded-lg text-slate-500 hover:text-indigo-400 hover:border-indigo-500 hover:bg-indigo-500/5 transition duration-200">
        + Tạo Lớp Mới
    </Link>
</div>
                    </nav>

                    <div class="p-4 bg-slate-900/80 border-t border-white/5 relative z-20">
                         <div v-if="userRole === 'student'" class="mb-4 relative group">
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-[10px] font-black text-cyan-400 tracking-wider">LEVEL {{ $page.props.auth.user.level }}</span>
                                <span class="text-[9px] font-mono text-slate-400">{{ $page.props.auth.user.xp }} XP</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden border border-white/5 relative">
                                <div class="h-full bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-600 shadow-[0_0_15px_rgba(6,182,212,0.6)] relative z-10" :style="{ width: $page.props.auth.user.xp_progress + '%' }"></div>
                            </div>
                        </div>

                        <div class="relative">
                            <transition 
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95 translate-y-4"
                                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                                leave-to-class="transform opacity-0 scale-95 translate-y-4"
                            >
                                <div v-show="showingUserMenu" 
                                     class="absolute bottom-[calc(100%+12px)] left-0 w-full rounded-2xl bg-[#0f172a] border border-slate-700 shadow-[0_0_50px_rgba(0,0,0,0.8)] overflow-hidden z-50 ring-1 ring-white/10 p-2 backdrop-blur-xl">
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-3 py-2 text-xs text-slate-300 hover:bg-white/10 rounded-xl transition">
                                        <div class="p-1.5 bg-slate-800 rounded-lg"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                        <span>Hồ sơ cá nhân</span>
                                    </Link>
                                    <div class="border-t border-slate-800 my-1"></div>
                                    <form @submit.prevent="logout">
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs text-rose-400 hover:bg-rose-500/10 rounded-xl transition mt-1">
                                            <div class="p-1.5 bg-rose-500/10 rounded-lg"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg></div>
                                            <span>Ngắt kết nối</span>
                                        </button>
                                    </form>
                                </div>
                            </transition>

                            <button @click="showingUserMenu = !showingUserMenu" 
                                    class="flex items-center gap-3 w-full group focus:outline-none p-2.5 rounded-2xl hover:bg-white/5 transition duration-300 border border-white/5 hover:border-white/20 bg-slate-800/40 backdrop-blur-md shadow-lg">
                                <div class="relative">
                                    <img class="h-9 w-9 rounded-xl object-cover border-2 border-slate-700 group-hover:border-cyan-400 transition shadow-lg" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                    <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse"></div>
                                </div>
                                <div class="flex-1 text-left overflow-hidden">
                                    <p class="text-sm font-bold text-slate-200 group-hover:text-white transition truncate font-exo">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="text-[9px] text-cyan-500 uppercase font-mono tracking-wider truncate opacity-70 group-hover:opacity-100">
                                        {{ userRole }} UNIT
                                    </p>
                                </div>
                                <svg class="w-4 h-4 text-slate-500 group-hover:text-white transition-transform duration-300" :class="{'rotate-180': showingUserMenu}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 md:ml-[304px] h-screen overflow-y-auto relative z-10 scroll-smooth custom-scrollbar">
                
                <div class="md:hidden sticky top-0 z-40 bg-slate-900/90 backdrop-blur-xl border-b border-white/10 px-4 py-3 flex justify-between items-center shadow-2xl">
                    <div class="flex items-center gap-3">
                         <ApplicationMark class="h-8 w-auto text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]" />
                         <span class="font-black text-white text-lg tracking-tight font-exo">LMS<span class="text-cyan-400">.AI</span></span>
                    </div>
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2 text-slate-400 hover:text-white bg-white/5 rounded-lg border border-white/10 transition-colors">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <transition enter-active-class="transition duration-200 ease-out" enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform -translate-y-4 opacity-0">
                    <div v-if="showingNavigationDropdown" class="md:hidden bg-slate-900/95 border-b border-white/10 relative z-40 shadow-2xl backdrop-blur-xl">
                         <div class="p-4 space-y-2">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                                Lớp: {{ currentTeam.name }}
                            </ResponsiveNavLink>
                            <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                <ResponsiveNavLink v-if="currentTeam" :href="route('teams.show', currentTeam)" :active="route().current('teams.show')">Cài đặt lớp</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('questions.index')" :active="route().current('questions.index')">Ngân hàng câu hỏi</ResponsiveNavLink>
                            </template>
                            <ResponsiveNavLink v-if="currentTeam":href="route('memory-shards.index', { teamId: currentTeam?.id })" :active="route().current('memory-shards.*')" class="text-teal-400">
                                Góc học tập (Memory)
                            </ResponsiveNavLink>
                            <div v-if="userRole === 'student'" class="border-t border-slate-800 pt-2 mt-2">
                                <ResponsiveNavLink :href="route('study.documents')" :active="route().current('study.documents')" class="text-purple-400">Kho Tài Liệu</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('study.mistakes')" :active="route().current('study.mistakes')" class="text-rose-400">Sửa Lỗi Sai</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('gym.index', { team_id: currentTeam?.id })" :active="route().current('gym.index')" class="text-orange-400">
                                    ⚡ Luyện Đề ({{ currentTeam ? currentTeam.name : 'Chung' }})
                                </ResponsiveNavLink>
                            </div>
                            <div class="border-t border-slate-800 my-2 pt-2"></div>
                             <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button" class="text-rose-400">Đăng xuất</ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </transition>

                <div class="relative min-h-full p-4 md:p-8">
>>>>>>> Stashed changes
                    <slot />
                </main>
    
                <JoinClassroomModal 
                    :show="showJoinModal" 
                    @close="showJoinModal = false" 
                />
            </div>
        </div>
<<<<<<< Updated upstream
    </template>
=======
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=JetBrains+Mono:wght@100..800&display=swap');

.font-exo { font-family: 'Exo 2', sans-serif; }

/* Enhanced Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(6, 182, 212, 0.5); }

/* Ambient Animations */
@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}
@keyframes pulse-slow {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(1.1); }
}
.animate-float { animation: float 10s infinite ease-in-out; }
.animate-pulse-slow { animation: pulse-slow 8s infinite ease-in-out; }
</style>
>>>>>>> Stashed changes
