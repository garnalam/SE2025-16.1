<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    team: Object,
    headers: Object, // Chứa list quizzes và assignments
    students: Array, // Chứa list học sinh và điểm
    isTeacher: Boolean,
});

// Hàm helper để tô màu điểm số
const getScoreColor = (score, max = 10) => {
    if (score === null || score === undefined) return 'text-gray-400'; // Chưa làm
    const percentage = (score / max) * 10;
    if (percentage >= 8) return 'text-green-600 font-bold';
    if (percentage >= 5) return 'text-blue-600';
    return 'text-red-600';
};
</script>

<template>
    <AppLayout title="Sổ điểm">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sổ điểm - {{ team.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg overflow-x-auto">
                    
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 uppercase text-xs leading-normal text-gray-600">
                                <th class="py-3 px-4 text-left border-r sticky left-0 bg-gray-100 z-10" rowspan="2">
                                    Học sinh
                                </th>

                                <th :colspan="headers.quizzes.length + 1" class="py-3 px-4 text-center border-r bg-blue-50 text-blue-700 font-bold">
                                    Điểm Quiz (40%)
                                </th>

                                <th :colspan="headers.assignments.length + 1" class="py-3 px-4 text-center border-r bg-purple-50 text-purple-700 font-bold">
                                    Điểm Bài tập (60%)
                                </th>

                                <th class="py-3 px-4 text-center w-24" rowspan="2">
                                    Trung bình
                                </th>
                            </tr>

                            <tr class="bg-gray-50 text-xs text-gray-600 border-b">
                                <th v-for="quiz in headers.quizzes" :key="'h-q-'+quiz.id" class="py-2 px-2 border-r min-w-[80px] text-center">
                                    <div class="truncate w-20" :title="quiz.title">{{ quiz.title }}</div>
                                </th>
                                <th class="py-2 px-2 border-r font-bold bg-blue-50 text-blue-800 text-center">TB Quiz</th>

                                <th v-for="assign in headers.assignments" :key="'h-a-'+assign.id" class="py-2 px-2 border-r min-w-[80px] text-center">
                                    <div class="truncate w-20" :title="assign.title">{{ assign.title }}</div>
                                </th>
                                <th class="py-2 px-2 border-r font-bold bg-purple-50 text-purple-800 text-center">TB Bài tập</th>
                            </tr>
                        </thead>

                        <tbody class="text-gray-600 text-sm font-light">
                            <tr v-for="student in students" :key="student.id" class="border-b border-gray-200 hover:bg-gray-100">
                                
                                <td class="py-3 px-4 text-left border-r whitespace-nowrap sticky left-0 bg-white group-hover:bg-gray-100 font-medium">
                                    <div class="flex items-center">
                                        <img :src="student.avatar" class="w-8 h-8 rounded-full mr-2" />
                                        <span>{{ student.name }}</span>
                                    </div>
                                </td>

                                <td v-for="quiz in headers.quizzes" :key="'q-'+quiz.id" class="py-3 px-2 text-center border-r">
                                    <span :class="getScoreColor(student.quiz_grades[quiz.id], quiz.max_points)">
                                        {{ student.quiz_grades[quiz.id] !== null ? student.quiz_grades[quiz.id] : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center border-r bg-blue-50 font-semibold">
                                    {{ student.quiz_avg }}
                                </td>

                                <td v-for="assign in headers.assignments" :key="'a-'+assign.id" class="py-3 px-2 text-center border-r">
                                    <span :class="getScoreColor(student.assign_grades[assign.id], assign.max_points)">
                                        {{ student.assign_grades[assign.id] !== null ? student.assign_grades[assign.id] : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center border-r bg-purple-50 font-semibold">
                                    {{ student.assign_avg }}
                                </td>

                                <td class="py-3 px-4 text-center font-bold text-lg">
                                    <span :class="student.overall_avg >= 5 ? 'text-green-600' : 'text-red-600'">
                                        {{ student.overall_avg }}
                                    </span>
                                </td>

                            </tr>
                            
                            <tr v-if="students.length === 0">
                                <td :colspan="headers.quizzes.length + headers.assignments.length + 5" class="text-center py-4">
                                    Chưa có dữ liệu học sinh
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </AppLayout>
</template>