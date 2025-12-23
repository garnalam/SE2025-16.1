<template>
    <AppLayout title="Lịch sử điểm danh">
        <template #header>
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Ma trận Điểm danh: <span class="text-indigo-400 ml-1">{{ team.name }}</span>
            </h2>
        </template>

        <div class="py-8 bg-slate-950 min-h-screen">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-6 bg-slate-900/80 border border-white/10 p-4 rounded-xl shadow-lg backdrop-blur-sm flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center space-x-6 text-sm">
                        <div class="flex items-center px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-2 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                            <span class="text-emerald-400 font-bold">Có mặt</span>
                        </div>
                        <div class="flex items-center px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20">
                            <span class="w-2.5 h-2.5 bg-rose-500 rounded-full mr-2 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                            <span class="text-rose-400 font-bold">Vắng</span>
                        </div>
                        <div class="text-slate-500 text-xs hidden sm:block">
                            * Tổng số buổi: <span class="text-white font-mono">{{ sessions.length }}</span>
                        </div>
                    </div>
                    
                    <div v-if="canEdit" class="text-xs text-indigo-400 animate-pulse font-mono">
                        ● Live Mode: Click vào ô để thay đổi trạng thái
                    </div>
                </div>

                <div class="bg-slate-900 border border-white/5 shadow-2xl rounded-xl overflow-hidden relative">
                    <div class="overflow-x-auto custom-scrollbar" style="max-height: 75vh;">
                        <table class="min-w-full border-collapse border-spacing-0">
                            
                            <thead class="bg-slate-950 sticky top-0 z-30 shadow-md">
                                <tr>
                                    <th class="sticky left-0 top-0 z-40 bg-slate-950 p-4 text-left min-w-[220px] border-b border-r border-slate-700 shadow-[4px_0_12px_rgba(0,0,0,0.5)]">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sinh viên</div>
                                    </th>

                                    <th v-for="(session, index) in sessions" :key="session.id" class="p-2 min-w-[100px] border-b border-slate-800 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-[10px] text-slate-500 uppercase font-mono mb-1">Session {{ index + 1 }}</span>
                                            <span class="text-xs font-bold text-white bg-slate-800 px-2 py-1 rounded border border-white/5">
                                                {{ formatDate(session.created_at) }}
                                            </span>
                                            <span class="text-[9px] text-slate-500 mt-1 font-mono">
                                                {{ formatTime(session.created_at) }}
                                            </span>
                                        </div>
                                    </th>

                                    <th class="sticky right-0 top-0 z-40 bg-slate-950 p-4 text-center min-w-[150px] border-b border-l border-slate-700 shadow-[-4px_0_12px_rgba(0,0,0,0.5)]">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng kết</div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                <tr v-for="student in students" :key="student.id" class="group hover:bg-white/5 transition-colors duration-150">
                                    
                                    <td class="sticky left-0 z-20 bg-slate-900 group-hover:bg-slate-800 transition-colors duration-150 p-4 border-r border-slate-700 shadow-[4px_0_12px_rgba(0,0,0,0.5)]">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-xs font-bold text-white mr-3 ring-2 ring-slate-800">
                                                {{ getInitials(student.name) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-200">{{ student.name }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono">{{ student.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td v-for="session in sessions" :key="session.id" class="p-1 text-center border-r border-slate-800/50">
                                        <button 
                                            @click="toggleAttendance(session.id, student.id)"
                                            :disabled="!canEdit"
                                            class="w-full h-12 rounded-md flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-slate-900 relative overflow-hidden"
                                            :class="[
                                                canEdit ? 'cursor-pointer hover:scale-95 active:scale-90' : 'cursor-default',
                                                isPresent(student, session.id) 
                                                    ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-500 border border-emerald-500/30' 
                                                    : 'bg-rose-500/5 hover:bg-rose-500/10 text-rose-500/50 hover:text-rose-500 border border-transparent hover:border-rose-500/30'
                                            ]"
                                        >
                                            <span v-if="isPresent(student, session.id)" class="absolute inset-0 bg-emerald-500/10 animate-pulse-slow"></span>
                                            
                                            <svg v-if="isPresent(student, session.id)" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 relative z-10 drop-shadow-[0_0_5px_rgba(16,185,129,0.8)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>

                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 relative z-10 opacity-40 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </td>

                                    <td class="sticky right-0 z-20 bg-slate-900 group-hover:bg-slate-800 transition-colors duration-150 p-4 border-l border-slate-700 shadow-[-4px_0_12px_rgba(0,0,0,0.5)]">
                                        <div class="flex flex-col justify-center h-full">
                                            <div class="flex justify-between items-end mb-1">
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">Tỷ lệ</span>
                                                <span class="text-xs font-bold font-mono" :class="getRateColor(calculateRate(student))">
                                                    {{ calculateRate(student) }}%
                                                </span>
                                            </div>
                                            <div class="w-full bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-500 ease-out shadow-[0_0_8px_currentColor]"
                                                    :class="getRateColorClass(calculateRate(student))"
                                                    :style="{ width: calculateRate(student) + '%' }"
                                                ></div>
                                            </div>
                                            <div class="text-[10px] text-slate-500 mt-1 text-right">
                                                {{ countPresent(student) }}/{{ sessions.length }} buổi
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>

                        <div v-if="students.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Chưa có dữ liệu sinh viên.</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center text-xs text-slate-600 font-mono">
                    Hệ thống tự động lưu trữ dữ liệu mỗi khi bạn thay đổi trạng thái.
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

// Helper: Lấy chữ cái đầu tên
const getInitials = (name) => {
    if (!name) return '';
    return name.split(' ').map(n => n[0]).join('').slice(-2).toUpperCase();
};

// Helper: Kiểm tra có mặt
const isPresent = (student, sessionId) => {
    return student.attendance_records.some(r => r.attendance_session_id === sessionId);
};

// Logic: Tính toán số buổi có mặt
const countPresent = (student) => {
    return student.attendance_records.filter(r => 
        props.sessions.some(s => s.id === r.attendance_session_id)
    ).length;
};

// Logic: Tính % đi học
const calculateRate = (student) => {
    if (props.sessions.length === 0) return 0;
    const presentCount = countPresent(student);
    return Math.round((presentCount / props.sessions.length) * 100);
};

// Helper: Màu chữ theo tỷ lệ
const getRateColor = (rate) => {
    if (rate >= 80) return 'text-emerald-400';
    if (rate >= 50) return 'text-yellow-400';
    return 'text-rose-400';
};

// Helper: Màu thanh progress bar
const getRateColorClass = (rate) => {
    if (rate >= 80) return 'bg-emerald-500';
    if (rate >= 50) return 'bg-yellow-500';
    return 'bg-rose-500';
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return `${date.getDate()}/${date.getMonth() + 1}`;
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

// Toggle Attendance
const toggleAttendance = (sessionId, userId) => {
    if (!props.canEdit) return;

    router.post(route('attendance.toggle'), {
        session_id: sessionId,
        user_id: userId,
    }, {
        preserveScroll: true,
        preserveState: true, // Quan trọng để giữ trải nghiệm mượt mà
    });
};
</script>

<style scoped>
/* Custom Scrollbar cho bảng */
.custom-scrollbar::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569; 
}

/* Animation nhẹ cho trạng thái Active */
.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: .1; }
    50% { opacity: .2; }
}
</style>