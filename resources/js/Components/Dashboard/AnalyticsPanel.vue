<script setup>
import { Bar } from 'vue-chartjs';

const props = defineProps({
    selectedTeamId: Number,
    ownedTeams: {
        type: Array,
        default: () => [],
    },
    analyticsData: Object,
    chartOptions: Object,
    isLoading: Boolean,
    analyticsError: String,
});

const emit = defineEmits(['update:selectedTeamId']);

const onSelectTeam = (event) => {
    emit('update:selectedTeamId', Number(event.target.value));
};
</script>

<template>
    <div class="relative overflow-hidden rounded-[24px] border border-white/12 bg-white/6 p-6 backdrop-blur-2xl">
        <div class="absolute -bottom-28 right-0 h-48 w-48 rounded-full bg-gradient-to-tr from-sky-500/20 via-cyan-500/15 to-transparent blur-[140px]"></div>
        <div class="relative z-10 space-y-4">
            <header class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-cyan-200/80">Analytics</p>
                    <h3 class="mt-1 text-xl font-semibold text-white">Phân tích lớp học</h3>
                </div>
                <select
                    class="min-w-[200px] rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-white focus:border-cyan-400 focus:outline-none"
                    :value="props.selectedTeamId"
                    @change="onSelectTeam"
                >
                    <option disabled value="">Chọn lớp</option>
                    <option
                        v-for="team in props.ownedTeams"
                        :key="team.id"
                        :value="team.id"
                        class="bg-slate-950 text-white"
                    >
                        {{ team.name }}
                    </option>
                </select>
            </header>

            <div class="relative h-64 overflow-hidden rounded-[18px] border border-white/12 bg-slate-950/60">
                <div v-if="props.isLoading" class="flex h-full items-center justify-center text-sm text-slate-400">
                    Đang tải biểu đồ...
                </div>
                <div v-else-if="props.analyticsError" class="flex h-full items-center justify-center text-sm text-rose-300">
                    {{ props.analyticsError }}
                </div>
                <Bar
                    v-else-if="props.analyticsData?.chartGradeDistribution"
                    :data="props.analyticsData.chartGradeDistribution"
                    :options="props.chartOptions"
                />
                <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
                    Chọn lớp để xem biểu đồ.
                </div>
            </div>
        </div>
    </div>
</template>
