<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>


<template>
    <label class="inline-flex items-center gap-3 text-sm text-slate-100/90">
        <span class="relative flex size-5 items-center justify-center">
            <input
                v-model="proxyChecked"
                type="checkbox"
                :value="value"
                class="peer size-full appearance-none rounded-md border border-white/20 bg-slate-900 transition focus:outline-none focus:ring-2 focus:ring-sky-400/60"
            >
            <span class="pointer-events-none absolute inset-1 rounded-sm bg-gradient-to-br from-sky-500 via-cyan-400 to-indigo-500 opacity-0 transition peer-checked:opacity-100"></span>
            <svg class="pointer-events-none size-3 text-white opacity-0 transition peer-checked:opacity-100" viewBox="0 0 20 20" fill="none">
                <path d="M5 10.5L8.5 14L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <span class="select-none"><slot /></span>
    </label>
</template>
