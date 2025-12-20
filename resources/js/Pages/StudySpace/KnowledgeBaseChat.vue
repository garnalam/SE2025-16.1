<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onUpdated, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';
import axios from 'axios';

const props = defineProps({
    session: Object,
    messages: Array,
});

const messagesList = ref([...props.messages]);
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

    // Optimistic UI update
    const tempUserMsg = {
        role: 'user',
        content: form.message + (form.file ? `\n\n📎 [UPLOADING: ${form.file.name}...]` : ''),
        is_temp: true
    };
    messagesList.value.push(tempUserMsg);
    
    const formData = new FormData();
    formData.append('message', form.message);
    if (form.file) {
        formData.append('file', form.file);
    }

    form.message = '';
    form.file = null;
    if (fileInput.value) fileInput.value.value = null;

    scrollToBottom();

    try {
        const response = await axios.post(route('knowledge-base.send', props.session.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        messagesList.value.pop(); // Remove temp
        messagesList.value.push({ 
            role: 'user', 
            content: tempUserMsg.content.replace('📎 [UPLOADING:', '📎 [UPLOADED:') 
        });
        messagesList.value.push({
             role: 'model', 
             content: response.data.content 
        });
        
        scrollToBottom();

    } catch (error) {
        messagesList.value.pop();
        alert("⚠️ CONNECTION LOST: Unable to reach AI Core.");
        console.error("AI Error:", error);
    }
};
</script>

<template>
    <AppLayout title="Neural Link Chat">
        <div class="py-6 h-[calc(100vh-64px)] flex flex-col max-w-[1600px] mx-auto w-full px-4">
            
            <div class="bg-[#0f172a]/90 backdrop-blur-2xl border border-slate-700 shadow-[0_0_50px_rgba(0,0,0,0.5)] sm:rounded-[32px] flex-1 flex flex-col overflow-hidden relative">
                
                <!-- Background Effects -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/5 rounded-full blur-[100px] pointer-events-none"></div>

                <!-- Header -->
                <div class="px-8 py-5 border-b border-white/5 bg-slate-900/50 flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse"></div>
                        </div>
                        <div>
                            <h3 class="font-bold text-white font-exo text-lg truncate max-w-md">{{ session.title || 'New Session' }}</h3>
                            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Connected to Gemini-Pro-Vision</p>
                        </div>
                    </div>
                    <div class="text-[9px] font-mono text-slate-600 border border-slate-800 px-2 py-1 rounded">
                        SECURE_LINK_ESTABLISHED
                    </div>
                </div>

                <!-- Chat Body -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-8 custom-scrollbar relative z-10">
                    
                    <div v-if="messagesList.length === 0" class="h-full flex flex-col items-center justify-center text-slate-600 opacity-60">
                        <div class="w-24 h-24 border border-dashed border-slate-700 rounded-full flex items-center justify-center mb-6 animate-spin-slow">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <p class="font-mono text-sm tracking-wider">Awaiting input data...</p>
                    </div>

                    <div v-for="(msg, index) in messagesList" :key="index" 
                        class="flex w-full animate-fade-in-up" 
                        :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        
                        <div class="max-w-[80%] flex gap-4" :class="msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center border"
                                :class="msg.role === 'user' ? 'bg-slate-800 border-slate-600' : 'bg-purple-900/50 border-purple-500/50'">
                                <span v-if="msg.role === 'user'" class="text-xs">YOU</span>
                                <svg v-else class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>

                            <!-- Bubble -->
                            <div class="p-5 rounded-2xl shadow-lg text-sm leading-relaxed relative group"
                                :class="msg.role === 'user' 
                                    ? 'bg-slate-800 text-slate-200 rounded-tr-none border border-slate-700' 
                                    : 'bg-purple-900/10 text-slate-100 rounded-tl-none border border-purple-500/20'">
                                
                                <div class="prose prose-sm prose-invert max-w-none break-words" 
                                    v-html="marked.parse(msg.content || '')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-6 bg-slate-900/80 border-t border-slate-700 relative z-20">
                    <form @submit.prevent="sendMessage" class="relative max-w-4xl mx-auto">
                        <div class="relative flex items-end gap-3 bg-[#020617] p-2 rounded-2xl border border-slate-700 focus-within:border-purple-500/50 focus-within:ring-1 focus-within:ring-purple-500/50 transition-all shadow-inner">
                            
                            <!-- File Upload -->
                            <div class="relative">
                                <input type="file" ref="fileInput" @change="e => form.file = e.target.files[0]" 
                                    class="hidden" id="file-upload" accept=".pdf,.txt,.jpg,.png">
                                <label for="file-upload" class="cursor-pointer flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition border border-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                    </svg>
                                </label>
                            </div>

                            <!-- Text Input -->
                            <div class="flex-1 flex flex-col min-h-[44px]">
                                <div v-if="form.file" class="px-2 pt-2 text-xs text-purple-400 font-mono flex items-center gap-2">
                                    <span class="bg-purple-500/10 px-1.5 py-0.5 rounded border border-purple-500/20">FILE_ATTACHED</span>
                                    <span>{{ form.file.name }}</span>
                                    <button @click.prevent="form.file = null; if(fileInput) fileInput.value = null" class="text-rose-400 hover:text-white">✕</button>
                                </div>
                                <textarea v-model="form.message" rows="1" 
                                    class="w-full bg-transparent border-none text-white placeholder-slate-600 focus:ring-0 resize-none py-3 px-2 max-h-32 text-sm font-sans"
                                    placeholder="Enter command or query..."
                                    @keydown.enter.exact.prevent="sendMessage"
                                ></textarea>
                            </div>

                            <!-- Send Button -->
                            <button type="submit" :disabled="form.processing || (!form.message && !form.file)"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95">
                                <svg v-if="!form.processing" class="w-5 h-5 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                <svg v-else class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        </div>
                        <div class="text-center mt-2">
                            <span class="text-[9px] text-slate-600 font-mono uppercase tracking-widest">Shift + Enter for new line</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(168, 85, 247, 0.3); }

.animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-spin-slow { animation: spin 4s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>