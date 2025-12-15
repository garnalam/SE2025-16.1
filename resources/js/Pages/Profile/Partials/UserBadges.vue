<script setup>
import SectionBorder from '@/Components/SectionBorder.vue';

defineProps({
    badges: Array,
});
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <!-- Title Section -->
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-black text-white font-exo uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                    Hall of Fame
                </h3>
                <p class="mt-2 text-sm text-slate-400 font-mono">
                    Bộ sưu tập các thành tựu và vinh quang bạn đã chinh phục.
                </p>
            </div>
        </div>

        <!-- Content Section -->
        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="bg-[#0f172a] border border-slate-800 rounded-3xl shadow-[0_0_40px_rgba(0,0,0,0.3)] relative overflow-hidden">
                
                <!-- Background Ambient Light -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-[80px] pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-[80px] pointer-events-none"></div>

                <div class="px-4 py-6 sm:p-8 relative z-10">
                    
                    <!-- Empty State -->
                    <div v-if="badges.length === 0" class="flex flex-col items-center justify-center py-12 text-center border-2 border-dashed border-slate-800 rounded-2xl bg-slate-900/30">
                        <div class="w-16 h-16 mb-4 rounded-full bg-slate-800 flex items-center justify-center text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                        </div>
                        <h4 class="text-slate-400 font-exo uppercase font-bold tracking-wider">Vault Empty</h4>
                        <p class="text-xs text-slate-500 mt-1 font-mono">Hoàn thành nhiệm vụ để mở khóa huy hiệu.</p>
                    </div>

                    <!-- Badge Grid -->
                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-6"> 
                        <div v-for="badge in badges" :key="badge.id" 
                             class="group relative flex flex-col items-center p-4 rounded-2xl bg-gradient-to-b from-slate-800/50 to-slate-900/50 border border-slate-700 hover:border-amber-500/50 transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(245,158,11,0.15)] overflow-hidden">
                            
                            <!-- Shine Effect -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition duration-700 transform translate-y-full group-hover:-translate-y-full pointer-events-none"></div>

                            <!-- Icon Container -->
                            <div class="relative w-24 h-24 mb-4 flex items-center justify-center">
                                <!-- Glow Background -->
                                <div class="absolute inset-0 bg-amber-500/20 rounded-full blur-xl group-hover:bg-amber-500/30 transition duration-500"></div>
                                
                                <div class="relative w-full h-full rounded-full border-2 border-slate-700 bg-slate-900/80 flex items-center justify-center p-3 group-hover:border-amber-400 transition-colors duration-500 shadow-inner">
                                     <img v-if="badge.icon_path && badge.icon_path !== 'badges/default.png'" 
                                          :src="'/storage/' + badge.icon_path" 
                                          class="w-full h-full object-contain drop-shadow-[0_4px_6px_rgba(0,0,0,0.5)] transform group-hover:scale-110 transition duration-500" 
                                          :alt="badge.name">
                                     
                                     <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-amber-500 drop-shadow-[0_0_10px_rgba(245,158,11,0.5)]">
                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                        </svg>
                                     </div>
                                </div>
                            </div>
                            
                            <!-- Text Info -->
                            <div class="text-center w-full relative z-10">
                                <h4 class="text-sm font-bold text-slate-200 group-hover:text-amber-400 transition font-exo uppercase tracking-wide truncate">
                                    {{ badge.name }}
                                </h4> 
                                <p class="text-[10px] text-slate-500 mt-1 line-clamp-2 min-h-[2.5em] leading-relaxed">
                                    {{ badge.description }}
                                </p>
                                
                                <div class="mt-3 pt-2 border-t border-slate-700/50 w-full flex justify-between items-center text-[9px] font-mono text-slate-600">
                                    <span class="uppercase">Acquired</span>
                                    <span class="text-emerald-500 font-bold">{{ new Date(badge.pivot.awarded_at).toLocaleDateString('vi-VN') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <SectionBorder />
</template>