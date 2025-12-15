<script setup>
import { ref, watch } from 'vue';
import { useForm, Link , router} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import VueMultiselect from 'vue-multiselect';
import Pagination from '@/Components/Pagination.vue'; 
import ActionMessage from '@/Components/ActionMessage.vue'; 

const props = defineProps({
    post: Object,
    quizQuestions: Array,
    availableQuestions: Object, 
    subjects: Array,
    tags: Array,
    templates: Array,
    students: Array,
    currentSettings: Object,
    filters: Object, 
});

const currentTab = ref(props.currentSettings.quiz_mode || 'manual');

const getSummary = (options) => {
    return options.map(op => op.option_text.substring(0, 30) + (op.is_correct ? ' [TRUE]' : '')).join(' | ');
};

const preselectedSubject = props.subjects.find(s => s.id === props.post.random_quiz_settings?.subject_id) || null;
const preselectedTags = props.tags.filter(t => props.post.random_quiz_settings?.tags?.includes(t.id)) || [];
const preselectedStudents = props.students.filter(s => props.currentSettings.assignedUserIds.includes(s.id)) || [];

const generateForm = useForm({
    settings: {
        subject_id: preselectedSubject, 
        tags: preselectedTags,
        count: props.post.random_quiz_settings?.count || 10,
        shuffle: props.currentSettings.shuffle,
        points: props.currentSettings.points,
        is_proctored: props.currentSettings.is_proctored || false,
    },
    assignment: {
        assign_mode: props.currentSettings.assign_mode,
        assigned_users: preselectedStudents, 
    }
});

const manualSettingsForm = useForm({
    settings: {
        shuffle: props.currentSettings.shuffle,
        is_proctored: props.currentSettings.is_proctored || false,
    },
    assignment: {
        assign_mode: props.currentSettings.assign_mode,
        assigned_users: preselectedStudents,
    }
});

const submitManualSettings = () => {
    const payload = {
        ...manualSettingsForm.data(),
        assignment: {
            ...manualSettingsForm.assignment,
            assigned_users: manualSettingsForm.assignment.assigned_users.map(s => s.id),
        }
    };
    useForm(payload).post(route('post.quiz.saveManual', props.post.id), {
        preserveScroll: false,
        onSuccess: () => {
            currentTab.value = 'manual';
        }
    });
};

const manualFilterForm = ref({
    filter_search: props.filters.filter_search || '',
    filter_subject: props.subjects.find(s => s.id == props.filters.filter_subject) || null,
    filter_tags: props.tags.filter(t => props.filters.filter_tags?.includes(t.id)) || [],
});

watch(manualFilterForm, (newFilters) => {
    const payload = {
        filter_search: newFilters.filter_search,
        filter_subject: newFilters.filter_subject ? newFilters.filter_subject.id : null,
        filter_tags: newFilters.filter_tags.map(t => t.id),
    };
    router.get(route('post.quiz.manage', props.post.id), payload, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, { deep: true });

const templateForm = useForm({ name: '', settings: {} });
const selectedTemplate = ref(null);

watch(selectedTemplate, (newTemplate) => {
    if (newTemplate) {
        const settings = newTemplate.settings;
        generateForm.settings.subject_id = props.subjects.find(s => s.id === settings.subject_id) || null;
        generateForm.settings.tags = props.tags.filter(t => settings.tags?.includes(t.id)) || [];
        generateForm.settings.is_proctored = settings.is_proctored || false;
        generateForm.settings.count = settings.count;
        generateForm.settings.shuffle = settings.shuffle;
        generateForm.settings.points = settings.points;
    }
});

const preparePayload = () => {
    return {
        settings: {
            ...generateForm.settings,
            subject_id: generateForm.settings.subject_id ? generateForm.settings.subject_id.id : null,
            tags: generateForm.settings.tags.map(t => t.id),
        },
        assignment: {
            ...generateForm.assignment,
            assigned_users: generateForm.assignment.assigned_users.map(s => s.id),
        }
    };
};

const submitGenerate = () => {
    const payload = preparePayload();
    useForm(payload).post(route('post.quiz.generate', props.post.id), {
        preserveScroll: false,
        onSuccess: () => { currentTab.value = 'random'; }
    });
};

const saveAsTemplate = () => {
    const name = prompt('Template Designation Name:');
    if (name) {
        templateForm.name = name;
        templateForm.settings = preparePayload().settings; 
        templateForm.post(route('quiz-templates.store'), {
            preserveScroll: true,
            onSuccess: () => templateForm.reset(),
        });
    }
};

const deleteTemplate = (id) => {
    if (confirm('CONFIRM DELETION: Remove this configuration template?')) {
        useForm({}).delete(route('quiz-templates.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedTemplate.value?.id === id) {
                    selectedTemplate.value = null;
                }
            }
        });
    }
};
</script>

