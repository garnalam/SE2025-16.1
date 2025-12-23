<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
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
    <input
        ref="input"
        class="w-full border-slate-700 bg-slate-900/50 text-white focus:border-cyan-500 focus:ring-cyan-500/50 rounded-lg shadow-sm placeholder-slate-600 font-sans transition-all duration-300"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>