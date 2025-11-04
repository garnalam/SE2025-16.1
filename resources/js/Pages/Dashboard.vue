<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
// V↓↓↓ SỬA DÒNG NÀY: Xóa 'computed' vì không cần nữa V↓↓↓
import { ref, watch } from 'vue';
// V↑↑↑ SỬA DÒNG NÀY V↑↑↑
import axios from 'axios'; // Dùng để gọi API

// Import Chart.js và các thành phần
import { Bar, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement
} from 'chart.js';

// Đăng ký các thành phần Chart.js
ChartJS.register(
    Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement
);

// [Hàm tiện ích] Định dạng thời gian
const formatTimeAgo = (timestamp) => {
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

// --- Dữ liệu từ Controller ---
const props = defineProps({
    stats: Object,
    priorityActions: Object,
    activityFeed: Array,
    ownedTeams: Array, // <-- Nhận danh sách lớp học
});

// --- Logic Phân tích (Analytics) ---
const selectedTeamId = ref(props.ownedTeams[0]?.id || null);
const analyticsData = ref(null);
const isLoadingAnalytics = ref(false);
const analyticsError = ref(null);

// V↓↓↓ XÓA REF 'selectedStudentId' VÌ KHÔNG CẦN NỮA V↓↓↓
// const selectedStudentId = ref('class_average'); 
// V↑↑↑ XÓA REF 'selectedStudentId' V↑↑↑

// Tùy chọn chung cho các biểu đồ
// Tùy chọn chung cho các biểu đồ
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    
    // V↓↓↓ THÊM TOÀN BỘ KHỐI 'scales' NÀY V↓↓↓
    scales: {
        y: { // Áp dụng cho trục Y (trục tung)
            ticks: {
                // Định nghĩa một hàm callback để định dạng nhãn
                callback: function(value) {
                    // 'value' là con số (ví dụ: 10, 20)
                    // Trả về chuỗi mới có thêm ký tự '%'
                    return value + '%';
                }
            }
        }
    }
    // V↑↑↑ HẾT KHỐI THÊM VÀO V↑↑↑
};

// Hàm gọi API để lấy dữ liệu phân tích
const fetchAnalytics = async (teamId) => {
    if (!teamId) {
        analyticsData.value = null;
        return;
    }
    isLoadingAnalytics.value = true;
    analyticsError.value = null;
    try {
        const response = await axios.get(`/analytics/class/${teamId}`);
        analyticsData.value = response.data;
        // V↓↓↓ XÓA DÒNG RESET DROPDOWN VÌ KHÔNG CẦN NỮA V↓↓↓
        // selectedStudentId.value = 'class_average';
        // V↑↑↑ XÓA DÒNG RESET DROPDOWN V↑↑↑
    } catch (error) {
        console.error("Lỗi khi tải phân tích:", error);
        analyticsError.value = error.response?.data?.message || "Không thể tải dữ liệu";
        analyticsData.value = null;
    } finally {
        isLoadingAnalytics.value = false;
    }
};

// Theo dõi sự thay đổi của dropdown và gọi lại API
watch(selectedTeamId, (newId) => {
    fetchAnalytics(newId);
}, { immediate: true }); // immediate: true => Tải ngay lần đầu tiên


// V↓↓↓ XÓA TOÀN BỘ COMPUTED PROPERTY 'comparisonChartData' V↓↓↓
// const comparisonChartData = computed(() => { ... });
// V↑↑↑ XÓA TOÀN BỘ COMPUTED PROPERTY V↑↑↑
</script>


