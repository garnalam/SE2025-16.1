<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';

const props = defineProps({
    attempt: Object,
    mistakes: Array,
});

const selectedMistake = ref(null);
const aiExplanation = ref('');
const isLoading = ref(false);

const selectMistake = (mistake) => {
    selectedMistake.value = mistake;
    aiExplanation.value = ''; // Reset AI chat box
};

const askAI = async () => {
    if (!selectedMistake.value) return;

    isLoading.value = true;
    try {
        const response = await axios.post(route('study.analyze-mistake'), {
            question_text: selectedMistake.value.question_text,
            student_answer: selectedMistake.value.student_answer,
            correct_answer: selectedMistake.value.correct_answer,
            subject: selectedMistake.value.subject,
        });
        aiExplanation.value = marked.parse(response.data.explanation);
    } catch (error) {
        aiExplanation.value = "⚠️ Có lỗi khi kết nối với AI. Vui lòng thử lại.";
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <AppLayout title="Glitch Log - Sửa lỗi sai">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                👾 Glitch Log: Phân tích lỗi sai
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row gap-6 h-[80vh]">
                    
                    <div class="w-full md:w-1/3 bg-white overflow-y-auto shadow-xl sm:rounded-lg p-4 border-l-4 border-red-500">
                        <h3 class="font-bold text-lg mb-4 text-gray-700">DANH SÁCH LỖI SAI ({{ mistakes.length }})</h3>
                        <div v-if="mistakes.length === 0" class="text-green-600">
                            Tuyệt vời! Bạn không làm sai câu nào.
                        </div>
                        <ul v-else class="space-y-3">
                            <li v-for="(mistake, index) in mistakes" :key="index" 
                                @click="selectMistake(mistake)"
                                :class="{'bg-blue-50 border-blue-500': selectedMistake === mistake, 'hover:bg-gray-50': selectedMistake !== mistake}"
                                class="cursor-pointer p-3 border rounded transition border-l-4 border-transparent">
                                <div class="text-xs font-bold text-gray-500 uppercase">{{ mistake.subject }}</div>
                                <div class="text-sm font-medium text-gray-800 truncate">{{ mistake.question_text }}</div>
                                <div class="text-xs text-red-500 mt-1">Bạn chọn: {{ mistake.student_answer }}</div>
                            </li>
                        </ul>
                    </div>

                    <div class="w-full md:w-2/3 bg-gray-900 text-white shadow-xl sm:rounded-lg p-6 flex flex-col relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-blue-500 opacity-10 blur-3xl"></div>

                        <div v-if="!selectedMistake" class="flex items-center justify-center h-full text-gray-400">
                            <p>Chọn một câu sai bên trái để bắt đầu phân tích</p>
                        </div>

                        <div v-else class="flex flex-col h-full z-10">
                            <div class="mb-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                                <h4 class="text-blue-400 font-bold text-sm mb-2">CÂU HỎI:</h4>
                                <p class="text-lg">{{ selectedMistake.question_text }}</p>
                                <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                                    <div class="text-red-400">❌ Bạn chọn: {{ selectedMistake.student_answer }}</div>
                                    <div class="text-green-400">✅ Đáp án đúng: {{ selectedMistake.correct_answer }}</div>
                                </div>
                            </div>

                            <div v-if="!aiExplanation" class="flex justify-center mt-4">
                                <button @click="askAI" :disabled="isLoading"
                                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full font-bold shadow-lg hover:shadow-blue-500/50 transition transform hover:-translate-y-1 disabled:opacity-50 flex items-center">
                                    <span v-if="isLoading" class="animate-spin mr-2">⚙️</span>
                                    {{ isLoading ? 'AI Đang suy nghĩ...' : '✨ Yêu cầu Gia sư AI giải thích' }}
                                </button>
                            </div>

                            <div v-else class="flex-1 overflow-y-auto custom-scrollbar bg-gray-800/50 p-4 rounded border border-gray-700 mt-4">
                                <h4 class="text-purple-400 font-bold mb-2 flex items-center">
                                    <span class="text-xl mr-2">🤖</span> GIA SƯ AI:
                                </h4>
                                <div class="prose prose-invert max-w-none text-sm leading-relaxed" v-html="aiExplanation"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>