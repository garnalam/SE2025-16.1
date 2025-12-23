<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserBadges from '@/Pages/Profile/Partials/UserBadges.vue';
import UserListModal from '@/Pages/Profile/Partials/UserListModal.vue';

const props = defineProps({
    profileUser: Object,
    isFollowing: Boolean,
    heatmap: Array,
    pinnedMissions: Array,
    stats: Object,
    organizations: Array,
    badges: Array,
});

// --- Modal Logic ---
const showModal = ref(false);
const modalType = ref(null);

const openFollowers = () => { modalType.value = 'followers'; showModal.value = true; };
const openFollowing = () => { modalType.value = 'following'; showModal.value = true; };

const toggleFollow = () => {
    if (props.isFollowing) {
        router.delete(route('user.unfollow', props.profileUser.id), { preserveScroll: true });
    } else {
        router.post(route('user.follow', props.profileUser.id), {}, { preserveScroll: true });
    }
};

const getHeatmapColor = (level) => {
    const colors = [
        'bg-slate-800 border-slate-700',       
        'bg-cyan-900/60 border-cyan-800',     
        'bg-cyan-700 border-cyan-600',        
        'bg-cyan-500 border-cyan-400',        
        'bg-cyan-300 border-white shadow-[0_0_8px_cyan]' 
    ];
    return colors[level] || colors[0];
};
</script>

<template>
    <AppLayout :title="profileUser.name">
        <div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 text-slate-300">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- LEFT COLUMN -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="relative group">
                        <div class="w-full aspect-square rounded-2xl border border-slate-700 bg-slate-900 overflow-hidden relative mb-6 ring-1 ring-white/10 shadow-2xl">
                            <img :src="profileUser.profile_photo_url" class="w-full h-full object-cover">
                            <!-- Overlay Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>
                            
                            <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                <div class="bg-black/60 backdrop-blur border border-cyan-500/50 rounded-lg px-2 py-1 text-white text-xs font-bold font-mono">
                                    LVL {{ profileUser.level || 1 }}
                                </div>
                            </div>
                        </div>
                        <h1 class="text-3xl font-black text-white uppercase font-exo tracking-wide">{{ profileUser.name }}</h1>
                        <p class="text-sm text-slate-500 font-mono mt-1">{{ profileUser.email }}</p>
                    </div>

                    <div class="text-sm text-slate-300 leading-relaxed font-sans border-l-2 border-cyan-500 pl-3">
                        {{ stats.bio || 'No bio signature.' }}
                    </div>

                    <div v-if="$page.props.auth.user.id !== profileUser.id">
                        <button 
                            @click="toggleFollow"
                            class="w-full py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider transition shadow-lg border flex items-center justify-center gap-2 group"
                            :class="isFollowing 
                                ? 'bg-slate-800 text-slate-400 border-slate-600 hover:border-rose-500 hover:text-rose-500' 
                                : 'bg-cyan-600 text-white border-cyan-500 hover:bg-cyan-500 hover:shadow-[0_0_20px_cyan]'"
                        >
                            <span v-if="isFollowing">Connected</span>
                            <span v-else>+ Connect</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-6 text-sm text-slate-400 font-mono border-y border-slate-800 py-4">
                        <div @click="openFollowers" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1.5">
                            <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.followers_count }}</span> followers
                        </div>
                        <div @click="openFollowing" class="cursor-pointer hover:text-cyan-400 transition group flex items-center gap-1.5">
                            <span class="font-bold text-white group-hover:text-cyan-400">{{ stats.following_count }}</span> following
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-slate-500 text-[10px] uppercase tracking-[0.2em] mb-3 font-mono">Lớp học đã tham gia</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="team in organizations" :key="team.id" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border border-slate-700 hover:border-cyan-500 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] cursor-pointer transition-all duration-300 group" :title="team.name">
                                <img v-if="team.profile_photo_url" :src="team.profile_photo_url" class="w-full h-full object-cover">
                                <span v-else class="text-slate-400 font-bold text-xs group-hover:text-white font-mono">{{ team.name.substring(0,2).toUpperCase() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6" v-if="profileUser.role === 'student'">
                         <UserBadges :badges="badges || []" />
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="lg:col-span-9 space-y-8 animate-fade-in-up">
                    
                    <!-- Pinned -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">Các thành tựu được ghim</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="mission in pinnedMissions" :key="mission.id" class="group bg-[#0f172a] border border-slate-800 rounded-xl p-5 hover:border-cyan-500/50 transition-all duration-300 hover:shadow-[0_0_30px_rgba(6,182,212,0.15)] flex flex-col justify-between min-h-[140px] relative overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition duration-500"></div>

                                <div>
                                    <div class="flex items-center gap-2 mb-2 relative z-10">
                                        <svg class="w-4 h-4 text-slate-500 group-hover:text-cyan-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span class="font-bold text-cyan-400 text-sm hover:underline cursor-pointer truncate font-mono">{{ mission.title }}</span>
                                        <span class="border border-slate-700 bg-slate-900 rounded-full px-2 py-0.5 text-[9px] text-slate-400 font-bold uppercase tracking-wider ml-auto">Public</span>
                                    </div>
                                    <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                        Mission completed in sector <span class="text-slate-300 font-bold">{{ mission.class_name }}</span>.
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
                            
                            <div v-if="!pinnedMissions || pinnedMissions.length === 0" class="col-span-full border border-dashed border-slate-800 rounded-xl p-8 text-center bg-[#0f172a]/50">
                                <span class="text-sm text-slate-500 font-mono">No public achievements pinned.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Heatmap -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider font-exo">{{ stats.total_contributions }} contributions</h2>
                        </div>

                        <div class="border border-slate-800 rounded-xl p-5 bg-[#0f172a] overflow-x-auto custom-scrollbar shadow-lg relative">
                            <div class="flex gap-[3px] min-w-max">
                                <div v-for="week in 52" :key="week" class="flex flex-col gap-[3px]">
                                    <div v-for="day in 7" :key="day" 
                                         class="w-[11px] h-[11px] rounded-[2px] border border-transparent"
                                         :class="getHeatmapColor(heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].level : 0)"
                                         :title="(heatmap && heatmap[((week-1)*7) + (day-1)] ? heatmap[((week-1)*7) + (day-1)].count : 0) + ' contributions'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <UserListModal 
            :show="showModal" 
            :title="modalType === 'followers' ? 'Danh sách người dùng đã follow' : 'Danh sách người dùng đang follow'"
            :users="modalType === 'followers' ? stats.followers_list : stats.following_list"
            @close="showModal = false"
        />
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>