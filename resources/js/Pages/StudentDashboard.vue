<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref, onMounted } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale, RadialLinearScale, Filler } from 'chart.js';

// --- CHART REGISTRATION ---
ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, PointElement, ArcElement, CategoryScale, LinearScale, RadialLinearScale, Filler);

const { props } = usePage();

// --- DEFINED PROPS (Dữ liệu thật từ Backend) ---
defineProps({
    taskCompletionData: Object,   // Dữ liệu biểu đồ tròn: Trạng thái bài tập
    performanceChartData: Object, // Dữ liệu biểu đồ đường: Lịch sử điểm số
    quizAnalyticsData: Object,    // Dữ liệu biểu đồ cột: So sánh điểm Quiz
    upcomingAssignments: Array,   // Danh sách bài tập sắp tới
    latestAnnouncements: Array    // Danh sách thông báo
});

// --- STATE ---
const activeTab = ref('priority'); // 'priority' | 'all'
const attendanceCode = ref('');
const isSubmittingCode = ref(false);
const analyticsTimeframe = ref('ALL'); // Mặc định ALL vì backend đang lấy 10 bài gần nhất

// --- CHART CONFIGURATION (Style chung) ---
const commonChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#fff',
            bodyColor: '#cbd5e1',
            borderColor: 'rgba(56, 189, 248, 0.3)', // Viền xanh Cyan
            borderWidth: 1,
            padding: 10,
            displayColors: true,
            titleFont: { family: "'Exo 2', sans-serif" },
            bodyFont: { family: "'JetBrains Mono', monospace" }
        }
    },
    scales: {
        y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#64748b', font: { family: "'JetBrains Mono', monospace", size: 10 } },
            border: { display: false },
            beginAtZero: true,
            max: 100 // Quy đổi % nên max là 100
        },
        x: {
            grid: { display: false },
            ticks: { color: '#64748b', font: { family: "'JetBrains Mono', monospace", size: 10 } },
            border: { display: false }
        }
    }
};

// --- LOGIC ---
const submitAttendance = () => {
    if (!attendanceCode.value || attendanceCode.value.length < 6) return;
    
    isSubmittingCode.value = true;
    router.post(route('attendance.join-code'), {
        code: attendanceCode.value
    }, {
        onSuccess: () => {
            attendanceCode.value = '';
        },
        onFinish: () => isSubmittingCode.value = false
    });
};

const getDueDateStatus = (dateString) => {
    if (!dateString) return { text: 'No Deadline', color: 'slate', urgent: false };
    const date = new Date(dateString);
    const now = new Date();
    const diff = date - now;
    
    if (diff < 0) return { text: 'EXPIRED', color: 'rose', urgent: false };
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    if (days > 2) return { text: `${days} DAYS LEFT`, color: 'emerald', urgent: false };
    if (days >= 1) return { text: `${days} DAYS LEFT`, color: 'amber', urgent: true };
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    if (hours > 1) return { text: `${hours} HOURS LEFT`, color: 'orange', urgent: true };
    
    const minutes = Math.floor(diff / (1000 * 60));
    return { text: `${minutes} MIN LEFT`, color: 'rose', urgent: true }; // Critical
};

const formatTimeAgo = (dateString) => {
    if (!dateString) return 'Unknown';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    if (seconds < 60) return 'Vừa xong';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m trước`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h trước`;
    return `${Math.floor(hours / 24)}d trước`;
};

const truncate = (text, length = 100) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

// Sắp xếp bài tập
const processedAssignments = computed(() => {
    // Sử dụng prop upcomingAssignments từ backend
    if (!props.upcomingAssignments) return [];
    return props.upcomingAssignments.map(post => {
        const dueDate = new Date(post.due_date);
        const now = new Date();
        const diff = dueDate - now;
        return { ...post, diff, status: getDueDateStatus(post.due_date) };
    }).sort((a, b) => a.diff - b.diff);
});

// Lọc bài tập ưu tiên (Sắp hết hạn hoặc hết hạn gần đây)
const priorityAssignments = computed(() => {
    return processedAssignments.value.filter(a => a.status.urgent || a.diff < 0).slice(0, 4);
});

const nextMission = computed(() => {
    return processedAssignments.value.find(a => a.diff > 0);
});

onMounted(() => {
    // Tự động refresh dữ liệu dashboard mỗi 60s
    const interval = setInterval(() => {
        router.reload({ only: ['latestAnnouncements', 'upcomingAssignments', 'taskCompletionData', 'performanceChartData', 'quizAnalyticsData'] });
    }, 60000);
    return () => clearInterval(interval);
});
</script>

