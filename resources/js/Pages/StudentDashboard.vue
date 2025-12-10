<script setup>
import { usePage, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProgressChart from '@/Components/ProgressChart.vue'; 
import { computed, ref } from 'vue';

// Lấy props từ controller
const { props } = usePage();

defineProps({
    progressChartData: Object,
    upcomingAssignments: Array,
    latestAnnouncements: Array
});

// --- 1. LOGIC ĐIỂM DANH (MỚI) ---
const attendanceCode = ref('');
const isSubmittingCode = ref(false);

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

// --- 2. CÁC HÀM XỬ LÝ HIỂN THỊ (CŨ) ---

// Hàm format thời gian (Vừa xong, x phút trước...)
const formatTimeAgo = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
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
    return "Vừa xong";
};

// Hàm tính thời gian còn lại (Còn 2 ngày...)
const formatDueDate = (dateString) => {
    if (!dateString) return 'Không có hạn nộp';
    const date = new Date(dateString);
    const now = new Date();
    const diff = date - now;
    
    if (diff < 0) return 'Đã hết hạn';
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    if (days > 1) return `Còn ${days} ngày`;
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    if (hours > 1) return `Còn ${hours} giờ`;
    
    const minutes = Math.floor(diff / (1000 * 60));
    if (minutes > 1) return `Còn ${minutes} phút`;
    
    return 'Hết hạn hôm nay';
};

// Hàm cắt ngắn nội dung
const truncate = (text, length = 50) => {
    if (text && text.length > length) {
        return text.substring(0, length) + '...';
    }
    return text;
};

// Sắp xếp bài tập: Cái nào gần hết hạn nhất lên đầu
const sortedUpcomingAssignments = computed(() => {
    if (!props.upcomingAssignments) return [];
    return props.upcomingAssignments.map(post => {
        const dueDate = new Date(post.due_date);
        const now = new Date();
        const diff = dueDate - now;
        return { ...post, diff };
    }).sort((a, b) => a.diff - b.diff);
});
</script>

<template>
    <AppLayout title="Student Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard (Học sinh)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="md:col-span-2 space-y-6">
                        
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="mr-2">📅</span> Bài tập sắp đến hạn
                            </h3>
                            <div v-if="sortedUpcomingAssignments.length > 0" class="space-y-3">
                                <div v-for="post in sortedUpcomingAssignments" :key="post.id" class="p-3 bg-red-50 rounded-lg shadow-sm border border-red-200 hover:shadow-md transition">
                                    <div class="flex justify-between items-center">
                                        <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" class="text-sm font-bold text-indigo-700 hover:text-indigo-900 hover:underline">
                                            {{ post.title }}
                                        </Link>
                                        <span class="text-xs font-semibold text-red-600 bg-white px-2 py-1 rounded border border-red-100">
                                            {{ formatDueDate(post.due_date) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 flex justify-between">
                                        <span>Lớp: {{ post.team.name }}</span>
                                        <span>{{ new Date(post.due_date).toLocaleDateString('vi-VN') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-gray-500 italic">Tuyệt vời! Bạn đã hoàn thành hết bài tập.</p>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <span class="mr-2">📢</span> Thông báo mới nhất
                            </h3>
                            <div v-if="props.latestAnnouncements.length > 0" class="space-y-3">
                                <div v-for="post in props.latestAnnouncements" :key="post.id" class="p-4 bg-gray-50 rounded-lg shadow-sm border border-gray-200">
                                    <div class="text-sm text-gray-800">
                                        <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" class="hover:text-indigo-600 transition">
                                            {{ truncate(post.content, 120) }}
                                        </Link>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2 flex justify-between items-center border-t border-gray-200 pt-2">
                                        <span class="font-medium text-gray-500">{{ post.team.name }}</span>
                                        <span>{{ formatTimeAgo(post.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-gray-500 italic">Không có thông báo mới nào.</p>
                            </div>
                        </div>

                    </div> 

                    <div class="md:col-span-1 space-y-6">
                        
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-t-4 border-green-500 relative">
                            <span class="absolute top-0 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-bl">Mới</span>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                                🙋 Điểm danh
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">Nhập mã 6 ký tự từ màn hình giáo viên:</p>
                            
                            <form @submit.prevent="submitAttendance" class="flex gap-2">
                                <input 
                                    v-model="attendanceCode" 
                                    type="text" 
                                    placeholder="A1B2C3" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 uppercase font-mono tracking-widest text-center"
                                    maxlength="6"
                                    required
                                >
                                <button 
                                    :disabled="isSubmittingCode"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 font-bold shadow transition flex-shrink-0"
                                >
                                    {{ isSubmittingCode ? '...' : 'Gửi' }}
                                </button>
                            </form>
                        </div>

                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                📊 Tiến Độ Học Tập
                            </h3>
                            <div class="h-48">
                                <ProgressChart :chart-data="props.progressChartData" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>