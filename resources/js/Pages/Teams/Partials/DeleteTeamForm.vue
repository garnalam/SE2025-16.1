<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    team: Object,
});

const confirmingTeamDeletion = ref(false);
const form = useForm({});

const confirmTeamDeletion = () => {
    confirmingTeamDeletion.value = true;
};

const deleteTeam = () => {
    form.delete(route('teams.destroy', props.team), {
        errorBag: 'deleteTeam',
    });
};
</script>

<template>
    <div class="mt-8 relative group">
        <!-- Hazard Stripes Background -->
        <div class="absolute inset-0 bg-[repeating-linear-gradient(45deg,#450a0a,#450a0a_10px,#000000_10px,#000000_20px)] opacity-20 rounded-3xl pointer-events-none"></div>
        
        <div class="relative bg-red-950/20 border border-red-500/30 rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden">
            
            <div class="flex items-center gap-4 z-10">
                <div class="p-3 bg-red-500/10 rounded-xl border border-red-500/20 text-red-500 animate-pulse">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-500 font-exo uppercase tracking-widest">CẢNH BÁO</h3>
                    <p class="text-xs text-red-400/70 font-mono">Hành động này sẽ xóa toàn bộ dữ liệu trong lớp học</p>
                </div>
            </div>

            <div class="z-10">
                 <button @click="confirmTeamDeletion" class="group relative px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-bold uppercase tracking-widest text-xs rounded-xl overflow-hidden transition-all shadow-[0_0_20px_rgba(220,38,38,0.4)] hover:shadow-[0_0_40px_rgba(220,38,38,0.6)]">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Class
                    </span>
                    <div class="absolute inset-0 bg-[repeating-linear-gradient(45deg,transparent,transparent_5px,rgba(0,0,0,0.2)_5px,rgba(0,0,0,0.2)_10px)] opacity-0 group-hover:opacity-100 transition duration-300"></div>
                </button>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="confirmingTeamDeletion" @close="confirmingTeamDeletion = false">
            <template #title>
                CONFIRM DELETION PROTOCOL
            </template>

            <template #content>
                <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm font-mono mb-4">
                    WARNING: THIS ACTION IS IRREVERSIBLE.
                </div>
                Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted.
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingTeamDeletion = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="deleteTeam"
                >
                    Confirm Delete
                </DangerButton>
            </template>
        </ConfirmationModal>
    </div>
</template>