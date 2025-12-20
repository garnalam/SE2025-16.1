<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    team: Object,
    permissions: Object,
});

const form = useForm({
    name: props.team.name,
});

const updateTeamName = () => {
    form.put(route('teams.update', props.team), {
        errorBag: 'updateTeamName',
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="bg-[#0f172a] border border-indigo-500/30 rounded-3xl p-6 relative overflow-hidden shadow-[0_0_40px_rgba(79,70,229,0.1)]">
        <!-- Background Decor -->
        <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-indigo-900/20 to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>

        <form @submit.prevent="updateTeamName" class="relative z-10 flex flex-col lg:flex-row items-center gap-8">
            
            <!-- 1. Thông tin người dùng (Avatar) -->
            <div class="flex-shrink-0 flex flex-col items-center gap-3">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-2xl blur opacity-30 group-hover:opacity-75 transition duration-500"></div>
                    <div class="relative w-20 h-20 bg-slate-900 rounded-xl flex items-center justify-center border border-white/10 overflow-hidden">
                         <img v-if="team.owner.profile_photo_url" class="w-full h-full object-cover" :src="team.owner.profile_photo_url" :alt="team.owner.name">
                         <span v-else class="text-3xl font-black text-indigo-500 font-exo">{{ team.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <!-- Owner Badge -->
                    <div class="absolute -bottom-2 -right-2 bg-slate-900 border border-indigo-500 text-indigo-400 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest shadow-lg">
                        Owner
                    </div>
                </div>
            </div>

            <!-- 2. Main Data Entry (Horizontal Layout) -->
            <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                
                <!-- Class Name Input -->
                <div class="md:col-span-7 space-y-2">
                    <InputLabel for="name" value="Tên lớp học" />
                    <div class="relative group">
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full bg-slate-900/50 border-slate-700 text-lg font-bold text-white focus:border-indigo-500 focus:ring-indigo-500/50 placeholder-slate-600 font-exo tracking-wide h-12"
                            :disabled="!permissions.canUpdateTeam"
                        />
                         <!-- Tech Decor Corner -->
                        <div class="absolute top-0 right-0 w-2 h-2 border-t border-r border-indigo-500 opacity-50 group-hover:opacity-100 transition"></div>
                        <div class="absolute bottom-0 left-0 w-2 h-2 border-b border-l border-indigo-500 opacity-50 group-hover:opacity-100 transition"></div>
                    </div>
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Class Code Display (Read Only) -->
                <div class="md:col-span-3 space-y-2">
                    <InputLabel value="Mã lớp học" />
                    <div class="flex items-center justify-between px-4 h-12 bg-black/40 border border-slate-700 rounded-lg group hover:border-cyan-500/50 transition cursor-copy" title="Class ID">
                        <span class="font-mono text-cyan-400 font-bold tracking-[0.1em] text-lg truncate">{{ team.id }}</span>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-cyan-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="md:col-span-2 flex justify-end pb-0.5">
                    <div v-if="permissions.canUpdateTeam" class="w-full">
                         <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full h-12 flex items-center justify-center">
                            Save
                        </PrimaryButton>
                    </div>
                </div>
            </div>
            
        </form>
    </div>
</template>