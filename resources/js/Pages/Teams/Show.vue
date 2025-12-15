<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';
import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';

const props = defineProps({
    team: Object,
    availableRoles: Array,
    permissions: Object,
});

const copyToClipboard = () => {
    navigator.clipboard.writeText(props.team.join_code);
    const el = document.getElementById('join-code-btn');
    const originalText = el.innerHTML;
    el.innerHTML = '<span class="text-emerald-400">COPIED!</span>';
    setTimeout(() => { el.innerHTML = originalText; }, 2000);
};
</script>

<template>
    <AppLayout title="Class Settings">
        <template #header>
            <h2 class="font-black text-xl text-white leading-tight tracking-wide flex items-center gap-3 font-exo uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                System Configuration
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-10">
                
                <UpdateTeamNameForm :team="team" :permissions="permissions" />

                <template v-if="permissions.canUpdateTeam && team.join_code">
                    <SectionBorder />

                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1 flex justify-between">
                            <div class="px-4 sm:px-0">
                                <h3 class="text-lg font-bold text-white font-exo">Mã lớp học</h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    Mã truy cập lớp học dành cho học sinh sử dụng để tham gia lớp học.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 md:mt-0 md:col-span-2">
                            <div class="p-6 bg-[#0f172a] border border-white/5 shadow-xl rounded-2xl relative overflow-hidden group">
                                <!-- Background fx -->
                                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
                                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>

                                <div class="max-w-xl text-sm text-slate-300 relative z-10 mb-6">
                                    Sử dụng mã định danh bên dưới để cấp quyền truy cập cho sinh viên vào lớp học này.
                                </div>

                                <div class="relative">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-cyan-500 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500"></div>
                                    <div class="relative flex items-center justify-between p-1 bg-slate-950 rounded-xl border border-white/10">
                                        
                                        <div class="pl-6 font-mono text-3xl font-bold tracking-[0.3em] text-cyan-400 select-all py-3">
                                            {{ team.join_code }}
                                        </div>

                                        <button id="join-code-btn" type="button" 
                                                @click="copyToClipboard" 
                                                class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold uppercase tracking-widest rounded-lg transition-all duration-200 shadow-lg shadow-indigo-500/20 active:scale-95 border-l border-white/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                            </svg>
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <SectionBorder />
                
                <TeamMemberManager
                    class="mt-10 sm:mt-0"
                    :team="team"
                    :available-roles="availableRoles"
                    :user-permissions="permissions"
                />

                <template v-if="permissions.canDeleteTeam && ! team.personal_team">
                    <SectionBorder />
                    <DeleteTeamForm class="mt-10 sm:mt-0" :team="team" />
                </template>

            </div>
        </div>
    </AppLayout>
</template>