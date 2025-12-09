<script setup>
    import SectionBorder from '@/Components/SectionBorder.vue';
    
    defineProps({
        badges: Array,
    });
    </script>
    
    <template>
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium text-gray-900">Bộ sưu tập Huy hiệu</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Những thành tích bạn đã đạt được trong quá trình học tập.
                    </p>
                </div>
            </div>
    
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-md">
                    
                    <div v-if="badges.length === 0" class="text-center py-8 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-2 opacity-50">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                        Chưa có huy hiệu nào. Hãy tích cực học tập nhé!
                    </div>
    
                    <div v-else class="grid grid-cols-2 gap-6"> <div v-for="badge in badges" :key="badge.id" class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition shadow-sm hover:shadow-md">
                            
                            <div class="w-40 h-40 mb-4 bg-yellow-50 rounded-full border-4 border-yellow-200 shadow-md overflow-hidden relative">
                                 
                                 <img v-if="badge.icon_path && badge.icon_path !== 'badges/default.png'" 
                                      :src="'/storage/' + badge.icon_path" 
                                      class="w-full h-full object-cover" 
                                      :alt="badge.name">
                                 
                                 <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-20 h-20 text-yellow-500">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                    </svg>
                                 </div>
                            </div>
                            
                            <div class="text-center w-full px-2">
                                <h4 class="text-lg font-bold text-gray-900">{{ badge.name }}</h4> <p class="text-sm text-gray-600 mt-1" :title="badge.description">
                                    {{ badge.description }}
                                </p>
                                <p class="text-xs text-gray-400 mt-3 border-t pt-2 w-full">
                                    Nhận ngày: {{ new Date(badge.pivot.awarded_at).toLocaleDateString('vi-VN') }}
                                </p>
                            </div>
                        </div>
                    </div>
    
                </div>
            </div>
        </div>
        
        <SectionBorder />
    </template>