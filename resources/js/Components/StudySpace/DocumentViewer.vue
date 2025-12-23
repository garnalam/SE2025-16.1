<script setup>
import { ref, watch, nextTick, computed, onBeforeUnmount } from 'vue';
import VuePdfEmbed from 'vue-pdf-embed';
import { router } from '@inertiajs/vue3';
import * as fabricModule from 'fabric';

// Setup Fabric
const fabric = fabricModule.default || fabricModule;

const props = defineProps({
    document: Object
});

const containerRef = ref(null);
const canvasRef = ref(null);
const fabricCanvas = ref(null);

const currentPage = ref(1);
const isDrawingMode = ref(false); 
const brushColor = ref('#ef4444'); 
const brushSize = ref(2); 
const currentTool = ref('pen'); // 'pen' hoặc 'eraser'

// --- KIỂM TRA LOẠI FILE ---
const isPdf = computed(() => props.document.file_type === 'pdf');
const isImage = computed(() => ['jpg', 'jpeg', 'png', 'webp'].includes(props.document.file_type));
const isUnsupported = computed(() => !isPdf.value && !isImage.value);

// --- LOGIC VẼ (CANVAS) ---
const initCanvas = (width, height) => {
    if (!canvasRef.value) return;

    if (fabricCanvas.value) {
        fabricCanvas.value.dispose();
    }

    const canvas = new fabric.Canvas(canvasRef.value, {
        width: width,
        height: height,
        isDrawingMode: isDrawingMode.value, 
        backgroundColor: null 
    });

    fabricCanvas.value = canvas;

    // --- SỬA LỖI CỤC TẨY Ở ĐÂY ---
    // Khi một nét vẽ được tạo xong
    canvas.on('path:created', function(opt) {
        if (currentTool.value === 'eraser') {
            // Gán thuộc tính xóa cho đối tượng nét vẽ này
            opt.path.globalCompositeOperation = 'destination-out';
            // Quan trọng: Phải render lại để thấy hiệu ứng xóa ngay lập tức
            canvas.renderAll();
        }
    });
    // -----------------------------

    updateBrush(); 
    loadAnnotations(currentPage.value);
};

// Cấu hình Bút / Tẩy
const updateBrush = () => {
    if (!fabricCanvas.value) return;

    fabricCanvas.value.isDrawingMode = isDrawingMode.value;
    
    // Luôn tạo bút mới để reset trạng thái
    const brush = new fabric.PencilBrush(fabricCanvas.value);

    if (currentTool.value === 'eraser') {
        // Chế độ tẩy
        brush.color = 'white'; // Màu không quan trọng vì sẽ dùng destination-out
        brush.width = brushSize.value * 10; // Tẩy to gấp 10 lần bút thường cho dễ xóa
        // Lưu ý: Setting này chỉ ảnh hưởng lúc đang kéo chuột
        // Sự kiện path:created ở trên sẽ xử lý việc lưu trạng thái xóa
    } else {
        // Chế độ bút
        brush.color = brushColor.value;
        brush.width = parseInt(brushSize.value);
    }
    
    fabricCanvas.value.freeDrawingBrush = brush;
};

// Load Annotation
const loadAnnotations = (page) => {
    if (!fabricCanvas.value) return;
    fabricCanvas.value.clear();
    const pageAnno = props.document.annotations?.find(a => a.page_number === page);
    if (pageAnno && pageAnno.data) {
        fabricCanvas.value.loadFromJSON(pageAnno.data, fabricCanvas.value.renderAll.bind(fabricCanvas.value));
    }
};

const saveAnnotation = () => {
    if (!fabricCanvas.value) return;
    const json = JSON.stringify(fabricCanvas.value.toJSON());
    
    router.post(route('memory-shards.annotation.save', { documentId: props.document.id }), {
        page_number: currentPage.value,
        data: json
    }, { preserveScroll: true, onSuccess: () => alert('Đã lưu thành công!') });
};

const setTool = (tool) => {
    currentTool.value = tool;
    isDrawingMode.value = true;
    updateBrush();
};

const toggleDrawingMode = () => {
    isDrawingMode.value = !isDrawingMode.value;
    updateBrush();
};

const clearPage = () => {
    if(confirm('Xóa hết nét vẽ trang này?')) fabricCanvas.value.clear();
};

const onMediaLoaded = (event) => {
    nextTick(() => {
        const contentEl = containerRef.value?.querySelector('.media-content');
        if (contentEl) {
            initCanvas(contentEl.clientWidth, contentEl.clientHeight);
        }
    });
};

watch(() => props.document, () => {
    currentPage.value = 1;
    if(fabricCanvas.value) fabricCanvas.value.clear();
});

