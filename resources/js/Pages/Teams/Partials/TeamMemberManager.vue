<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    team: Object,
    availableRoles: Array,
    userPermissions: Object,
});

const page = usePage();
const addTeamMemberForm = useForm({ email: '', role: null });
const updateRoleForm = useForm({ role: null });
const leaveTeamForm = useForm({});
const removeTeamMemberForm = useForm({});

const currentlyManagingRole = ref(false);
const managingRoleFor = ref(null);
const confirmingLeavingTeam = ref(false);
const teamMemberBeingRemoved = ref(null);

const addTeamMember = () => {
    addTeamMemberForm.post(route('team-members.store', props.team), {
        errorBag: 'addTeamMember',
        preserveScroll: true,
        onSuccess: () => addTeamMemberForm.reset(),
    });
};

const cancelTeamInvitation = (invitation) => {
    router.delete(route('team-invitations.destroy', invitation), { preserveScroll: true });
};

const manageRole = (teamMember) => {
    managingRoleFor.value = teamMember;
    updateRoleForm.role = teamMember.membership.role;
    currentlyManagingRole.value = true;
};

const updateRole = () => {
    updateRoleForm.put(route('team-members.update', [props.team, managingRoleFor.value]), {
        preserveScroll: true,
        onSuccess: () => currentlyManagingRole.value = false,
    });
};

const confirmLeavingTeam = () => { confirmingLeavingTeam.value = true; };
const leaveTeam = () => { leaveTeamForm.delete(route('team-members.destroy', [props.team, page.props.auth.user])); };
const confirmTeamMemberRemoval = (teamMember) => { teamMemberBeingRemoved.value = teamMember; };
const removeTeamMember = () => {
    removeTeamMemberForm.delete(route('team-members.destroy', [props.team, teamMemberBeingRemoved.value]), {
        errorBag: 'removeTeamMember',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => teamMemberBeingRemoved.value = null,
    });
};

const displayableRole = (role) => {
    const foundRole = props.availableRoles.find(r => r.key === role);
    return foundRole ? foundRole.name : role;
};
</script>

