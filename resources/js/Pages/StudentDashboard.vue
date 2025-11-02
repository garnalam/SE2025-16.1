<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProgressChart from '@/Components/ProgressChart.vue'; 
import { computed } from 'vue';

// Lấy props từ controller
const { props } = usePage();

// 2. ĐỊNH NGHĨA PROP (để Vue nhận diện)
defineProps({
    progressChartData: Object,
    upcomingAssignments: Array,
    latestAnnouncements: Array
});

// Hàm format thời gian (Giữ nguyên)
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

// Hàm tính thời gian còn lại (Giữ nguyên)
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

// Hàm cắt ngắn nội dung (Giữ nguyên)
const truncate = (text, length = 50) => {
    if (text && text.length > length) {
        return text.substring(0, length) + '...';
    }
    return text;
};

// Hàm tính toán cho bài tập (Giữ nguyên)
const sortedUpcomingAssignments = computed(() => {
    if (!props.upcomingAssignments) return [];
    return props.upcomingAssignments.map(post => {
        const dueDate = new Date(post.due_date);
        const now = new Date();
        const diff = dueDate - now;
        return { ...post, diff }; // Thêm 'diff' để sort
    }).sort((a, b) => a.diff - b.diff); // Sắp xếp, cái nào gần hết hạn nhất lên đầu
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
                
                <!-- Thanh thông báo (Đã xóa) -->

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Cột chính: Việc cần làm -->
                    <div class="md:col-span-2 space-y-6">
                        
                        <!-- 1. Bài tập sắp đến hạn -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Bài tập sắp đến hạn
                            </h3>
                            <div v-if="sortedUpcomingAssignments.length > 0" class="space-y-3">
                                <div v-for="post in sortedUpcomingAssignments" :key="post.id" class="p-3 bg-red-50 rounded-lg shadow-sm border border-red-200">
                                    <div class="flex justify-between items-center">
                                        <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                            {{ post.title }}
                                        </Link>
                                        <span class="text-xs font-semibold text-red-600">
                                            {{ formatDueDate(post.due_date) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span>{{ post.team.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <p class="text-gray-500">Tuyệt vời! Bạn đã hoàn thành hết bài tập.</p>
                            </div>
                        </div>

                        <!-- 2. Hiển thị Thông báo -->
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Thông báo mới nhất
                            </h3>
                            <div v-if="props.latestAnnouncements.length > 0" class="space-y-3">
                                <div v-for="post in props.latestAnnouncements" :key="post.id" class="p-3 bg-gray-50 rounded-lg shadow-sm border border-gray-200">
                                    <div class="text-sm text-gray-800">
                                        <Link :href="route('topics.show', { topic: post.topic_id, '#post-': post.id })" class="hover:underline">
                                            {{ truncate(post.content, 100) }}
                                        </Link>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1 flex justify-between">
                                        <span>{{ post.team.name }}</span>
                                        
                                        <!-- ===== SỬA LỖI Ở ĐÂY ===== -->
                                        <!-- 
                                            Lỗi là ở đây:
                                            TRƯỚC: <span>{{ formatTimeAgo(post.due_date) }}</span> 
                                            SAU:   <span>{{ formatTimeAgo(post.created_at) }}</span>
                                        -->
                                        <span>{{ formatTimeAgo(post.created_at) }}</span>
                                        <!-- ===== KẾT THÚC SỬA LỖI ===== -->

                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <p class="text-gray-500">Không có thông báo mới nào.</p>
                            </div>
                        </div>

                    </div> <!-- Hết cột chính -->

                    <!-- Cột phụ: Biểu đồ -->
                    <div class="md:col-span-1">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Tiến Độ Học Tập
                            </h3>
                            
                            <ProgressChart :chart-data="props.progressChartData" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

