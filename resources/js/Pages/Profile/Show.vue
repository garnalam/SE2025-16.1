<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UserBadges from '@/Pages/Profile/Partials/UserBadges.vue';
import UserListModal from '@/Pages/Profile/Partials/UserListModal.vue'; // <--- [MỚI] Import Modal

const props = defineProps({
    heatmap: Array,
    pinnedMissions: Array,
    stats: Object,
    organizations: Array,
});

const activeTab = ref('overview'); 

// --- [MỚI] Logic xử lý Modal Follow ---
const showModal = ref(false);
const modalType = ref(null); // 'followers' hoặc 'following'

const openFollowers = () => {
    modalType.value = 'followers';
    showModal.value = true;
};

const openFollowing = () => {
    modalType.value = 'following';
    showModal.value = true;
};
// --------------------------------------

// Màu Heatmap
const getHeatmapColor = (level) => {
    const colors = [
        'bg-slate-800',       
        'bg-cyan-900/60',     
        'bg-cyan-700',        
        'bg-cyan-500',        
        'bg-cyan-300 shadow-[0_0_10px_cyan]' 
    ];
    return colors[level] || colors[0];
};

const getLangColor = (lang) => {
    const map = { 'PHP': '#4F5D95', 'Vue': '#41B883', 'JS': '#f1e05a', 'Laravel': '#FF2D20' };
    return map[lang] || '#94a3b8';
};
</script>

<template>
    <AppLayout title="Hồ sơ cá nhân">
        <div class="max-w-[1400px] mx-auto py-6 text-slate-300">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-3 space-y-6">
                    <div class="group relative">
                        <div class="w-full aspect-square rounded-full border-4 border-slate-900 overflow-hidden relative mb-4 ring-2 ring-cyan-500/50 group-hover:ring-cyan-400 transition-all shadow-[0_0_40px_rgba(6,182,212,0.2)]">
                            <img :src="$page.props.auth.user.profile_photo_url" class="w-full h-full object-cover">
                        </div>
                        <h1 class="text-2xl font-black text-white leading-tight font-exo uppercase tracking-wide">{{ $page.props.auth.user.name }}</h1>
                        <p class="text-sm text-slate-500 font-mono">{{ $page.props.auth.user.email }}</p>
                    </div>

                    <div class="text-sm text-slate-300 italic border-l-2 border-cyan-500 pl-3 bg-cyan-500/5 py-2 pr-2 rounded-r">
                        {{ stats?.bio || 'Học viên chưa cập nhật giới thiệu.' }}
                    </div>

                    <button @click="activeTab = 'settings'" class="w-full py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-cyan-500/50 rounded-lg text-sm font-bold text-white transition shadow-lg">
                        Chỉnh sửa hồ sơ
                    </button>

<div class="flex items-center gap-4 text-sm text-slate-400 font-mono">
    <div @click="openFollowers" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1">
        <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.followers_count }}</span> followers
    </div>

    <div class="w-px h-3 bg-slate-700"></div>

    <div @click="openFollowing" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1">
        <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.following_count }}</span> following
    </div>
</div>

<UserListModal 
    :show="showModal" 
    :title="modalType === 'followers' ? 'Người theo dõi' : 'Đang theo dõi'"
    :users="modalType === 'followers' ? stats.followers_list : stats.following_list"
    @close="showModal = false"
