<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 bg-transparent border border-slate-600 rounded-xl font-bold text-xs text-slate-300 uppercase tracking-widest shadow-sm hover:bg-slate-800 hover:text-white hover:border-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-25 transition ease-in-out duration-150">
        <slot />
    </button>
</template>