<template>
    <AppLayout title="Bảng Điều Khiển Học Sinh">
        <div class="max-w-[1600px] mx-auto p-4 md:p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                <div class="md:col-span-3 bg-[#0f172a] border border-slate-800 rounded-3xl p-5 shadow-lg relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <img :src="$page.props.auth.user.profile_photo_url" class="w-14 h-14 rounded-xl border-2 border-slate-700 object-cover">
                            <div class="absolute -bottom-1 -right-1 bg-black rounded-full border border-slate-600 px-1.5 py-0.5">
                                <span class="text-[9px] font-black text-white block leading-none">L{{ $page.props.auth.user.level }}</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-white font-bold font-exo truncate">{{ $page.props.auth.user.name }}</h3>
                            <div class="mt-1">
                                <div class="flex justify-between text-[9px] text-slate-400 font-mono mb-1">
                                    <span>XP PROGRESS</span>
                                    <span>{{ $page.props.auth.user.xp }} / {{ $page.props.auth.user.level * 1000 }}</span>
                                </div>
                                <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500" :style="{ width: $page.props.auth.user.xp_progress + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-6 relative">
                    <div class="absolute inset-0 bg-emerald-500/5 rounded-3xl blur-xl"></div>
                    <div class="relative bg-slate-900 border border-emerald-500/30 rounded-3xl p-1 flex items-center shadow-[0_0_30px_rgba(16,185,129,0.1)]">
                        <div class="flex-shrink-0 w-16 h-full flex items-center justify-center border-r border-slate-800 bg-black/20 rounded-l-2xl">
                            <svg class="w-8 h-8 text-emerald-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        </div>
                        
                        <form @submit.prevent="submitAttendance" class="flex-1 flex items-center p-2 gap-3">
                            <div class="flex-1">
                                <label class="block text-[9px] text-emerald-500/70 font-mono uppercase tracking-widest mb-1 pl-2">Nhập mã điểm danh
</label>
                                <input 
                                    v-model="attendanceCode" 
                                    type="text" 
                                    placeholder="XXXXXX" 
                                    maxlength="6"
                                    class="w-full bg-transparent border-none text-2xl font-black font-mono text-white placeholder-slate-700 focus:ring-0 p-0 pl-2 tracking-[0.5em] uppercase"
                                >
                            </div>
                            <button 
                                :disabled="isSubmittingCode || attendanceCode.length < 6"
                                class="h-12 px-6 bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-800 disabled:text-slate-600 text-white font-bold rounded-xl transition-all shadow-lg uppercase tracking-wider text-xs flex items-center gap-2"
                            >
                                <span v-if="isSubmittingCode">Verifying...</span>
                                <span v-else>Connect</span>
                                <svg v-if="!isSubmittingCode" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="md:col-span-3 bg-[#0f172a] border border-slate-800 rounded-3xl p-5 relative overflow-hidden flex flex-col justify-center">
                    <div v-if="nextMission">
                        <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-ping"></span>
                            Next Deadline
                        </div>
                        <div class="text-sm font-bold text-white truncate font-exo">{{ nextMission.title }}</div>
                        <div class="text-[10px] text-slate-400 font-mono mt-1">{{ nextMission.status.text }}</div>
                        <div class="absolute bottom-0 left-0 h-1 bg-rose-500" :style="{ width: '100%' }"></div>
                    </div>
                    <div v-else class="text-center">
                        <span class="text-emerald-500 text-xs font-mono font-bold uppercase">Thông báo công việc</span>
                        <p class="text-[9px] text-slate-500 mt-1">Hiện không có deadlines.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:h-[550px] min-h-[550px]">
                
                <div class="lg:col-span-2 bg-slate-900/50 border border-slate-800 rounded-3xl flex flex-col overflow-hidden backdrop-blur-sm shadow-xl">
                    
                    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
                        <h3 class="text-sm font-bold text-white font-exo uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Công việc sắp tới
                        </h3>
                        
                        <div class="flex bg-black/40 rounded-lg p-1">
                            <button 
                                @click="activeTab = 'priority'" 
                                class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all"
                                :class="activeTab === 'priority' ? 'bg-slate-700 text-white shadow' : 'text-slate-500 hover:text-slate-300'"
                            >
                                Ưu tiên ({{ priorityAssignments.length }})
                            </button>
                            <button 
                                @click="activeTab = 'all'" 
                                class="px-4 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all"
                                :class="activeTab === 'all' ? 'bg-slate-700 text-white shadow' : 'text-slate-500 hover:text-slate-300'"
                            >
                                Toàn bộ 
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-[#0b1121]">
                        
                        <div v-if="activeTab === 'priority'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="priorityAssignments.length === 0" class="col-span-full h-full flex flex-col items-center justify-center text-slate-500 py-12">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0 1 18 0z" /></svg>
                                <p class="text-xs font-mono">Hiện không có công việc cần hoàn thiện</p>
                            </div>

                            <Link v-for="post in priorityAssignments" :key="post.id" 
                                :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })"
                                class="group relative bg-slate-900 border border-slate-800 hover:border-rose-500/50 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-900/20"
                            >
                                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-500 to-amber-500 rounded-t-2xl"></div>
                                
                                <div class="flex justify-between items-start mb-3">
                                    <span class="px-2 py-1 rounded bg-slate-800 text-[9px] font-bold text-slate-400 border border-slate-700 group-hover:border-rose-500/30 group-hover:text-rose-400 transition">{{ post.team?.name || 'Class' }}</span>
                                    <span class="text-[10px] font-black font-mono" :class="post.status.color === 'rose' ? 'text-rose-500' : 'text-amber-500'">{{ post.status.text }}</span>
                                </div>
                                
                                <h4 class="text-sm font-bold text-white font-exo mb-2 line-clamp-2 group-hover:text-rose-200 transition">{{ post.title }}</h4>
                                
                                <div class="flex items-center justify-between mt-4 border-t border-slate-800 pt-3">
                                    <span class="text-[10px] text-slate-500 font-mono">PTS: {{ post.max_points }}</span>
                                    <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center group-hover:bg-rose-600 transition">
                                        <svg class="w-3 h-3 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="space-y-2">
                            <div v-for="post in processedAssignments" :key="post.id" 
                                class="flex items-center justify-between p-3 bg-slate-900/50 border border-slate-800 hover:border-cyan-500/30 rounded-xl group transition hover:bg-slate-900"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500 group-hover:text-cyan-400 group-hover:bg-cyan-900/20 transition">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    </div>
                                    <div>
                                        <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" class="text-sm font-bold text-slate-200 group-hover:text-white font-exo block">
                                            {{ post.title }}
                                        </Link>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ post.team?.name || 'Unknown Class' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold font-mono" :class="`text-${post.status.color}-500`">{{ post.status.text }}</div>
                                    <div class="text-[9px] text-slate-600">{{ new Date(post.due_date).toLocaleDateString() }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="lg:col-span-1 bg-[#0f172a] border border-slate-800 rounded-3xl flex flex-col overflow-hidden shadow-xl">
                    <div class="px-5 py-4 border-b border-slate-800 bg-slate-900/80 backdrop-blur">
                        <h3 class="text-xs font-bold text-slate-400 font-mono uppercase tracking-[0.2em]">Thông báo của giáo viên</h3>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-0 relative custom-scrollbar">
                        
                        <div v-if="latestAnnouncements && latestAnnouncements.length > 0" class="py-6 px-4">
                            <div v-for="post in latestAnnouncements" :key="post.id" class="relative pl-10 mb-8 group last:mb-0">
                                <div class="absolute left-[19px] top-8 bottom-[-32px] w-px bg-slate-800 group-last:hidden"></div>

                                <div class="absolute left-0 top-0">
                                    <img :src="post.user?.profile_photo_url || 'https://ui-avatars.com/api/?name=System&background=334155&color=fff'" class="w-10 h-10 rounded-lg border border-indigo-500/50 shadow-[0_0_15px_rgba(99,102,241,0.2)] object-cover bg-slate-900">
                                </div>
                                
                                <div class="flex flex-col">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-white font-exo">{{ post.user?.name || 'System Command' }}</span>
                                            <span class="text-[9px] font-bold text-indigo-400 font-mono">{{ post.team?.name || 'General' }}</span>
                                        </div>
                                        <span class="text-[9px] text-slate-600 font-mono bg-slate-900 px-1 rounded">{{ formatTimeAgo(post.created_at) }}</span>
                                    </div>
                                    
                                    <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" 
                                          class="block text-xs text-slate-300 leading-relaxed hover:text-white transition bg-slate-900/60 p-3 rounded-r-xl rounded-bl-xl border border-slate-800 hover:border-indigo-500/30 relative group-hover:shadow-lg group-hover:shadow-indigo-900/10">
                                        <div class="absolute top-0 left-[-6px] w-0 h-0 border-t-[6px] border-t-slate-900/60 border-l-[6px] border-l-transparent"></div>
                                        {{ truncate(post.content, 120) }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="h-full flex flex-col items-center justify-center text-slate-600 text-xs font-mono space-y-2 opacity-50">
                            <div class="w-12 h-12 border border-dashed border-slate-700 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <span>// FREQUENCY SILENT</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <div class="lg:col-span-2 bg-slate-900/40 border border-slate-800 rounded-3xl p-6 relative overflow-hidden flex flex-col shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-sm font-bold text-white font-exo uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                Lịch sử nộp bài 
                            </h3>
                            <p class="text-[10px] text-slate-500 font-mono mt-1">Tỷ lệ % nộp bài của bạn</p>
                        </div>
                    </div>
                    <div class="flex-1 w-full min-h-[200px]">
                        <Line v-if="performanceChartData && performanceChartData.datasets[0].data.length > 0" 
                              :data="performanceChartData" :options="commonChartOptions" />
                        
                        <div v-else class="h-full flex items-center justify-center text-slate-600 text-xs font-mono">
                            Không có dữ liệu lịch sử nộp bài
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-slate-900/40 border border-slate-800 rounded-3xl p-6 relative overflow-hidden flex flex-col shadow-lg">
                    <h3 class="text-sm font-bold text-white font-exo uppercase tracking-widest mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                        Trạng thái bài tập
                    </h3>
                    <div class="flex-1 relative flex items-center justify-center min-h-[180px]">
                        <Doughnut v-if="taskCompletionData" 
                                  :data="taskCompletionData" 
                                  :options="{ ...commonChartOptions, cutout: '75%' }" />
                        
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-black text-white font-exo">
                                {{ 
                                    (taskCompletionData && taskCompletionData.datasets[0].data.reduce((a, b) => a + b, 0)) > 0 
                                    ? Math.round((taskCompletionData.datasets[0].data[0] / taskCompletionData.datasets[0].data.reduce((a, b) => a + b, 0)) * 100)
                                    : 0
                                }}%
                            </span>
                            <span class="text-[8px] text-slate-500 uppercase tracking-widest">Done</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center" v-if="taskCompletionData">
                        <div class="bg-emerald-500/10 rounded p-1 border border-emerald-500/20">
                            <div class="text-[10px] text-emerald-400 font-bold">{{ taskCompletionData.datasets[0].data[0] }}</div>
                            <div class="text-[8px] text-slate-500 uppercase">Done</div>
                        </div>
                        <div class="bg-blue-500/10 rounded p-1 border border-blue-500/20">
                            <div class="text-[10px] text-blue-400 font-bold">{{ taskCompletionData.datasets[0].data[1] }}</div>
                            <div class="text-[8px] text-slate-500 uppercase">Wait</div>
                        </div>
                        <div class="bg-rose-500/10 rounded p-1 border border-rose-500/20">
                            <div class="text-[10px] text-rose-400 font-bold">{{ taskCompletionData.datasets[0].data[2] }}</div>
                            <div class="text-[8px] text-slate-500 uppercase">Late</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-slate-900/40 border border-slate-800 rounded-3xl p-6 relative overflow-hidden flex flex-col shadow-lg">
                    <h3 class="text-sm font-bold text-white font-exo uppercase tracking-widest mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        So với lớp học
                    </h3>
                    <div class="flex-1 w-full min-h-[180px]">
                        <Bar v-if="quizAnalyticsData && quizAnalyticsData.labels.length > 0" 
                             :data="quizAnalyticsData" :options="commonChartOptions" />
                        
                        <div v-else class="h-full flex items-center justify-center text-slate-600 text-xs font-mono">
                            Không có dữ liệu Quiz
                        </div>
                    </div>
                    
                    <div class="mt-4 p-2 bg-slate-800/50 rounded-lg border border-slate-700 flex justify-between items-center" 
                         v-if="quizAnalyticsData && quizAnalyticsData.datasets[0].data.length > 0">
                        <span class="text-[9px] text-slate-400 uppercase tracking-widest">My Recent Avg</span>
                        <span class="text-sm font-bold text-cyan-400 font-mono">
                            {{ Math.round(quizAnalyticsData.datasets[0].data.reduce((a,b)=>a+b,0) / quizAnalyticsData.datasets[0].data.length) }}%
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Reuse custom scan animation */
@keyframes scan {
    0% { background-position: 0 0; }
    100% { background-position: 0 100px; }
}
.animate-scan {
    animation: scan 4s linear infinite;
}
</style>