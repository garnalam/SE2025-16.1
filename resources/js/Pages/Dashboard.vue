<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, onUnmounted } from 'vue';
import axios from 'axios';
import QrcodeVue from 'qrcode.vue'; // Thư viện QR
import Modal from '@/Components/Modal.vue'; // Modal có sẵn của Jetstream

// Import Chart.js (GIỮ NGUYÊN)
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: Object,
    priorityActions: Object,
    activityFeed: Array,
    ownedTeams: Array,
});

// --- LOGIC CHART (GIỮ NGUYÊN) ---
const formatTimeAgo = (timestamp) => { /* ... giữ nguyên logic cũ ... */ 
    const now = new Date();
    const past = new Date(timestamp);
    const seconds = Math.floor((now - past) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " năm trước";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " tháng trước";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " ngày trước";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " giờ trước";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " phút trước";
    return Math.floor(seconds) + " giây trước";
};

const selectedTeamId = ref(props.ownedTeams[0]?.id || null);
const analyticsData = ref(null);
const isLoadingAnalytics = ref(false);
const analyticsError = ref(null);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { ticks: { callback: function(value) { return value + '%'; } } }
    }
};

const fetchAnalytics = async (teamId) => {
    if (!teamId) { analyticsData.value = null; return; }
    isLoadingAnalytics.value = true;
    analyticsError.value = null;
    try {
        const response = await axios.get(`/analytics/class/${teamId}`);
        analyticsData.value = response.data;
    } catch (error) {
        analyticsError.value = "Không thể tải dữ liệu";
    } finally {
        isLoadingAnalytics.value = false;
    }
};

watch(selectedTeamId, (newId) => { fetchAnalytics(newId); }, { immediate: true });

// --- LOGIC ĐIỂM DANH (THÊM MỚI) ---
const showAttendanceModal = ref(false);
const currentSessionId = ref(null);
const currentToken = ref('...');
const currentQrUrl = ref('');
const joinedStudents = ref([]);
const refreshInterval = ref(null);

// Mở modal tạo phiên
const startAttendance = async (teamId) => {
    try {
        // Gọi API tạo phiên mới
        const response = await axios.post(route('attendance.create', teamId));
        currentSessionId.value = response.data.session_id;
        currentToken.value = response.data.token;
        updateQrUrl();
        
        showAttendanceModal.value = true;
        
        // 1. Bắt đầu vòng lặp đổi mã mỗi 10s
        refreshInterval.value = setInterval(refreshCode, 10000);
        
        // 2. Lắng nghe ai vào lớp (Real-time)
        Echo.private(`attendance.${currentSessionId.value}`)
            .listen('.StudentAttended', (e) => {
                console.log("Sự kiện nhận được:", e);
                // Thêm học sinh vào đầu danh sách hiển thị
                joinedStudents.value.unshift(e.student);
            });
            
    } catch (error) {
        alert("Lỗi tạo phiên điểm danh. Vui lòng thử lại.");
        console.error(error);
    }
};

// Hàm đổi mã (Refresh Token)
const refreshCode = async () => {
    if (!currentSessionId.value) return;
    try {
        const res = await axios.post(route('attendance.refresh', currentSessionId.value));
        currentToken.value = res.data.token;
        updateQrUrl();
    } catch (e) { console.error(e); }
};

// Cập nhật link QR Code
const updateQrUrl = () => {
    // Tạo URL: http://localhost:8000/attendance/{session}/{token}
    currentQrUrl.value = `${window.location.origin}/attendance/${currentSessionId.value}/${currentToken.value}`;
};
const showSummaryModal = ref(false); // Điều khiển hiển thị modal kết quả
const sessionSummary = ref(null);    // Chứa dữ liệu báo cáo từ server
// Kết thúc phiên
const closeAttendance = async () => {
    if (!confirm("Bạn có chắc muốn chốt sổ điểm danh?")) return;

    try {
        // Gọi API đóng phiên
        const response = await axios.post(route('attendance.close', currentSessionId.value));
        
        // 1. Dọn dẹp logic real-time cũ
        if (refreshInterval.value) clearInterval(refreshInterval.value);
        Echo.leave(`attendance.${currentSessionId.value}`);
        
        // 2. Tắt modal QR code
        showAttendanceModal.value = false;
        
        // 3. Lưu dữ liệu báo cáo và HIỆN MODAL THỐNG KÊ
        sessionSummary.value = response.data.summary;
        showSummaryModal.value = true; // <--- Mấu chốt ở đây

        // 4. Reset các biến tạm
        joinedStudents.value = [];
        currentSessionId.value = null;

    } catch (error) {
        console.error(error);
        alert("Có lỗi khi đóng phiên điểm danh.");
    }
};

onUnmounted(() => {
    if (refreshInterval.value) clearInterval(refreshInterval.value);
});
</script>

