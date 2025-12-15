<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-500/80 mb-2 font-mono">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>