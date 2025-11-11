<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    attempt: Object,
});

const scorePercent = ((props.attempt.score / props.attempt.post.max_points) * 100).toFixed(0);
</script>

<template>
    <AppLayout title="Kết quả">
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-center">
                <div class="bg-white shadow-xl sm:rounded-lg p-8">
                    <h2 class_content="text-xl font-semibold text-gray-600 mb-2">
                        Kết quả bài kiểm tra: {{ attempt.post.title }}
                    </h2>
                    
                    <div class="my-8">
                        <p class="text-gray-700">Điểm số của bạn:</p>
                        <p class="text-6xl font-bold text-indigo-600 my-2">
                            {{ Number(attempt.score).toFixed(2) }} 
                            <span class="text-3xl text-gray-500">/ {{ attempt.post.max_points }}</span>
                        </p>
                        <p class="text-4xl font-semibold text-indigo-500">
                            ({{ scorePercent }}%)
                        </p>
                    </div>

                    <Link
                        :href="route('topics.show', attempt.post.topic_id)"
                        class="inline-block px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700"
                    >
                        Quay lại chủ đề
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>