<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UserBadges from '@/Pages/Profile/Partials/UserBadges.vue';
import UserListModal from '@/Pages/Profile/Partials/UserListModal.vue';

const props = defineProps({
    heatmap: Array,
    pinnedMissions: Array,
    stats: Object,
    organizations: Array,
});

const activeTab = ref('overview'); 

// --- Modal Logic ---
const showModal = ref(false);
const modalType = ref(null);

const openFollowers = () => { modalType.value = 'followers'; showModal.value = true; };
const openFollowing = () => { modalType.value = 'following'; showModal.value = true; };

// Heatmap Colors (Cyberpunk Shades)
const getHeatmapColor = (level) => {
    const colors = [
        'bg-slate-800 border-slate-700',       // Level 0
        'bg-cyan-900/60 border-cyan-800',      // Level 1
        'bg-cyan-700 border-cyan-600',         // Level 2
        'bg-cyan-500 border-cyan-400',         // Level 3
        'bg-cyan-300 border-white shadow-[0_0_8px_cyan]' // Level 4
    ];
    return colors[level] || colors[0];
};
</script>

<template>
    <AppLayout title="Operative Profile">
        <div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 text-slate-300">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- LEFT COLUMN: IDENTITY CARD -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="relative group">
                        <!-- Avatar Container -->
                        <div class="w-full aspect-square rounded-2xl border border-slate-700 bg-slate-900 overflow-hidden relative mb-6 ring-1 ring-white/10 shadow-2xl">
                            <img :src="$page.props.auth.user.profile_photo_url" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                            
                            <!-- Level Badge -->
                            <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                <div class="bg-black/60 backdrop-blur border border-cyan-500/50 rounded-lg px-2 py-1 text-white text-xs font-bold font-mono">
                                    LVL {{ $page.props.auth.user.level || 1 }}
                                </div>
                            </div>
                        </div>

                        <h1 class="text-3xl font-black text-white leading-tight font-exo uppercase tracking-wide">{{ $page.props.auth.user.name }}</h1>
                        <p class="text-sm text-slate-500 font-mono mt-1">{{ $page.props.auth.user.email }}</p>
                    </div>

                    <!-- Bio -->
                    <div class="text-sm text-slate-300 leading-relaxed font-sans border-l-2 border-slate-700 pl-3">
                        {{ stats?.bio || 'No bio signature detected.' }}
                    </div>

                    <!-- Action Buttons -->
                    <button @click="activeTab = 'settings'" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-cyan-500/50 rounded-xl text-sm font-bold text-white transition shadow-lg flex items-center justify-center gap-2 group">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        Chỉnh sửa Hồ sơ
                    </button>

                    <!-- Stats Row -->
                    <div class="flex items-center gap-6 text-sm text-slate-400 font-mono border-y border-slate-800 py-4">
                        <div @click="openFollowers" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.followers_count }}</span> followers
                        </div>
                        <div @click="openFollowing" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1.5">
                            <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.following_count }}</span> following
                        </div>
                    </div>

                    <!-- Teams/Orgs -->
                    <div>
                        <h3 class="font-bold text-slate-500 text-[10px] uppercase tracking-[0.2em] mb-3 font-mono">Lớp học đã tham gia</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="team in organizations" :key="team.id" 
                                 class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border border-slate-700 hover:border-cyan-500 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] cursor-pointer transition-all duration-300 group relative" 
                                 :title="team.name">
                                <img v-if="team.profile_photo_url" :src="team.profile_photo_url" class="w-full h-full object-cover">
                                <span v-else class="text-slate-400 font-bold text-xs group-hover:text-white font-mono">{{ team.name.substring(0,2).toUpperCase() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Badges (Mini View) -->
                    <div class="mt-6" v-if="$page.props.auth.user.role === 'student'">
                         <UserBadges :badges="$page.props.auth.user.badges || []" />
                    </div>
                </div>

                <!-- RIGHT COLUMN: MAIN CONTENT -->
                <div class="lg:col-span-9">
                    
                    <!-- Navigation Tabs -->
                    <div class="border-b border-slate-800 mb-8 sticky top-0 bg-[#020617]/90 backdrop-blur-md z-30 pt-2 transition-all">                        
                        <nav class="flex gap-8 text-sm font-mono">
                            <button @click="activeTab = 'overview'" 
                                class="pb-3 border-b-2 flex items-center gap-2 transition-all duration-300 uppercase tracking-widest text-xs font-bold"
                                :class="activeTab === 'overview' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-700'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                Tổng quan
                            </button>
                            <button @click="activeTab = 'settings'" 
                                class="pb-3 border-b-2 flex items-center gap-2 transition-all duration-300 uppercase tracking-widest text-xs font-bold"
                                :class="activeTab === 'settings' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-700'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Cài đặt
                            </button>
                        </nav>
                    </div>

                    <!-- TAB CONTENT: OVERVIEW -->
                    <div v-if="activeTab === 'overview'" class="space-y-8 animate-fade-in-up">
                        
                        <!-- Pinned Items -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">Các thành tựu được ghim</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="mission in pinnedMissions" :key="mission.id" class="group bg-[#0f172a] border border-slate-800 rounded-xl p-5 hover:border-cyan-500/50 transition-all duration-300 hover:shadow-[0_0_30px_rgba(6,182,212,0.15)] flex flex-col justify-between min-h-[140px] relative overflow-hidden">
                                    <!-- Glow -->
                                    <div class="absolute -right-10 -top-10 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition duration-500"></div>

                                    <div>
                                        <div class="flex items-center gap-2 mb-2 relative z-10">
                                            <svg class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <span class="font-bold text-cyan-400 text-sm hover:underline cursor-pointer truncate font-mono">{{ mission.title }}</span>
                                            <span class="border border-slate-700 bg-slate-900 rounded-full px-2 py-0.5 text-[9px] text-slate-400 font-bold uppercase tracking-wider ml-auto">Public</span>
                                        </div>
                                        <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                            Completed in class <span class="text-slate-300 font-bold">{{ mission.class_name }}</span>.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-slate-500 mt-4 font-mono relative z-10 border-t border-slate-800 pt-3">
                                        <div class="flex items-center gap-1 text-emerald-400">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ mission.grade }}/{{ mission.max_points }}
                                        </div>
                                        <div class="text-slate-600">{{ new Date(mission.updated_at).toLocaleDateString() }}</div>
                                    </div>
                                </div>
                                <div v-if="!pinnedMissions || pinnedMissions.length === 0" class="col-span-full border border-dashed border-slate-800 rounded-xl p-8 text-center bg-[#0f172a]/50 flex flex-col items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <span class="text-sm text-slate-500 font-mono">Chưa có thành tích nào được ghim lên</span>
                                </div>
                            </div>
                        </div>

                        <!-- Heatmap -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">{{ stats?.total_contributions || 0 }} hoạt động (Trong năm)</h2>
                                <div class="text-xs text-slate-500 font-mono">Ma trận hoạt động</div>
                            </div>

                            <div class="border border-slate-800 rounded-xl p-5 bg-[#0f172a] overflow-x-auto custom-scrollbar shadow-lg relative">
                                <div class="flex gap-[3px] min-w-max">
                                    <div v-for="week in 52" :key="week" class="flex flex-col gap-[3px]">
                                        <div v-for="day in 7" :key="day" 
                                             class="w-[11px] h-[11px] rounded-[2px] transition-all hover:ring-1 hover:ring-white relative group border border-transparent"
                                             :class="getHeatmapColor(heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].level : 0)">
                                             <!-- Tooltip -->
                                             <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block whitespace-nowrap bg-black/90 text-white text-[10px] py-1 px-2 rounded border border-slate-700 font-mono z-50">
                                                {{ heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].count : 0 }} events
                                                <span class="text-slate-500"> on </span>
                                                {{ heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].date : 'N/A' }}
                                             </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end items-center gap-2 mt-4 text-[10px] text-slate-500 font-mono">
                                    <span>Less</span>
                                    <div class="w-[10px] h-[10px] bg-slate-800 border border-slate-700 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-900/60 border border-cyan-800 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-700 border border-cyan-600 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-500 border border-cyan-400 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-300 border border-white rounded-[2px]"></div>
                                    <span>More</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB CONTENT: SETTINGS -->
                    <div v-if="activeTab === 'settings'" class="animate-fade-in-up space-y-10">
                        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                            <UpdateProfileInformationForm :user="$page.props.auth.user" />
                            <SectionBorder />
                        </div>

                        <div v-if="$page.props.jetstream.canUpdatePassword">
                            <UpdatePasswordForm />
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal for User Lists -->
        <UserListModal 
            :show="showModal" 
            :title="modalType === 'followers' ? 'Danh sách người dùng đã follow' : 'Danh sách người dùng đang follow'"
            :users="modalType === 'followers' ? stats.followers_list : stats.following_list"
            @close="showModal = false"
        />
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>