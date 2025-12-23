<script setup>
import Modal from '@/Components/Modal.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    show: Boolean,
    title: String,
    users: Array,
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close">
        <div class="bg-[#0f172a] border border-slate-700/50 rounded-2xl overflow-hidden shadow-[0_0_50px_rgba(0,0,0,0.5)]">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-700 bg-slate-900/80 backdrop-blur-md flex justify-between items-center">
                <h3 class="text-lg font-bold text-white font-exo uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-cyan-500 rounded-full"></span>
                    {{ title }}
                </h3>
                <button @click="close" class="text-slate-400 hover:text-white transition transform hover:rotate-90 duration-200">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- List -->
            <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar bg-slate-900/50">
                <div v-if="users && users.length > 0" class="space-y-3">
                    <div v-for="user in users" :key="user.id" 
                         class="flex items-center justify-between p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:border-cyan-500/30 hover:bg-slate-800 transition-all group">
                        
                        <Link :href="route('profile.public', user.id)" class="flex items-center gap-4">
                            <div class="relative">
                                <img :src="user.profile_photo_url" class="w-10 h-10 rounded-lg object-cover border border-slate-600 group-hover:border-cyan-400 transition">
                                <div class="absolute -bottom-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-200 group-hover:text-cyan-400 font-exo transition">{{ user.name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ user.email }}</div>
                            </div>
                        </Link>
                        
                        <Link :href="route('profile.public', user.id)" class="px-4 py-1.5 bg-slate-900 border border-slate-600 rounded-lg text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:bg-cyan-600 hover:text-white hover:border-cyan-500 transition shadow-lg">
                            Profile
                        </Link>
                    </div>
                </div>
                
                <div v-else class="flex flex-col items-center justify-center py-12 text-slate-500 opacity-60">
                    <svg class="w-12 h-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="text-xs font-mono">NO DATA FOUND</span>
                </div>
            </div>
        </div>
    </Modal>
</template>