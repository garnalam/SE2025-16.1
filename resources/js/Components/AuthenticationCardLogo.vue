<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="group inline-flex items-center gap-3 rounded-full border border-white/10 bg-slate-900/80 px-5 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-100 transition hover:border-white/30 hover:bg-slate-900">
        <span class="size-2 rounded-full bg-sky-400 transition group-hover:bg-indigo-400"></span>
        Lớp học tương tác
    </Link>
</template>
