<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue'; 
// Chúng ta sẽ tạo 2 components này ở bước sau
import NotebookEditor from '@/Components/StudySpace/NotebookEditor.vue';
import DocumentViewer from '@/Components/StudySpace/DocumentViewer.vue';

const props = defineProps({
    teamId: [Number, String],
    documents: Array,
    notebooks: Array,
});

const selectedItem = ref(null); // Item đang chọn
const isUploadModalOpen = ref(false);
const isCreateNotebookModalOpen = ref(false);

// Form Upload
const uploadForm = useForm({
    file: null,
});

// Form Tạo Notebook
const notebookForm = useForm({
    title: '',
    type: 'notebook', // hoặc 'spreadsheet'
});

const handleUpload = () => {
    uploadForm.post(route('memory-shards.upload', { teamId: props.teamId }), {
        onSuccess: () => {
            isUploadModalOpen.value = false;
            uploadForm.reset();
        },
    });
};

const handleCreateNotebook = () => {
    notebookForm.post(route('memory-shards.notebook.save', { teamId: props.teamId }), {
        onSuccess: () => {
            isCreateNotebookModalOpen.value = false;
            notebookForm.reset();
        },
    });
};

const selectItem = (type, data) => {
    selectedItem.value = { type, data };
};

const getFileIcon = (type) => {
    if (['jpg', 'jpeg', 'png'].includes(type)) return '🖼️';
    if (type === 'pdf') return '📄';
    if (['doc', 'docx'].includes(type)) return '📝';
    return '📁';
};

const onFileChange = (e) => {
    uploadForm.file = e.target.files[0];
};
</script>

