<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    show: Boolean,
    currentToken: String,
    currentQrUrl: String,
    joinedStudents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const timeLeft = ref(10);
let intervalId = null;

const startCountdown = () => {
    clearInterval(intervalId);
    timeLeft.value = 10;
    intervalId = setInterval(() => {
        timeLeft.value = (timeLeft.value || 10) - 1;
        if (timeLeft.value <= 0) {
            timeLeft.value = 10;
        }
    }, 1000);
};

watch(() => props.currentToken, () => {
    startCountdown();
});

watch(() => props.show, (value) => {
    if (!value) {
        clearInterval(intervalId);
    }
});

onMounted(() => {
    if (props.show) {
        startCountdown();
    }
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <transition name="fade">
        <div
            v-if="props.show"
            class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/80 backdrop-blur-md"
        >
            <div class="relative w-full max-w-3xl overflow-hidden rounded-[32px] border border-white/12 bg-white/8 p-8 backdrop-blur-2xl">
                <div class="absolute -top-16 -left-20 h-48 w-48 rounded-full bg-gradient-to-br from-indigo-500/25 via-purple-500/20 to-fuchsia-500/15 blur-[160px]"></div>
                <div class="absolute -bottom-16 -right-20 h-48 w-48 rounded-full bg-gradient-to-br from-sky-400/20 via-cyan-500/15 to-emerald-400/20 blur-[160px]"></div>
                <button
                    class="absolute right-6 top-6 rounded-full border border-white/10 bg-white/10 p-2 text-white transition hover:bg-white/20"
                    @click="emit('close')"
                >
                    ✕
                </button>

                <div class="relative z-10 grid gap-8 md:grid-cols-[1fr_1fr]">
                    <div class="space-y-4">
                        <p class="text-xs uppercase tracking-[0.35em] text-indigo-200/80">Attendance 4.0</p>
                        <h2 class="text-3xl font-bold text-white">Quét mã để điểm danh</h2>
                        <p class="text-sm leading-relaxed text-slate-300/90">
                            Yêu cầu học sinh mở ứng dụng SmartClass và quét mã QR dưới đây hoặc nhập token thủ công.
                        </p>

                        <div class="rounded-[20px] border border-white/12 bg-white/15 p-4">
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Mã token</p>
                            <p class="mt-2 text-4xl font-mono font-bold tracking-[0.4em] text-white">
                                {{ props.currentToken || '------' }}
                            </p>
                            <p class="mt-2 flex items-center gap-2 text-xs text-rose-200">
                                <span class="h-6 w-6 rounded-full bg-rose-500/20 text-center text-rose-200">⏱</span>
                                Token đổi sau {{ timeLeft }} giây
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="overflow-hidden rounded-[24px] border border-white/12 bg-slate-950/80 p-6 shadow-[0_30px_70px_-50px_rgba(129,140,248,0.7)]">
                            <QrcodeVue :value="props.currentQrUrl" :size="240" level="H" />
                        </div>

                        <div class="max-h-40 overflow-y-auto rounded-[20px] border border-white/12 bg-white/10 p-3">
                            <div class="flex items-center justify-between border-b border-white/10 pb-2 text-xs uppercase tracking-[0.3em] text-slate-400">
                                <span>Danh sách đã vào</span>
                                <span>{{ props.joinedStudents.length }} học sinh</span>
                            </div>
                            <ul class="mt-2 space-y-2 text-sm text-slate-200">
                                <transition-group name="slide-fade">
                                    <li
                                        v-for="student in props.joinedStudents"
                                        :key="student.id"
                                        class="flex items-center gap-3 rounded-[14px] border border-white/12 bg-white/6 px-3 py-2"
                                    >
                                        <img
                                            :src="student.profile_photo_url"
                                            alt="profile"
                                            class="h-8 w-8 rounded-full border border-white/20"
                                        />
                                        <span class="font-medium">{{ student.name }}</span>
                                        <span class="ml-auto text-xs text-emerald-300">✔ Có mặt</span>
                                    </li>
                                </transition-group>
                                <li
                                    v-if="props.joinedStudents.length === 0"
                                    class="text-center text-xs text-slate-400"
                                >
                                    Chưa có học sinh nào điểm danh
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
