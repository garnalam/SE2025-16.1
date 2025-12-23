<script setup>
import { ref, computed } from 'vue';
import { BrainCircuit, Check, PenTool, Sparkles, User, Users } from 'lucide-vue-next';

const tabs = [
    {
        key: 'teacher',
        label: 'Giáo viên',
        description: 'Quản lý lớp học với AI',
        accent: 'from-indigo-500 via-purple-500 to-fuchsia-500',
    },
    {
        key: 'student',
        label: 'Học sinh',
        description: 'Trải nghiệm học tập thông minh',
        accent: 'from-sky-400 via-cyan-500 to-emerald-400',
    },
];

const activeTab = ref('teacher');
const attendance = ref(Array.from({ length: 5 }, () => Array.from({ length: 5 }, () => Math.random() > 0.2)));

const presentTotal = computed(() => attendance.value.flat().filter(Boolean).length);
const absentTotal = computed(() => 25 - presentTotal.value);

const toggleCell = (rowIndex, colIndex) => {
    attendance.value[rowIndex][colIndex] = !attendance.value[rowIndex][colIndex];
};

const aiMessages = ref([
    { role: 'user', text: 'Tạo 5 câu hỏi về lịch sử Việt Nam cho lớp 10.' },
    {
        role: 'assistant',
        text: '1. Trình bày những điểm nổi bật của cuộc Cách mạng tháng Tám 1945.\n2. Giải thích vì sao chiến thắng Điện Biên Phủ được coi là “chấn động địa cầu”.\n3. Phân tích vai trò của phong trào Đấu tranh ngoại giao trong kháng chiến chống Pháp.\n4. Nêu những điểm khác biệt giữa Hiệp định Giơ-ne-vơ 1954 và Hiệp định Pari 1973.\n5. Giải thích chính sách Đổi mới 1986 đã tác động như thế nào đến phát triển kinh tế - xã hội Việt Nam.',
    },
]);
</script>

