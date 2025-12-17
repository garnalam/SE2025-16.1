<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, nextTick, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';
import axios from 'axios';

const props = defineProps({ documents: Array });

// State
const selectedDoc = ref(null); // File đang được "gắn" vào khung chat
const chatHistory = ref([{ role: 'model', content: 'Xin chào! Chọn một tài liệu bên trái để mình đọc, hoặc hỏi mình bất cứ điều gì nhé.' }]);
const userInput = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);

// Scroll xuống cuối khi có tin nhắn mới
const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    });
};
watch(chatHistory.value, scrollToBottom);

// Upload
const uploadForm = useForm({ file: null });
const handleUpload = () => {
    uploadForm.post(route('study.documents.upload'), {
        onSuccess: () => uploadForm.reset(),
    });
};

// Chọn tài liệu (Chỉ gán biến, không reset chat)
const selectDocument = (doc) => {
    selectedDoc.value = doc;
    // Thông báo nhẹ trong khung chat (Optional)
    // chatHistory.value.push({ role: 'system', content: `Đã chọn tài liệu: ${doc.title}` });
};

// Bỏ chọn
const clearSelection = () => {
    selectedDoc.value = null;
};

const sendMessage = async () => {
    if (!userInput.value.trim()) return;

    const msg = userInput.value;
    // Add user message to UI
    chatHistory.value.push({ role: 'user', content: msg });
    userInput.value = '';
    isTyping.value = true;
    scrollToBottom();

    try {
        const res = await axios.post(route('study.documents.chat'), {
            document_id: selectedDoc.value ? selectedDoc.value.id : null,
            source_type: selectedDoc.value ? selectedDoc.value.source_type : 'personal', // <--- THÊM DÒNG NÀY
            message: msg,
            history: chatHistory.value.slice(-10)
        });
        
        chatHistory.value.push({ role: 'model', content: res.data.answer });
    } catch (e) {
        console.error(e);
        chatHistory.value.push({ role: 'model', content: "⚠️ Mất kết nối với AI Server." });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <AppLayout title="Kho Tài Liệu">
        <div class="flex h-[calc(100vh-64px)] bg-[#0f172a] text-slate-300 overflow-hidden">
            
            <div class="w-1/3 min-w-[300px] border-r border-slate-700 flex flex-col bg-slate-900/50">
                <div class="p-4 border-b border-slate-700">
                    <h3 class="font-bold text-white text-lg mb-4">📂 Thư viện</h3>
                    <div class="relative group">
                        <input type="file" @change="e => uploadForm.file = e.target.files[0]" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.txt"/>
                        <div class="border border-dashed border-slate-600 rounded-xl p-3 text-center group-hover:bg-slate-800 transition">
                            <span v-if="!uploadForm.file" class="text-sm text-slate-400">+ Upload PDF/TXT</span>
                            <span v-else class="text-sm text-purple-400 font-bold">{{ uploadForm.file.name }}</span>
                        </div>
                    </div>
                    <button v-if="uploadForm.file" @click="handleUpload" class="mt-2 w-full bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold py-2 rounded transition">
                        Tải lên ngay
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                    <div v-for="doc in documents" :key="doc.id" @click="selectDocument(doc)"
                        class="p-3 rounded-lg cursor-pointer border transition group relative"
                        :class="selectedDoc?.id === doc.id ? 'bg-purple-900/40 border-purple-500' : 'border-transparent hover:bg-slate-800'">
                        
                        <div class="font-bold text-slate-200 text-sm truncate pr-6">{{ doc.title }}</div>
                        <div class="text-[10px] text-slate-500 mt-1">{{ new Date(doc.created_at).toLocaleDateString() }}</div>
                        
                        <div v-if="selectedDoc?.id === doc.id" class="absolute right-3 top-3 w-2 h-2 rounded-full bg-purple-400 animate-pulse"></div>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col relative bg-gradient-to-br from-slate-900 to-[#0B1120]">
                
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-6 custom-scrollbar scroll-smooth">
                    <div v-for="(chat, i) in chatHistory" :key="i" class="flex w-full" 
                        :class="chat.role === 'user' ? 'justify-end' : 'justify-start'">
                        
                        <div class="max-w-[85%] p-4 rounded-2xl shadow-lg text-sm leading-relaxed" 
                            :class="chat.role === 'user' ? 'bg-purple-600 text-white rounded-br-none' : 'bg-slate-800 text-slate-300 border border-slate-700 rounded-bl-none'">
                            
                            <div class="text-[10px] font-bold mb-1 opacity-70 uppercase tracking-wider" :class="chat.role === 'user' ? 'text-purple-200 text-right' : 'text-cyan-500'">
                                {{ chat.role === 'user' ? 'Bạn' : 'AI Assistant' }}
                            </div>
                            
                            <div class="prose prose-invert prose-sm max-w-none" v-html="marked.parse(chat.content)"></div>
                        </div>
                    </div>
                    
                    <div v-if="isTyping" class="flex justify-start">
                        <div class="bg-slate-800 p-3 rounded-2xl rounded-bl-none border border-slate-700 flex gap-1">
                            <span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce"></span>
                            <span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce delay-100"></span>
                            <span class="w-2 h-2 bg-slate-500 rounded-full animate-bounce delay-200"></span>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-700/50 bg-slate-900/80 backdrop-blur-md">
                    
                    <div v-if="selectedDoc" class="mb-2 flex items-center justify-between bg-purple-900/30 border border-purple-500/50 px-3 py-2 rounded-lg backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-xs text-purple-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            <span>Đang hỏi về: <span class="font-bold text-white">{{ selectedDoc.title }}</span></span>
                        </div>
                        <button @click="clearSelection" class="text-slate-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input v-model="userInput" type="text" 
                            :placeholder="selectedDoc ? 'Hỏi chi tiết về tài liệu này...' : 'Hỏi bất cứ điều gì (hoặc chọn file bên trái)...'" 
                            class="flex-1 bg-slate-800 border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all py-3 px-4 shadow-inner">
                        <button type="submit" :disabled="!userInput.trim() || isTyping"
                            class="bg-purple-600 hover:bg-purple-500 text-white p-3 rounded-xl transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>