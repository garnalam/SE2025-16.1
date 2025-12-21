<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue'; 
import NotebookEditor from '@/Components/StudySpace/NotebookEditor.vue';
import DocumentViewer from '@/Components/StudySpace/DocumentViewer.vue';
import FlashcardViewer from '@/Components/StudySpace/FlashcardViewer.vue';

const props = defineProps({
    teamId: [Number, String],
    documents: Array,
    notebooks: Array,
    flashcardSets: Array,
});

const selectedItem = ref(null); 
const sidebarFilter = ref('all');

// --- STATE MODALS ---
const isUploadModalOpen = ref(false);
const isCreateNotebookModalOpen = ref(false);
const isCreateFlashcardSetModalOpen = ref(false);
const isAddCardModalOpen = ref(false);
const isAiGenerateModalOpen = ref(false);

// --- FORMS ---
const uploadForm = useForm({ file: null });
const notebookForm = useForm({ title: '', type: 'notebook' });
const flashcardSetForm = useForm({ title: '', description: '', color: '#2dd4bf' });
const cardForm = useForm({ front: '', back: '' });
const aiGenerateForm = useForm({ document_id: '', quantity: 5 });

// --- ACTIONS ---
const selectItem = (type, data) => {
    selectedItem.value = { type, data };
};

const handleUpload = () => {
    uploadForm.post(route('memory-shards.upload', { teamId: props.teamId }), {
        onSuccess: () => { isUploadModalOpen.value = false; uploadForm.reset(); },
    });
};

const handleCreateNotebook = () => {
    notebookForm.post(route('memory-shards.notebook.save', { teamId: props.teamId }), {
        onSuccess: () => { isCreateNotebookModalOpen.value = false; notebookForm.reset(); },
    });
};

const handleCreateFlashcardSet = () => {
    flashcardSetForm.post(route('memory-shards.flashcards.set.store', { teamId: props.teamId }), {
        onSuccess: () => { isCreateFlashcardSetModalOpen.value = false; flashcardSetForm.reset(); },
    });
};

const handleAddCard = () => {
    if (selectedItem.value?.type !== 'flashcard') return;
    cardForm.post(route('memory-shards.flashcards.add', { 
        teamId: props.teamId, 
        setId: selectedItem.value.data.id 
    }), {
        onSuccess: () => { 
            isAddCardModalOpen.value = false; 
            cardForm.reset(); 
        },
    });
};

const handleAiGenerate = () => {
    if (!selectedItem.value?.data?.id) return;
    aiGenerateForm.post(route('memory-shards.flashcards.generate-ai', { 
        teamId: props.teamId, 
        setId: selectedItem.value.data.id 
    }), {
        onSuccess: () => { 
            isAiGenerateModalOpen.value = false; 
            aiGenerateForm.reset();
        },
    });
};

const getFileIcon = (type) => {
    if (['jpg', 'jpeg', 'png', 'webp'].includes(type)) return '🖼️';
    if (type === 'pdf') return '📄';
    return '📁';
};

const filteredNotebooks = computed(() => sidebarFilter.value === 'documents' || sidebarFilter.value === 'flashcards' ? [] : props.notebooks);
const filteredDocuments = computed(() => sidebarFilter.value === 'notebooks' || sidebarFilter.value === 'flashcards' ? [] : props.documents);
const filteredFlashcards = computed(() => sidebarFilter.value === 'notebooks' || sidebarFilter.value === 'documents' ? [] : props.flashcardSets);
</script>

