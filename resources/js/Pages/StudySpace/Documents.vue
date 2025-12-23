<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, nextTick, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { marked } from 'marked';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ 
    documents: Array,
    auth: Object 
});

// --- STATE ---
const selectedDoc = ref(null);
const chatHistory = ref([]);
const userInput = ref('');
const isTyping = ref(false);
const chatContainer = ref(null);
const showUploadModal = ref(false);
const searchQuery = ref('');

// --- COMPUTED ---
const filteredDocuments = computed(() => {
    if (!props.documents) return [];
    if (!searchQuery.value) return props.documents;
    return props.documents.filter(doc => 
        doc.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// --- METHODS ---
const getFileExtension = (path) => {
    if (!path) return 'DOC';
    return path.split('.').pop().toUpperCase();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTo({
                top: chatContainer.value.scrollHeight,
                behavior: 'smooth'
            });
        }
    });
};
watch(chatHistory.value, scrollToBottom);

// Upload Logic
const uploadForm = useForm({ file: null });
const fileInput = ref(null);

const handleFileSelect = (e) => {
    uploadForm.file = e.target.files[0];
};

const handleUpload = () => {
    uploadForm.post(route('study.documents.upload'), {
        onSuccess: () => {
            uploadForm.reset();
            if(fileInput.value) fileInput.value.value = null;
            showUploadModal.value = false;
        },
    });
};

const selectDocument = (doc) => {
    if (selectedDoc.value?.id === doc.id) return;
    selectedDoc.value = doc;
    
    // Simulate AI Initialization
    chatHistory.value = [];
    isTyping.value = true;
    
    setTimeout(() => {
        chatHistory.value = [
            { 
                id: 'sys-init',
                role: 'system', 
                content: `>> Tài liệu đã chọn: **${doc.title}**\n>> Sẵn sàng hỗ trợ.` 
            },
            {
                id: 'ai-welcome',
                role: 'model',
                content: `Tôi đã đọc tài liệu **${doc.title}**. Bạn cần tôi tóm tắt, tìm kiếm thông tin hay tạo câu hỏi ôn tập?`
            }
        ];
        isTyping.value = false;
    }, 800);
};