/>

                    <div class="border-t border-slate-800 my-4"></div>

                    <div>
                        <h3 class="font-bold text-slate-400 text-xs uppercase tracking-widest mb-3 font-mono">Lớp học</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="team in organizations" :key="team.id" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border border-slate-700 hover:border-cyan-500 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] cursor-pointer transition-all duration-300 group" :title="team.name">
                                <img v-if="team.profile_photo_url" :src="team.profile_photo_url" class="w-full h-full object-cover">
                                <span v-else class="text-slate-400 font-bold text-xs group-hover:text-white">{{ team.name.substring(0,2).toUpperCase() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6" v-if="$page.props.auth.user.role === 'student'">
                         <UserBadges :badges="$page.props.auth.user.badges || []" />
                    </div>
                </div>

                <div class="lg:col-span-9">
                    
                    <div class="border-b border-slate-800 mb-6 sticky top-0 bg-[#020617]/95 backdrop-blur z-30 pt-2">
                        <nav class="flex gap-8 text-sm font-mono">
                            <button @click="activeTab = 'overview'" 
                                class="pb-3 border-b-2 flex items-center gap-2 transition-all duration-300 uppercase tracking-wider font-bold"
                                :class="activeTab === 'overview' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                Tổng quan
                            </button>
                            <button @click="activeTab = 'settings'" 
                                class="pb-3 border-b-2 flex items-center gap-2 transition-all duration-300 uppercase tracking-wider font-bold"
                                :class="activeTab === 'settings' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Cài đặt tài khoản
                            </button>
                        </nav>
                    </div>

                    <div v-if="activeTab === 'overview'" class="space-y-8 animate-fade-in">
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">Bài làm tiêu biểu</h2>
                                <!-- <span class="text-xs text-cyan-400 hover:text-cyan-300 cursor-pointer font-mono hover:underline">Tùy chỉnh</span> -->
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="mission in pinnedMissions" :key="mission.id" class="group bg-[#0f172a] border border-slate-800 rounded-xl p-4 hover:border-cyan-500/50 transition-all duration-300 hover:shadow-[0_0_20px_rgba(6,182,212,0.15)] flex flex-col justify-between h-[130px] relative overflow-hidden">
                                    <div class="absolute -right-10 -top-10 w-20 h-20 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition"></div>

                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <span class="font-bold text-cyan-400 text-sm hover:underline cursor-pointer truncate font-mono">{{ mission.title }}</span>
                                            <span class="border border-slate-700 bg-slate-900 rounded-full px-2 py-0.5 text-[9px] text-slate-400 font-bold uppercase tracking-wider">Public</span>
                                        </div>
                                        <p class="text-xs text-slate-400 line-clamp-2">Nhiệm vụ thuộc lớp <span class="text-slate-300 font-bold">{{ mission.class_name }}</span>. Đã hoàn thành xuất sắc.</p>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-slate-500 mt-3 font-mono">
                                        <!-- <div class="flex items-center gap-1.5"> -->
                                            <!-- <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: getLangColor(mission.language) }"></span> -->
                                            <!-- {{ mission.language }} -->
                                        <!-- </div> -->
                                        <div class="flex items-center gap-1 text-yellow-500">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ mission.grade }}/{{ mission.max_points }}
                                        </div>
                                        <div class="text-slate-600">{{ mission.updated_at }}</div>
                                    </div>
                                </div>
                                <div v-if="!pinnedMissions || pinnedMissions.length === 0" class="col-span-2 border border-dashed border-slate-800 rounded-xl p-8 text-center text-sm text-slate-500 bg-[#0f172a]/50">
                                    Chưa có bài tập nổi bật nào để hiển thị.
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">{{ stats?.total_contributions || 0 }} contributions (Năm nay)</h2>
                                <div class="text-xs text-slate-500 font-mono">Dữ liệu hoạt động</div>
                            </div>

                            <div class="border border-slate-800 rounded-xl p-4 bg-[#0f172a] overflow-x-auto custom-scrollbar shadow-lg">
                                <div class="flex gap-[3px] min-w-max">
                                    <div v-for="week in 52" :key="week" class="flex flex-col gap-[3px]">
                                        <div v-for="day in 7" :key="day" 
                                             class="w-[11px] h-[11px] rounded-[2px] transition-all hover:ring-1 hover:ring-white relative group"
                                             :class="getHeatmapColor(heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].level : 0)">
                                             <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block whitespace-nowrap bg-black text-white text-[10px] py-1 px-2 rounded z-20 pointer-events-none border border-slate-700">
                                                {{ heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].count : 0 }} hoạt động
                                             </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end items-center gap-2 mt-3 text-[10px] text-slate-500 font-mono">
                                    <span>Ít</span>
                                    <div class="w-[10px] h-[10px] bg-slate-800 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-900/60 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-700 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-500 rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-cyan-300 rounded-[2px]"></div>
                                    <span>Nhiều</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'settings'" class="animate-fade-in space-y-10">
                        
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
    </AppLayout>
</template>

<style scoped>
/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

/* === Ghi đè Style mặc định của Jetstream sang Dark Mode === */
:deep(.bg-white) {
    background-color: #0f172a !important; 
    border: 1px solid #1e293b; 
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

:deep(input), :deep(select), :deep(textarea) {
    background-color: #020617 !important; 
    border-color: #334155 !important; 
    color: white !important;
}
:deep(input:focus), :deep(select:focus), :deep(textarea:focus) {
    border-color: #06b6d4 !important; 
    box-shadow: 0 0 0 1px #06b6d4 !important;
}

:deep(h3.text-lg), :deep(h3.font-medium) {
    color: white !important;
    font-family: 'Exo 2', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

:deep(.text-gray-600), 
:deep(.text-gray-700), 
:deep(.text-gray-800), 
:deep(.text-gray-900) {
    color: #94a3b8 !important; 
}

:deep(label) {
    color: #cbd5e1 !important; 
    font-weight: 600;
}

:deep(button) {
    transition: all 0.2s;
}
</style>