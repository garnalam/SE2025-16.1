<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // <-- Thêm 'usePage'
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import JoinClassroomModal from '@/Components/JoinClassroomModal.vue'; // <-- Bạn đã import cái này, tốt!

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

// ===== PHẦN CỦA BẠN (GIỮ NGUYÊN) =====
const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const allTeams = computed(() => page.props.auth.user.all_teams || []);
const currentTeam = computed(() => page.props.auth.user.current_team || null);
const currentTeamId = computed(() => page.props.auth.user.current_team_id || null);

const hasTeamFeatures = computed(() => page.props.jetstream.hasTeamFeatures);
const canCreateTeams = computed(() => page.props.jetstream.canCreateTeams);
// ======================================

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

// ===== THÊM MỚI (1) =====
// Biến trạng thái để điều khiển modal
const showJoinModal = ref(false);

// ===== SỬA ĐỔI (2) =====
// Đổi hàm này để mở modal, thay vì điều hướng
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
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <NavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                                    Lớp học hiện tại
                                </NavLink>

                                <!-- ===== THÊM MỚI (3): Nút "+" trên Nav Bar Desktop ===== -->
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
                                <!-- ============================================ -->

                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            
                            <!-- Teams Dropdown -->
                            <div class="ms-3 relative" v-if="hasTeamFeatures">
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <!-- ... (Code trigger của bạn, giữ nguyên) ... -->
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ currentTeam ? currentTeam.name : 'Chọn Lớp' }}
                                                <svg class="ms-2 -me-0.5 size-4">
                                                    <!-- ... -->
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            
                                            <!-- Giáo viên (Giữ nguyên) -->
                                            <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                                <!-- ... -->
                                                <DropdownLink :href="route('teams.create')">
                                                    Tạo Lớp học mới
                                                </DropdownLink>
                                            </template>
                                            
                                            <!-- Học sinh (Sửa hàm @click) -->
                                            <template v-if="userRole === 'student'">
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Lớp học (HS)
                                                </div>

                                                <!-- ===== SỬA ĐỔI (4) ===== -->
                                                <DropdownLink as="button" @click="openJoinModal">
                                                    Tham gia Lớp học
                                                </DropdownLink>
                                                <!-- ======================== -->
                                            </template>

                                            <!-- Chuyển Lớp học (Giữ nguyên) -->
                                            <template v-if="allTeams.length > 0">
                                                <!-- ... (Code chuyển team của bạn, giữ nguyên) ... -->
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                            
                            <!-- Settings Dropdown (Giữ nguyên) -->
                            <div class="ms-3 relative">
                                <!-- ... (Code dropdown setting của bạn, giữ nguyên) ... -->
                            </div>
                        </div>

                        <!-- Hamburger (Giữ nguyên) -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <!-- ... -->
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        
                        <ResponsiveNavLink v-if="currentTeam" :href="route('teams.feed', currentTeam)" :active="route().current('teams.feed')">
                            Lớp học hiện tại
                        </ResponsiveNavLink>
                        
                        <!-- ===== THÊM MỚI (5): Nút trên Nav Bar Mobile ===== -->
                        <ResponsiveNavLink 
                            v-if="userRole === 'student'"
                            as="button"
                            @click="openJoinModal"
                        >
                            Tham gia Lớp học mới
                        </ResponsiveNavLink>
                        <!-- ========================================= -->
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <!-- ... (Code setting responsive của bạn, giữ nguyên) ... -->
                        <div class="mt-3 space-y-1">
                            <!-- ... (Các link hồ sơ, API, đăng xuất...) ... -->

                            <!-- Phân quyền Mobile -->
                            <div class="border-t border-gray-200 mt-2 pt-2">
                                <!-- Giáo viên (Giữ nguyên) -->
                                <template v-if="userRole === 'teacher' || userRole === 'admin'">
                                    <!-- ... -->
                                </template>

                                <!-- Học sinh (Sửa hàm @click) -->
                                <template v-if="userRole === 'student'">
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Lớp học (HS)
                                    </div>
                                    <!-- ===== SỬA ĐỔI (6) ===== -->
                                    <ResponsiveNavLink as="button" @click="openJoinModal">
                                        Tham gia Lớp học
                                    </ResponsiveNavLink>
                                    <!-- ======================== -->
                                </template>

                                <!-- Chuyển Lớp học (Giữ nguyên) -->
                                <template v-if="allTeams.length > 0">
                                    <!-- ... -->
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- ===== THÊM MỚI (7): Thêm Modal vào cuối Layout ===== -->
            <JoinClassroomModal 
                :show="showJoinModal" 
                @close="showJoinModal = false" 
            />
            <!-- ============================================== -->

        </div>
    </div>
</template>