watch([brushColor, brushSize], () => {
    if(currentTool.value === 'pen') updateBrush();
});

onBeforeUnmount(() => {
    if(fabricCanvas.value) fabricCanvas.value.dispose();
});
</script>

<template>
    <div class="relative w-full h-full flex flex-col bg-slate-900 overflow-hidden">
        
        <div class="h-14 bg-slate-800 border-b border-white/10 flex items-center px-4 justify-between shrink-0 z-30 shadow-md">
            <div class="flex items-center gap-2 text-white">
                <button @click="currentPage > 1 && currentPage--" class="p-1.5 hover:bg-white/10 rounded disabled:opacity-50" :disabled="currentPage <= 1">◀</button>
                <span class="text-xs font-bold font-mono bg-black/30 px-2 py-1 rounded">PAGE {{ currentPage }}</span>
                <button @click="currentPage++" class="p-1.5 hover:bg-white/10 rounded">▶</button>
            </div>

            <div v-if="!isUnsupported" class="flex items-center gap-4 bg-black/20 px-3 py-1.5 rounded-lg border border-white/5">
                
                <button 
                    @click="toggleDrawingMode"
                    class="flex items-center gap-2 px-3 py-1 text-xs font-bold rounded transition border"
                    :class="isDrawingMode ? 'bg-teal-600 border-teal-500 text-white' : 'bg-slate-700 border-slate-600 text-slate-300'"
                >
                    {{ isDrawingMode ? '✍️ Đang Vẽ' : '✋ Cuộn Trang' }}
                </button>

                <div v-if="isDrawingMode" class="flex items-center gap-3 animate-fade-in">
                    <div class="h-6 w-px bg-white/20"></div> <div class="flex flex-col items-center">
                        <input type="color" v-model="brushColor" class="w-6 h-6 rounded cursor-pointer border-none bg-transparent p-0" title="Màu bút" />
                    </div>

                    <div class="flex flex-col w-20">
                        <input type="range" v-model="brushSize" min="1" max="20" class="h-1 bg-slate-600 rounded-lg appearance-none cursor-pointer accent-teal-400">
                    </div>

                    <button @click="setTool('pen')" 
                        class="p-1.5 rounded transition"
                        :class="currentTool === 'pen' ? 'bg-teal-500/30 text-teal-400 ring-1 ring-teal-500' : 'text-slate-400 hover:text-white'"
                        title="Bút vẽ"
                    >
                        ✏️
                    </button>

                    <button @click="setTool('eraser')" 
                        class="p-1.5 rounded transition"
                        :class="currentTool === 'eraser' ? 'bg-rose-500/30 text-rose-400 ring-1 ring-rose-500' : 'text-slate-400 hover:text-white'"
                        title="Tẩy"
                    >
                        🧹
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="clearPage" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded transition" title="Xóa hết trang này">🗑️</button>
                <button @click="saveAnnotation" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded shadow-lg transition transform active:scale-95">
                    Lưu Note
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-auto relative bg-gray-600 flex justify-center p-8 custom-scrollbar" ref="containerRef">
            
            <div v-if="isUnsupported" class="flex flex-col items-center justify-center text-slate-300 mt-20">
                <div class="text-4xl mb-4">⚠️</div>
                <p class="font-bold">Định dạng file không hỗ trợ vẽ (.{{ document.file_type }})</p>
                <p class="text-sm opacity-70 mt-2">Vui lòng upload file <span class="text-teal-400 font-bold">PDF</span> hoặc <span class="text-teal-400 font-bold">Hình ảnh</span> để sử dụng tính năng ghi chú.</p>
                <a :href="document.file_path" download class="mt-4 px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded text-sm">Tải xuống file này</a>
            </div>

            <div v-else class="relative shadow-2xl transition-transform duration-200">
                
                <div class="media-content bg-white min-w-[600px] min-h-[800px]">
                    <VuePdfEmbed 
                        v-if="isPdf"
                        :source="document.file_path" 
                        :page="currentPage"
                        @rendered="onMediaLoaded"
                        width="800"
                    />
                    <img 
                        v-else-if="isImage"
                        :src="document.file_path"
                        class="max-w-[800px] w-full h-auto block"
                        @load="onMediaLoaded"
                    />
                </div>
                
                <div class="absolute inset-0 z-20" 
                     :class="isDrawingMode ? 'pointer-events-auto cursor-crosshair' : 'pointer-events-none'">
                    <canvas ref="canvasRef"></canvas>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}
/* Style cho thanh trượt */
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 12px;
  width: 12px;
  border-radius: 50%;
  background: #2dd4bf; /* Teal-400 */
  margin-top: -4px;
}
input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 4px;
  cursor: pointer;
  background: #475569;
  border-radius: 2px;
}
</style>