<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onUpdated, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';
import axios from 'axios'; // Đảm bảo đã import axios

const props = defineProps({
    session: Object,
    messages: Array,
});

const messagesList = ref([...props.messages]); // Copy prop sang ref để mutate
const chatContainer = ref(null);
const fileInput = ref(null);

const form = useForm({
    message: '',
    file: null,
});

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

onUpdated(() => {
    scrollToBottom();
});

const sendMessage = async () => {
    if (!form.message && !form.file) return;

    // 1. UI Optimistic: Hiển thị tin nhắn tạm thời của User ngay lập tức
    const tempUserMsg = {
        role: 'user',
        content: form.message + (form.file ? `\n\n📎 [Đang gửi file: ${form.file.name}...]` : ''),
        is_temp: true
    };
    messagesList.value.push(tempUserMsg);
    
    // Chuẩn bị gửi
    const formData = new FormData();
    formData.append('message', form.message);
    if (form.file) {
        formData.append('file', form.file);
    }

    // Reset form ngay lập tức
    form.message = '';
    form.file = null;
    if (fileInput.value) fileInput.value.value = null;

    scrollToBottom();

    try {
        // 2. Gửi request lên Server
        const response = await axios.post(route('knowledge-base.send', props.session.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // 3. Xử lý thành công
        messagesList.value.pop(); // Xóa tin nhắn tạm
        
        // Push tin nhắn User (từ server trả về hoặc tự tạo lại để đảm bảo đồng bộ)
        // Lưu ý: response của bạn trả về tin nhắn AI (model), nên ta cần giữ tin nhắn user
        messagesList.value.push({ 
            role: 'user', 
            content: tempUserMsg.content.replace('📎 [Đang gửi file:', '📎 [Đã tải lên:') // Sửa lại trạng thái
        });

        // Push tin nhắn AI trả lời
        messagesList.value.push({
             role: 'model', 
             content: response.data.content 
        });
        
        scrollToBottom();

    } catch (error) {
        // 4. Xử lý lỗi
        messagesList.value.pop(); // Xóa tin tạm
        alert("⚠️ Lỗi: Không thể kết nối với AI. Vui lòng kiểm tra lại file (không quá lớn) hoặc thử lại sau.");
        console.error("AI Error:", error);
    }
};
</script>

<template>
    <AppLayout title="Chat với Tài liệu">
        <div class="py-6 h-[calc(100vh-64px)] flex flex-col">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 w-full flex-1 flex flex-col h-full">
                
                <div class="bg-slate-900 border border-slate-700 shadow-2xl sm:rounded-2xl flex-1 flex flex-col overflow-hidden">
                    
                    <div class="p-4 border-b border-slate-700 bg-slate-900/50 flex justify-between items-center backdrop-blur-md">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <h3 class="font-bold text-slate-200 font-exo truncate max-w-xs md:max-w-md">{{ session.title }}</h3>
                        </div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-purple-400 border border-purple-500/30 px-2 py-1 rounded bg-purple-500/10">
                            Gemini 2.5 Flash
                        </span>
                    </div>

                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-6 bg-slate-950/30 custom-scrollbar">
                        <div v-if="messagesList.length === 0" class="flex flex-col items-center justify-center h-full text-slate-600 opacity-50">
                            <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                            <p>Bắt đầu cuộc trò chuyện hoặc tải lên tài liệu...</p>
                        </div>

                        <div v-for="(msg, index) in messagesList" :key="index" 
                            class="flex w-full" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            
                            <div class="max-w-[85%] md:max-w-[75%] rounded-2xl p-4 shadow-lg relative group"
                                :class="msg.role === 'user' 
                                    ? 'bg-purple-600 text-white rounded-tr-none' 
                                    : 'bg-slate-800 text-slate-300 border border-slate-700 rounded-tl-none'">
                                
                                <div class="text-[10px] font-bold mb-1 opacity-70 flex items-center gap-1 uppercase tracking-wider"
                                    :class="msg.role === 'user' ? 'text-purple-200 justify-end' : 'text-cyan-400'">
                                    <span v-if="msg.role === 'model'">AI GURU</span>
                                    <span v-else>YOU</span>
                                </div>
                                
                                <div class="prose prose-sm prose-invert max-w-none break-words leading-relaxed" 
                                    v-html="marked.parse(msg.content || '')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-900 border-t border-slate-700">
                        <form @submit.prevent="sendMessage" class="flex items-end gap-3 relative">
                            
                            <div class="relative group">
                                <input type="file" ref="fileInput" @change="e => form.file = e.target.files[0]" 
                                    class="hidden" id="file-upload" accept=".pdf,.txt">
                                <label for="file-upload" class="cursor-pointer flex items-center justify-center w-10 h-10 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition border border-slate-700 hover:border-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                    </svg>
                                </label>
                                <span class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-black text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Upload PDF/TXT</span>
                            </div>

                            <div class="flex-1 bg-slate-800 rounded-2xl border border-slate-700 focus-within:border-purple-500 focus-within:ring-1 focus-within:ring-purple-500 transition-all">
                                <div v-if="form.file" class="px-4 pt-2 text-xs text-cyan-400 flex items-center justify-between">
                                    <span class="flex items-center gap-1">📎 {{ form.file.name }}</span>
                                    <button @click.prevent="form.file = null; if(fileInput) fileInput.value = null" class="text-rose-400 hover:text-rose-300 font-bold">×</button>
                                </div>
                                <textarea v-model="form.message" rows="1" 
                                    class="w-full bg-transparent border-none text-white placeholder-slate-500 focus:ring-0 resize-none py-3 px-4 max-h-32 min-h-[44px]"
                                    placeholder="Hỏi tôi bất cứ điều gì về tài liệu... (Shift+Enter để xuống dòng)"
                                    @keydown.enter.exact.prevent="sendMessage"
                                ></textarea>
                            </div>

                            <button type="submit" :disabled="form.processing || (!form.message && !form.file)"
                                class="w-10 h-10 flex items-center justify-center rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95 transition-all">
                                <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-0.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                                <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* CSS cho thanh cuộn */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(71, 85, 105, 0.8); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(147, 51, 234, 0.5); }
</style>