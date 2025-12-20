<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { watch, onBeforeUnmount, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    notebook: Object,
    teamId: [Number, String]
});

const isSaving = ref(false);

const editor = useEditor({
    content: props.notebook.content || '<p>Bắt đầu ghi chú...</p>',
    extensions: [
        StarterKit,
        Table.configure({ resizable: true }),
        TableRow,
        TableHeader,
        TableCell,
    ],
editorProps: {
    attributes: {
        // Thêm 'prose-invert' vào ngay sau 'prose'
        class: 'prose prose-invert prose-sm sm:prose-base lg:prose-lg xl:prose-2xl m-5 focus:outline-none text-slate-300 max-w-none',
    },
},
    onUpdate: ({ editor }) => {
        // Tự động lưu sau 2 giây (Debounce logic đơn giản)
        saveContent(editor.getHTML());
    },
});

let timeoutId = null;
const saveContent = (html) => {
    isSaving.value = true;
    clearTimeout(timeoutId);
    
    timeoutId = setTimeout(() => {
        router.post(route('memory-shards.notebook.save', { teamId: props.teamId }), {
            id: props.notebook.id,
            title: props.notebook.title,
            type: props.notebook.type,
            content: html
        }, {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => isSaving.value = false
        });
    }, 2000); // Lưu sau 2s ngừng gõ
};

// Clean up
onBeforeUnmount(() => {
    editor.value?.destroy();
});

// Update content khi chuyển sang notebook khác
watch(() => props.notebook, (newVal) => {
    if (editor.value && newVal.content !== editor.value.getHTML()) {
        editor.value.commands.setContent(newVal.content);
    }
});
</script>

<template>
    <div class="flex flex-col h-full bg-slate-900">
        <div v-if="editor" class="flex flex-wrap gap-2 p-2 bg-slate-800 border-b border-white/10 items-center">
            
            <div class="flex gap-1 border-r border-white/10 pr-2">
                <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-teal-500/20 text-teal-400': editor.isActive('bold') }" class="p-1.5 rounded hover:bg-white/5 text-slate-400 font-bold">B</button>
                <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-teal-500/20 text-teal-400': editor.isActive('italic') }" class="p-1.5 rounded hover:bg-white/5 text-slate-400 italic">I</button>
                <button @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-teal-500/20 text-teal-400': editor.isActive('strike') }" class="p-1.5 rounded hover:bg-white/5 text-slate-400 line-through">S</button>
            </div>

            <div class="flex gap-1 border-r border-white/10 pr-2">
                <button @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'bg-teal-500/20 text-teal-400': editor.isActive('heading', { level: 1 }) }" class="p-1.5 rounded hover:bg-white/5 text-slate-400 text-xs">H1</button>
                <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-teal-500/20 text-teal-400': editor.isActive('heading', { level: 2 }) }" class="p-1.5 rounded hover:bg-white/5 text-slate-400 text-xs">H2</button>
            </div>

            <div class="flex gap-1">
                <button @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()" class="p-1.5 rounded hover:bg-white/5 text-slate-400 text-xs flex items-center gap-1">
                    <span>▦</span> Table
                </button>
            </div>

            <div class="ml-auto text-xs text-slate-500 font-mono">
                {{ isSaving ? 'Đang lưu...' : 'Đã lưu' }}
            </div>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar bg-[#0f172a]">
            <editor-content :editor="editor" />
        </div>
    </div>
</template>

<style>
/* CSS cho bảng trong Editor */
.ProseMirror table {
  border-collapse: collapse;
  margin: 0;
  overflow: hidden;
  table-layout: fixed;
  width: 100%;
}
.ProseMirror td, .ProseMirror th {
  border: 1px solid #334155;
  box-sizing: border-box;
  min-width: 1em;
  padding: 6px 8px;
  position: relative;
  vertical-align: top;
}
.ProseMirror th {
  background-color: #1e293b;
  font-weight: bold;
}
.ProseMirror { outline: none; }
</style>