<template>
    <section class="relative py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl space-y-4">
                    <p class="text-xs uppercase tracking-[0.45em] text-slate-500">Role-based Simulation</p>
                    <h2 class="text-4xl font-bold text-white md:text-5xl">
                      Các chức năng mô phỏng theo vai trò
                    </h2>
                    <p class="text-base text-slate-300/90">
                        Khám phá các tính năng độc đáo của Smart Classroom thông qua mô phỏng tương tác dành riêng cho giáo viên và học sinh.
                    </p>
                </div>

                <div class="flex w-full gap-3 rounded-2xl border border-white/10 bg-white/6 p-3 backdrop-blur-xl md:w-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="flex-1 rounded-xl px-6 py-4 text-left transition md:flex-none"
                        :class="[
                            activeTab === tab.key
                                ? `bg-gradient-to-r ${tab.accent} text-white shadow-[0_0_35px_-15px_rgba(168,85,247,0.9)]`
                                : 'text-slate-400 hover:text-white hover:bg-white/10'
                        ]"
                        @click="activeTab = tab.key"
                    >
                        <span class="block text-xs uppercase tracking-[0.35em]">{{ tab.label }}</span>
                        <span class="text-sm font-medium normal-case text-white/90">{{ tab.description }}</span>
                    </button>
                </div>
            </header>

            <div class="mt-12 grid gap-10 lg:grid-cols-2">
                <template v-if="activeTab === 'teacher'">
                    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/6 p-8 backdrop-blur-2xl shadow-[0_45px_120px_-35px_rgba(129,140,248,0.6)]">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-fuchsia-500/15 blur-3xl opacity-70"></div>
                        <div class="relative z-10 space-y-8">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-indigo-400/30 bg-indigo-500/20">
                                    <Users class="h-6 w-6 text-indigo-200" />
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-indigo-100/70">Ma trận điểm danh</p>
                                    <h3 class="text-2xl font-semibold text-white">Giải phóng sức lao động</h3>
                                </div>
                            </div>

                            <div class="grid grid-cols-5 gap-2">
                                <template v-for="(row, rowIndex) in attendance" :key="`row-${rowIndex}`">
                                    <button
                                        v-for="(cell, colIndex) in row"
                                        :key="`cell-${rowIndex}-${colIndex}`"
                                        type="button"
                                        class="relative flex aspect-square flex-col items-center justify-center gap-2 rounded-2xl border border-white/10 transition"
                                        :class="cell ? 'bg-emerald-500/20 text-emerald-200 shadow-[0_0_35px_-15px_rgba(16,185,129,0.8)]' : 'bg-rose-500/15 text-rose-200/80'"
                                        @click="toggleCell(rowIndex, colIndex)"
                                    >
                                        <div class="h-8 w-8 rounded-full bg-slate-900/50 border border-white/10"></div>
                                        <span class="text-[0.65rem] uppercase tracking-[0.4em]">{{ cell ? 'Có mặt' : 'Vắng' }}</span>
                                    </button>
                                </template>
                            </div>

                            <div class="grid grid-cols-3 gap-4 text-sm text-white/80">
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4">
                                    <p class="text-xs uppercase tracking-[0.35em] text-emerald-200/80">Có mặt</p>
                                    <p class="mt-2 text-2xl font-bold">{{ presentTotal }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4">
                                    <p class="text-xs uppercase tracking-[0.35em] text-rose-200/80">Vắng mặt</p>
                                    <p class="mt-2 text-2xl font-bold">{{ absentTotal }}</p>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/8 p-4">
                                    <Sparkles class="h-10 w-10 text-fuchsia-300" />
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-200/80">AI Monitor</p>
                                        <p class="text-sm">Gợi ý hành động tức thời</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/8 p-8 backdrop-blur-2xl shadow-[0_45px_120px_-35px_rgba(217,70,239,0.5)]">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(129,140,248,0.35),transparent_60%)]"></div>
                        <div class="relative z-10 space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-fuchsia-400/30 bg-fuchsia-500/20">
                                    <BrainCircuit class="h-6 w-6 text-fuchsia-200" />
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-fuchsia-200/80">AI Soạn đề</p>
                                    <h3 class="text-2xl font-semibold text-white">Chat với trợ lý sư phạm</h3>
                                </div>
                            </div>

                            <div class="space-y-4 rounded-3xl border border-white/10 bg-slate-900/60 p-6 backdrop-blur-xl">
                                <div
                                    v-for="(msg, index) in aiMessages"
                                    :key="index"
                                    class="flex"
                                    :class="msg.role === 'user' ? 'justify-end text-right' : 'justify-start text-left'"
                                >
                                    <div
                                        class="max-w-[80%] rounded-2xl px-4 py-3 text-sm leading-relaxed whitespace-pre-line"
                                        :class="msg.role === 'user'
                                            ? 'bg-gradient-to-r from-indigo-500/60 to-purple-500/60 text-white border border-white/20'
                                            : 'bg-white/10 border border-white/10 text-slate-200'"
                                    >
                                        {{ msg.text }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-500">
                                    <PenTool class="h-4 w-4 text-fuchsia-200" />
                                    <span>AI đang tối ưu câu hỏi theo cấp độ lớp học...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/6 p-8 backdrop-blur-2xl shadow-[0_45px_120px_-35px_rgba(56,189,248,0.6)]">
                        <div class="absolute inset-0 bg-gradient-to-br from-sky-400/25 via-cyan-500/20 to-emerald-400/15 blur-3xl opacity-70"></div>
                        <div class="relative z-10 space-y-8">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-cyan-400/30 bg-cyan-500/20">
                                    <User class="h-6 w-6 text-cyan-100" />
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-cyan-200/80">Học sinh chủ động</p>
                                    <h3 class="text-2xl font-semibold text-white">Khung trải nghiệm di động</h3>
                                </div>
                            </div>

                            <div class="mx-auto w-72 rounded-[2.5rem] border border-white/10 bg-gradient-to-b from-slate-900/90 to-slate-950 p-3 shadow-[0_25px_120px_-40px_rgba(56,189,248,0.8)]">
                                <div class="mx-auto mb-4 h-8 w-24 rounded-full bg-black/60"></div>
                                <div class="overflow-hidden rounded-[2rem] border border-white/12 bg-slate-950/95">
                                    <header class="flex h-14 items-center justify-between border-b border-white/10 bg-white/5 px-5">
                                        <div class="text-xs uppercase tracking-[0.35em] text-slate-400">Class Feed</div>
                                        <span class="text-sky-200/80">⚡</span>
                                    </header>
                                    <div class="space-y-4 p-4">
                                        <article class="space-y-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                            <header class="flex items-center gap-3">
                                                <span class="h-8 w-8 rounded-full bg-sky-500"></span>
                                                <div>
                                                    <p class="text-xs text-slate-400">Giáo viên</p>
                                                    <p class="text-sm font-semibold text-white">Thông báo bài tập mới</p>
                                                </div>
                                            </header>
                                            <p class="text-xs leading-relaxed text-slate-300/90">
                                                Tham gia quiz "AI in Education" trước 21:00 tối nay. Nhớ điểm danh bằng QR ngay tại lớp nhé!
                                            </p>
                                            <footer class="flex items-center justify-between text-[11px] text-slate-500">
                                                <div class="flex gap-3">
                                                    <span>💬 12</span>
                                                    <span>❤️ 45</span>
                                                </div>
                                                <span>20 phút trước</span>
                                            </footer>
                                        </article>

                                        <div class="flex items-center gap-4 rounded-2xl border border-cyan-400/25 bg-gradient-to-r from-cyan-500/15 to-emerald-400/15 p-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-xl">🔍</div>
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.35em] text-cyan-200/80">Điểm danh QR</p>
                                                <p class="text-sm text-white">Quét mã tức thì, tự động lưu lịch sử chuyên cần.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/8 p-8 backdrop-blur-2xl shadow-[0_45px_120px_-35px_rgba(56,189,248,0.6)]">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(14,165,233,0.3),transparent_65%)]"></div>
                        <div class="relative z-10 space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-400/30 bg-emerald-500/20">
                                    <Check class="h-6 w-6 text-emerald-200" />
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-emerald-200/80">Năng động & tin cậy</p>
                                    <h3 class="text-2xl font-semibold text-white">Học tập chủ động hơn</h3>
                                </div>
                            </div>

                            <ul class="space-y-4 text-sm text-slate-200">
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span>Nhận thông báo nhiệm vụ tức thời và phản hồi bằng biểu cảm sinh động.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span>Ứng dụng điểm danh QR thân thiện di động, lưu vết chuyên cần rõ ràng.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span>AI nhắc nhở deadline và gợi ý tài liệu học tập phù hợp.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>
