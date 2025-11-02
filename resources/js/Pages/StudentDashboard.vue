<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProgressChart from '@/Components/ProgressChart.vue';
import { computed } from 'vue';

// Lấy props từ controller
const { props } = usePage();

// Định nghĩa props mới
defineProps({
    progressChartData: Object,
    upcomingAssignments: Array,
    latestAnnouncements: Array
});

// Helper tính thời gian (bạn có thể dùng day.js cho xịn hơn)
const formatTimeAgo = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = date.getTime() - now.getTime();
    
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) {
        return 'Hết hạn hôm nay';
    } else if (days === 1) {
        return 'Còn 1 ngày';
    } else if (days > 1) {
        return `Còn ${days} ngày`;
    } else if (days < -30) {
        return `Đã đăng ${Math.floor(-days/30)} tháng trước`;
    } else if (days < -7) {
        return `Đã đăng ${Math.floor(-days/7)} tuần trước`;
    } else if (days < -1) {
        return `Đã đăng ${-days} ngày trước`;
    } else if (diff > 0) {
        return 'Hết hạn hôm nay';
    } else {
        const diffHours = Math.ceil(diff / (1000 * 60 * 60));
        if (diffHours < -1) {
            return `Đã đăng ${-diffHours} giờ trước`;
        }
        return 'Vừa xong';
    }
};

// Cắt ngắn nội dung thông báo
const truncate = (text, length = 100) => {
    if (text.length <= length) {
        return text;
    }
    return text.substring(0, length) + '...';
};

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
                
                <!-- Thanh thông báo 'status' đã bị xóa -->

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- [THAY THẾ] Cột chính: Việc cần làm ngay -->
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Bài tập sắp đến hạn
                            </h3>
                            
                            <!-- Danh sách bài tập -->
                            <div v-if="props.upcomingAssignments && props.upcomingAssignments.length > 0" class="space-y-4">
                                <Link v-for="assignment in props.upcomingAssignments" :key="assignment.id" :href="route('topics.show', assignment.topic_id)" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-indigo-600">{{ assignment.title }}</span>
                                        <span class="text-sm font-medium text-red-600">{{ formatTimeAgo(assignment.due_date) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ assignment.team.name }}
                                    </div>
                                </Link>
                            </div>
                            
                            <!-- Trạng thái rỗng -->
                            <div v-else class="text-gray-500">
                                Tuyệt vời! Bạn đã hoàn thành hết bài tập.
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Thông báo mới nhất
                            </h3>
                            
                            <!-- Danh sách thông báo -->
                            <div v-if="props.latestAnnouncements && props.latestAnnouncements.length > 0" class="space-y-4">
                                 <Link v-for="post in props.latestAnnouncements" :key="post.id" :href="route('topics.show', post.topic_id)" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-gray-800">{{ post.team.name }}</span>
                                        <span class="text-sm text-gray-500">{{ formatTimeAgo(post.created_at) }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1" v-html="truncate(post.content)">
                                    </div>
                                </Link>
                            </div>
                            
                            <!-- Trạng thái rỗng -->
                            <div v-else class="text-gray-500">
                                Không có thông báo nào mới.
                            </div>
                        </div>
                    </div>

                    <!-- [GIỮ NGUYÊN] Cột phụ: Biểu đồ -->
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

