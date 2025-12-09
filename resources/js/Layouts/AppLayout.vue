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
                    <slot />
                </main>
    
                <JoinClassroomModal 
                    :show="showJoinModal" 
                    @close="showJoinModal = false" 
                />
            </div>
        </div>
    </template>