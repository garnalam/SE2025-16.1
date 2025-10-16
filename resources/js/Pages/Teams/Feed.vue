<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import CreatePostForm from '@/Pages/Teams/Partials/CreatePostForm.vue';

defineProps({
    team: Object,
    permissions: Object,
    posts: Array,
});
</script>

<template>
    <AppLayout :title="team.name">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ team.name }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="permissions.canAddTeamMembers">
                    <CreatePostForm :team="team" />
                    <SectionBorder />
                </div>

                <div class="mt-10 sm:mt-0">
                    <h3 class="text-lg font-medium text-gray-900">Dòng thời gian của lớp</h3>
                    <div class="mt-4 space-y-4">
                        <div v-if="posts.length > 0" v-for="post in posts" :key="post.id" class="bg-white shadow-sm rounded-lg p-4">
                             <div class="flex items-center mb-2">
                                <img class="h-8 w-8 rounded-full object-cover" :src="post.user.profile_photo_url" :alt="post.user.name">
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">{{ post.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ new Date(post.created_at).toLocaleString() }}</div>
                                </div>
                            </div>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ post.content }}</p>
                        </div>
                        <div v-else class="text-center text-gray-500 py-6">
                            Chưa có bài đăng nào.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>