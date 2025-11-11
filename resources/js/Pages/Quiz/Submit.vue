<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    attempt: Object,
    answeredCount: Number,
    totalQuestions: Number,
});
</script>

<template>
    <AppLayout title="Nộp bài">
        <div class="py-12">
            <div class="max-w-xl mx-auto sm:px-6 lg:px-8 text-center">
                <div class="bg-white shadow-xl sm:rounded-lg p-8">
                    <h2 class="text-2xl font-semibold mb-4">Xác nhận nộp bài</h2>
                    <p class="text-gray-700 mb-6">
                        Bạn đã trả lời {{ answeredCount }} / {{ totalQuestions }} câu.
                        <span v-if="answeredCount < totalQuestions" class="font-bold text-red-600">
                            Bạn vẫn còn câu chưa trả lời!
                        </span>
                    </p>
                    <p class="mb-8">Bạn có chắc muốn nộp bài không? Bạn sẽ không thể thay đổi câu trả lời sau khi nộp.</p>
                    
                    <div class="flex justify-center space-x-4">
                        <Link
                            :href="route('quiz.question.show', { attempt: attempt.id, questionNumber: totalQuestions })"
                            class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                        >
                            Quay lại
                        </Link>
                        
                        <Link
                            :href="route('quiz.finish', attempt.id)"
                            method="post"
                            as="button"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700"
                        >
                            Nộp bài
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>