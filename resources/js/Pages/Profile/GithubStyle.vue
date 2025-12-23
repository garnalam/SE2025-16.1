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

// Github Dark Colors
const getHeatmapColor = (level) => {
    const colors = [
        'bg-[#161b22] border-[#161b22]', 
        'bg-[#0e4429] border-[#0e4429]', 
        'bg-[#006d32] border-[#006d32]', 
        'bg-[#26a641] border-[#26a641]', 
        'bg-[#39d353] border-[#39d353]'  
    ];
    return colors[level] || colors[0];
};
</script>

<template>
    <AppLayout title="Legacy Profile View">
        <div class="max-w-[1280px] mx-auto py-8 px-4 md:px-6 bg-[#0d1117] text-[#c9d1d9] min-h-screen font-sans border-x border-[#30363d]">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <div class="md:col-span-3 lg:col-span-3 space-y-6">
                    <div class="group relative">
                        <div class="rounded-full border border-[#30363d] overflow-hidden aspect-square shadow-xl z-10 relative bg-[#0d1117]">
                            <img :src="$page.props.auth.user.profile_photo_url" class="w-full h-full object-cover">
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
                        <div class="hover:text-[#58a6ff] cursor-pointer">
                            <span class="font-bold text-white">{{ stats.followers }}</span> followers
                        </div>
                        <div class="hover:text-[#58a6ff] cursor-pointer">
                            <span class="font-bold text-white">{{ stats.following }}</span> following
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
                    
                    <div class="border-b border-[#30363d] mb-6">
                        <nav class="flex gap-6 text-sm">
                            <a href="#" class="pb-3 border-b-2 border-[#f78166] font-bold text-white flex items-center gap-2">
                                Tổng quan 
                            </a>
                            <a href="#" class="pb-3 border-b-2 border-transparent hover:border-[#8b949e] text-[#c9d1d9] flex items-center gap-2">
                                Cài đặt
                            </a>
                        </nav>
                    </div>

                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-[16px] font-normal text-white">Pinned</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="mission in pinnedMissions" :key="mission.id" class="border border-[#30363d] rounded-md p-4 bg-[#0d1117] hover:border-[#8b949e] transition flex flex-col justify-between h-[130px]">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-bold text-[#58a6ff] text-sm hover:underline cursor-pointer truncate">{{ mission.title }}</span>
                                        <span class="border border-[#30363d] rounded-full px-2 text-[10px] text-[#8b949e] font-bold">Public</span>
                                    </div>
                                    <p class="text-xs text-[#8b949e] line-clamp-2">
                                        {{ mission.class_name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-[16px] font-normal text-white">{{ stats.total_contributions }} contributions in the last year</h2>
                        </div>

                        <div class="border border-[#30363d] rounded-md p-4 bg-[#0d1117] overflow-x-auto">
                            <div class="flex gap-1 min-w-max">
                                <div v-for="week in 52" :key="week" class="flex flex-col gap-1">
                                    <div v-for="day in 7" :key="day" 
                                         class="w-[10px] h-[10px] rounded-[2px] border"
                                         :class="getHeatmapColor(heatmap[((week-1)*7) + (day-1)]?.level || 0)">
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