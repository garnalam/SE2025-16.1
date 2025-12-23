<script setup>
import { ref, watch } from 'vue';
import MathRender from '@/Components/MathRender.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    modelValue: String,
    imagePreview: String
});

const emit = defineEmits(['update:modelValue', 'update:image']);
const localContent = ref(props.modelValue);
const fileInput = ref(null);
const localImage = ref(props.imagePreview);

watch(localContent, (val) => emit('update:modelValue', val));
watch(() => props.modelValue, (val) => localContent.value = val);

const insert = (text) => localContent.value = (localContent.value || '') + text;
const handleFile = (e) => {
    const file = e.target.files[0];
    if (file) {
        localImage.value = URL.createObjectURL(file);
        emit('update:image', file);
    }
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-[400px]">
        <div class="flex flex-col gap-2">
            <div class="flex gap-2 bg-slate-800 p-2 rounded-t border border-slate-700">
                <button type="button" @click="insert(' $x^2$ ')" class="px-2 bg-slate-700 text-white text-xs rounded hover:bg-cyan-600">x²</button>
                <button type="button" @click="insert(' $\\frac{a}{b}$ ')" class="px-2 bg-slate-700 text-white text-xs rounded hover:bg-cyan-600">a/b</button>
                <button type="button" @click="$refs.fileInput.click()" class="px-2 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-500 ml-auto">📸 Ảnh</button>
                <input ref="fileInput" type="file" hidden accept="image/*" @change="handleFile">
            </div>
            <TextArea v-model="localContent" class="flex-1 bg-slate-900 border-slate-700 font-mono text-sm" placeholder="Nhập câu hỏi... (Dùng $...$ cho công thức)"/>
        </div>
        <div class="bg-slate-950 border border-slate-800 rounded p-4 overflow-y-auto">
            <div class="text-[10px] text-slate-500 uppercase font-bold mb-2">Xem trước</div>
            <img v-if="localImage" :src="localImage" class="max-h-32 mb-2 rounded border border-slate-700">
            <MathRender :content="localContent" />
        </div>
    </div>
</template>
