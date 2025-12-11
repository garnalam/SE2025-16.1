<template>
    <AppLayout title="Lịch sử điểm danh">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Điểm danh lớp: {{ team.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <div class="mb-4 flex items-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <span class="w-4 h-4 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-1">✔</span>
                            <span>: Có mặt</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-4 h-4 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-1">✕</span>
                            <span>: Vắng</span>
                        </div>
                        <span v-if="canEdit" class="text-gray-500 italic">(Giáo viên click vào ô để sửa đổi)</span>
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
                        
                        <div v-if="students.length === 0" class="text-center py-8 text-gray-500">
                            Chưa có sinh viên nào trong lớp.
                        </div>
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
});

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