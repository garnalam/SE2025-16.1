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
        <div class="bg-[#0f172a] border border-slate-700 rounded-lg overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-slate-700 bg-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white font-exo uppercase tracking-wider">
                    {{ title }}
                </h3>
                <button @click="close" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                <div v-if="users && users.length > 0" class="space-y-4">
                    <div v-for="user in users" :key="user.id" class="flex items-center justify-between group">
                        <Link :href="route('profile.public', user.id)" class="flex items-center gap-3">
                            <img :src="user.profile_photo_url" class="w-10 h-10 rounded-lg object-cover border border-slate-600 group-hover:border-cyan-500 transition">
                            <div>
                                <div class="text-sm font-bold text-slate-200 group-hover:text-cyan-400 font-exo">{{ user.name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ user.email }}</div>
                            </div>
                        </Link>
                        
                        <Link :href="route('profile.public', user.id)" class="px-3 py-1 bg-slate-800 border border-slate-700 rounded text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:bg-cyan-600 hover:text-white hover:border-cyan-500 transition">
                            View Profile
                        </Link>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-slate-500 italic">
                    Danh sách trống.
                </div>
            </div>
        </div>
    </Modal>
</template>