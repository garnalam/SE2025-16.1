<template>
    <AppLayout title="Sổ điểm lớp học">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sổ điểm: {{ team.name }}
                </h2>
                <button 
                    v-if="canManage" 
                    @click="showSettingsModal = true" 
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50 flex items-center shadow-sm transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.532 1.532 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    Cấu hình
                </button>
            </div>
        </template>

        <div class="py-6 w-full max-h-[calc(100vh-100px)] h-auto flex flex-col">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col min-h-0">
                
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 flex flex-col flex-1 min-h-0">
                    
                    <div class="flex flex-1 overflow-hidden w-full">
                        
                        <div class="w-64 flex-none border-r border-gray-200 shadow-[4px_0_10px_-5px_rgba(0,0,0,0.1)] flex flex-col z-30 bg-white">
                            <div class="h-10 bg-gray-50 border-b border-gray-200 flex-none"></div>
                            <div class="h-[50px] bg-white border-b border-gray-300 p-3 flex items-center justify-between font-bold text-gray-800 text-xs uppercase cursor-pointer hover:bg-gray-50 flex-none" @click="sortBy('name')">
                                <span>Học sinh</span>
                                <span v-if="sortKey === 'name'" class="text-blue-600">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                            </div>
                            <div class="flex-1 overflow-hidden sync-scroll-area">
                                <div class="pb-4">
                                    <div v-for="student in sortedStudents" :key="'name-'+student.id" class="h-14 flex-none border-b border-gray-100 flex items-center px-3 gap-3 hover:bg-gray-50 bg-white box-border">
                                        <img :src="student.avatar" class="w-8 h-8 rounded-full border border-gray-200 object-cover flex-none">
                                        <span class="font-medium text-gray-700 text-sm truncate" :title="student.name">{{ student.name }}</span>
                                    </div>
                                    <div v-if="sortedStudents.length === 0" class="h-14 flex items-center justify-center text-xs text-gray-400 italic border-b border-gray-100">
                                        (Trống)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col border-r border-gray-200 min-w-0">
                            <div class="h-10 bg-green-50 text-green-800 font-bold uppercase text-xs flex items-center justify-center border-b border-green-200 flex-none">
                                Phần Quiz
                            </div>
                            <div class="flex flex-1 min-h-0 relative">
                                <div class="flex-1 overflow-auto sync-scroll-area custom-scrollbar">
                                    <table class="border-collapse border-spacing-0 min-w-max w-full">
                                        <thead class="sticky top-0 z-20 bg-white shadow-sm">
                                            <tr class="h-[50px]">
                                                <th v-for="quiz in quizzes" :key="'h-q-'+quiz.id" class="border-r border-b border-gray-200 p-2 min-w-[120px] w-[120px] bg-white align-top">
                                                    <div class="font-semibold text-gray-700 truncate w-[110px] mx-auto text-xs" :title="quiz.title">{{ quiz.title }}</div>
                                                    <div class="text-[9px] text-gray-400 font-normal mt-1">Max: {{ quiz.max_points }}</div>
                                                </th>
                                                <th class="border-l border-b border-green-200 p-2 w-[90px] min-w-[90px] bg-green-50 text-green-900 cursor-pointer sticky right-0 z-30 shadow-[-2px_0_5px_rgba(0,0,0,0.05)]" @click="sortBy('quiz_avg')">
                                                    <div class="flex flex-col items-center">
                                                        <span class="font-bold text-xs">TB Quiz</span>
                                                        <span v-if="sortKey === 'quiz_avg'" class="text-[9px]">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="student in sortedStudents" :key="'q-row-'+student.id" class="h-14 hover:bg-gray-50 bg-white">
                                                <td v-for="quiz in quizzes" :key="'d-q-'+quiz.id" class="border-r border-b border-gray-100 p-2 text-center text-gray-600 min-w-[120px]">
                                                    <span v-if="student.quizzes[quiz.id] !== null" class="font-bold text-gray-800 text-sm">
                                                        {{ Number(student.quizzes[quiz.id]).toFixed(2) }}
                                                    </span>
                                                    <span v-else class="text-gray-300 text-xs">--</span>
                                                </td>
                                                <td class="border-l border-b border-green-100 p-2 text-center font-bold text-green-700 bg-green-50 sticky right-0 z-10">
                                                    {{ student.quiz_avg }}
                                                </td>
                                            </tr>
                                            <tr v-if="sortedStudents.length === 0">
                                                <td :colspan="quizzes.length + 1" class="h-14 border-b border-gray-100"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col min-w-0">
                            <div class="h-10 bg-indigo-50 text-indigo-800 font-bold uppercase text-xs flex items-center justify-center border-b border-indigo-200 flex-none">
                                Phần Bài tập
                            </div>
                            <div class="flex flex-1 min-h-0 relative">
                                <div class="flex-1 overflow-auto sync-scroll-area custom-scrollbar">
                                    <table class="border-collapse border-spacing-0 min-w-max w-full">
                                        <thead class="sticky top-0 z-20 bg-white shadow-sm">
                                            <tr class="h-[50px]">
                                                <th v-for="assign in assignments" :key="'h-a-'+assign.id" class="border-r border-b border-gray-200 p-2 min-w-[140px] w-[140px] bg-white align-top">
                                                    <div class="font-semibold text-gray-700 truncate w-[130px] mx-auto text-xs" :title="assign.title">{{ assign.title }}</div>
                                                    <div class="text-[9px] text-gray-400 font-normal mt-1">Max: {{ assign.max_points }}</div>
                                                </th>
                                                <th class="border-l border-b border-indigo-200 p-2 w-[90px] min-w-[90px] bg-indigo-50 text-indigo-900 cursor-pointer sticky right-0 z-30 shadow-[-2px_0_5px_rgba(0,0,0,0.05)]" @click="sortBy('assign_avg')">
                                                    <div class="flex flex-col items-center">
                                                        <span class="font-bold text-xs">TB Bài tập</span>
                                                        <span v-if="sortKey === 'assign_avg'" class="text-[9px]">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="student in sortedStudents" :key="'a-row-'+student.id" class="h-14 hover:bg-gray-50 bg-white">
                                                <td v-for="assign in assignments" :key="'d-a-'+assign.id" class="border-r border-b border-gray-100 p-2 text-center min-w-[140px]">
                                                    <div v-if="student.assignments[assign.id]" class="flex flex-col items-center">
                                                        <span v-if="student.assignments[assign.id].status === 'missing'" class="text-red-500 font-bold text-xs">0</span>
                                                        <span v-else-if="student.assignments[assign.id].status === 'pending'" class="text-gray-300 italic text-xs">...</span>
                                                        <span v-else-if="student.assignments[assign.id].status === 'submitted'" class="text-blue-500 text-xs font-medium">Chấm bài</span>
                                                        <div v-else>
                                                            <span class="font-bold text-gray-800 text-sm">{{ student.assignments[assign.id].final_score }}</span>
                                                            <div v-if="student.assignments[assign.id].status === 'late'" class="text-[9px] text-red-500 whitespace-nowrap">
                                                                (Gốc: {{ student.assignments[assign.id].raw }})
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-l border-b border-indigo-100 p-2 text-center font-bold text-indigo-700 bg-indigo-50 sticky right-0 z-10">
                                                    {{ student.assign_avg }}
                                                </td>
                                            </tr>
                                            <tr v-if="sortedStudents.length === 0">
                                                <td :colspan="assignments.length + 1" class="h-14 border-b border-gray-100"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex-none" v-if="gradebook.total > 0">
                        <Pagination :links="gradebook.links" />
                        <div class="mt-2 text-xs text-gray-500 text-right">
                            Hiển thị {{ gradebook.from }}-{{ gradebook.to }} / {{ gradebook.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showSettingsModal" @close="showSettingsModal = false">
             <div class="p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-900 border-b pb-2">Cấu hình phạt nộp muộn</h3>
                <div v-if="form.hasErrors" class="mb-4 text-sm text-red-600">
                    Vui lòng kiểm tra lại dữ liệu.
                </div>
                <div class="mb-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Chế độ trừ điểm</label>
                            <select v-model="form.late_policy_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="none">Không trừ điểm</option>
                                <option value="fixed">Trừ cố định (%)</option>
                                <option value="daily">Trừ theo ngày (% / ngày)</option>
                            </select>
                        </div>
                        <div v-if="form.late_policy_type !== 'none'">
                            <label class="block text-sm font-medium text-gray-700">Mức phạt (%)</label>
                            <div class="flex items-center mt-1">
                                <input type="number" v-model="form.late_penalty_percent" min="0" max="100" class="block w-24 border-gray-300 rounded-md shadow-sm mr-2">
                                <span>%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end pt-4 border-t">
                    <button @click="showSettingsModal = false" class="mr-3 px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">Đóng</button>
                    <button @click="saveSettings" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium shadow" :class="{ 'opacity-50': form.processing }">Lưu thay đổi</button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    team: Object,
    assignments: Array,
    quizzes: Array,
    gradebook: Object, 
    settings: Object,
    canManage: Boolean 
});