<template>
    <AppLayout title="Góc Học Tập">
        <div class="flex h-[calc(100vh-100px)] gap-4">
            
            <div class="w-1/4 bg-slate-900/50 border border-white/10 rounded-xl p-4 flex flex-col overflow-hidden backdrop-blur-sm">
                <h2 class="text-lg font-bold text-teal-400 mb-4 flex items-center gap-2 font-exo">
                    <span>🧩 MEMORY SHARDS</span>
                </h2>

                <div class="flex gap-2 mb-4">
                    <button 
                        @click="isUploadModalOpen = true"
                        class="flex-1 bg-teal-500/10 hover:bg-teal-500/20 text-teal-400 text-xs py-2 rounded-lg border border-teal-500/30 transition font-bold"
                    >
                        + Upload File
                    </button>
                    <button 
                        @click="isCreateNotebookModalOpen = true"
                        class="flex-1 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 text-xs py-2 rounded-lg border border-purple-500/30 transition font-bold"
                    >
                        + Ghi chú
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar space-y-6 pr-2">
                    <div>
                        <h3 class="text-xs font-bold text-slate-500 uppercase mb-2 tracking-wider">Sổ tay</h3>
                        <div class="space-y-1">
                            <div v-for="nb in notebooks" :key="'nb-'+nb.id"
                                @click="selectItem('notebook', nb)"
                                class="p-2 rounded-lg cursor-pointer text-sm flex items-center gap-2 transition border"
                                :class="selectedItem?.data?.id === nb.id && selectedItem.type === 'notebook' 
                                    ? 'bg-purple-600/20 text-purple-300 border-purple-500/30' 
                                    : 'border-transparent text-slate-400 hover:bg-white/5 hover:text-slate-200'"
                            >
                                <span>{{ nb.type === 'spreadsheet' ? '📊' : '📒' }}</span>
                                <span class="truncate">{{ nb.title }}</span>
                            </div>
                            <p v-if="notebooks.length === 0" class="text-xs text-slate-600 italic">Chưa có sổ tay.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-slate-500 uppercase mb-2 tracking-wider">Tài liệu</h3>
                        <div class="space-y-1">
                            <div v-for="doc in documents" :key="'doc-'+doc.id"
                                @click="selectItem('document', doc)"
                                class="p-2 rounded-lg cursor-pointer text-sm flex items-center gap-2 transition border"
                                :class="selectedItem?.data?.id === doc.id && selectedItem.type === 'document' 
                                    ? 'bg-teal-600/20 text-teal-300 border-teal-500/30' 
                                    : 'border-transparent text-slate-400 hover:bg-white/5 hover:text-slate-200'"
                            >
                                <span>{{ getFileIcon(doc.file_type) }}</span>
                                <div class="flex-1 truncate">
                                    <div class="truncate">{{ doc.title }}</div>
                                    <span v-if="doc.is_teacher_resource" class="text-[9px] bg-blue-500/20 text-blue-400 px-1 rounded border border-blue-500/30">Giáo viên</span>
                                </div>
                            </div>
                            <p v-if="documents.length === 0" class="text-xs text-slate-600 italic">Chưa có tài liệu.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 bg-slate-900/50 border border-white/10 rounded-xl overflow-hidden backdrop-blur-sm relative">
                <div v-if="!selectedItem" class="h-full flex flex-col items-center justify-center text-slate-500">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mb-4 animate-float">
                        <span class="text-4xl">🧩</span>
                    </div>
                    <p>Chọn tài liệu hoặc sổ tay để bắt đầu.</p>
                </div>
                
                <div v-else class="h-full w-full">
                    <NotebookEditor 
                        v-if="selectedItem.type === 'notebook'" 
                        :notebook="selectedItem.data" 
                        :team-id="teamId"
                    />
                    <DocumentViewer 
                        v-if="selectedItem.type === 'document'" 
                        :document="selectedItem.data"
                    />
                </div>
            </div>
        </div>

        <Modal :show="isUploadModalOpen" @close="isUploadModalOpen = false">
            <div class="p-6 bg-slate-800 text-white rounded-lg">
                <h3 class="text-lg font-bold mb-4">Upload Tài Liệu</h3>
                <form @submit.prevent="handleUpload">
                    <div class="mb-4 border-2 border-dashed border-slate-600 rounded-lg p-8 text-center hover:border-teal-500 transition cursor-pointer relative">
                        <input type="file" @change="onFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                        <span v-if="uploadForm.file" class="text-teal-400">{{ uploadForm.file.name }}</span>
                        <span v-else class="text-slate-400">Kéo thả hoặc click chọn file</span>
                    </div>
                    <div v-if="uploadForm.progress" class="w-full bg-slate-700 h-2 rounded-full mt-2 overflow-hidden">
                        <div class="bg-teal-500 h-full" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="isUploadModalOpen = false" class="px-4 py-2 text-slate-300 hover:bg-slate-700 rounded">Hủy</button>
                        <button type="submit" :disabled="uploadForm.processing" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded font-bold">
                            {{ uploadForm.processing ? 'Đang tải...' : 'Upload' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="isCreateNotebookModalOpen" @close="isCreateNotebookModalOpen = false">
            <div class="p-6 bg-slate-800 text-white rounded-lg">
                <h3 class="text-lg font-bold mb-4">Tạo Sổ Tay Mới</h3>
                <form @submit.prevent="handleCreateNotebook">
                    <div class="mb-4">
                        <label class="block text-sm text-slate-400 mb-1">Tên sổ tay</label>
                        <input v-model="notebookForm.title" type="text" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white focus:border-purple-500 outline-none" placeholder="Ví dụ: Ghi chú..." required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-slate-400 mb-1">Loại</label>
                        <select v-model="notebookForm.type" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white outline-none">
                            <option value="notebook">📝 Sổ ghi chép</option>
                            <option value="spreadsheet">📊 Bảng tính</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="isCreateNotebookModalOpen = false" class="px-4 py-2 text-slate-300 hover:bg-slate-700 rounded">Hủy</button>
                        <button type="submit" :disabled="notebookForm.processing" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded font-bold">Tạo mới</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>