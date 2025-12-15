<script setup>
const props = defineProps({
    ownedTeams: {
        type: Array,
        default: () => [],
    },
    startAttendance: {
        type: Function,
        required: true,
    },
});
</script>

<template>
    <div v-if="props.ownedTeams.length > 0" class="relative overflow-hidden rounded-[24px] border border-white/12 bg-white/6 p-6 backdrop-blur-2xl shadow-[0_28px_90px_-45px_rgba(79,70,229,0.55)]">
        <div class="absolute -top-10 right-0 h-32 w-32 rounded-full bg-gradient-to-br from-sky-400/25 via-cyan-400/15 to-transparent blur-[100px]"></div>
        <div class="relative z-10 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Attendance 4.0</p>
                    <h3 class="mt-1 text-xl font-semibold text-white">Điểm danh nhanh</h3>
                    <p class="mt-2 text-sm text-slate-300/85">
                        Chọn lớp để mở phiên điểm danh bằng QR và token động.
                    </p>
                </div>
                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-200">
                    {{ props.ownedTeams.length }} lớp
                </span>
            </div>

            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                <button
                    v-for="team in props.ownedTeams"
                    :key="team.id"
                    @click="props.startAttendance(team.id)"
                    class="group relative overflow-hidden rounded-[18px] border border-white/12 bg-white/8 px-4 py-3 text-left text-sm font-semibold text-white transition hover:border-indigo-400/40 hover:bg-white/15"
                >
                    <div class="absolute inset-0 translate-x-[-110%] bg-gradient-to-r from-indigo-500/25 via-purple-500/20 to-transparent skew-x-[22deg] transition duration-700 group-hover:translate-x-[120%]"></div>
                    <div class="relative z-10 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500/35 via-purple-500/35 to-fuchsia-500/25 text-xs font-bold">
                            {{ team.name.slice(0, 2).toUpperCase() }}
                        </span>
                        <div>
                            <p>{{ team.name }}</p>
                            <p class="text-[11px] uppercase tracking-[0.3em] text-slate-400">Bắt đầu</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
