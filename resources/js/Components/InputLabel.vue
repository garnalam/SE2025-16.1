<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-200/80 whitespace-nowrap md:tracking-[0.28em]">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
