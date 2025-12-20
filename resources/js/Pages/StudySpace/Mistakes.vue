<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, nextTick, watch } from 'vue';
import { marked } from 'marked';
import axios from 'axios';

const props = defineProps({ mistakes: Array });

const selectedMistake = ref(null);
const chatHistory = ref([{ role: 'model', content: 'Đây là hệ thống phân tích lỗi sai trong các bài tập bằng AI, hãy lựa chọn câu hỏi cần phân tích trước khi bắt đầu' }]);
const userInput = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => { if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight; });
};
watch(chatHistory.value, scrollToBottom);

const selectMistake = (m) => { 
    selectedMistake.value = m;
    chatHistory.value.push({ role: 'system', content: `>> Lỗi đang được chọn: ${m.subject} // ID: ${m.id}` });
};
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
        chatHistory.value.push({ role: 'model', content: "CONNECTION ERROR: DEBUGGER OFFLINE." });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <AppLayout title="Error Correction">
        <div class="flex h-[calc(100vh-80px)] max-w-[1800px] mx-auto p-4 gap-6">
            
            <!-- LEFT: ERROR LOG LIST -->
            <div class="w-1/3 min-w-[320px] max-w-md flex flex-col bg-[#0f172a] border border-rose-500/30 rounded-3xl overflow-hidden shadow-[0_0_40px_rgba(244,63,94,0.1)]">
                <div class="p-6 border-b border-rose-500/20 bg-rose-950/20">
                    <h3 class="font-black text-rose-500 text-lg uppercase tracking-widest font-exo flex items-center gap-2">
                        <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Các lỗi sai gần đây
                    </h3>
                    <p class="text-xs text-rose-400/60 mt-1 font-mono">Phát hiện {{ mistakes.length }} lỗi sai bạn cần chú ý.</p>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                    <div v-for="m in mistakes" :key="m.id" @click="selectMistake(m)"
                        class="p-4 rounded-xl cursor-pointer border transition-all duration-300 relative group overflow-hidden"
                        :class="selectedMistake?.id === m.id 
                            ? 'bg-rose-900/20 border-rose-500 shadow-[inset_0_0_15px_rgba(244,63,94,0.2)]' 
                            : 'bg-slate-900/50 border-slate-800 hover:bg-slate-900 hover:border-rose-500/40'">
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase font-mono border border-slate-700 px-1.5 py-0.5 rounded bg-slate-950">
                                {{ m.subject }}
                            </span>
                            <span class="text-[9px] font-mono text-rose-500">{{ m.date }}</span>
                        </div>
                        
                        <div class="font-bold text-slate-300 text-sm line-clamp-2 group-hover:text-white transition font-mono mb-2">
                            {{ m.question_text }}
                        </div>
                        
                        <div v-if="selectedMistake?.id === m.id" class="absolute right-0 top-0 h-full w-1 bg-rose-500"></div>
                        <div class="text-[10px] text-rose-400/70 truncate border-t border-slate-800 pt-2 mt-2">
                            > Input: {{ m.student_answer }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: DEBUGGER CONSOLE -->
            <div class="flex-1 flex flex-col bg-slate-950 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl relative font-mono">
                
                <!-- Screen Glitch Overlay -->
                <div class="absolute inset-0 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] bg-[length:100%_2px,3px_100%] pointer-events-none z-0 opacity-20"></div>

                <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar relative z-10">
                    <div v-for="(chat, i) in chatHistory" :key="i" class="flex flex-col" :class="chat.role === 'user' ? 'items-end' : 'items-start'">
                        
                        <!-- System Messages -->
                        <div v-if="chat.role === 'system'" class="w-full text-center my-4">
                            <span class="text-[10px] text-rose-500 border border-rose-900 bg-rose-950/30 px-3 py-1 rounded uppercase tracking-widest">
                                {{ chat.content }}
                            </span>
                        </div>

                        <!-- Chat Bubbles -->
                        <div v-else class="max-w-[85%] p-4 rounded-xl border text-sm" 
                            :class="chat.role === 'user' 
                                ? 'bg-rose-900/20 border-rose-500/50 text-rose-100 rounded-br-none' 
                                : 'bg-slate-900 border-slate-700 text-slate-300 rounded-bl-none'">
                             <div class="prose prose-invert prose-sm" v-html="marked.parse(chat.content)"></div>
                        </div>
                    </div>
                    <div v-if="isTyping" class="text-rose-500 text-xs animate-pulse">> ANALYZING LOGIC...</div>
                </div>

                <div class="p-6 border-t border-slate-800 bg-slate-900/50 backdrop-blur relative z-20">
                    <div v-if="selectedMistake" class="mb-3 flex items-center justify-between bg-black/40 border border-slate-700 px-4 py-2 rounded text-xs">
                        <span class="text-slate-400 truncate max-w-[80%]">Đề bài: <span class="text-rose-400 font-bold">{{ selectedMistake.question_text }}</span></span>
                        <button @click="clearSelection" class="text-slate-500 hover:text-white">EXIT</button>
                    </div>

                    <form @submit.prevent="sendMessage" class="flex gap-4">
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-3 text-rose-500 font-bold">></span>
                            <input v-model="userInput" type="text" 
                                :placeholder="selectedMistake ? 'Ở bài tập này tôi đã sai ở điểm gì?' : 'Chọn 1 bài tập để phân tích lỗi sai'" 
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg text-rose-100 placeholder-slate-600 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 pl-8 py-3 font-mono text-sm shadow-inner"
                            >
                        </div>
                        <button type="submit" :disabled="!userInput.trim() || isTyping" class="px-6 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-lg uppercase tracking-wider text-xs shadow-lg shadow-rose-900/20 transition disabled:opacity-50">
                            Gửi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>