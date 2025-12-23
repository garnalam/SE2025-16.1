<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onUnmounted, onMounted } from 'vue';
import axios from 'axios';
import QrcodeVue from 'qrcode.vue';
import Modal from '@/Components/Modal.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, defaults } from 'chart.js';

// --- CONFIG CHART ---
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);
defaults.font.family = "'JetBrains Mono', monospace";
defaults.color = '#64748b';
defaults.borderColor = 'rgba(255,255,255,0.05)';

const props = defineProps({
    stats: Object,
    priorityActions: Object,
    activityFeed: Array,
    ownedTeams: Array,
});

// --- TIME & DATE ---
const currentTime = ref('');
const currentDate = ref('');
const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('vi-VN', { hour12: false, hour: '2-digit', minute: '2-digit' });
    currentDate.value = now.toLocaleDateString('vi-VN', { weekday: 'short', month: 'short', day: 'numeric' });
};
let timer;
onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 60000);
});
onUnmounted(() => clearInterval(timer));

// --- UTILS ---
const formatTimeAgo = (timestamp) => {
    const now = new Date();
    const past = new Date(timestamp);
    const seconds = Math.floor((now - past) / 1000);
    if (seconds < 60) return `${seconds}s`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h`;
    return `${Math.floor(hours / 24)}d`;
};

// --- ANALYTICS ---
const selectedTeamId = ref(props.ownedTeams[0]?.id || null);
const analyticsData = ref(null);
const isLoadingAnalytics = ref(false);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleColor: '#22d3ee',
            bodyColor: '#e2e8f0',
            borderColor: '#22d3ee',
            borderWidth: 1,
            padding: 10,
            displayColors: false,
            titleFont: { family: "'Exo 2', sans-serif" }
        }
    },
    scales: {
        y: { 
            ticks: { color: '#475569', font: {size: 10} },
            grid: { color: 'rgba(255, 255, 255, 0.02)' },
            border: { display: false }
        },
        x: {
            ticks: { color: '#64748b', font: {size: 10} },
            grid: { display: false },
            border: { display: false }
        }
    }
};

const fetchAnalytics = async (teamId) => {
    if (!teamId) { analyticsData.value = null; return; }
    isLoadingAnalytics.value = true;
    try {
        const response = await axios.get(`/analytics/class/${teamId}`);
        if(response.data.chartGradeDistribution?.datasets) {
            const ctx = document.createElement('canvas').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, '#06b6d4'); // Cyan 500
            gradient.addColorStop(1, 'rgba(6, 182, 212, 0.05)');
            
            response.data.chartGradeDistribution.datasets[0].backgroundColor = gradient;
            response.data.chartGradeDistribution.datasets[0].borderColor = '#06b6d4';
            response.data.chartGradeDistribution.datasets[0].borderWidth = 1;
            response.data.chartGradeDistribution.datasets[0].borderRadius = 4;
        }
        analyticsData.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoadingAnalytics.value = false;
    }
};

watch(selectedTeamId, (newId) => { fetchAnalytics(newId); }, { immediate: true });

// --- ATTENDANCE ---
const showAttendanceModal = ref(false);
const currentSessionId = ref(null);
const currentToken = ref('...');
const currentQrUrl = ref('');
const joinedStudents = ref([]);
const refreshInterval = ref(null);
const activeTeamName = ref('');

const startAttendance = async (teamId) => {
    try {
        const team = props.ownedTeams.find(t => t.id === teamId);
        activeTeamName.value = team ? team.name : 'Unknown';
        
        const response = await axios.post(route('attendance.create', teamId));
        currentSessionId.value = response.data.session_id;
        currentToken.value = response.data.token;
        updateQrUrl();
        showAttendanceModal.value = true;
        refreshInterval.value = setInterval(refreshCode, 10000);
        
        if (window.Echo) {
             window.Echo.private(`attendance.${currentSessionId.value}`)
            .listen('StudentAttended', (e) => {
                joinedStudents.value.unshift(e.student);
            });
        }
    } catch (error) {
        alert("System Malfunction: Cannot start session.");
    }
};

const refreshCode = async () => {
    if (!currentSessionId.value) return;
    try {
        const res = await axios.post(route('attendance.refresh', currentSessionId.value));
        currentToken.value = res.data.token;
        updateQrUrl();
    } catch (e) { console.error(e); }
};

const updateQrUrl = () => {
    currentQrUrl.value = `${window.location.origin}/attendance/${currentSessionId.value}/${currentToken.value}`;
};

const closeAttendance = () => {
    router.post(route('attendance.close', currentSessionId.value), {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (refreshInterval.value) clearInterval(refreshInterval.value);
            if (window.Echo) window.Echo.leave(`attendance.${currentSessionId.value}`);
            showAttendanceModal.value = false;
            joinedStudents.value = [];
            currentSessionId.value = null;
        }
    });
};

onUnmounted(() => {
    if (refreshInterval.value) clearInterval(refreshInterval.value);
});
</script>

<template>
    <AppLayout title="Trung tâm điều khiển">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- HEADER: HERO BANNER -->
            <div class="relative rounded-3xl overflow-hidden bg-slate-900 border border-white/10 shadow-2xl group">
                <!-- Animated Background -->
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1534972195531-d756b9bfa9f2?q=80&w=2070&auto=format&fit=crop')] bg-cover bg-center opacity-40 mix-blend-overlay group-hover:scale-105 transition-transform duration-[2s]"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-transparent"></div>
                
                <div class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row justify-between items-end gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-[10px] font-bold tracking-widest uppercase mb-3 font-mono">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                            Online
                        </div>
                        <h1 class="text-4xl md:text-5xl font-black text-white font-exo leading-tight">
                            Chào mừng trở lại,
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">{{ $page.props.auth.user.name }}</span>
                        </h1>
                        <p class="text-slate-400 mt-2 max-w-lg text-sm">
                            Hiện tại bạn đang có <span class="text-white font-bold">{{ stats.totalClasses }}</span> lớp học đang quản lý và <span class="text-white font-bold">{{ stats.totalStudents }}</span> học sinh.
                        </p>
                    </div>

                    <!-- Holographic Stats -->
                    <div class="flex gap-4">
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-4 rounded-2xl text-center min-w-[100px] hover:bg-white/10 transition">
                            <div class="text-xs text-slate-400 uppercase tracking-wider font-mono">Ngày</div>
                            <div class="text-xl font-bold text-white font-exo">{{ currentDate }}</div>
                        </div>
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-4 rounded-2xl text-center min-w-[100px] hover:bg-white/10 transition">
                            <div class="text-xs text-slate-400 uppercase tracking-wider font-mono">Giờ</div>
                            <div class="text-xl font-bold text-cyan-400 font-mono">{{ currentTime }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: CLASS LAUNCHPAD -->
            <div>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xl font-bold text-white font-exo flex items-center gap-3">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Điểm danh nhanh
                    </h3>
                    <div class="h-px bg-slate-800 flex-1 ml-4"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="team in ownedTeams" :key="team.id" class="group relative bg-slate-900 border border-slate-800 hover:border-cyan-500/50 rounded-2xl p-5 transition-all duration-300 hover:shadow-[0_0_30px_rgba(6,182,212,0.15)] hover:-translate-y-1 overflow-hidden">
                        <!-- Decorative bg -->
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition"></div>
                        <!-- <div class="font-bold text-white">{{ team.name }}</div> -->
                        <div class="relative z-10 flex flex-col h-full">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 group-hover:border-cyan-500/50 transition">
                                    <span class="font-mono font-bold">{{ team.name.substring(0,2).toUpperCase() }}</span>
                                </div>
                                <span class="text-[10px] font-mono text-slate-500">ID: {{ team.id }}</span>
                            </div>
                            
                            <h4 class="text-lg font-bold text-slate-200 group-hover:text-white truncate font-exo">{{ team.name }}</h4>
                            
                            <button @click="startAttendance(team.id)" class="mt-4 w-full py-2 bg-slate-800 hover:bg-cyan-600 text-slate-400 hover:text-white rounded-lg text-xs font-bold uppercase tracking-wider border border-slate-700 hover:border-cyan-500 transition-colors flex items-center justify-center gap-2 group-active:scale-95">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                Tạo QR Điểm Danh
                            </button>
                        </div>
                    </div>

                    <!-- Create New -->
                    <Link :href="route('teams.create')" class="border border-dashed border-slate-800 hover:border-slate-600 rounded-2xl p-5 flex flex-col items-center justify-center text-slate-600 hover:text-slate-400 hover:bg-white/5 transition group">
                        <div class="w-12 h-12 rounded-full bg-slate-800/50 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider">Tạo lớp học</span>
                    </Link>
                </div>
            </div>

            <!-- SECTION: MAIN DASHBOARD GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- LEFT COL: CHART & TASKS -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Analytics Panel -->
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-cyan-500 rounded-full"></span>
                                Biểu đồ phân tích
                            </h3>
                            <select v-model="selectedTeamId" class="bg-black/40 border border-slate-700 text-slate-300 text-xs rounded-lg focus:ring-cyan-500 focus:border-cyan-500 py-1.5 px-3 outline-none font-mono">
                                <option v-for="team in ownedTeams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                        </div>
                        
                        <div class="h-[300px] w-full">
                            <Bar v-if="analyticsData?.chartGradeDistribution" :data="analyticsData.chartGradeDistribution" :options="chartOptions" />
                             <div v-else class="h-full flex flex-col items-center justify-center text-slate-600 space-y-3">
                                <div class="relative w-10 h-10">
                                    <div class="absolute inset-0 border-2 border-slate-700 rounded-full"></div>
                                    <div class="absolute inset-0 border-t-2 border-cyan-500 rounded-full animate-spin"></div>
                                </div>
                                <span class="text-xs font-mono uppercase tracking-widest">Processing Data...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Priority Tasks -->
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span>
                                Việc cần làm
                            </h3>
                            <span v-if="priorityActions.assignmentsToGrade.length" class="px-2 py-1 bg-rose-500/20 text-rose-400 text-[10px] font-bold rounded uppercase animate-pulse">Action Required</span>
                        </div>

                        <div v-if="priorityActions.assignmentsToGrade.length > 0" class="space-y-3">
                            <div v-for="post in priorityActions.assignmentsToGrade" :key="post.id" 
                                 class="flex items-center justify-between bg-black/20 p-4 rounded-xl border border-white/5 hover:border-rose-500/50 transition group">
                                <div class="flex items-center gap-4">
                                    <div class="p-2 bg-rose-500/10 rounded-lg text-rose-500">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-slate-200 font-bold group-hover:text-white text-sm">{{ post.title }}</div>
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wide font-mono mt-0.5">{{ post.team.name }}</div>
                                    </div>
                                </div>
                                <Link :href="`/posts/${post.id}/submissions`" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-lg shadow-lg shadow-rose-900/40 transition">
                                    Số lượng bài :  {{ post.ungraded_submissions_count }}
                                </Link>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/50">
                            <p class="text-slate-500 text-sm">All cleared. No pending tasks.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COL: TERMINAL FEED -->
                <div class="lg:col-span-1 bg-black/40 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden flex flex-col h-[600px] shadow-2xl">
                    <div class="p-4 bg-slate-900 border-b border-white/10 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 font-mono uppercase">Log Hoạt động</span>
                        <div class="flex gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500/20 border border-red-500/50"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500/20 border border-green-500/50"></div>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar font-mono text-xs">
                        <div v-for="(activity, index) in activityFeed" :key="index" class="flex gap-3 group opacity-70 hover:opacity-100 transition">
                            <div class="min-w-[40px] text-slate-600 text-[10px] pt-0.5 text-right">{{ formatTimeAgo(activity.timestamp) }}</div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-cyan-400 font-bold">root@{{ activity.user?.name.split(' ')[0].toLowerCase() || 'sys' }}:~#</span>
                                    <span class="text-emerald-400">{{ activity.activity_type.toLowerCase() }}</span>
                                </div>
                                <div class="text-slate-400 pl-4 border-l border-slate-800 mt-1">
                                    Target: <span class="text-slate-300">{{ activity.team?.name || 'Global' }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="activityFeed.length === 0" class="text-slate-600 italic px-4">> No recent events captured...</div>
                        
                        <!-- Blinking Cursor -->
                        <div class="px-4 text-cyan-500 animate-pulse font-bold">>_</div>
                    </div>
                </div>
            </div>

            <!-- ATTENDANCE MODAL (HUD Style) -->
            <Modal :show="showAttendanceModal" @close="closeAttendance">
                <div class="bg-black text-white relative border border-cyan-500/50 shadow-[0_0_100px_rgba(6,182,212,0.2)] max-w-4xl w-full mx-auto overflow-hidden">
                    
                    <!-- HUD Overlay -->
                    <div class="absolute inset-0 pointer-events-none z-0">
                        <!-- Grid -->
                        <div class="absolute inset-0 bg-[linear-gradient(rgba(6,182,212,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(6,182,212,0.05)_1px,transparent_1px)] bg-[size:20px_20px]"></div>
                        <!-- Scanline -->
                        <div class="absolute w-full h-1 bg-cyan-400/30 blur-sm animate-scan"></div>
                        <!-- Corners -->
                        <svg class="absolute top-4 left-4 w-12 h-12 text-cyan-500" viewBox="0 0 50 50" fill="none"><path d="M1 15V1H15" stroke="currentColor" stroke-width="2"/></svg>
                        <svg class="absolute top-4 right-4 w-12 h-12 text-cyan-500" viewBox="0 0 50 50" fill="none"><path d="M49 15V1H35" stroke="currentColor" stroke-width="2"/></svg>
                        <svg class="absolute bottom-4 left-4 w-12 h-12 text-cyan-500" viewBox="0 0 50 50" fill="none"><path d="M1 35V49H15" stroke="currentColor" stroke-width="2"/></svg>
                        <svg class="absolute bottom-4 right-4 w-12 h-12 text-cyan-500" viewBox="0 0 50 50" fill="none"><path d="M49 35V49H35" stroke="currentColor" stroke-width="2"/></svg>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-2">
                        <!-- Left: QR & Token -->
                        <div class="p-12 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-cyan-500/30 bg-slate-900/50 backdrop-blur-sm">
                            <div class="mb-8 text-center">
                                <h2 class="text-3xl font-black text-white tracking-tighter uppercase font-exo">{{ activeTeamName }}</h2>
                                <p class="text-cyan-500 text-xs font-mono tracking-[0.3em] mt-1">Quét QR Để Điểm Danh</p>
                            </div>

                            <div class="p-2 bg-white rounded-sm shadow-[0_0_30px_rgba(255,255,255,0.2)] mb-8 relative group">
                                <div class="absolute -inset-2 border border-cyan-500/50 rounded-lg animate-pulse"></div>
                                <QrcodeVue :value="currentQrUrl" :size="240" level="H" class="mix-blend-multiply" />
                            </div>

                            <div class="w-full max-w-xs">
                                <div class="flex justify-between text-[10px] text-cyan-400 font-mono mb-1">
                                    <span>Mã Điểm Danh</span>
                                    <span>Thời gian reset: 10s</span>
                                </div>
                                <div class="bg-black border border-cyan-500/50 p-4 text-center">
                                    <span class="text-4xl font-mono text-white tracking-[0.5em] drop-shadow-[0_0_10px_rgba(255,255,255,0.8)]">{{ currentToken }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Student List -->
                        <div class="flex flex-col bg-black/80">
                            <div class="p-4 border-b border-cyan-500/30 flex justify-between items-center bg-cyan-950/30">
                                <span class="font-mono text-cyan-400 text-xs">> Danh sách học sinh</span>
                                <span class="bg-cyan-500/20 text-cyan-400 px-2 py-1 text-xs font-bold">{{ joinedStudents.length }} ONLINE</span>
                            </div>

                            <div class="flex-1 p-4 overflow-y-auto custom-scrollbar min-h-[400px]">
                                <transition-group name="list" tag="ul" class="space-y-2">
                                    <li v-for="student in joinedStudents" :key="student.id" class="flex items-center gap-3 p-3 bg-slate-900 border-l-2 border-emerald-500 shadow-lg">
                                        <div class="relative">
                                            <img :src="student.profile_photo_url" class="w-8 h-8 rounded bg-slate-800">
                                            <div class="absolute -bottom-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border border-black"></div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-white font-exo">{{ student.name }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono">ID_REF: {{ student.id }}</div>
                                        </div>
                                        <div class="ml-auto text-emerald-500 text-[10px] font-mono border border-emerald-500/30 px-1">VERIFIED</div>
                                    </li>
                                </transition-group>
                                
                                <div v-if="joinedStudents.length === 0" class="h-full flex flex-col items-center justify-center opacity-50 space-y-4">
                                    <div class="w-16 h-16 border-4 border-cyan-900 border-t-cyan-500 rounded-full animate-spin"></div>
                                    <span class="text-xs font-mono text-cyan-700">Chưa có ai điểm danh...</span>
                                </div>
                            </div>

                            <div class="p-4 border-t border-cyan-500/30 bg-slate-900">
                                <button @click="closeAttendance" class="group w-full py-4 bg-red-600/10 hover:bg-red-600 text-red-500 hover:text-white border border-red-600/50 hover:border-red-600 font-bold tracking-widest uppercase text-sm transition-all duration-300 relative overflow-hidden">
                                    <span class="relative z-10">Kết thúc điểm danh</span>
                                    <div class="absolute inset-0 bg-red-600 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 ease-out z-0"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&family=JetBrains+Mono:wght@100..800&display=swap');

.font-exo { font-family: 'Exo 2', sans-serif; }

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

@keyframes scan {
    0% { top: 0%; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
.animate-scan {
    animation: scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
</style>