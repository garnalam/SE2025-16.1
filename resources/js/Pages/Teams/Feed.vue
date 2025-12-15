<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CreateTopicForm from '@/Pages/Teams/Partials/CreateTopicForm.vue';

// COMPONENTS
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    team: Object,
    permissions: Object,
    topics: Array, 
});

const canCreateTopics = computed(() => props.permissions.canCreateTopics);
const showCreatePanel = ref(false);
const searchQuery = ref('');

// Filter topics (Simple client-side filter for UI effect)
const filteredTopics = computed(() => {
    if (!searchQuery.value) return props.topics;
    return props.topics.filter(topic => 
        topic.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        (topic.description && topic.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

// === MODAL LOGIC ===
const confirmingTopicDeletion = ref(false);
const topicToUpdate = ref(null);
const topicToDelete = ref(null);

const updateTopicForm = useForm({ name: '', description: '' });
const deleteTopicForm = useForm({});

const openUpdateModal = (topic) => {
    topicToUpdate.value = topic;
    updateTopicForm.name = topic.name;
    updateTopicForm.description = topic.description;
};

const updateTopic = () => {
    updateTopicForm.put(route('topics.update', topicToUpdate.value), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const openDeleteModal = (topic) => {
    topicToDelete.value = topic;
    confirmingTopicDeletion.value = true;
};

const deleteTopic = () => {
    deleteTopicForm.delete(route('topics.destroy', topicToDelete.value), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    topicToUpdate.value = null;
    topicToDelete.value = null;
    confirmingTopicDeletion.value = false;
    updateTopicForm.reset();
};
</script>

<template>
    <AppLayout :title="team.name">
        <template #header>
            <!-- HUD Header -->
            <div class="flex items-center justify-between font-exo">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 bg-cyan-500/10 border border-cyan-500/30 rounded">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h2 class="font-black text-xl text-white uppercase tracking-wider">
                        {{ team.name }} <span class="text-slate-600 mx-2">//</span> <span class="text-cyan-400">COMMAND_GRID</span>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                     <div class="hidden md:flex items-center gap-2 px-3 py-1 bg-slate-900 border border-slate-700 rounded-full">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                        <span class="text-[10px] font-mono text-emerald-500 uppercase tracking-widest">Net_Link: Stable</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- SIDEBAR: MISSION CONTROL MODULE (Left - 3 Cols) -->
                <div class="lg:col-span-4 xl:col-span-3 space-y-6 sticky top-24">
                    
                    <!-- Core Unit Info -->
                    <div class="bg-[#0b1121] border border-slate-800 rounded-2xl overflow-hidden relative shadow-2xl group">
                        <!-- Background Scanline Effect -->
                        <div class="absolute inset-0 bg-[linear-gradient(transparent_0%,rgba(6,182,212,0.05)_50%,transparent_100%)] bg-[length:100%_4px] animate-scan-fast pointer-events-none"></div>
                        
                        <!-- Header Image/Color -->
                        <div class="h-24 bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-900 relative">
                            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                            <div class="absolute -bottom-10 left-1/2 transform -translate-x-1/2 p-1 bg-[#0b1121] rounded-2xl">
                                <img :src="team.owner.profile_photo_url" class="w-20 h-20 rounded-xl object-cover border-2 border-indigo-500/50 shadow-[0_0_20px_rgba(99,102,241,0.3)]">
                            </div>
                        </div>

                        <div class="pt-12 pb-6 px-4 text-center relative z-10">
                            <div class="inline-block px-2 py-0.5 bg-indigo-500/20 border border-indigo-500/30 rounded text-[9px] text-indigo-300 font-mono uppercase tracking-widest mb-2">
                                Giáo viên chủ nhiệm
                            </div>
                            <h3 class="text-lg font-bold text-white font-exo">{{ team.owner.name }}</h3>
                            <p class="text-xs text-slate-500 font-mono mt-1">{{ team.owner.email }}</p>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 border-t border-slate-800 divide-x divide-slate-800 bg-slate-900/50 backdrop-blur">
                            <div class="p-4 text-center hover:bg-white/5 transition">
                                <div class="text-2xl font-bold text-white font-exo">{{ topics.length }}</div>
                                <div class="text-[9px] text-slate-500 uppercase font-mono tracking-wider">Topic</div>
                            </div>
                            <div class="p-4 text-center hover:bg-white/5 transition">
                                <div class="text-2xl font-bold text-cyan-400 font-exo">{{ team.users.length }}</div>
                                <div class="text-[9px] text-slate-500 uppercase font-mono tracking-wider">Thành viên</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Module -->
                    <div class="space-y-3">
                         <div class="flex items-center gap-2 mb-1 px-1">
                            <div class="w-1 h-3 bg-cyan-500 rounded-sm"></div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest font-exo">Thao tác</span>
                        </div>

                        <Link :href="route('attendance.history', team.id)" 
                            class="group relative flex items-center justify-between w-full px-5 py-4 bg-indigo-600/10 border border-indigo-500/30 hover:border-indigo-400 rounded-xl overflow-hidden transition-all duration-300">
                            <div class="absolute inset-0 bg-indigo-600/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300"></div>
                            <div class="relative z-10 flex flex-col items-start">
                                <span class="text-xs font-black text-indigo-400 group-hover:text-white uppercase tracking-widest font-exo">Lịch sử điểm danh</span>
                                <span class="text-[9px] text-indigo-300/60 font-mono">Xem bảng chuyên cần</span>
                            </div>
                            <svg class="w-5 h-5 text-indigo-500 group-hover:text-white relative z-10 transform group-hover:scale-110 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </Link>
                    </div>
                </div>

                <!-- MAIN FEED: COMMS ARRAY (Right - 9 Cols) -->
                <div class="lg:col-span-8 xl:col-span-9">
                    
                    <!-- Signal Control Toolbar -->
                    <div class="flex flex-col md:flex-row gap-4 mb-8">
                        <!-- Search Module -->
                        <div class="flex-1 relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl blur opacity-20 group-hover:opacity-50 transition"></div>
                            <div class="relative flex items-center bg-[#0f172a] border border-slate-700 rounded-xl overflow-hidden">
                                <div class="pl-4 text-slate-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <input v-model="searchQuery" type="text" placeholder="Tên 1 topic bất kỳ..." class="w-full bg-transparent border-none text-white placeholder-slate-600 focus:ring-0 text-sm font-mono py-3" />
                            </div>
                        </div>

                        <!-- Initialize Button -->
                        <button v-if="canCreateTopics" @click="showCreatePanel = !showCreatePanel"
                            class="relative px-6 py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold uppercase tracking-widest text-xs rounded-xl transition-all shadow-[0_0_15px_rgba(6,182,212,0.3)] hover:shadow-[0_0_25px_rgba(6,182,212,0.5)] flex items-center gap-2 group whitespace-nowrap">
                            <span class="absolute inset-0 bg-white/10 translate-y-[100%] group-hover:translate-y-0 transition-transform duration-300"></span>
                            <svg class="w-4 h-4 transition-transform duration-300" :class="{'rotate-45': showCreatePanel}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="relative z-10">{{ showCreatePanel ? 'Đóng Form' : 'Tạo Topic' }}</span>
                        </button>
                    </div>

                    <!-- Create Terminal (Collapsible) -->
                    <transition 
                        enter-active-class="transition ease-out duration-300" 
                        enter-from-class="opacity-0 -translate-y-4 scale-95" 
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-4 scale-95"
                    >
                        <div v-if="showCreatePanel && canCreateTopics" class="mb-8">
                             <div class="bg-[#0b1121] border border-cyan-500/30 rounded-2xl p-1 shadow-[0_0_40px_rgba(6,182,212,0.05)] relative overflow-hidden">
                                <!-- Decorative header line -->
                                <div class="h-1 w-full bg-gradient-to-r from-cyan-500 via-transparent to-transparent mb-1"></div>
                                <div class="bg-slate-900/50 rounded-xl">
                                    <CreateTopicForm :team="team" />
                                </div>
                             </div>
                        </div>
                    </transition>

                    <!-- Topics Grid -->
                    <div class="space-y-4 min-h-[400px]">
                        
                        <!-- Grid Header -->
                        <div class="flex items-center gap-4 mb-4 opacity-60">
                             <span class="text-[10px] font-mono text-cyan-500 uppercase tracking-[0.2em] px-1 bg-slate-900/50 border border-cyan-500/20 rounded">Các Topic Hiện Có Trong Lớp Học</span>
                             <div class="h-px bg-gradient-to-r from-cyan-500/30 to-transparent flex-1"></div>
                             <span class="text-[10px] font-mono text-slate-500">{{ filteredTopics.length }} TOPIC</span>
                        </div>

                        <transition-group 
                            tag="div" 
                            class="grid grid-cols-1 md:grid-cols-2 gap-5"
                            enter-active-class="transition-all duration-500 ease-out"
                            enter-from-class="opacity-0 translate-y-10"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-300 ease-in absolute"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div v-for="topic in filteredTopics" :key="topic.id" 
                                 class="group relative bg-[#0f172a] hover:bg-slate-900 border border-slate-800 hover:border-cyan-500/60 rounded-xl p-6 transition-all duration-300 hover:shadow-[0_0_30px_rgba(6,182,212,0.1)] flex flex-col h-full overflow-hidden">
                                
                                <!-- Arbitrary Effect: Tech Corners -->
                                <div class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-slate-700 group-hover:border-cyan-400 transition-colors duration-300 rounded-tr-lg"></div>
                                <div class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-slate-700 group-hover:border-cyan-400 transition-colors duration-300 rounded-bl-lg"></div>
                                <!-- Scanline on hover -->
                                <div class="absolute inset-0 bg-gradient-to-b from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 pointer-events-none"></div>

                                <!-- Header -->
                                <div class="flex justify-between items-start mb-4 relative z-10">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full ring-2 ring-slate-800" :class="topic.is_locked ? 'bg-rose-500 shadow-[0_0_8px_#f43f5e]' : 'bg-cyan-400 shadow-[0_0_8px_#22d3ee]'"></div>
                                        <span class="text-[9px] font-mono text-slate-500 group-hover:text-cyan-400 transition uppercase tracking-wider">Topic ID: // {{ topic.id }}</span>
                                    </div>
                                    <div v-if="topic.is_locked" class="px-1.5 py-0.5 bg-rose-950/50 border border-rose-500/30 text-rose-500 text-[8px] font-bold uppercase rounded tracking-widest shadow-inner">
                                        Encrypted
                                    </div>
                                </div>

                                <!-- Content -->
                                <Link :href="route('topics.show', topic.id)" class="flex-1 block relative z-10 group-hover:cursor-pointer mb-6">
                                    <h3 class="text-xl font-bold text-slate-200 group-hover:text-white group-hover:translate-x-1 transition-all duration-300 font-exo truncate mb-2">
                                        {{ topic.name }}
                                    </h3>
                                    <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-mono border-l-2 border-slate-700 pl-3 group-hover:border-cyan-500/50 transition-colors">
                                        {{ topic.description || 'Topic này không có mô tả' }}
                                    </p>
                                </Link>

                                <!-- Footer / Meta -->
                                <div class="mt-auto pt-4 border-t border-white/5 flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                             <!-- <img :src="topic.user.profile_photo_url" class="w-7 h-7 rounded bg-slate-800 border border-slate-600 group-hover:border-cyan-500 transition" /> -->
                                             <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 bg-slate-900 rounded-full flex items-center justify-center">
                                                <div class="w-1 h-1 bg-emerald-500 rounded-full"></div>
                                             </div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] text-slate-500 uppercase font-mono tracking-wider">Người tạo Topic</span>
                                            <span class="text-[10px] text-slate-300 font-bold uppercase">{{ topic.user.name }}</span>
                                        </div>
                                    </div>

                                    <!-- Actions (Slide up on Hover) -->
                                    <div v-if="canCreateTopics" class="flex gap-2 translate-y-8 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 ease-out">
                                        <button @click="openUpdateModal(topic)" class="p-1.5 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded transition-colors border border-white/5" title="Reconfigure">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </button>
                                        <button @click="openDeleteModal(topic)" class="p-1.5 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded transition-colors border border-white/5" title="Purge">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition-group>
                        
                        <!-- Empty State -->
                        <div v-if="filteredTopics.length === 0" class="col-span-full py-24 relative rounded-2xl border border-dashed border-slate-800 bg-slate-900/20 flex flex-col items-center justify-center overflow-hidden">
                            <!-- Animated Radar -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-10">
                                <div class="w-64 h-64 border border-cyan-500 rounded-full animate-ping-slow"></div>
                                <div class="w-48 h-48 border border-cyan-500 rounded-full absolute animate-ping-slow delay-300"></div>
                            </div>
                            
                            <div class="relative z-10 text-center">
                                <div class="inline-flex p-4 bg-slate-900 rounded-full mb-4 border border-slate-700 shadow-[0_0_20px_rgba(0,0,0,0.5)]">
                                    <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                                </div>
                                <h3 class="text-white font-bold font-exo text-lg">Chưa có Topic</h3>
                                <p class="text-slate-500 text-sm font-mono mt-1 max-w-xs mx-auto">
                                    <span v-if="searchQuery">No frequencies match your filter parameters.</span>
                                    <span v-else>Giáo viên chưa thêm Topic nào cho lớp học</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- UPDATE MODAL (Re-styled) -->
        <DialogModal :show="topicToUpdate != null" @close="closeModal">
            <template #title>
                <div class="flex items-center gap-2">
                     <span class="w-2 h-6 bg-cyan-500 rounded-sm"></span>
                     <span class="text-white font-exo uppercase tracking-wide">Chỉnh sửa Topic</span>
                </div>
            </template>

            <template #content>
                <div class="space-y-4">
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="update_name" value="Tên Topic" />
                        <TextInput
                            id="update_name"
                            v-model="updateTopicForm.name"
                            type="text"
                            class="mt-1 block w-full bg-slate-950 border-slate-700 text-white focus:border-cyan-500 focus:ring-cyan-500/20 font-exo font-bold"
                            autofocus
                        />
                        <InputError :message="updateTopicForm.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="update_description" value="Mô tả Topic" />
                        <TextArea
                            id="update_description"
                            v-model="updateTopicForm.description"
                            class="mt-1 block w-full bg-slate-950 border-slate-700 text-white focus:border-cyan-500 focus:ring-cyan-500/20 font-mono text-xs"
                            rows="4"
                        />
                        <InputError :message="updateTopicForm.errors.description" class="mt-2" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">Đóng</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': updateTopicForm.processing }"
                    :disabled="updateTopicForm.processing"
                    @click="updateTopic"
                >
                    Lưu thay đổi
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- DELETE MODAL -->
        <DialogModal :show="confirmingTopicDeletion" @close="closeModal">
            <template #title>
                 <div class="flex items-center gap-2">
                     <span class="w-2 h-6 bg-red-600 rounded-sm"></span>
                     <span class="text-red-500 font-exo uppercase tracking-wide">Critical Warning</span>
                </div>
            </template>

            <template #content>
                <div class="p-4 bg-red-950/20 border-l-4 border-red-600 rounded-r-lg mb-6">
                    <p class="text-red-400 font-bold font-mono text-xs uppercase mb-1">Alert: Data Loss Imminent</p>
                    <p class="text-slate-400 text-sm">
                        You are initiating a purge sequence for topic <span class="text-white font-bold">"{{ topicToDelete ? topicToDelete.name : '' }}"</span>. This will permanently erase all transmission logs.
                    </p>
                </div>
                <p class="text-sm text-slate-500">To proceed, confirm authorization below.</p>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">Abort</SecondaryButton>
                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': deleteTopicForm.processing }"
                    :disabled="deleteTopicForm.processing"
                    @click="deleteTopic"
                >
                    Confirm Purge
                </DangerButton>
            </template>
        </DialogModal>

    </AppLayout>
</template>

<style scoped>
@keyframes scan-fast {
    0% { background-position: 0 0; }
    100% { background-position: 0 100px; }
}
.animate-scan-fast {
    animation: scan-fast 4s linear infinite;
}

@keyframes ping-slow {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}
.animate-ping-slow {
    animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>