<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    stats: Object,
    heatmap: Array,
    pinnedMissions: Array,
    organizations: Array,
    badges: Array
});

// Hàm lấy màu cho Heatmap (Style Cyberpunk/Github Dark)
const getHeatmapColor = (level) => {
    // 0: Grey, 1-4: Shades of Green/Cyan
    const colors = [
        'bg-[#161b22]', // Level 0 (Empty)
        'bg-[#0e4429]', // Level 1
        'bg-[#006d32]', // Level 2
        'bg-[#26a641]', // Level 3
        'bg-[#39d353]'  // Level 4 (Highest)
    ];
    // Nếu bạn muốn màu Cyan Cyberpunk thì đổi mã màu ở trên
    return colors[level] || colors[0];
};

// Hàm lấy màu ngôn ngữ lập trình (Trang trí)
const getLangColor = (lang) => {
    const map = { 'PHP': '#4F5D95', 'Vue': '#41B883', 'JS': '#f1e05a', 'Laravel': '#FF2D20' };
    return map[lang] || '#ccc';
};
</script>

<template>
    <AppLayout title="Hồ sơ cá nhân">
        <div class="max-w-[1280px] mx-auto py-8 px-4 md:px-6 bg-[#0d1117] text-[#c9d1d9] min-h-screen font-sans">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <div class="md:col-span-3 lg:col-span-3 space-y-6 relative">
                    <div class="group relative">
                        <div class="rounded-full border border-[#30363d] overflow-hidden aspect-square shadow-xl z-10 relative bg-[#0d1117]">
                            <img :src="$page.props.auth.user.profile_photo_url" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                        <div class="absolute bottom-10 right-0 bg-[#21262d] border border-[#30363d] w-8 h-8 rounded-full flex items-center justify-center shadow-lg text-sm z-20 cursor-pointer hover:text-cyan-400">
                            🎯
                        </div>
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold text-white leading-tight">{{ $page.props.auth.user.name }}</h1>
                        <p class="text-xl text-[#8b949e] font-light">{{ $page.props.auth.user.email }}</p>
                    </div>

                    <div class="text-[14px] text-white">
                        {{ stats.bio }}
                    </div>

                    <button class="w-full py-1.5 bg-[#21262d] border border-[#30363d] rounded-md text-sm font-bold text-[#c9d1d9] hover:bg-[#30363d] hover:border-[#8b949e] transition">
                        Edit profile
                    </button>

                    <div class="flex items-center gap-4 text-sm text-[#8b949e]">
                        <div class="hover:text-cyan-400 cursor-pointer">
                            <span class="font-bold text-white">{{ stats.followers }}</span> followers
                        </div>
                        <div class="hover:text-cyan-400 cursor-pointer">
                            <span class="font-bold text-white">{{ stats.following }}</span> following
                        </div>
                    </div>

                    <div class="border-t border-[#30363d] my-4"></div>

                    <div>
                        <h3 class="font-bold text-white text-sm mb-2">Achievements</h3>
                        <div class="flex flex-wrap gap-2">
                            <img src="https://github.githubassets.com/images/modules/profile/achievements/pull-shark-default.png" class="w-16 h-16" title="Code Shark">
                            <img src="https://github.githubassets.com/images/modules/profile/achievements/yolo-default.png" class="w-16 h-16" title="YOLO Deploy">
                            </div>
                    </div>

                    <div class="border-t border-[#30363d] my-4"></div>

                    <div>
                        <h3 class="font-bold text-white text-sm mb-2">Organizations</h3>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="org in organizations" :key="org.id" class="w-9 h-9 bg-white rounded-md flex items-center justify-center overflow-hidden border border-[#30363d] cursor-pointer" :title="org.name">
                                <img v-if="org.avatar_url" :src="org.avatar_url" class="w-full h-full">
                                <span v-else class="text-black font-bold text-xs">{{ org.name.substring(0,2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-9 lg:col-span-9">
                    
                    <div class="border-b border-[#30363d] mb-6 sticky top-0 bg-[#0d1117] z-30 pt-4">
                        <nav class="flex gap-6 text-sm">
                            <a href="#" class="pb-3 border-b-2 border-[#f78166] font-bold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#8b949e]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                Overview
                            </a>
                            <a href="#" class="pb-3 border-b-2 border-transparent hover:border-[#8b949e] text-[#c9d1d9] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#8b949e]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                Missions <span class="bg-[#30363d] text-[#c9d1d9] rounded-full px-2 text-xs">15</span>
                            </a>
                            <a href="#" class="pb-3 border-b-2 border-transparent hover:border-[#8b949e] text-[#c9d1d9] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#8b949e]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                Projects <span class="bg-[#30363d] text-[#c9d1d9] rounded-full px-2 text-xs">3</span>
                            </a>
                        </nav>
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-[16px] font-normal text-white">Pinned</h2>
                            <span class="text-xs text-[#58a6ff] hover:underline cursor-pointer">Customize your pins</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="mission in pinnedMissions" :key="mission.id" class="border border-[#30363d] rounded-md p-4 bg-[#0d1117] hover:border-[#8b949e] transition flex flex-col justify-between h-[130px]">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-4 h-4 text-[#8b949e]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                        <span class="font-bold text-[#58a6ff] text-sm hover:underline cursor-pointer truncate">{{ mission.title }}</span>
                                        <span class="border border-[#30363d] rounded-full px-2 text-[10px] text-[#8b949e] font-bold">Public</span>
                                    </div>
                                    <p class="text-xs text-[#8b949e] line-clamp-2">
                                        Bài làm đạt điểm cao trong lớp {{ mission.class_name }}. Được đánh giá bởi AI và Giáo viên.
                                    </p>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-[#8b949e] mt-3">
                                    <!-- <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: getLangColor(mission.language) }"></span>
                                        {{ mission.language }}
                                    </div> -->
                                    <div class="hover:text-[#58a6ff] cursor-pointer flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                        {{ mission.grade }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-[16px] font-normal text-white">{{ stats.total_contributions }} contributions in the last year</h2>
                            <div class="text-xs text-[#8b949e]">Configuration settings</div>
                        </div>

                        <div class="border border-[#30363d] rounded-md p-4 bg-[#0d1117] overflow-x-auto">
                            <div class="flex gap-1 min-w-max">
                                <div v-for="week in 52" :key="week" class="flex flex-col gap-1">
                                    <div v-for="day in 7" :key="day" 
                                         class="w-[10px] h-[10px] rounded-[2px]"
                                         :class="getHeatmapColor(heatmap[((week-1)*7) + (day-1)]?.level || 0)"
                                         :title="heatmap[((week-1)*7) + (day-1)]?.date + ': ' + (heatmap[((week-1)*7) + (day-1)]?.count || 0) + ' contributions'">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-4 text-xs text-[#8b949e]">
                                <a href="#" class="hover:text-[#58a6ff]">Learn how we count contributions</a>
                                <div class="flex items-center gap-1">
                                    <span>Less</span>
                                    <div class="w-[10px] h-[10px] bg-[#161b22] rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-[#0e4429] rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-[#006d32] rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-[#26a641] rounded-[2px]"></div>
                                    <div class="w-[10px] h-[10px] bg-[#39d353] rounded-[2px]"></div>
                                    <span>More</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-[16px] font-normal text-white mb-4">Contribution activity</h2>
                        
                        <div class="relative pl-6 pb-6 border-l border-[#30363d] space-y-6">
                            <div class="relative">
                                <div class="absolute -left-[33px] bg-[#30363d] rounded-full p-1.5 border-[2px] border-[#0d1117]">
                                    <svg class="w-4 h-4 text-[#c9d1d9]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                </div>
                                <div class="flex justify-between items-baseline mb-2">
                                    <span class="text-sm font-bold text-white">Created a submission</span>
                                    <span class="text-xs text-[#8b949e]">Dec 17</span>
                                </div>
                                <div class="text-sm text-[#8b949e]">
                                    Created submission for <a href="#" class="text-[#58a6ff] hover:underline">assignment-laravel-crud</a> in class PHP-01
                                </div>
                            </div>
                            
                            <div class="relative">
                                <div class="absolute -left-[33px] bg-[#30363d] rounded-full p-1.5 border-[2px] border-[#0d1117]">
                                    <span class="text-xs">🏆</span>
                                </div>
                                <div class="flex justify-between items-baseline mb-2">
                                    <span class="text-sm font-bold text-white">Unlocked Achievement</span>
                                    <span class="text-xs text-[#8b949e]">Dec 15</span>
                                </div>
                                <div class="text-sm text-[#8b949e]">
                                    Earned the <span class="font-bold text-[#eac54f]">Bug Hunter</span> badge!
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-[#30363d] mt-4 pt-4 text-center">
                            <button class="w-full py-2 bg-transparent border border-[#30363d] rounded-md text-[#58a6ff] text-xs font-bold hover:bg-[#30363d] hover:text-white transition uppercase tracking-wider">
                                Show more activity
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>