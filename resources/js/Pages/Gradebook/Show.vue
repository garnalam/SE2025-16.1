<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    team: Object,
    isTeacher: Boolean,
    rawPosts: Array,      
    gradebook: Array,     
    currentWeights: Object,
    studentData: Object   
});

// --- STATE ---
const showSettings = ref(false);
const sortField = ref('name'); 
const sortDirection = ref('asc');
const currentPage = ref(1);
const itemsPerPage = 20;

const examSearchQuery = ref('');

// Refs sync scroll (Thêm colAttdRef cho cột chuyên cần)
const colNameRef = ref(null);
const colAttdRef = ref(null); 
const colQuizRef = ref(null);
const colAssignRef = ref(null);
const colSummaryRef = ref(null);

const form = useForm({
    weights: props.currentWeights ? { ...props.currentWeights } : { attendance: 10, regular: 50, midterm: 20, final: 20 },
    midterm_id: props.rawPosts?.find(p => p.grading_type === 'midterm')?.id || null,
    final_id: props.rawPosts?.find(p => p.grading_type === 'final')?.id || null,
});

// --- COMPUTED ---
const quizColumns = computed(() => props.rawPosts ? props.rawPosts.filter(p => p.type === 'quiz' && p.grading_type === 'regular') : []);
const assignColumns = computed(() => props.rawPosts ? props.rawPosts.filter(p => p.type === 'assignment' && p.grading_type === 'regular') : []);

// Lọc bài kiểm tra trong Modal
const filteredPosts = computed(() => {
    if (!props.rawPosts) return [];
    if (!examSearchQuery.value) return props.rawPosts;
    const query = examSearchQuery.value.toLowerCase();
    return props.rawPosts.filter(post => post.title.toLowerCase().includes(query));
});

// Sort
const sortedGradebook = computed(() => {
    if (!props.gradebook) return [];
    let data = [...props.gradebook];
    data.sort((a, b) => {
        let valA, valB;
        if (sortField.value === 'name') {
            valA = a.student.name.split(' ').pop().toLowerCase();
            valB = b.student.name.split(' ').pop().toLowerCase();
        } else {
            valA = a[sortField.value];
            valB = b[sortField.value];
        }
        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });
    return data;
});

// Pagination
const totalPages = computed(() => Math.ceil(sortedGradebook.value.length / itemsPerPage));
const paginatedGradebook = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedGradebook.value.slice(start, end);
});

// --- METHODS ---
const handleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    currentPage.value = 1;
};

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        if(colNameRef.value) colNameRef.value.scrollTop = 0;
    }
};

const getScoreColor = (score) => {
    if (score === undefined || score === null) return 'text-slate-500';
    if (score >= 8.5) return 'text-emerald-400 font-bold';
    else if (score >= 6.5) return 'text-cyan-300';
    else if (score >= 5) return 'text-yellow-400';
    return 'text-rose-500';
};

const setExamType = (postId, type) => {
    if (type === 'midterm') {
        form.midterm_id = postId;
        if (form.final_id === postId) form.final_id = null;
    } else if (type === 'final') {
        form.final_id = postId;
        if (form.midterm_id === postId) form.midterm_id = null;
    } else {
        if (form.midterm_id === postId) form.midterm_id = null;
        if (form.final_id === postId) form.final_id = null;
    }
};

const saveSettings = () => {
    form.post(route('gradebook.updateSettings', props.team.id), {
        onSuccess: () => showSettings.value = false
    });
};

const syncScroll = (e) => {
    const scrollTop = e.target.scrollTop;
    if (colNameRef.value && e.target !== colNameRef.value) colNameRef.value.scrollTop = scrollTop;
    if (colAttdRef.value && e.target !== colAttdRef.value) colAttdRef.value.scrollTop = scrollTop;
    if (colQuizRef.value && e.target !== colQuizRef.value) colQuizRef.value.scrollTop = scrollTop;
    if (colAssignRef.value && e.target !== colAssignRef.value) colAssignRef.value.scrollTop = scrollTop;
    if (colSummaryRef.value && e.target !== colSummaryRef.value) colSummaryRef.value.scrollTop = scrollTop;
};
</script>

