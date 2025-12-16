<template>
    <AppLayout title="Lịch sử điểm danh">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Điểm danh lớp: {{ team.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-l-4 border-indigo-500 pl-3">
                        📊 Thống kê chuyên cần
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-200">
                            <thead class="bg-indigo-50 text-indigo-700">
                                <tr>
                                    <th class="border border-gray-200 p-3 text-left">Học sinh</th>
                                    <th class="border border-gray-200 p-3 text-center">Số buổi có mặt</th>
                                    <th class="border border-gray-200 p-3 text-left w-1/2">Tỷ lệ chuyên cần</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="student in students" :key="'summary-' + student.id" class="hover:bg-gray-50">
                                    <td class="border border-gray-200 p-3 font-medium">{{ student.name }}</td>
                                    <td class="border border-gray-200 p-3 text-center font-bold">
                                        {{ student.present_count }} / {{ totalSessions }}
                                    </td>
                                    <td class="border border-gray-200 p-3">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                                                <div class="h-4 rounded-full transition-all duration-500"
                                                    :class="getProgressBarColor(student.attendance_rate)"
                                                    :style="{ width: student.attendance_rate + '%' }">
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold w-12 text-right">{{ student.attendance_rate }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="students.length === 0">
                                    <td colspan="3" class="text-center p-4 text-gray-500">Chưa có dữ liệu học sinh.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-l-4 border-green-500 pl-3">
                        📅 Chi tiết từng buổi
                    </h3>
                    
                    <div class="mb-4 flex items-center space-x-4 text-sm">
                        </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-200">
                             <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 p-3 text-left w-48 sticky left-0 bg-gray-50 z-10">
                                        Sinh viên
                                    </th>
                                    <th v-for="session in sessions" :key="session.id" class="border border-gray-300 p-3 text-center min-w-[120px]">
                                        <div class="text-xs font-bold text-gray-700">
                                            {{ formatDate(session.created_at) }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ formatTime(session.created_at) }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50">
                                    <td class="border border-gray-300 p-3 sticky left-0 bg-white z-10 font-medium text-gray-700">
                                        {{ student.name }}
                                    </td>
                                    
                                    <td v-for="session in sessions" :key="session.id" class="border border-gray-300 p-0 text-center relative group">
                                        <button 
                                            @click="toggleAttendance(session.id, student.id)"
                                            :disabled="!canEdit"
                                            class="w-full h-full p-3 flex items-center justify-center transition-colors duration-150"
                                            :class="{
                                                'cursor-pointer hover:bg-gray-100': canEdit,
                                                'cursor-default': !canEdit
                                            }"
                                        >
                                            <div v-if="isPresent(student, session.id)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div v-else>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </div>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    team: Object,
    sessions: Array,
    students: Array,
    canEdit: Boolean,
    totalSessions: Number,
});

const getProgressBarColor = (rate) => {
    if (rate >= 80) return 'bg-green-500';
    if (rate >= 50) return 'bg-yellow-400';
    return 'bg-red-500';
};

// Helper: Kiểm tra sinh viên có record trong session này không
const isPresent = (student, sessionId) => {
    // Tìm trong mảng attendance_records đã được eager load từ Controller
    return student.attendance_records.some(r => r.attendance_session_id === sessionId);
};

// Helper: Format ngày giờ
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN');
};
const formatTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

// Action: Toggle trạng thái
const toggleAttendance = (sessionId, userId) => {
    if (!props.canEdit) return;

    router.post(route('attendance.toggle'), {
        session_id: sessionId,
        user_id: userId,
    }, {
        preserveScroll: true, // Giữ vị trí cuộn để thao tác nhanh
        preserveState: true,
        onSuccess: () => {
            // Có thể thêm thông báo nhỏ nếu cần
        }
    });
};
</script>