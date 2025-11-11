<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    attempt: Object,
    question: Object,
    questionNumber: Number,
    totalQuestions: Number,
    previousAnswerOptionId: Number, // ID của lựa chọn trước đó
});

// Form để lưu câu trả lời
const form = useForm({
    option_id: props.previousAnswerOptionId, // Điền sẵn đáp án đã chọn
});

// Hàm submit, sẽ chuyển sang câu tiếp theo (logic ở controller)
function saveAndNext() {
    form.post(route('quiz.question.save', { 
        attempt: props.attempt.id, 
        questionNumber: props.questionNumber 
    }), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :title="'Câu ' + questionNumber">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg">
                    
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold">
                            Câu {{ questionNumber }} / {{ totalQuestions }}
                        </h2>
                    </div>
                    
                    <form @submit.prevent="saveAndNext">
                        <div class="p-6">
                            <p class="text-lg font-medium text-gray-900 mb-6">
                                {{ question.question_text }}
                            </p>

                            <div class="space-y-4">
                                <label
                                    v-for="option in question.options"
                                    :key="option.id"
                                    class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                    :class="{ 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-500': form.option_id === option.id }"
                                >
                                    <input
                                        type="radio"
                                        :value="option.id"
                                        v-model="form.option_id"
                                        class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                    />
                                    <span class="ml-4 text-gray-700">{{ option.option_text }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 border-t flex justify-between items-center">
                            <a 
                                v-if="questionNumber > 1"
                                :href="route('quiz.question.show', { attempt: attempt.id, questionNumber: questionNumber - 1 })"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                &larr; Câu trước
                            </a>
                            <span v-else></span> <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing || !form.option_id">
                                {{ questionNumber < totalQuestions ? 'Lưu & Câu tiếp' : 'Đến trang nộp bài' }}
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>