<template>
    <AppLayout title="Ghi chú & Flashcards" :team-id="teamId">
        <div class="h-[calc(100vh-80px)] flex bg-[#020617] gap-5 p-5">
            
            <!-- LEFT SIDEBAR: MINIMALIST LIST -->
            <aside class="w-80 flex flex-col gap-5 shrink-0 overflow-hidden">
                <!-- Action Hub (Floating style) -->
                <div class="bg-slate-900/60 border border-white/10 rounded-[2.5rem] p-5 backdrop-blur-3xl shadow-2xl">
                    <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.4em] mb-5 pl-2">Các tính năng</h2>
                    <div class="grid grid-cols-3 gap-3">
                        <button @click="isUploadModalOpen = true" class="flex flex-col items-center justify-center aspect-square rounded-3xl bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 hover:border-blue-500/50 transition-all group">
                            <span class="text-xl mb-1 group-hover:scale-110 transition">📤</span>
                            <span class="text-[8px] font-black text-blue-400 uppercase tracking-widest">Import File</span>
                        </button>
                        <button @click="isCreateNotebookModalOpen = true" class="flex flex-col items-center justify-center aspect-square rounded-3xl bg-purple-500/10 border border-purple-500/20 hover:bg-purple-500/20 hover:border-purple-500/50 transition-all group">
                            <span class="text-xl mb-1 group-hover:scale-110 transition">📓</span>
                            <span class="text-[8px] font-black text-purple-400 uppercase tracking-widest">Tạo Note</span>
                        </button>
                        <button @click="isCreateFlashcardSetModalOpen = true" class="flex flex-col items-center justify-center aspect-square rounded-3xl bg-teal-500/10 border border-teal-500/20 hover:bg-teal-500/20 hover:border-teal-500/50 transition-all group">
                            <span class="text-xl mb-1 group-hover:scale-110 transition">🗂️</span>
                            <span class="text-[8px] font-black text-teal-400 uppercase tracking-widest">Tạo Flashcard</span>
                        </button>
                    </div>
                </div>

                <!-- Navigator List -->
                <div class="flex-1 bg-slate-900/40 border border-white/5 rounded-[2.5rem] p-5 flex flex-col overflow-hidden backdrop-blur-xl">
                    <div class="flex p-1 bg-black/40 rounded-2xl mb-6 border border-white/5">
                        <button v-for="f in ['all', 'flashcards', 'notebooks', 'documents']" :key="f"
                            @click="sidebarFilter = f"
                            :class="sidebarFilter === f ? 'bg-slate-700 text-white shadow-xl' : 'text-slate-500 hover:text-slate-300'"
                            class="flex-1 py-2 text-[9px] font-black uppercase tracking-tighter rounded-xl transition-all"
                        >{{ f.charAt(0) }}</button>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-6 pr-1">
                        <!-- Flashcard Sets -->
                        <div v-if="filteredFlashcards.length" class="space-y-3">
                            <h3 class="text-[9px] font-black text-teal-500/50 uppercase tracking-[0.3em] px-2">Flashcard đã tạo</h3>
                            <div v-for="set in filteredFlashcards" :key="set.id" @click="selectItem('flashcard', set)"
                                class="p-3.5 rounded-[1.5rem] cursor-pointer transition-all border flex items-center gap-4 group"
                                :class="selectedItem?.data?.id === set.id && selectedItem.type === 'flashcard' ? 'bg-teal-500/10 border-teal-500/50 shadow-[0_0_20px_rgba(20,184,166,0.1)]' : 'border-transparent hover:bg-white/5 hover:border-white/10'">
                                <div class="w-10 h-10 rounded-2xl bg-slate-800 flex items-center justify-center group-hover:bg-teal-500/20 transition-colors">🗂️</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-bold text-slate-200 truncate font-exo">{{ set.title }}</div>
                                    <div class="text-[9px] text-slate-500 font-mono tracking-widest uppercase">{{ set.cards_count }} Thẻ</div>
                                </div>
                            </div>
                        </div>

                        <!-- Notebooks -->
                        <div v-if="filteredNotebooks.length" class="space-y-3">
                            <h3 class="text-[9px] font-black text-purple-500/50 uppercase tracking-[0.3em] px-2">Data Shards</h3>
                            <div v-for="nb in filteredNotebooks" :key="nb.id" @click="selectItem('notebook', nb)"
                                class="p-3.5 rounded-[1.5rem] cursor-pointer transition-all border flex items-center gap-4 group"
                                :class="selectedItem?.data?.id === nb.id && selectedItem.type === 'notebook' ? 'bg-purple-500/10 border-purple-500/50 shadow-[0_0_20px_rgba(168,85,247,0.1)]' : 'border-transparent hover:bg-white/5 hover:border-white/10'">
                                <div class="w-10 h-10 rounded-2xl bg-slate-800 flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">{{ nb.type === 'spreadsheet' ? '📊' : '📒' }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-bold text-slate-200 truncate font-exo">{{ nb.title }}</div>
                                    <div class="text-[9px] text-slate-500 font-mono tracking-widest uppercase">{{ nb.type }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div v-if="filteredDocuments.length" class="space-y-3">
                            <h3 class="text-[9px] font-black text-blue-500/50 uppercase tracking-[0.3em] px-2">Tài liệu đã tải lên</h3>
                            <div v-for="doc in filteredDocuments" :key="doc.id" @click="selectItem('document', doc)"
                                class="p-3.5 rounded-[1.5rem] cursor-pointer transition-all border flex items-center gap-4 group"
                                :class="selectedItem?.data?.id === doc.id && selectedItem.type === 'document' ? 'bg-blue-500/10 border-blue-500/50 shadow-[0_0_20px_rgba(59,130,246,0.1)]' : 'border-transparent hover:bg-white/5 hover:border-white/10'">
                                <div class="w-10 h-10 rounded-2xl bg-slate-800 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">{{ getFileIcon(doc.file_type) }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-bold text-slate-200 truncate font-exo">{{ doc.title }}</div>
                                    <div class="text-[9px] text-slate-500 font-mono tracking-widest uppercase">{{ doc.file_type }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN VIEWPORT (Expanded for Flashcard) -->
            <main class="flex-1 bg-slate-950/40 border border-white/5 rounded-[3rem] overflow-hidden relative flex flex-col shadow-2xl backdrop-blur-sm group">
                
                <!-- Dynamic Header (Floating style) -->
                <div v-if="selectedItem" class="h-16 border-b border-white/5 flex items-center justify-between px-10 bg-slate-900/20 backdrop-blur-xl shrink-0 z-40">
                    <div class="flex items-center gap-5">
                        <div class="w-2.5 h-2.5 rounded-full animate-pulse shadow-[0_0_10px_currentColor]" :class="selectedItem.type === 'flashcard' ? 'text-teal-400 bg-teal-400' : (selectedItem.type === 'notebook' ? 'text-purple-400 bg-purple-400' : 'text-blue-400 bg-blue-400')"></div>
                        <h3 class="font-black text-white uppercase tracking-[0.2em] font-exo text-sm">{{ selectedItem.data.title }}</h3>
                    </div>

                    <div v-if="selectedItem.type === 'flashcard'" class="flex gap-3">
                        <button @click="isAiGenerateModalOpen = true" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full transition-all shadow-lg shadow-purple-500/20 flex items-center gap-2">
                             <span>✨</span> Tạo Flashcard bằng AI
                        </button>
                        <button @click="isAddCardModalOpen = true" class="px-5 py-2 bg-teal-600 hover:bg-teal-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full transition-all shadow-lg shadow-teal-500/20 flex items-center gap-2">
                             <span>+</span> Thêm thẻ mới
                        </button>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1 relative overflow-hidden flex flex-col items-center justify-center">
                    <div v-if="!selectedItem" class="h-full flex flex-col items-center justify-center text-slate-700 animate-pulse text-center p-12">
                        <div class="text-7xl mb-8 opacity-20 filter grayscale">📡</div>
                        <h2 class="text-2xl font-black text-white uppercase tracking-[0.3em] font-exo mb-2">Đang chờ bạn</h2>
                        <p class="font-mono text-[10px] uppercase tracking-[0.4em] text-slate-500">Chúng tôi đang chờ bạn sử dụng chức năng...</p>
                    </div>
                    
                    <div v-else class="h-full w-full animate-fade-in">
                        <NotebookEditor v-if="selectedItem.type === 'notebook'" :notebook="selectedItem.data" :team-id="teamId" />
                        <DocumentViewer v-if="selectedItem.type === 'document'" :document="selectedItem.data" />
                        <FlashcardViewer v-if="selectedItem.type === 'flashcard'" :flashcard-set="selectedItem.data" />
                    </div>
                </div>
                
                <!-- Background Decoration Overlay -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(6,182,212,0.05)_0%,transparent_50%)] pointer-events-none opacity-50"></div>
            </main>
        </div>

        <!-- MODALS (Redesigned for modern look) -->
        <Modal :show="isUploadModalOpen" @close="isUploadModalOpen = false">
            <div class="p-10 bg-slate-950 text-white border border-white/10 rounded-[3rem]">
                <h3 class="text-xl font-black uppercase font-exo tracking-[0.2em] mb-8 border-b border-white/5 pb-4">Data Ingestion</h3>
                <form @submit.prevent="handleUpload" class="space-y-8">
                    <div class="border-2 border-dashed border-slate-800 rounded-[2.5rem] p-16 text-center hover:border-blue-500/50 hover:bg-blue-500/5 transition-all cursor-pointer relative group overflow-hidden">
                        <input type="file" @change="e => uploadForm.file = e.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <div class="flex flex-col items-center gap-5 relative z-0">
                            <span class="text-6xl group-hover:scale-110 transition duration-500">📄</span>
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono group-hover:text-blue-400 transition-colors">{{ uploadForm.file ? uploadForm.file.name : 'Click or drop source protocol' }}</span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="button" @click="isUploadModalOpen = false" class="px-8 py-3 text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest transition">Abort</button>
                        <button type="submit" :disabled="uploadForm.processing" class="px-10 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl transition-all active:scale-95">Initiate Uplink</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="isAiGenerateModalOpen" @close="isAiGenerateModalOpen = false">
            <div class="p-10 bg-slate-950 text-white border border-white/10 rounded-[3rem]">
                <h3 class="text-xl font-black uppercase font-exo tracking-[0.2em] mb-8 border-b border-white/5 pb-4 flex items-center gap-4">
                    <span class="text-purple-400">✨</span> Tạo Flashcard bằng AI
                </h3>
                <form @submit.prevent="handleAiGenerate" class="space-y-8">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest font-mono pl-2">Thêm nguồn tài liệu</label>
                        <select v-model="aiGenerateForm.document_id" class="w-full bg-slate-900 border border-slate-800 rounded-3xl p-5 text-white focus:border-purple-500 outline-none font-bold appearance-none transition-all" required>
                            <option value="" disabled>Chọn nguồn tài liệu...</option>
                            <option v-for="doc in documents" :key="doc.id" :value="doc.id">📄 {{ doc.title }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-4 block tracking-widest font-mono pl-2">Số lượng thẻ yêu cầu: {{ aiGenerateForm.quantity }}</label>
                        <input type="range" v-model="aiGenerateForm.quantity" min="1" max="20" class="w-full accent-purple-500 h-1.5 bg-slate-800 rounded-lg appearance-none cursor-pointer">
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <button type="button" @click="isAiGenerateModalOpen = false" class="px-8 py-3 text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest">Hủy</button>
                        <button type="submit" :disabled="aiGenerateForm.processing" class="px-10 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:shadow-purple-500/20 active:scale-95 transition-all">Start Synthesis</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="isAddCardModalOpen" @close="isAddCardModalOpen = false">
            <div class="p-10 bg-slate-950 text-white border border-white/10 rounded-[3rem]">
                <h3 class="text-xl font-black uppercase font-exo tracking-[0.2em] mb-8 border-b border-white/5 pb-4 text-teal-400">Tạo Flashcard thủ công</h3>
                <form @submit.prevent="handleAddCard" class="space-y-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2">Kiến thức mặt đằng trước</label>
                        <textarea v-model="cardForm.front" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-6 text-white focus:border-teal-500 outline-none font-bold h-32 resize-none transition-all placeholder:text-slate-700" placeholder="Nhập dữ liệu..." required></textarea>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2">Kiến thức mặt đằng sau</label>
                        <textarea v-model="cardForm.back" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-6 text-white focus:border-teal-500 outline-none font-bold h-32 resize-none transition-all placeholder:text-slate-700" placeholder="Nhập dữ liệu..." required></textarea>
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <button type="button" @click="isAddCardModalOpen = false" class="px-8 py-3 text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest transition">Cancel</button>
                        <button type="submit" :disabled="cardForm.processing" class="px-10 py-3 bg-teal-600 hover:bg-teal-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">Commit Node</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="isCreateNotebookModalOpen" @close="isCreateNotebookModalOpen = false">
            <div class="p-10 bg-slate-950 text-white border border-white/10 rounded-[3rem]">
                <h3 class="text-xl font-black uppercase font-exo tracking-[0.2em] mb-8 border-b border-white/5 pb-4 text-purple-400">Khởi tạo Note mới</h3>
                <form @submit.prevent="handleCreateNotebook" class="space-y-8">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2 block mb-3">Tên sổ tay</label>
                        <input v-model="notebookForm.title" type="text" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-5 text-white focus:border-purple-500 outline-none font-bold transition-all" placeholder="Ví dụ: Ghi chú Toán..." required />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2 block mb-3">Định dạng dữ liệu</label>
                        <select v-model="notebookForm.type" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-5 text-white outline-none focus:border-purple-500 appearance-none font-bold">
                            <option value="notebook">📝 Text Editor (Văn bản)</option>
                            <option value="spreadsheet">📊 Spreadsheet (Bảng tính)</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <button type="button" @click="isCreateNotebookModalOpen = false" class="px-8 py-3 text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest transition">Hủy</button>
                        <button type="submit" :disabled="notebookForm.processing" class="px-10 py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">Tạo Note</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="isCreateFlashcardSetModalOpen" @close="isCreateFlashcardSetModalOpen = false">
            <div class="p-10 bg-slate-950 text-white border border-white/10 rounded-[3rem]">
                <h3 class="text-xl font-black uppercase font-exo tracking-[0.2em] mb-8 border-b border-white/5 pb-4 text-teal-400">Tạo Bộ Flashcard</h3>
                <form @submit.prevent="handleCreateFlashcardSet" class="space-y-8">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2 block mb-3">Tên bộ thẻ</label>
                        <input v-model="flashcardSetForm.title" type="text" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-5 text-white focus:border-teal-500 outline-none font-bold transition-all" placeholder="Ví dụ: Từ vựng Unit 1..." required />
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest font-mono pl-2 block mb-3">Mô tả (Optional)</label>
                        <textarea v-model="flashcardSetForm.description" class="w-full bg-slate-900 border border-slate-800 rounded-[2rem] p-5 text-white focus:border-teal-500 outline-none font-bold h-24 resize-none transition-all"></textarea>
                    </div>
                    <div class="flex justify-end gap-4 pt-6 border-t border-white/5">
                        <button type="button" @click="isCreateFlashcardSetModalOpen = false" class="px-8 py-3 text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest transition">Hủy</button>
                        <button type="submit" :disabled="flashcardSetForm.processing" class="px-10 py-3 bg-teal-600 hover:bg-teal-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">Tạo Bộ Thẻ</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(34, 211, 238, 0.2); }

.animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>