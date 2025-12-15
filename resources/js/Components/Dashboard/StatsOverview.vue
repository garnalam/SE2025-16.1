<script setup>
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const cards = [
    {
        key: 'totalClasses',
        label: 'Lớp học',
        accent: 'from-indigo-500 via-purple-500 to-fuchsia-500',
        glow: 'shadow-[0_30px_70px_-45px_rgba(99,102,241,0.7)]',
        icon: '🏫',
    },
    {
        key: 'totalStudents',
        label: 'Học sinh',
        accent: 'from-sky-400 via-cyan-500 to-emerald-400',
        glow: 'shadow-[0_30px_70px_-45px_rgba(20,184,166,0.7)]',
        icon: '👨‍🎓',
    },
    {
        key: 'activeAssignments',
        label: 'Bài tập đang mở',
        accent: 'from-amber-500 via-orange-500 to-rose-500',
        glow: 'shadow-[0_30px_70px_-45px_rgba(249,115,22,0.7)]',
        icon: '📝',
    },
    {
        key: 'totalUngradedSubmissions',
        label: 'Bài chờ chấm',
        accent: 'from-rose-500 via-fuchsia-500 to-purple-500',
        glow: 'shadow-[0_30px_70px_-45px_rgba(236,72,153,0.7)]',
        icon: '⏳',
    },
];
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div
            v-for="card in cards"
            :key="card.key"
            class="relative overflow-hidden rounded-[22px] border border-white/12 bg-white/6 p-5 backdrop-blur-2xl"
        >
            <div :class="['absolute -top-20 -right-16 h-36 w-36 rounded-full blur-[140px]', `bg-gradient-to-br ${card.accent}`]"></div>
            <div class="relative z-10 space-y-4">
                <span class="text-xl">{{ card.icon }}</span>
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">{{ card.label }}</p>
                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ props.stats?.[card.key] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