<template>
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mt-6">
        
        <!-- LEFT COLUMN: ROSTER & INVITES (Main Data) -->
        <div class="xl:col-span-8 space-y-6 order-2 xl:order-1">
            
            <!-- SECTION 1: PENDING INVITATIONS (Alert Style) -->
            <div v-if="team.team_invitations.length > 0 && userPermissions.canAddTeamMembers" 
                 class="bg-amber-500/10 border border-amber-500/30 rounded-3xl overflow-hidden relative transition-all hover:bg-amber-500/15">
                <div class="absolute top-0 left-0 w-1 h-full bg-amber-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-amber-400 font-bold font-exo flex items-center gap-2 tracking-wide">
                            <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            PENDING HANDSHAKES
                        </h3>
                        <span class="text-xs font-mono text-amber-500/70 bg-amber-950/50 px-2 py-1 rounded border border-amber-500/20">WAITING FOR RESPONSE</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div v-for="invitation in team.team_invitations" :key="invitation.id" class="flex items-center justify-between p-3 bg-amber-950/30 border border-amber-500/20 rounded-xl hover:border-amber-500/50 transition">
                            <div class="text-slate-300 font-mono text-xs truncate pr-4">
                                {{ invitation.email }}
                            </div>
                            <button
                                v-if="userPermissions.canRemoveTeamMembers"
                                class="text-[10px] font-bold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-600 px-3 py-1.5 rounded-lg transition border border-rose-500/20 uppercase tracking-wider"
                                @click="cancelTeamInvitation(invitation)"
                            >
                                Abort
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: MEMBER ROSTER (Table/Grid Style) -->
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-2xl flex flex-col h-full">
                <div class="p-5 border-b border-white/5 flex justify-between items-center bg-white/5">
                    <div>
                        <h3 class="text-lg font-black text-white font-exo tracking-wide">Danh sách thành viên</h3>
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5 uppercase tracking-widest">Hiện có: <span class="text-cyan-400">{{ team.users.length }}</span></p>
                    </div>
                    <!-- Decorative Status Lights -->
                    <div class="flex gap-1.5 opacity-70">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_#10b981]"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-cyan-500 shadow-[0_0_5px_#06b6d4]"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-[0_0_5px_#6366f1]"></div>
                    </div>
                </div>

                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/5 bg-slate-950/30 text-[9px] uppercase font-mono text-slate-500 tracking-[0.1em]">
                                    <th class="py-3 pl-6">Người dùng</th>
                                    <th class="py-3">Vai trò</th>
                                    <th class="py-3 text-right pr-6">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="user in team.users" :key="user.id" class="group hover:bg-white/5 transition-colors">
                                    <td class="py-3 pl-6">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <img class="size-9 rounded-lg object-cover border border-slate-700 group-hover:border-cyan-400 transition" :src="user.profile_photo_url" :alt="user.name">
                                                <div class="absolute -bottom-1 -right-1 w-2 h-2 bg-emerald-500 border-2 border-slate-900 rounded-full"></div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-200 group-hover:text-cyan-300 transition text-sm font-exo">{{ user.name }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono uppercase tracking-wide">
                                            {{ displayableRole(user.membership.role) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right pr-6">
                                        <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                            <button
                                                v-if="userPermissions.canUpdateTeamMembers && availableRoles.length"
                                                class="p-1.5 rounded hover:bg-indigo-500/20 text-slate-400 hover:text-indigo-400 transition"
                                                @click="manageRole(user)"
                                                title="Edit Role"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>

                                            <button
                                                v-if="$page.props.auth.user.id === user.id"
                                                class="px-2 py-1 bg-rose-500/10 hover:bg-rose-600 text-rose-500 hover:text-white rounded text-[10px] font-bold uppercase transition"
                                                @click="confirmLeavingTeam"
                                            >
                                                Leave
                                            </button>

                                            <button
                                                v-else-if="userPermissions.canRemoveTeamMembers"
                                                class="p-1.5 rounded hover:bg-rose-500/20 text-slate-400 hover:text-rose-500 transition"
                                                @click="confirmTeamMemberRemoval(user)"
                                                title="Remove User"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: ADD MEMBER FORM (Sticky Sidebar) -->
        <div class="xl:col-span-4 order-1 xl:order-2">
            <div v-if="userPermissions.canAddTeamMembers" class="sticky top-6">
                
                <div class="bg-[#0f172a] border border-cyan-500/30 rounded-3xl overflow-hidden shadow-[0_0_40px_rgba(6,182,212,0.1)]">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-cyan-900/20 to-transparent border-b border-cyan-500/20 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white font-exo uppercase tracking-wider">Gửi lời mời tham gia lớp học</h3>
                        <div class="w-2 h-2 bg-cyan-500 rounded-full animate-pulse shadow-[0_0_10px_cyan]"></div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="addTeamMember" class="p-6 space-y-5">
                        
                        <div>
                            <InputLabel for="email" value="Email" />
                            <div class="relative mt-2 group">
                                <TextInput
                                    id="email"
                                    v-model="addTeamMemberForm.email"
                                    type="email"
                                    class="pl-10 bg-slate-900 border-slate-700 focus:border-cyan-500 focus:ring-cyan-500/50 text-sm"
                                    placeholder="user@system.io"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-500 group-hover:text-cyan-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                                </div>
                            </div>
                            <InputError :message="addTeamMemberForm.errors.email" class="mt-2" />
                        </div>

                        <div v-if="availableRoles.length > 0">
                            <InputLabel for="roles" value="Chọn vai trò" />
                            <InputError :message="addTeamMemberForm.errors.role" class="mt-2" />

                            <div class="space-y-2 mt-2">
                                <button
                                    v-for="(role, i) in availableRoles"
                                    :key="role.key"
                                    type="button"
                                    class="relative w-full flex items-center px-4 py-3 rounded-xl border transition-all duration-200 group text-left"
                                    :class="addTeamMemberForm.role == role.key 
                                        ? 'bg-cyan-500/10 border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.2)]' 
                                        : 'bg-slate-800/50 border-slate-700 hover:border-slate-500 hover:bg-slate-800'"
                                    @click="addTeamMemberForm.role = role.key"
                                >
                                    <div class="flex-1">
                                        <div class="text-sm font-bold font-exo" :class="addTeamMemberForm.role == role.key ? 'text-cyan-400' : 'text-slate-300'">
                                            {{ role.name }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">{{ role.description }}</div>
                                    </div>
                                    <div class="w-4 h-4 rounded-full border border-slate-600 flex items-center justify-center transition-colors"
                                         :class="addTeamMemberForm.role == role.key ? 'border-cyan-500' : ''">
                                        <div class="w-2 h-2 rounded-full bg-cyan-500 shadow-[0_0_5px_cyan] transition-transform duration-300 transform" :class="addTeamMemberForm.role == role.key ? 'scale-100' : 'scale-0'"></div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/5 flex items-center justify-between">
                            <ActionMessage :on="addTeamMemberForm.recentlySuccessful">
                                AUTHORIZED
                            </ActionMessage>
                            <PrimaryButton :class="{ 'opacity-25': addTeamMemberForm.processing }" :disabled="addTeamMemberForm.processing" class="text-xs uppercase tracking-widest px-8">
                                Gửi lời mời
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Role Management Modal (Floating) -->
        <DialogModal :show="currentlyManagingRole" @close="currentlyManagingRole = false">
            <template #title>
                Cập nhật vai trò thành viên
            </template>
            <template #content>
                <div v-if="managingRoleFor" class="grid grid-cols-1 gap-3">
                    <button
                        v-for="(role, i) in availableRoles"
                        :key="role.key"
                        type="button"
                        class="relative flex items-center p-4 rounded-xl border transition-all duration-200 text-left"
                        :class="updateRoleForm.role === role.key 
                            ? 'bg-indigo-600/20 border-indigo-500' 
                            : 'bg-slate-800/50 border-slate-700 hover:bg-slate-800 hover:border-slate-500'"
                        @click="updateRoleForm.role = role.key"
                    >
                         <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-bold font-exo" :class="updateRoleForm.role === role.key ? 'text-indigo-400' : 'text-slate-300'">
                                    {{ role.name }}
                                </div>
                                <span v-if="updateRoleForm.role === role.key" class="flex h-2 w-2 rounded-full bg-indigo-500 shadow-[0_0_8px_#6366f1]"></span>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 font-mono">
                                {{ role.description }}
                            </div>
                        </div>
                    </button>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="currentlyManagingRole = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': updateRoleForm.processing }" :disabled="updateRoleForm.processing" @click="updateRole">
                    Save Changes
                </PrimaryButton>
            </template>
        </DialogModal>

        <!-- Leave Confirmation -->
        <ConfirmationModal :show="confirmingLeavingTeam" @close="confirmingLeavingTeam = false">
            <template #title>
                Confirm Disengagement
            </template>

            <template #content>
                Are you sure you want to disconnect from this class module? Access privileges will be revoked immediately.
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingLeavingTeam = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': leaveTeamForm.processing }"
                    :disabled="leaveTeamForm.processing"
                    @click="leaveTeam"
                >
                    Confirm Leave
                </DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Remove Confirmation -->
        <ConfirmationModal :show="teamMemberBeingRemoved" @close="teamMemberBeingRemoved = null">
            <template #title>
                Xóa thành viên khỏi lớp học
            </template>

            <template #content>
Bạn có chắc chắn muốn xóa người dùng này không? Thao tác này sẽ xóa dữ liệu của họ khỏi lớp này.            </template>

            <template #footer>
                <SecondaryButton @click="teamMemberBeingRemoved = null">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': removeTeamMemberForm.processing }"
                    :disabled="removeTeamMemberForm.processing"
                    @click="removeTeamMember"
                >
                    Xóa khỏi lớp
                </DangerButton>
            </template>
        </ConfirmationModal>
    </div>
</template>