const sendMessage = async () => {
    if (!userInput.value.trim()) return;

    const msg = userInput.value;
    chatHistory.value.push({ 
        id: Date.now(), 
        role: 'user', 
        content: msg, 
        timestamp: new Date() 
    });
    
    userInput.value = '';
    isTyping.value = true;
    scrollToBottom();

    try {
        const res = await axios.post(route('study.documents.chat'), {
            document_id: selectedDoc.value ? selectedDoc.value.id : null,
            source_type: selectedDoc.value ? selectedDoc.value.source_type : 'personal',
            message: msg,
            history: chatHistory.value.slice(-6).map(m => ({ role: m.role, content: m.content }))
        });
        
        chatHistory.value.push({ 
            id: Date.now() + 1, 
            role: 'model', 
            content: res.data.answer,
            timestamp: new Date()
        });
    } catch (e) {
        chatHistory.value.push({ 
            id: Date.now() + 1, 
            role: 'model', 
            content: "⚠️ **ERR_CONNECTION_REFUSED**: AI Server không phản hồi.",
            isError: true
        });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <AppLayout title="Kho Tài Liệu AI">
        <div class="py-6 max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 h-[calc(100vh-64px)]">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">
                
                <!-- LEFT COLUMN: REPOSITORY (List) -->
                <div class="lg:col-span-4 flex flex-col gap-4 h-full overflow-hidden">
                    
                    <!-- Search & Actions -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="relative flex-1 group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                            <div class="relative flex items-center bg-slate-900 border border-slate-700 rounded-xl overflow-hidden shadow-lg">
                                <div class="pl-4 text-slate-500">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <input v-model="searchQuery" type="text" placeholder="Search data index..." class="w-full bg-transparent border-none text-white placeholder-slate-600 focus:ring-0 text-sm font-exo py-3" />
                            </div>
                        </div>
                        <button @click="showUploadModal = true" class="relative group p-3 bg-slate-800 hover:bg-slate-700 rounded-xl border border-slate-600 hover:border-cyan-500 transition-all duration-300 shadow-lg overflow-hidden shrink-0">
                            <div class="absolute inset-0 bg-cyan-500/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                            <svg class="w-6 h-6 text-slate-300 group-hover:text-cyan-400 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </button>
                    </div>

                    <!-- Document List -->
                    <div class="flex-1 bg-[#0f172a]/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-4 overflow-y-auto custom-scrollbar shadow-2xl relative">
                        <div v-if="filteredDocuments.length === 0" class="h-full flex flex-col items-center justify-center text-slate-600 opacity-60">
                            <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <p class="font-mono text-xs uppercase tracking-widest">No Data Found</p>
                        </div>

                        <div class="space-y-3">
                            <div v-for="doc in filteredDocuments" :key="doc.id" 
                                @click="selectDocument(doc)"
                                class="group relative p-4 rounded-2xl border transition-all duration-300 cursor-pointer overflow-hidden"
                                :class="selectedDoc?.id === doc.id 
                                    ? 'bg-cyan-900/10 border-cyan-500/50 shadow-[0_0_20px_rgba(6,182,212,0.15)]' 
                                    : 'bg-slate-900 border-slate-800 hover:border-slate-600 hover:bg-slate-800/80'"
                            >
                                <!-- Active Indicator -->
                                <div v-if="selectedDoc?.id === doc.id" class="absolute left-0 top-0 bottom-0 w-1 bg-cyan-500 shadow-[0_0_10px_cyan]"></div>

                                <div class="flex items-start gap-3 relative z-10">
                                    <div class="p-2.5 rounded-xl transition-colors duration-300"
                                        :class="selectedDoc?.id === doc.id ? 'bg-cyan-500 text-white' : 'bg-slate-800 text-slate-500 group-hover:text-cyan-400 group-hover:bg-slate-700'">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold truncate font-exo transition-colors duration-300"
                                            :class="selectedDoc?.id === doc.id ? 'text-white' : 'text-slate-300 group-hover:text-white'">
                                            {{ doc.title }}
                                        </h4>
                                        <div class="flex items-center gap-2 mt-1 text-[10px] font-mono text-slate-500">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-950 border border-slate-800 uppercase text-[9px]">{{ getFileExtension(doc.file_path) }}</span>
                                            <span>{{ formatDate(doc.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: TERMINAL (Chat) -->
                <div class="lg:col-span-8 h-full">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl h-full flex flex-col shadow-2xl relative overflow-hidden">
                        
                        <!-- Empty State -->
                        <div v-if="!selectedDoc" class="absolute inset-0 flex flex-col items-center justify-center bg-[#0b1121] z-20">
                            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-5 pointer-events-none"></div>
                            <div class="w-32 h-32 relative mb-8">
                                <div class="absolute inset-0 border-2 border-slate-800 rounded-full animate-[spin_10s_linear_infinite]"></div>
                                <div class="absolute inset-4 border-2 border-dashed border-cyan-900 rounded-full animate-[spin_8s_linear_infinite_reverse]"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-cyan-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                            </div>
                            <h2 class="text-2xl font-black text-white font-exo uppercase tracking-wide">Sẵn sàng để giúp bạn tìm hiểu tài liệu</h2>
                            <p class="text-slate-500 font-mono text-sm mt-2">Vui lòng chọn tài liệu và gửi yêu cầu để AI hỗ trợ bạn.</p>
                        </div>

                        <!-- Header -->
                        <div v-else class="px-6 py-4 bg-slate-950/80 border-b border-slate-800 flex justify-between items-center backdrop-blur relative z-10 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_10px_#10b981]"></div>
                                <div>
                                    <h3 class="text-white font-bold font-exo text-sm tracking-wide">{{ selectedDoc.title }}</h3>
                                    <p class="text-[10px] text-cyan-500 font-mono"></p>
                                </div>
                            </div>
                            <button @click="selectedDoc = null" class="lg:hidden text-slate-500 hover:text-white"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>

                        <!-- Chat Area -->
                        <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar relative z-10 bg-[#0b1121]">
                            <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.5)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.5)_1px,transparent_1px)] bg-[size:40px_40px] opacity-10 pointer-events-none"></div>

                            <div v-for="msg in chatHistory" :key="msg.id" 
                                class="flex gap-4 animate-fade-in-up max-w-4xl mx-auto"
                                :class="msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'">
                                
                                <!-- Avatar -->
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center border shadow-lg"
                                    :class="msg.role === 'user' ? 'bg-indigo-600 border-indigo-400' : 'bg-cyan-900/50 border-cyan-500/50'">
                                    <span v-if="msg.role === 'user'" class="text-[9px] font-bold text-white">YOU</span>
                                    <svg v-else class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>

                                <!-- Bubble -->
                                <div class="flex-1 max-w-[80%]">
                                    <div class="text-[9px] font-mono mb-1 opacity-50 uppercase tracking-wider" :class="msg.role === 'user' ? 'text-right text-indigo-400' : 'text-left text-cyan-400'">
                                        {{ msg.role === 'user' ? 'Operator' : 'AI Core' }}
                                    </div>
                                    <div class="p-4 rounded-2xl text-sm leading-relaxed shadow-lg backdrop-blur-sm border"
                                        :class="msg.role === 'user' 
                                            ? 'bg-indigo-600/10 border-indigo-500/30 text-indigo-100 rounded-tr-none' 
                                            : 'bg-slate-800/80 border-slate-700 text-slate-200 rounded-tl-none'">
                                        <div v-if="msg.role === 'system'" class="font-mono text-xs text-emerald-400 whitespace-pre-wrap">{{ msg.content }}</div>
                                        <div v-else class="prose prose-invert prose-sm max-w-none break-words" v-html="marked.parse(msg.content)"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Typing Indicator -->
                            <div v-if="isTyping" class="flex gap-4 max-w-4xl mx-auto">
                                <div class="w-8 h-8 rounded-lg bg-cyan-900/50 border border-cyan-500/50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </div>
                                <div class="flex items-center gap-1 h-8">
                                    <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full animate-bounce delay-75"></span>
                                    <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full animate-bounce delay-150"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Input Area -->
                        <div class="p-4 bg-slate-900 border-t border-slate-800 relative z-20 shrink-0">
                            <form @submit.prevent="sendMessage" class="relative max-w-4xl mx-auto flex gap-3">
                                <div class="relative flex-1 group">
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500 to-indigo-500 rounded-xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                                    <input 
                                        v-model="userInput" 
                                        type="text" 
                                        :placeholder="isTyping ? 'Processing...' : 'Input query for AI analysis...'" 
                                        :disabled="isTyping"
                                        class="relative w-full bg-[#020617] border border-slate-700 text-white placeholder-slate-600 rounded-xl px-4 py-3 focus:ring-0 focus:border-cyan-500/50 font-sans shadow-inner transition-all"
                                    >
                                </div>
                                <button type="submit" :disabled="!userInput || isTyping" 
                                    class="relative px-5 bg-slate-800 hover:bg-cyan-600 disabled:bg-slate-900 disabled:text-slate-700 text-cyan-400 hover:text-white rounded-xl border border-slate-700 hover:border-cyan-500 transition-all duration-300 shadow-lg flex items-center justify-center group overflow-hidden">
                                    <div class="absolute inset-0 bg-cyan-400/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                    <svg class="w-5 h-5 relative z-10 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- UPLOAD MODAL -->
        <DialogModal :show="showUploadModal" @close="showUploadModal = false">
            <template #title>
                <span class="text-white font-exo uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                    Upload Data Packet
                </span>
            </template>

            <template #content>
                <div class="space-y-4">
                    <p class="text-sm text-slate-400">Tải lên tài liệu (.pdf, .txt) để AI phân tích.</p>
                    
                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 flex flex-col items-center justify-center bg-slate-900/50 hover:bg-slate-800 transition-colors group cursor-pointer relative">
                        <input type="file" ref="fileInput" @change="handleFileSelect" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept=".pdf,.txt">
                        
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg class="w-8 h-8 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-300 font-exo">{{ uploadForm.file ? uploadForm.file.name : 'Click or Drag File Here' }}</p>
                        <p class="text-xs text-slate-500 mt-1 font-mono">PDF, TXT up to 10MB</p>
                    </div>
                    
                    <div v-if="uploadForm.progress" class="w-full bg-slate-800 rounded-full h-1.5 mt-4 overflow-hidden">
                        <div class="bg-cyan-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                    </div>
                    <InputError :message="uploadForm.errors.file" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showUploadModal = false">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3 !bg-cyan-600 hover:!bg-cyan-500" @click="handleUpload" :disabled="!uploadForm.file || uploadForm.processing">
                    {{ uploadForm.processing ? 'Uploading...' : 'Initiate Upload' }}
                </PrimaryButton>
            </template>
        </DialogModal>

    </AppLayout>
</template>