<template>
    <AppLayout title="Dashboard Giáo viên">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Giáo viên
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="ownedTeams.length > 0" class="bg-white p-6 rounded-lg shadow-xl mb-6 border-l-4 border-indigo-500">
                    <h3 class="text-lg font-bold mb-3 flex items-center text-indigo-700">
                        ⚡ Điểm danh nhanh
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">Chọn lớp để bắt đầu phiên điểm danh bằng mã QR:</p>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="team in ownedTeams" 
                            :key="team.id"
                            @click="startAttendance(team.id)"
                            class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition transform hover:scale-105"
                        >
                            Lớp {{ team.name }}
                        </button>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-6">
                    <div class="w-full md:w-7/10 space-y-6">
                        
                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">📥 Cần hành động ngay</h3>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-700">Bài tập chờ chấm</h4>
                                    <div v-if="priorityActions.assignmentsToGrade.length > 0" class="mt-2 space-y-2">
                                        <div v-for="post in priorityActions.assignmentsToGrade" :key="post.id" class="p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-150">
                                            <a :href="`/posts/${post.id}/submissions`" class="flex justify-between items-center">
                                                <div>
                                                    <span class="font-semibold text-blue-800">{{ post.title }}</span>
                                                    <span class="text-sm text-gray-600"> ({{ post.team.name }})</span>
                                                </div>
                                                <span class="flex-shrink-0 text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                                    {{ post.ungraded_submissions_count }} bài mới
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="mt-2 text-sm text-gray-500 italic">Không có bài tập nào cần chấm.</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">🔔 Hoạt động gần đây</h3>
                            <div v-if="activityFeed.length === 0" class="text-sm text-gray-500 italic">Chưa có hoạt động nào.</div>
                            <div v-else>
                                <div v-for="activity in activityFeed" :key="activity.timestamp" class="mb-3 pb-3 border-b last:border-0">
                                    <p class="text-sm text-gray-800">
                                        <span class="font-bold">{{ activity.user?.name || 'Hệ thống' }}</span>
                                        {{ activity.activity_type }} trong lớp {{ activity.team?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ formatTimeAgo(activity.timestamp) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-3/10 space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">📈 Tổng quan</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <div class="text-3xl font-bold text-indigo-600">{{ stats.totalClasses }}</div>
                                    <div class="text-sm text-gray-600">Lớp học</div>
                                </div>
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <div class="text-3xl font-bold text-indigo-600">{{ stats.totalStudents }}</div>
                                    <div class="text-sm text-gray-600">Học sinh</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">📊 Phân tích lớp học</h3>
                            <select v-model="selectedTeamId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option v-for="team in ownedTeams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <div class="mt-4 h-64">
                                <Bar v-if="analyticsData?.chartGradeDistribution" :data="analyticsData.chartGradeDistribution" :options="chartOptions" />
                                <div v-else class="flex items-center justify-center h-full text-gray-500">Chọn lớp để xem biểu đồ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showAttendanceModal" @close="closeAttendance">
            <div class="p-6 text-center bg-gray-50 rounded-lg">
                <h2 class="text-2xl font-bold mb-2 text-gray-800">Quét mã để điểm danh</h2>
                <p class="text-gray-500 mb-6 text-sm">Học sinh dùng camera điện thoại hoặc nhập mã bên dưới</p>
                
                <div class="flex justify-center mb-6 bg-white p-4 rounded shadow-inner inline-block mx-auto">
                    <QrcodeVue :value="currentQrUrl" :size="280" level="H" />
                </div>

                <div class="mb-6 bg-white p-4 rounded-lg border border-indigo-100 shadow-sm">
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">Mã số tham gia</p>
                    <p class="text-5xl font-mono font-black text-indigo-600 tracking-[0.2em] my-2">{{ currentToken }}</p>
                    <div class="flex justify-center items-center text-red-500 text-xs font-bold animate-pulse">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tự động đổi sau mỗi 10 giây
                    </div>
                </div>

                <div class="text-left bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">Danh sách đã vào</h3>
                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full">{{ joinedStudents.length }} học sinh</span>
                    </div>
                    
                    <ul class="h-48 overflow-y-auto p-2 space-y-1">
                        <transition-group name="list">
                            <li v-for="student in joinedStudents" :key="student.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded transition">
                                <img :src="student.profile_photo_url" class="w-8 h-8 rounded-full border border-gray-300">
                                <span class="font-medium text-gray-800">{{ student.name }}</span>
                                <span class="ml-auto text-green-600 font-bold flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Đã có mặt
                                </span>
                            </li>
                        </transition-group>
                        <li v-if="joinedStudents.length === 0" class="text-center text-gray-400 py-10 italic">
                            Chưa có học sinh nào điểm danh...
                        </li>
                    </ul>
                </div>

                <button 
                    @click="closeAttendance" 
                    class="mt-6 w-full py-3 bg-red-600 text-white font-bold rounded-lg shadow-md hover:bg-red-700 hover:shadow-lg transition transform hover:-translate-y-0.5"
                >
                    KẾT THÚC PHIÊN ĐIỂM DANH
                </button>
            </div>
        </Modal>
        <Modal :show="showSummaryModal" @close="showSummaryModal = false">
            <div class="p-6 bg-white rounded-lg">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Kết quả điểm danh</h3>
                    <div class="mt-2 flex justify-center items-baseline">
                        <span class="text-4xl font-extrabold text-indigo-600">
                            {{ sessionSummary?.present_count }}
                        </span>
                        <span class="ml-1 text-xl text-gray-500">
                            / {{ sessionSummary?.total_students }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Tỷ lệ chuyên cần: {{ sessionSummary?.rate }}%</p>
                </div>

                <div class="mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-2 uppercase">
                        Danh sách có mặt ({{ sessionSummary?.present_list.length }})
                    </h4>
                    <div class="bg-gray-50 rounded-md p-3 max-h-60 overflow-y-auto border border-gray-200">
                        <ul class="divide-y divide-gray-200">
                            <li v-for="user in sessionSummary?.present_list" :key="user.id" class="py-2 flex items-center">
                                <img :src="user.profile_photo_url" class="h-8 w-8 rounded-full mr-3">
                                <span class="text-sm font-medium text-gray-900">{{ user.name }}</span>
                            </li>
                            <li v-if="sessionSummary?.present_list.length === 0" class="text-center text-gray-500 italic py-2">
                                Không có ai.
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button @click="showSummaryModal = false" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm">
                        Xong
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<style scoped>
/* Hiệu ứng animation cho danh sách học sinh */
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>