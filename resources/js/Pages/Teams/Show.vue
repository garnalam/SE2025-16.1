<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';

import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';

import SectionBorder from '@/Components/SectionBorder.vue';

import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';

import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';

import FormSection from '@/Components/FormSection.vue'; // <-- THÊM MỚI

// Gán props để có thể dùng trong hàm copyToClipboard

const props = defineProps({ // <-- CHỈNH SỬA DÒNG NÀY

    team: Object,

    availableRoles: Array,

    permissions: Object,

});



// Hàm copy mã vào clipboard

const copyToClipboard = () => { // <-- THÊM MỚI

    navigator.clipboard.writeText(props.team.join_code);

    alert('Đã sao chép mã lớp!');

};

</script>



<template>

    <AppLayout title="Team Settings">

        <template #header>

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">

                Cài đặt Lớp học

            </h2>

        </template>



        <div>

            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

                <UpdateTeamNameForm :team="team" :permissions="permissions" />



            <template v-if="permissions.canUpdateTeam && team.join_code">

                <SectionBorder />



                <FormSection @submitted.prevent>

                    <template #title>

                        Mã Lớp Học

                    </template>



                    <template #description>

                        Sử dụng mã này để mời học sinh tham gia vào lớp học. Chỉ có giáo viên (chủ sở hữu lớp) mới thấy mã này.

                    </template>



                    <template #form>

                        <div class="col-span-6">

                            <div class="max-w-xl text-sm text-gray-900">

                                Đây là mã tham gia duy nhất của lớp. Hãy chia sẻ mã này một cách cẩn thận cho các học sinh bạn muốn mời.

                            </div>



                            <div id="join-code-display" 

                                 class="mt-4 p-3 bg-gray-100 rounded-md font-mono text-2xl text-gray-700 tracking-widest">

                                {{ team.join_code }}

                            </div>



                            <button type="button" @click="copyToClipboard" class="mt-3 text-sm text-blue-600 hover:text-blue-800">

                                Sao chép mã

                            </button>

                        </div>

                    </template>

                </FormSection>

            </template>

            <SectionBorder />                 <TeamMemberManager

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