<script setup>
import SectionBorder from '@/Components/SectionBorder.vue';

defineProps({
    badges: Array,
});
</script>

<template>
    <div class="mt-8">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 font-mono flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
            Huy hiệu ({{ badges ? badges.length : 0 }})
        </h3>

        <div v-if="!badges || badges.length === 0" class="text-sm text-slate-500 italic pl-2 border-l-2 border-slate-700">
            Chưa có huy hiệu nào.
        </div>

        <div v-else class="flex flex-wrap gap-4">
            <div v-for="badge in badges" :key="badge.id" class="group relative">
                
                <div class="w-16 h-16 rounded-full bg-[#0f172a] border-2 border-slate-700 group-hover:border-amber-500 group-hover:shadow-[0_0_15px_rgba(245,158,11,0.5)] transition-all duration-300 flex items-center justify-center cursor-help relative z-10 overflow-hidden p-0.5">
                    
                    <img v-if="badge.icon_path" 
                         :src="'/storage/' + badge.icon_path" 
                         class="w-full h-full object-contain drop-shadow-md group-hover:scale-110 transition duration-300" 
                         :alt="badge.name">
                    
                    <div v-else class="w-full h-full bg-slate-800 flex items-center justify-center rounded-full">
                       <span class="text-lg font-bold text-slate-500 select-none">{{ badge.name.substring(0,1) }}</span>
                    </div>
                </div>

                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max max-w-[150px] opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-20">
                    <div class="bg-black/90 text-white text-[10px] font-bold py-1 px-2 rounded border border-slate-700 shadow-xl text-center">
                        <div class="text-amber-400 uppercase tracking-wider mb-0.5">{{ badge.name }}</div>
                        <div class="text-slate-400 font-mono text-[9px]">{{ new Date(badge.pivot.awarded_at).toLocaleDateString('vi-VN') }}</div>
                    </div>
                    <div class="w-2 h-2 bg-black/90 border-r border-b border-slate-700 transform rotate-45 absolute left-1/2 -translate-x-1/2 -bottom-1"></div>
                </div>

            </div>
        </div>
    </div>

    <SectionBorder />
</template>