const showSettingsModal = ref(false);
const sortKey = ref('name');
const sortOrder = ref('asc');

// --- ĐỒNG BỘ CUỘN DỌC (SYNC SCROLL) ---
const syncScroll = (event) => {
    // Lấy vị trí cuộn dọc của phần tử đang được cuộn
    const scrollTop = event.target.scrollTop;
    
    const syncAreas = document.querySelectorAll('.sync-scroll-area');
    
    // Tạm gỡ sự kiện để tránh vòng lặp vô tận
    syncAreas.forEach(el => el.removeEventListener('scroll', syncScroll));

    // Gán vị trí cuộn cho các cột khác
    syncAreas.forEach(el => {
        if(el !== event.target) {
            el.scrollTop = scrollTop;
        }
    });

    // Gán lại sự kiện
    requestAnimationFrame(() => {
        syncAreas.forEach(el => el.addEventListener('scroll', syncScroll));
    });
};

onMounted(() => {
    const syncAreas = document.querySelectorAll('.sync-scroll-area');
    syncAreas.forEach(el => {
        el.addEventListener('scroll', syncScroll);
    });
});

onUnmounted(() => {
    const syncAreas = document.querySelectorAll('.sync-scroll-area');
    syncAreas.forEach(el => {
        el.removeEventListener('scroll', syncScroll);
    });
});
// ----------------------------------------

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const sortedStudents = computed(() => {
    if (!props.gradebook || !props.gradebook.data) return [];
    
    let data = [...props.gradebook.data]; 
    return data.sort((a, b) => {
        let valA = a[sortKey.value];
        let valB = b[sortKey.value];
        if (sortKey.value === 'name') {
            return sortOrder.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
        }
        return sortOrder.value === 'asc' ? valA - valB : valB - valA;
    });
});

const form = useForm({
    late_policy_type: props.settings.late_policy_type,
    late_penalty_percent: props.settings.late_penalty_percent,
});

const saveSettings = () => {
    form.post(route('gradebook.settings', props.team.id), {
        onSuccess: () => {
            showSettingsModal.value = false;
        }
    });
};
</script>

<style scoped>
/* Ẩn thanh cuộn cho cột Tên */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Thanh cuộn ngang đẹp cho Quiz và Bài tập */
.custom-scrollbar::-webkit-scrollbar {
    height: 8px; /* Chiều cao thanh ngang */
    width: 8px;  /* Chiều rộng thanh dọc */
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8; 
}
</style>