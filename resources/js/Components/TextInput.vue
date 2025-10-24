<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    // 1. THÊM ĐOẠN NÀY VÀO
    type: {
        type: String,
        default: 'text',
    },
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative">
        <input
            ref="input"
            class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-[0_18px_40px_-22px_rgba(59,130,246,0.55)] transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-400/25"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :type="type" >
        <div class="pointer-events-none absolute inset-0 -z-10 rounded-2xl border border-white/40 bg-white/40 blur-2xl opacity-40"></div>
    </div>
</template>