<script setup>
const props = defineProps({
    assignments: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="relative overflow-hidden rounded-[24px] border border-white/12 bg-white/6 p-6 backdrop-blur-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,113,133,0.2),transparent_55%)]"></div>
        <div class="relative z-10 space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-rose-200/80">Hành động ưu tiên</p>
                    <h3 class="mt-1 text-xl font-semibold text-white">Bài tập chờ chấm</h3>
                </div>
                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-rose-200">
                    {{ props.assignments.length }} mục
                </span>
            </header>

            <div v-if="props.assignments.length" class="space-y-3">
                <article
                    v-for="post in props.assignments"
                    :key="post.id"
                    class="relative overflow-hidden rounded-[18px] border border-white/12 bg-white/8 p-4 transition hover:border-rose-300/40 hover:bg-white/12"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-white">
                                <a :href="`/posts/${post.id}/submissions`" class="hover:text-rose-200">
                                    {{ post.title }}
                                </a>
                            </h4>
                            <p class="mt-1 text-xs uppercase tracking-[0.3em] text-slate-400">{{ post.team.name }}</p>
                        </div>
                        <span class="rounded-full border border-rose-200/50 bg-rose-500/15 px-3 py-1 text-[11px] font-semibold text-rose-200">
                            {{ post.ungraded_submissions_count }} bài
                        </span>
                    </div>
                </article>
            </div>

            <p v-else class="rounded-[18px] border border-dashed border-white/12 bg-white/4 px-4 py-6 text-center text-sm text-slate-400">
                Không có bài tập nào cần xử lý.
            </p>
        </div>
    </div>
</template>