<template>
    <AppLayout :title="'Manage Quiz: ' + post.title">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono text-purple-400 uppercase tracking-widest">Assessment Control</div>
                        <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                            {{ post.title || 'Quản lý Quiz' }}
                        </h2>
                    </div>
                </div>
                <Link :href="route('topics.show', post.topic_id)" class="group flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg text-xs font-bold text-slate-300 hover:text-white uppercase tracking-wider transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Return
                </Link>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="text-sm font-mono font-bold">{{ $page.props.flash.success }}</span>
            </div>
            
            <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl">
                <p class="font-bold text-sm font-exo uppercase mb-2">System Errors Detected:</p>
                <ul class="list-disc list-inside text-xs font-mono">
                    <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="mb-8 border-b border-slate-800">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="currentTab = 'manual'"
                        :class="[currentTab === 'manual' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-700']"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider font-exo transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        1. Cài đặt chung
                    </button>
                    <button
                        @click="currentTab = 'random'"
                        :class="[currentTab === 'random' ? 'border-cyan-500 text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-300 hover:border-slate-700']"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider font-exo transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        2. Cài đặt nâng cao
                    </button>
                </nav>
            </div>

            <!-- TAB 1: MANUAL -->
            <div v-if="currentTab === 'manual'" class="space-y-8 animate-fade-in-up"> 
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 h-full">
                    
                    <!-- Left: Current Quiz Questions -->
                    <div class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col h-[600px]">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-800 pb-2 flex justify-between items-center">
                            <span>Câu hỏi đã thêm ({{ quizQuestions.length }})</span>
                            <span class="text-[10px] font-mono text-cyan-500">READY_STATE</span>
                        </h3>
                        <ul class="space-y-2 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            <li v-if="quizQuestions.length === 0" class="py-12 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/30">
                                <div class="text-slate-500 font-mono text-xs">>> NO MODULES MOUNTED.</div>
                            </li>
                            <li v-for="question in quizQuestions" :key="'quiz-' + question.id" class="group flex items-start justify-between p-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-600 transition">
                                <div class="flex-1 pr-4">
                                    <p class="text-sm font-bold text-slate-200 mb-1 line-clamp-2">{{ question.question_text }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono truncate border-l border-slate-700 pl-2">
                                        {{ getSummary(question.options) }}
                                    </p>
                                </div>
                                <Link as="button" method="delete" :data="{ question_id: question.id }" :href="route('post.quiz.detach', post.id)" class="p-2 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg transition" preserve-scroll title="Detach">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Right: Question Bank -->
                    <div class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col h-[600px]">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-800 pb-2 flex justify-between items-center">
                            <span>Ngân hàng câu hỏi</span>
                            <span class="text-[10px] font-mono text-slate-500">{{ availableQuestions.total }} CÂU HỎI ĐƯỢC TÌM THẤY</span>
                        </h3>

                        <div class="space-y-3 mb-4 bg-slate-900/50 p-4 rounded-xl border border-white/5">
                            <VueMultiselect v-model="manualFilterForm.filter_subject" :options="props.subjects" label="name" track-by="id" placeholder="Lọc theo môn học..." class="custom-multiselect" />
                            <VueMultiselect v-model="manualFilterForm.filter_tags" :options="props.tags" :multiple="true" label="name" track-by="id" placeholder="Lọc theo nhãn..." class="custom-multiselect" />
                            <TextInput v-model="manualFilterForm.filter_search" type="text" class="w-full text-xs font-mono bg-slate-950 border-slate-700" placeholder="Tìm câu hỏi theo tên..." />
                        </div>

                        <ul class="space-y-2 overflow-y-auto custom-scrollbar flex-1 pr-2">
                            <li v-if="availableQuestions.data.length === 0" class="py-12 text-center text-slate-500 font-mono text-xs">
                                >> QUERY RETURNED 0 RESULTS.
                            </li>
                            <li v-for="question in availableQuestions.data" :key="'bank-' + question.id" class="group flex items-start justify-between p-3 bg-slate-900 border border-slate-800 rounded-xl hover:border-cyan-500/30 transition">
                                <div class="flex-1 pr-4">
                                    <div class="mb-2 flex flex-wrap gap-1">
                                        <span v-if="question.subject" class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-indigo-900/30 text-indigo-400 border border-indigo-500/20 rounded">{{ question.subject.name }}</span>
                                        <span v-for="tag in question.tags" :key="tag.id" class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700 rounded">{{ tag.name }}</span>
                                    </div>
                                    <p class="text-sm font-bold text-slate-200 mb-1 line-clamp-2">{{ question.question_text }}</p>
                                    <img v-if="question.image_path" :src="'/storage/' + question.image_path" class="mt-2 w-full max-w-[100px] h-auto rounded border border-slate-700 opacity-70">
                                </div>
                                <Link as="button" method="post" :data="{ question_id: question.id }" :href="route('post.quiz.attach', post.id)" class="p-2 bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-white rounded-lg transition border border-emerald-500/20" preserve-scroll title="Attach">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </Link>
                            </li>
                        </ul>
                        
                        <div class="pt-4 border-t border-slate-800">
                            <Pagination :links="availableQuestions.links" />
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitManualSettings" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2 mb-4">
                            <span class="w-1.5 h-6 bg-cyan-500 rounded-full"></span> Configuration
                        </h3>
                        <label class="flex items-center p-3 bg-black/20 rounded-xl border border-white/5 hover:bg-black/30 transition cursor-pointer">
                            <Checkbox v-model:checked="manualSettingsForm.settings.shuffle" class="text-cyan-500 focus:ring-cyan-500" />
                            <span class="ms-3 text-sm text-slate-300 font-bold">ĐẢO CÂU HỎI </span>
                        </label>
                    </div>

                    <div class="p-4 bg-rose-900/10 border border-rose-500/20 rounded-xl">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox v-model:checked="manualSettingsForm.settings.is_proctored" class="text-rose-500 focus:ring-rose-500" />
                            <span class="ml-3 text-sm text-rose-400 font-bold uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                BẬT CHẾ ĐỘ GIÁM SÁT BÀI THI
                            </span>
                        </label>
                        <p class="text-[10px] text-rose-300/60 ml-8 mt-1 font-mono uppercase">
                            Ngăn chặn việc ALT + TAB hoặc chuyển đổi cửa sổ trong suốt thời gian làm bài thi.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2 mb-4">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> ĐỐI TƯỢNG LÀM BÀI
                        </h3>
                        <div class="space-y-2 mb-4">
                            <label class="flex items-center cursor-pointer p-3 bg-black/20 rounded-xl border border-white/5 hover:border-indigo-500/50 transition" :class="{'border-indigo-500 bg-indigo-500/10': manualSettingsForm.assignment.assign_mode === 'all'}">
                                <input type="radio" v-model="manualSettingsForm.assignment.assign_mode" value="all" class="form-radio text-indigo-500 bg-slate-900 border-slate-600 focus:ring-indigo-500">
                                <span class="ms-3 text-sm text-slate-300">Cả lớp ({{ students.length }} thành viên)</span>
                            </label>
                            <label class="flex items-center cursor-pointer p-3 bg-black/20 rounded-xl border border-white/5 hover:border-indigo-500/50 transition" :class="{'border-indigo-500 bg-indigo-500/10': manualSettingsForm.assignment.assign_mode === 'specific'}">
                                <input type="radio" v-model="manualSettingsForm.assignment.assign_mode" value="specific" class="form-radio text-indigo-500 bg-slate-900 border-slate-600 focus:ring-indigo-500">
                                <span class="ms-3 text-sm text-slate-300">Chỉ định học sinh</span>
                            </label>
                        </div>
                        <div v-if="manualSettingsForm.assignment.assign_mode === 'specific'">
                            <VueMultiselect
                                v-model="manualSettingsForm.assignment.assigned_users"
                                :options="props.students"
                                :multiple="true"
                                label="name" track-by="id"
                                placeholder="Chọn học sinh..."
                                class="custom-multiselect"
                            />
                            <InputError :message="manualSettingsForm.errors['assignment.assigned_users']" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end items-center pt-4 border-t border-slate-800">
                        <ActionMessage :on="manualSettingsForm.recentlySuccessful" class="me-4 text-emerald-400 font-mono text-xs uppercase tracking-widest">
                            CẤU HÌNH ĐÃ LƯU!
                        </ActionMessage>
                        <PrimaryButton :class="{ 'opacity-25': manualSettingsForm.processing }" :disabled="manualSettingsForm.processing">
                            HOÀN TẤT
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- TAB 2: RANDOM -->
            <div v-if="currentTab === 'random'" class="space-y-8 animate-fade-in-up">
                
                <div class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <h3 class="text-sm font-bold text-cyan-400 uppercase tracking-widest mb-6 font-exo flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                        Load config đã lưu
                    </h3>
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <InputLabel value="Chọn config" />
                            <VueMultiselect
                                v-model="selectedTemplate"
                                :options="props.templates"
                                label="name" track-by="id"
                                placeholder="Chọn config..."
                                class="custom-multiselect mt-1"
                            />
                        </div>
                        <SecondaryButton v-if="selectedTemplate" @click="deleteTemplate(selectedTemplate.id)" class="!bg-rose-500/10 !text-rose-500 hover:!bg-rose-500 hover:!text-white border-rose-500/20">
                            Delete
                        </SecondaryButton>
                    </div>
                </div>

                <form @submit.prevent="submitGenerate" class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl relative overflow-hidden space-y-8">
                    <!-- Decor -->
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-cyan-500 to-emerald-500"></div>

                    <div>
                        <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2 mb-6">
                            <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span> CÀI ĐẶT CHI TIẾT
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel value="1. MÔN HỌC (BẮT BUỘC)" />
                                <VueMultiselect
                                    v-model="generateForm.settings.subject_id"
                                    :options="props.subjects"
                                    label="name" track-by="id"
                                    placeholder="Chọn môn học"
                                    class="custom-multiselect mt-1"
                                />
                                <InputError :message="generateForm.errors['settings.subject_id']" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="2. TAGS CHO BÀI QUIZ (KHÔNG BẮT BUỘC)" />
                                <VueMultiselect
                                    v-model="generateForm.settings.tags"
                                    :options="props.tags"
                                    :multiple="true"
                                    label="name" track-by="id"
                                    placeholder="Chọn Tags"
                                    class="custom-multiselect mt-1"
                                />
                                <InputError :message="generateForm.errors['settings.tags']" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="3. Số câu hỏi cho bài QUIZ" />
                                <TextInput v-model="generateForm.settings.count" type="number" min="1" class="w-full mt-1 bg-slate-900 border-slate-700 focus:border-purple-500 focus:ring-purple-500/20 text-white font-mono" />
                                <InputError :message="generateForm.errors['settings.count']" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel value="4. Số điểm cho mỗi câu hỏi" />
                                <TextInput v-model="generateForm.settings.points" type="number" min="0.1" step="0.1" class="w-full mt-1 bg-slate-900 border-slate-700 focus:border-purple-500 focus:ring-purple-500/20 text-white font-mono" />
                                <InputError :message="generateForm.errors['settings.points']" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="mt-6 flex flex-wrap gap-4">
                            <label class="flex items-center cursor-pointer p-3 bg-black/20 rounded-xl border border-white/5 hover:bg-black/30 transition">
                                <Checkbox v-model:checked="generateForm.settings.shuffle" class="text-purple-500 focus:ring-purple-500" />
                                <span class="ms-3 text-sm text-slate-300 font-bold">Xáo trộn câu hỏi</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 bg-rose-900/10 border border-rose-500/20 rounded-xl">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox v-model:checked="generateForm.settings.is_proctored" class="text-rose-500 focus:ring-rose-500" />
                            <span class="ml-3 text-sm text-rose-400 font-bold uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                BẬT CHẾ ĐỘ GIÁM SÁT BÀI THI
                            </span>
                        </label>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2 mb-4">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Đối tượng làm bài 
                        </h3>
                        
                        <div class="space-y-2 mb-4">
                            <label class="flex items-center cursor-pointer p-3 bg-black/20 rounded-xl border border-white/5 hover:border-indigo-500/50 transition" :class="{'border-indigo-500 bg-indigo-500/10': generateForm.assignment.assign_mode === 'all'}">
                                <input type="radio" v-model="generateForm.assignment.assign_mode" value="all" class="form-radio text-indigo-500 bg-slate-900 border-slate-600 focus:ring-indigo-500">
                                <span class="ms-3 text-sm text-slate-300">Cả lớp ({{ students.length }} học sinh)</span>
                            </label>
                            <label class="flex items-center cursor-pointer p-3 bg-black/20 rounded-xl border border-white/5 hover:border-indigo-500/50 transition" :class="{'border-indigo-500 bg-indigo-500/10': generateForm.assignment.assign_mode === 'specific'}">
                                <input type="radio" v-model="generateForm.assignment.assign_mode" value="specific" class="form-radio text-indigo-500 bg-slate-900 border-slate-600 focus:ring-indigo-500">
                                <span class="ms-3 text-sm text-slate-300">Chỉ định học sinh</span>
                            </label>
                        </div>
                        
                        <div v-if="generateForm.assignment.assign_mode === 'specific'">
                            <VueMultiselect
                                v-model="generateForm.assignment.assigned_users"
                                :options="props.students"
                                :multiple="true"
                                label="name" track-by="id"
                                placeholder="Select Students..."
                                class="custom-multiselect"
                            />
                            <InputError :message="generateForm.errors['assignment.assigned_users']" class="mt-2" />
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center pt-4 border-t border-slate-800">
                        <div class="flex items-center gap-4">
                            <ActionMessage :on="templateForm.recentlySuccessful" class="text-cyan-400 font-mono text-xs uppercase">
                                Đã lưu config.
                            </ActionMessage>
                            <SecondaryButton type="button" @click="saveAsTemplate" :disabled="templateForm.processing">
                                Lưu cấu hình này dưới dạng mẫu
                            </SecondaryButton>
                        </div>

                        <div class="flex items-center gap-4">
                            <ActionMessage :on="generateForm.recentlySuccessful" class="text-emerald-400 font-mono text-xs uppercase">
                                Generation Complete.
                            </ActionMessage>
                            <PrimaryButton :class="{ 'opacity-25': generateForm.processing }" :disabled="generateForm.processing" class="!bg-gradient-to-r !from-purple-600 !to-indigo-600 hover:!from-purple-500 hover:!to-indigo-500">
                                HOÀN TẤT 
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </AppLayout>
</template>

<style>
/* Multiselect Dark Mode Overrides */
.custom-multiselect .multiselect__tags {
    background-color: #0f172a;
    border-color: #334155;
    color: white;
    border-radius: 0.5rem;
}
.custom-multiselect .multiselect__input, .custom-multiselect .multiselect__single {
    background-color: #0f172a;
    color: white;
}
.custom-multiselect .multiselect__content-wrapper {
    background-color: #1e293b;
    border-color: #334155;
}
.custom-multiselect .multiselect__option {
    background-color: #1e293b;
    color: #cbd5e1;
}
.custom-multiselect .multiselect__option--highlight {
    background-color: #06b6d4; /* Cyan */
    color: white;
}
.custom-multiselect .multiselect__option--selected {
    background-color: #334155;
    color: white;
}
.custom-multiselect .multiselect__tag {
    background-color: #06b6d4;
    color: #0f172a;
    font-weight: bold;
}
.custom-multiselect .multiselect__tag-icon:hover {
    background-color: #0891b2;
}
.custom-multiselect .multiselect__placeholder {
    color: #64748b;
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>