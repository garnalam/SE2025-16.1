<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, nextTick, watch } from 'vue';
import { marked } from 'marked';
import axios from 'axios';

const props = defineProps({ mistakes: Array });

const selectedMistake = ref(null);
const chatHistory = ref([{ role: 'model', content: 'Chào bạn! Mình có thể giúp gì cho việc ôn tập hôm nay?' }]);
const userInput = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight; });
};
watch(chatHistory.value, scrollToBottom);

const selectMistake = (m) => { selectedMistake.value = m; };
const clearSelection = () => { selectedMistake.value = null; };

const sendMessage = async () => {
    if (!userInput.value.trim()) return;
    const msg = userInput.value;
    chatHistory.value.push({ role: 'user', content: msg });
    userInput.value = '';
    isTyping.value = true;
    scrollToBottom();

    try {
        const res = await axios.post(route('study.mistakes.chat'), {
            mistake_id: selectedMistake.value ? selectedMistake.value.id : null,
            message: msg,
            history: chatHistory.value.slice(-10)
        });
        chatHistory.value.push({ role: 'model', content: res.data.answer });
    } catch (e) {
        chatHistory.value.push({ role: 'model', content: "Lỗi kết nối." });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <AppLayout title="Sửa Lỗi Sai">
        <div class="flex h-[calc(100vh-64px)] bg-[#0f172a] text-slate-300 overflow-hidden">
            
            <div class="w-1/3 min-w-[300px] border-r border-slate-700 flex flex-col bg-slate-900/50">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="font-bold text-rose-500 text-lg">👾 Kho Lỗi Sai ({{ mistakes.length }})</h3>
                </div>
                <div class="flex-1 overflow-y-auto p-2 custom-scrollbar space-y-2">
                    <div v-for="m in mistakes" :key="m.id" @click="selectMistake(m)"
                        class="p-3 rounded-lg border cursor-pointer transition relative group"
                        :class="selectedMistake?.id === m.id ? 'bg-rose-900/20 border-rose-500' : 'border-slate-800 bg-slate-800/50 hover:bg-slate-800'">
                        <div class="text-[10px] font-bold text-slate-500 uppercase">{{ m.subject }} • {{ m.date }}</div>
                        <div class="font-medium text-slate-200 line-clamp-2 my-1">{{ m.question_text }}</div>
                        <div v-if="selectedMistake?.id === m.id" class="absolute right-2 top-2 w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col relative bg-gradient-to-br from-slate-900 to-[#0B1120]">
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-6 custom-scrollbar">
                    <div v-for="(chat, i) in chatHistory" :key="i" class="flex" :class="chat.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] p-4 rounded-2xl shadow-lg" 
                            :class="chat.role === 'user' ? 'bg-rose-600 text-white rounded-br-none' : 'bg-slate-800 text-slate-300 border border-slate-700 rounded-bl-none'">
                             <div class="prose prose-invert prose-sm" v-html="marked.parse(chat.content)"></div>
                        </div>
                    </div>
                    <div v-if="isTyping" class="text-slate-500 text-xs ml-4">AI đang suy nghĩ...</div>
                </div>

                <div class="p-4 border-t border-slate-700/50 bg-slate-900/80 backdrop-blur-md">
                    <div v-if="selectedMistake" class="mb-2 flex items-center justify-between bg-rose-900/30 border border-rose-500/50 px-3 py-2 rounded-lg">
                        <div class="text-xs text-rose-300 truncate max-w-[90%]">
                            Đang xem lỗi: <span class="font-bold text-white">{{ selectedMistake.question_text }}</span>
                        </div>
                        <button @click="clearSelection" class="text-slate-400 hover:text-white">✕</button>
                    </div>

                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input v-model="userInput" type="text" 
                            :placeholder="selectedMistake ? 'Tại sao mình sai câu này?' : 'Hỏi chung chung hoặc chọn lỗi bên trái...'" 
                            class="flex-1 bg-slate-800 border-slate-700 rounded-xl text-white focus:ring-rose-500 py-3 px-4">
                        <button type="submit" :disabled="!userInput.trim() || isTyping" class="bg-rose-600 text-white p-3 rounded-xl font-bold">Gửi</button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>