<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard giáo viên
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row gap-6">

                    <div class="w-full md:w-7/10 space-y-6">

                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">
                                📥 Cần hành động ngay
                            </h3>
                            <div class="space-y-4">
                                
                                <div>
                                    <h4 class="font-medium text-gray-700">Bài tập chờ chấm</h4>
                                    <div v-if="priorityActions.assignmentsToGrade.length > 0" class="mt-2 space-y-2">
                                        <div v-for="post in priorityActions.assignmentsToGrade" :key="post.id" 
                                            class="p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-150">
                                            
                                            <a :href="`/posts/${post.id}/submissions`" class="flex justify-between items-center">
                                                <div>
                                                    <span class="font-semibold text-blue-800">{{ post.title }}</span>
                                                    <span class="text-sm text-gray-600"> ({{ post.team.name }})</span>
                                                </div>
                                                <span class="flex-shrink-0 text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                                    {{ post.ungraded_submissions_count }} bài nộp mới
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="mt-2 text-sm text-gray-500 italic">
                                        Không có bài tập nào cần chấm. Tuyệt vời!
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">
                                🔔 Hoạt động gần đây
                            </h3>
                            <div class="space-y-4">
                                <div v-for="activity in activityFeed" :key="activity.timestamp + activity.activity_type" class="flex items-start space-x-3">
                                    
                                    <div class="flex-shrink-0">
                                        <span v-if="activity.activity_type == 'submission'" class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        </span>
                                        <span v-else-if="activity.activity_type == 'comment'" class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100 text-yellow-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27. ২৯3 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.17 48.17 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                                        </span>
                                        <span v-else-if="activity.activity_type == 'post'" class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        </span>
                                        <span v-else-if="activity.activity_type == 'join'" class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A1.25 1.25 0 0 1 14.25 20h-9.5A1.25 1.25 0 0 1 3 19.235Z" /></svg>
                                        </span>
                                    </div>
                                    
                                    <div class="text-sm">
                                        <p class="text-gray-800">
                                            <template v-if="activity.activity_type == 'submission'">
                                                <span class="font-semibold">{{ activity.user.name }}</span>
                                                vừa nộp bài cho 
                                                <span class="font-semibold">{{ activity.post.title }}</span>
                                                tại 
                                                <span class="font-semibold">{{ activity.post.team.name }}</span>.
                                            </template>
                                            <template v-else-if="activity.activity_type == 'comment'">
                                                <span class="font-semibold">{{ activity.user.name }}</span>
                                                vừa bình luận trong 
                                                <span class="font-semibold">{{ activity.post.title || 'bài đăng' }}</span>
                                                tại 
                                                <span class="font-semibold">{{ activity.post.team.name }}</span>.
                                            </template>
                                            <template v-else-if="activity.activity_type == 'post'">
                                                <span class="font-semibold">Bạn</span>
                                                vừa đăng 
                                                <span class="font-semibold">{{ activity.title }}</span>
                                                tại 
                                                <span class="font-semibold">{{ activity.team.name }}</span>.
                                            </template>
                                            <template v-else-if="activity.activity_type == 'join'">
                                                <span class="font-semibold">{{ activity.user_name }}</span>
                                                vừa tham gia lớp
                                                <span class="font-semibold">{{ activity.team_name }}</span>.
                                            </template>
                                        </p>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ formatTimeAgo(activity.timestamp) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="activityFeed.length === 0" class="text-sm text-gray-500 italic">
                                    Chưa có hoạt động nào.
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
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <div class="text-3xl font-bold text-indigo-600">{{ stats.activeAssignments }}</div>
                                    <div class="text-sm text-gray-600">Bài tập đang diễn ra</div>
                                </div>
                                <div class="bg-gray-100 p-4 rounded-lg border border-red-200">
                                    <div class="text-3xl font-bold text-red-600">{{ stats.totalUngradedSubmissions }}</div>
                                    <div class="text-sm text-gray-600">Bài chưa chấm</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">📊 Phân tích lớp học</h3>
                            
                            <select v-model="selectedTeamId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option v-if="ownedTeams.length === 0" value="null" disabled>Bạn chưa tạo lớp học nào</option>
                                <option v-for="team in ownedTeams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>

                            <div class="mt-4" style="min-height: 200px;">
                                <div v-if="isLoadingAnalytics" class="flex items-center justify-center h-full text-gray-500">
                                    Đang tải dữ liệu...
                                </div>

                                <div v-else-if="analyticsError" class="flex items-center justify-center h-full text-red-500">
                                    {{ analyticsError }}
                                </div>

                                <div class="max-h-[800px] overflow-y-auto pr-2 space-y-6">
                                    <div v-if="analyticsData"> 
                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2 text-sm">Phân phối điểm (TB học sinh)</h4>
                                            <div class="h-64">
                                                <Bar v-if="analyticsData.chartGradeDistribution"
                                                    :data="analyticsData.chartGradeDistribution" 
                                                    :options="chartOptions" 
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2 text-sm">Tỷ lệ nộp bài (theo bài tập)</h4>
                                            <div class="h-64">
                                                <Bar v-if="analyticsData.chartCompletionRate"
                                                    :data="analyticsData.chartCompletionRate" 
                                                    :options="chartOptions" 
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2 text-sm">Hiệu suất Học sinh vs TB Lớp</h4>
                                            
                                            <div class="h-64">
                                                <Bar v-if="analyticsData.chartStudentPerformance"
                                                    :data="analyticsData.chartStudentPerformance" 
                                                    :options="chartOptions" 
                                                />
                                            </div>
                                        </div>
                                        </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>