<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-500 px-6 py-2.5 text-sm font-bold text-white shadow-[0_0_20px_rgba(14,165,233,0.3)] transition-all duration-300 hover:shadow-[0_0_30px_rgba(14,165,233,0.5)] hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-sky-400/60 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
    >
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.4),_transparent)] opacity-0 transition group-hover:opacity-100"></div>
        <slot />
        <svg class="w-4 h-4 translate-x-0 opacity-70 transition-all group-hover:translate-x-1 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
    </button>
</template>