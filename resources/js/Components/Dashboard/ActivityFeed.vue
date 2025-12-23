<script setup>
const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    formatTimeAgo: {
        type: Function,
        required: true,
    },
});
</script>

<template>
    <div class="relative overflow-hidden rounded-[24px] border border-white/12 bg-white/6 p-6 backdrop-blur-2xl">
        <div class="absolute -top-24 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full bg-gradient-to-b from-indigo-500/25 via-purple-500/20 to-transparent blur-[150px]"></div>
        <div class="relative z-10 space-y-4">
            <header>
                <p class="text-xs uppercase tracking-[0.35em] text-indigo-200/80">Dòng chảy lớp học</p>
                <h3 class="mt-1 text-xl font-semibold text-white">Hoạt động gần đây</h3>
            </header>

            <ul v-if="props.items.length" class="space-y-3">
                <li
                    v-for="activity in props.items"
                    :key="activity.timestamp"
                    class="rounded-[18px] border border-white/10 bg-white/8 p-4"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-white">
                                {{ activity.user?.name || 'Hệ thống' }}
                            </p>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">
                                {{ activity.team?.name || 'Không xác định' }} • {{ activity.activity_type }}
                            </p>
                        </div>
                        <span class="text-xs text-slate-400">
                            {{ props.formatTimeAgo(activity.timestamp) }}
                        </span>
                    </div>
                </li>
            </ul>

            <p v-else class="rounded-[18px] border border-dashed border-white/12 bg-white/4 px-4 py-6 text-center text-sm text-slate-400">
                Chưa có hoạt động nào.
            </p>
        </div>
    </div>
</template>