<template>
    <AppLayout :title="`Bảng điểm - ${team.name}`">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-exo font-bold text-xl text-cyan-400 uppercase tracking-wider">
                    QUẢN LÝ ĐIỂM SỐ
                </h2>
                <Link :href="`/team/${team.id}`" class="text-xs font-mono text-slate-400 hover:text-white transition">
                    [ Quay lại lớp học ]
                </Link>
            </div>
        </template>

        <div class="max-w-[98%] mx-auto py-6">
            
            <div v-if="isTeacher" class="mb-4 flex justify-between items-end">
                <div class="text-xs font-mono text-slate-500">
                    Hiển thị {{ paginatedGradebook.length }} / {{ gradebook.length }} học sinh
                    <span v-if="totalPages > 1">(Trang {{ currentPage }}/{{ totalPages }})</span>
                </div>
                <button @click="showSettings = true" 
                    class="flex items-center gap-2 px-4 py-2 bg-slate-800 border border-cyan-500/30 hover:bg-cyan-900/20 text-cyan-400 text-xs uppercase font-bold transition rounded shadow-lg shadow-cyan-500/10 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:rotate-90 transition duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Cài đặt & Trọng số
                </button>
            </div>
            
            <div v-if="isTeacher" class="flex flex-col gap-4">
                <div class="bg-slate-900 border border-slate-700 rounded-lg shadow-2xl overflow-hidden flex flex-row h-auto max-h-[70vh] min-h-[300px]">
                    
                    <div class="w-64 shrink-0 flex flex-col border-r border-slate-700 bg-slate-900 z-20 shadow-[5px_0_10px_rgba(0,0,0,0.3)]">
                        <div class="h-14 shrink-0 flex items-center px-4 bg-slate-950 border-b border-slate-700 text-xs font-mono uppercase text-slate-400 cursor-pointer hover:text-white select-none"
                             @click="handleSort('name')">
                            Họ và Tên <span v-if="sortField === 'name'" class="ml-2 text-cyan-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        </div>
                        <div ref="colNameRef" @scroll="syncScroll" class="flex-1 overflow-hidden hover:overflow-y-auto custom-scrollbar no-scrollbar-visual">
                            <div v-for="row in paginatedGradebook" :key="row.student.id" class="h-14 flex items-center px-4 border-b border-slate-800 group hover:bg-slate-800 transition">
                                <img :src="row.student.profile_photo_url" class="h-8 w-8 rounded-full mr-3 border border-slate-600">
                                <span class="text-sm font-medium text-slate-200 truncate">{{ row.student.name }}</span>
                            </div>
                             <div v-if="paginatedGradebook.length === 0" class="h-full flex items-center justify-center text-slate-600 italic text-xs">Chưa có HS</div>
                        </div>
                    </div>

                    <div class="w-24 shrink-0 flex flex-col border-r border-slate-700 bg-slate-900 z-10">
                         <div class="h-14 shrink-0 flex flex-col items-center justify-center bg-slate-950 border-b border-slate-700 text-xs font-mono uppercase text-emerald-500 font-bold select-none">
                            Chuyên Cần
                        </div>
                        <div ref="colAttdRef" @scroll="syncScroll" class="flex-1 overflow-hidden hover:overflow-y-auto custom-scrollbar no-scrollbar-visual bg-emerald-950/5">
                            <div v-for="row in paginatedGradebook" :key="row.student.id" class="h-14 flex flex-col items-center justify-center border-b border-slate-800 hover:bg-slate-800/30 transition text-sm font-mono">
                                <div class="text-slate-300 font-bold">{{ row.attendance_stats.attended }}/{{ row.attendance_stats.total }}</div>
                                <div class="text-[10px] text-emerald-400">{{ row.attendance_stats.percent }}%</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="quizColumns.length > 0" class="flex-1 flex flex-col border-r border-slate-700 overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-1 text-[9px] font-bold text-indigo-500/20 uppercase pointer-events-none z-0">Quiz Zone</div>
                        <div class="flex-1 overflow-x-auto custom-scrollbar flex flex-col">
                            <div class="flex sticky top-0 z-10 bg-slate-950 border-b border-slate-700 h-14 shrink-0 min-w-full w-fit">
                                <div v-for="col in quizColumns" :key="col.id" 
                                    class="w-28 shrink-0 flex flex-col justify-center items-center px-2 text-center border-r border-slate-800/50 bg-indigo-950/10">
                                    <div class="truncate w-full text-xs font-mono text-indigo-400" :title="col.title">{{ col.title }}</div>
                                    <div class="text-[9px] text-slate-600">Max: {{ col.max_points }}</div>
                                </div>
                            </div>
                            <div ref="colQuizRef" @scroll="syncScroll" class="flex-1 overflow-y-auto custom-scrollbar no-scrollbar-visual bg-indigo-950/5 min-w-full w-fit">
                                 <div v-for="row in paginatedGradebook" :key="row.student.id" class="flex h-14 border-b border-slate-800 hover:bg-slate-800/30 transition">
                                    <div v-for="col in quizColumns" :key="col.id" 
                                        class="w-28 shrink-0 flex items-center justify-center border-r border-slate-800/30 text-sm font-mono text-indigo-200">
                                        {{ row.details[col.id] !== undefined ? row.details[col.id] : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="assignColumns.length > 0" class="flex-1 flex flex-col border-r border-slate-700 overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-1 text-[9px] font-bold text-emerald-500/20 uppercase pointer-events-none z-0">Assignment Zone</div>
                         <div class="flex-1 overflow-x-auto custom-scrollbar flex flex-col">
                            <div class="flex sticky top-0 z-10 bg-slate-950 border-b border-slate-700 h-14 shrink-0 min-w-full w-fit">
                                <div v-for="col in assignColumns" :key="col.id" 
                                    class="min-w-[6rem] flex-1 flex flex-col justify-center items-center px-2 text-center border-r border-slate-800/50 bg-emerald-950/10">
                                    <div class="truncate w-full text-xs font-mono text-emerald-400" :title="col.title">{{ col.title }}</div>
                                    <div class="text-[9px] text-slate-600">Max: {{ col.max_points }}</div>
                                </div>
                            </div>
                            <div ref="colAssignRef" @scroll="syncScroll" class="flex-1 overflow-y-auto custom-scrollbar no-scrollbar-visual bg-emerald-950/5 min-w-full w-fit">
                                 <div v-for="row in paginatedGradebook" :key="row.student.id" class="flex h-14 border-b border-slate-800 hover:bg-slate-800/30 transition">
                                    <div v-for="col in assignColumns" :key="col.id" 
                                        class="min-w-[6rem] flex-1 flex items-center justify-center border-r border-slate-800/30 text-sm font-mono text-emerald-200">
                                        {{ row.details[col.id] !== undefined ? row.details[col.id] : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-[300px] shrink-0 flex flex-col border-l border-slate-700 bg-slate-900 z-20 shadow-[-5px_0_10px_rgba(0,0,0,0.3)]">
                        <div class="h-14 shrink-0 flex bg-slate-950 border-b border-slate-700 select-none">
                            <div class="w-20 flex items-center justify-center border-r border-slate-800 cursor-pointer hover:text-white text-xs font-mono uppercase text-slate-400" @click="handleSort('midterm_score')">
                                Giữa Kỳ <span v-if="sortField === 'midterm_score'" class="ml-1 text-cyan-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </div>
                            <div class="w-20 flex items-center justify-center border-r border-slate-800 cursor-pointer hover:text-white text-xs font-mono uppercase text-slate-400" @click="handleSort('final_score')">
                                Cuối Kỳ <span v-if="sortField === 'final_score'" class="ml-1 text-cyan-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </div>
                            <div class="flex-1 flex items-center justify-center font-bold text-white cursor-pointer bg-cyan-950/30 text-sm uppercase tracking-wider" @click="handleSort('overall_avg')">
                                TỔNG <span v-if="sortField === 'overall_avg'" class="ml-1 text-cyan-400">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            </div>
                        </div>
                         <div ref="colSummaryRef" @scroll="syncScroll" class="flex-1 overflow-hidden hover:overflow-y-auto custom-scrollbar">
                            <div v-for="row in paginatedGradebook" :key="row.student.id" class="h-14 flex border-b border-slate-800 group hover:bg-slate-800 transition">
                                <div class="w-20 flex items-center justify-center border-r border-slate-800 text-sm font-mono text-yellow-500 font-bold">{{ row.midterm_score }}</div>
                                <div class="w-20 flex items-center justify-center border-r border-slate-800 text-sm font-mono text-orange-500 font-bold">{{ row.final_score }}</div>
                                <div class="flex-1 flex items-center justify-center text-lg font-bold font-mono bg-cyan-950/20" :class="getScoreColor(row.overall_avg)">
                                    {{ row.overall_avg }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-2">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                        class="px-3 py-1 bg-slate-800 border border-slate-700 rounded text-xs text-slate-400 disabled:opacity-50 hover:bg-slate-700 transition">
                        &laquo; Trước
                    </button>
                    <button v-for="page in totalPages" :key="page" @click="changePage(page)"
                        class="px-3 py-1 rounded text-xs font-mono border transition"
                        :class="page === currentPage ? 'bg-cyan-600 border-cyan-500 text-white font-bold' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700'">
                        {{ page }}
                    </button>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="px-3 py-1 bg-slate-800 border border-slate-700 rounded text-xs text-slate-400 disabled:opacity-50 hover:bg-slate-700 transition">
                        Sau &raquo;
                    </button>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1 space-y-6">
                    <div class="bg-slate-900 border border-cyan-500/30 rounded-lg p-6 text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-cyan-500/5 group-hover:bg-cyan-500/10 transition duration-500"></div>
                        <h3 class="text-slate-400 font-exo uppercase tracking-widest text-sm mb-2">Điểm Trung Bình</h3>
                        <div class="text-6xl font-bold font-mono text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]">
                            {{ studentData.overall_avg }}
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-2 gap-y-3 gap-x-1 text-[10px] font-mono text-slate-500 text-left">
                            <div>Thường xuyên: <span class="text-white font-bold">{{ studentData.regular_avg }}</span></div>
                            <div>Giữa kỳ: <span class="text-yellow-400 font-bold">{{ studentData.midterm_score }}</span></div>
                            <div>Cuối kỳ: <span class="text-orange-400 font-bold">{{ studentData.final_score }}</span></div>
                            <div>Chuyên cần: 
                                <span class="text-emerald-400 font-bold">
                                    {{ studentData.attendance_stats.percent }}% 
                                    ({{ studentData.attendance_stats.attended }}/{{ studentData.attendance_stats.total }})
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase mb-3 border-b border-slate-700 pb-2">Cấu trúc điểm</h4>
                        <ul class="space-y-2 text-xs font-mono text-slate-300">
                            <li class="flex justify-between"><span>Chuyên cần:</span> <span class="text-cyan-400">{{ studentData.current_weights.attendance }}%</span></li>
                            <li class="flex justify-between"><span>Thường xuyên:</span> <span class="text-cyan-400">{{ studentData.current_weights.regular }}%</span></li>
                            <li class="flex justify-between"><span>Giữa kỳ:</span> <span class="text-cyan-400">{{ studentData.current_weights.midterm }}%</span></li>
                            <li class="flex justify-between"><span>Cuối kỳ:</span> <span class="text-cyan-400">{{ studentData.current_weights.final }}%</span></li>
                        </ul>
                    </div>
                </div>

                <div class="md:col-span-3 space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                        <div class="bg-slate-950 px-4 py-3 border-b border-slate-800">
                            <h3 class="text-indigo-400 font-bold font-exo uppercase">Chi tiết điểm số</h3>
                        </div>
                        <div class="divide-y divide-slate-800/50">
                            <div v-for="post in studentData.posts_list" :key="post.id" class="px-4 py-3 flex justify-between items-center hover:bg-slate-800/30 transition">
                                <div>
                                    <div class="text-slate-300 font-mono text-sm">{{ post.title }}</div>
                                    <div class="flex gap-2 mt-1">
                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-500 uppercase">{{ post.type }}</span>
                                        <span v-if="post.grading_type !== 'regular'" class="text-[9px] px-1.5 py-0.5 rounded bg-yellow-900/30 text-yellow-500 uppercase font-bold">{{ post.grading_type }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span :class="['font-bold font-mono text-lg', getScoreColor(post.score)]">
                                        {{ post.score }} <span class="text-xs text-slate-600 font-normal">/ {{ post.max_points }}</span>
                                    </span>
                                </div>
                            </div>
                             <div v-if="studentData.posts_list.length === 0" class="p-4 text-center text-xs text-slate-600">Chưa có bài tập nào</div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogModal :show="showSettings" @close="showSettings = false" maxWidth="4xl">
                <template #title>
                    <span class="text-cyan-400 uppercase font-bold">Cấu hình Bảng điểm</span>
                </template>
                <template #content>
                    <div class="space-y-6 text-slate-300">
                        <div class="bg-slate-950/50 p-4 rounded-lg border border-slate-800">
                            <h3 class="font-bold text-sm text-cyan-500 uppercase mb-3">1. Cấu hình Trọng số (%)</h3>
                            <div class="grid grid-cols-4 gap-4">
                                <div><label class="block text-xs mb-1">Chuyên cần</label><TextInput v-model="form.weights.attendance" type="number" class="w-full bg-slate-900 text-white text-center h-10 font-bold" /></div>
                                <div><label class="block text-xs mb-1">Thường xuyên</label><TextInput v-model="form.weights.regular" type="number" class="w-full bg-slate-900 text-white text-center h-10 font-bold" /></div>
                                <div><label class="block text-xs mb-1">Giữa kỳ</label><TextInput v-model="form.weights.midterm" type="number" class="w-full bg-slate-900 text-white text-center h-10 font-bold" /></div>
                                <div><label class="block text-xs mb-1">Cuối kỳ</label><TextInput v-model="form.weights.final" type="number" class="w-full bg-slate-900 text-white text-center h-10 font-bold" /></div>
                            </div>
                            <div class="text-[10px] text-rose-400 text-center mt-2" v-if="(parseInt(form.weights.attendance)+parseInt(form.weights.regular)+parseInt(form.weights.midterm)+parseInt(form.weights.final)) !== 100">
                                * Tổng trọng số phải bằng 100%
                            </div>
                        </div>
                        <div class="bg-slate-950/50 p-4 rounded-lg border border-slate-800 flex flex-col h-[400px]">
                            <div class="flex items-center justify-between mb-3 sticky top-0 bg-transparent z-10">
                                <h3 class="font-bold text-sm text-indigo-500 uppercase">2. Phân Loại Bài Kiểm Tra</h3>
                                <div class="relative w-64">
                                    <input type="text" v-model="examSearchQuery" placeholder="Tìm tên bài..." 
                                           class="w-full bg-slate-900 border-slate-700 rounded text-xs py-1 pl-2 text-white focus:ring-1 focus:ring-cyan-500">
                                </div>
                            </div>
                            <div class="flex-1 overflow-y-auto custom-scrollbar border border-slate-700 rounded bg-slate-900/50">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-900 border-b border-slate-700 text-slate-500 sticky top-0">
                                        <tr>
                                            <th class="py-2 pl-3">Tên bài</th>
                                            <th class="py-2 text-center w-20">Thường</th>
                                            <th class="py-2 text-center w-20">Giữa kỳ</th>
                                            <th class="py-2 text-center w-20">Cuối kỳ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800">
                                        <tr v-for="post in filteredPosts" :key="post.id" class="hover:bg-white/5">
                                            <td class="py-2 pl-3 font-mono text-slate-300" :title="post.title">{{ post.title }} <span class="text-[9px] text-slate-600">({{ post.type }})</span></td>
                                            <td class="text-center"><input type="radio" :name="'type_' + post.id" :checked="form.midterm_id !== post.id && form.final_id !== post.id" @change="setExamType(post.id, 'regular')" class="bg-slate-900 border-slate-700 text-slate-500 focus:ring-0 cursor-pointer"></td>
                                            <td class="text-center"><input type="radio" :name="'type_' + post.id" :checked="form.midterm_id === post.id" @change="setExamType(post.id, 'midterm')" class="bg-slate-900 border-slate-700 text-yellow-500 focus:ring-0 cursor-pointer"></td>
                                            <td class="text-center"><input type="radio" :name="'type_' + post.id" :checked="form.final_id === post.id" @change="setExamType(post.id, 'final')" class="bg-slate-900 border-slate-700 text-orange-500 focus:ring-0 cursor-pointer"></td>
                                        </tr>
                                        <tr v-if="filteredPosts.length === 0"><td colspan="4" class="py-4 text-center text-slate-600 italic">Không tìm thấy bài tập nào</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <SecondaryButton @click="showSettings = false">Hủy</SecondaryButton>
                    <PrimaryButton class="ml-2 bg-cyan-600 hover:bg-cyan-500" @click="saveSettings">Lưu Cấu Hình</PrimaryButton>
                </template>
            </DialogModal>

        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

.no-scrollbar-visual::-webkit-scrollbar { display: none; }
.no-scrollbar-visual { -ms-overflow-style: none; scrollbar-width: none; }
</style>