<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserBadges from '@/Pages/Profile/Partials/UserBadges.vue';
import UserListModal from '@/Pages/Profile/Partials/UserListModal.vue'; // <--- Import Modal

const props = defineProps({
    profileUser: Object,
    isFollowing: Boolean,
    heatmap: Array,
    pinnedMissions: Array,
    stats: Object,
    organizations: Array,
    badges: Array,
});

// --- LOGIC MODAL FOLLOW ---
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
// --------------------------

const toggleFollow = () => {
    if (props.isFollowing) {
        router.delete(route('user.unfollow', props.profileUser.id), { preserveScroll: true });
    } else {
        router.post(route('user.follow', props.profileUser.id), {}, { preserveScroll: true });
    }
};

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
    <AppLayout :title="profileUser.name">
        <div class="max-w-[1400px] mx-auto py-6 text-slate-300">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-3 space-y-6">
                    <div class="group relative">
                        <div class="w-full aspect-square rounded-full border-4 border-slate-900 overflow-hidden relative mb-4 ring-2 ring-cyan-500/50 shadow-[0_0_40px_rgba(6,182,212,0.2)]">
                            <img :src="profileUser.profile_photo_url" class="w-full h-full object-cover">
                        </div>
                        <h1 class="text-2xl font-black text-white uppercase font-exo">{{ profileUser.name }}</h1>
                        <p class="text-sm text-slate-500 font-mono">{{ profileUser.email }}</p>
                    </div>

                    <div class="text-sm text-slate-300 italic border-l-2 border-cyan-500 pl-3 bg-cyan-500/5 py-2 pr-2 rounded-r">
                        {{ stats.bio }}
                    </div>

                    <div v-if="$page.props.auth.user.id !== profileUser.id">
                        <button 
                            @click="toggleFollow"
                            class="w-full py-2 rounded-lg text-sm font-bold uppercase tracking-wider transition shadow-lg border flex items-center justify-center gap-2"
                            :class="isFollowing 
                                ? 'bg-slate-800 text-slate-400 border-slate-600 hover:border-rose-500 hover:text-rose-500' 
                                : 'bg-cyan-600 text-white border-cyan-500 hover:bg-cyan-500 hover:shadow-[0_0_20px_cyan]'"
                        >
                            <span v-if="isFollowing">Đang theo dõi</span>
                            <span v-else>+ Theo dõi</span>
                        </button>
                    </div>

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
                        <h3 class="font-bold text-slate-400 text-xs uppercase tracking-widest mb-3 font-mono">Lớp học tham gia</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="team in organizations" :key="team.id" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border border-slate-700 hover:border-cyan-500 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] cursor-pointer transition-all duration-300 group" :title="team.name">
                                <img v-if="team.profile_photo_url" :src="team.profile_photo_url" class="w-full h-full object-cover">
                                <span v-else class="text-slate-400 font-bold text-xs group-hover:text-white">{{ team.name.substring(0,2).toUpperCase() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6" v-if="profileUser.role === 'student'">
                         <UserBadges :badges="badges || []" />
                    </div>
                </div>

                <div class="lg:col-span-9 space-y-8 animate-fade-in">
                    
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">Bài làm tiêu biểu</h2>
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
                                    <p class="text-xs text-slate-400 line-clamp-2">Nhiệm vụ thuộc lớp <span class="text-slate-300 font-bold">{{ mission.class_name }}</span>.</p>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-slate-500 mt-3 font-mono">
                                    <!-- <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: getLangColor(mission.language) }"></span>
                                        {{ mission.language }}
                                    </div> -->
                                    <div class="flex items-center gap-1 text-yellow-500">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ mission.grade }}/{{ mission.max_points }}
                                    </div>
                                    <div class="text-slate-600">{{ mission.updated_at }}</div>
                                </div>
                            </div>
                            <div v-if="!pinnedMissions || pinnedMissions.length === 0" class="col-span-2 border border-dashed border-slate-800 rounded-xl p-8 text-center text-sm text-slate-500 bg-[#0f172a]/50">
                                Chưa có bài tập tiêu biểu nào.
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">{{ stats.total_contributions }} contributions (Năm nay)</h2>
                        </div>

                        <div class="border border-slate-800 rounded-xl p-4 bg-[#0f172a] overflow-x-auto custom-scrollbar shadow-lg">
                            <div class="flex gap-[3px] min-w-max">
                                <div v-for="week in 52" :key="week" class="flex flex-col gap-[3px]">
                                    <div v-for="day in 7" :key="day" 
                                         class="w-[11px] h-[11px] rounded-[2px] transition-all relative group"
                                         :class="getHeatmapColor(heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].level : 0)">
                                         <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block whitespace-nowrap bg-black text-white text-[10px] py-1 px-2 rounded z-20 pointer-events-none border border-slate-700">
                                            {{ heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].count : 0 }} hoạt động
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>