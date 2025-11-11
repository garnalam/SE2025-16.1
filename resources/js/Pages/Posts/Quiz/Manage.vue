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

// --- 1. NHẬN TẤT CẢ PROPS TỪ CONTROLLER ---
const props = defineProps({
    post: Object,
    quizQuestions: Array,
    availableQuestions: Object, // Đây là object Phân trang
    subjects: Array,
    tags: Array,
    templates: Array,
    students: Array,
    currentSettings: Object,
    filters: Object, 
});

// --- 2. STATE QUẢN LÝ TAB ---
const currentTab = ref(props.currentSettings.quiz_mode || 'manual');

// --- 3. FORM CHO TAB 1 (THỦ CÔNG) ---
const getSummary = (options) => {
    return options.map(op => op.option_text.substring(0, 30) + (op.is_correct ? ' (Đúng)' : '')).join(' | ');
};

// --- 4. PRE-FILL DATA (DÙNG CHUNG) ---
const preselectedSubject = props.subjects.find(s => s.id === props.post.random_quiz_settings?.subject_id) || null;
const preselectedTags = props.tags.filter(t => props.post.random_quiz_settings?.tags?.includes(t.id)) || [];
const preselectedStudents = props.students.filter(s => props.currentSettings.assignedUserIds.includes(s.id)) || [];

// --- 5. FORM CHO TAB 2 (NGẪU NHIÊN) ---
const generateForm = useForm({
    settings: {
        subject_id: preselectedSubject, 
        tags: preselectedTags,
        count: props.post.random_quiz_settings?.count || 10,
        shuffle: props.currentSettings.shuffle,
        points: props.currentSettings.points,
    },
    assignment: {
        assign_mode: props.currentSettings.assign_mode,
        assigned_users: preselectedStudents, 
    }
});

// --- 6. FORM CHO TAB 1 (CÀI ĐẶT THỦ CÔNG) ---
const manualSettingsForm = useForm({
    settings: {
        shuffle: props.currentSettings.shuffle,
    },
    assignment: {
        assign_mode: props.currentSettings.assign_mode,
        assigned_users: preselectedStudents,
    }
});

// Hàm submit cho Tab 1
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

// --- 7. FORM CHO BỘ LỌC CỦA TAB 1 ---
const manualFilterForm = ref({
    filter_search: props.filters.filter_search || '',
    filter_subject: props.subjects.find(s => s.id == props.filters.filter_subject) || null,
    filter_tags: props.tags.filter(t => props.filters.filter_tags?.includes(t.id)) || [],
});

// Tự động lọc khi form thay đổi
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

// --- 8. LOGIC CÒN LẠI CỦA TAB 2 (MẪU) ---
const templateForm = useForm({ name: '', settings: {} });
const selectedTemplate = ref(null);

watch(selectedTemplate, (newTemplate) => {
    if (newTemplate) {
        const settings = newTemplate.settings;
        generateForm.settings.subject_id = props.subjects.find(s => s.id === settings.subject_id) || null;
        generateForm.settings.tags = props.tags.filter(t => settings.tags?.includes(t.id)) || [];
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
    const name = prompt('Đặt tên cho mẫu cấu hình này (ví dụ: "Đề giữa kì - Toán 10"):');
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
    if (confirm('Bạn có chắc muốn xóa mẫu cấu hình này?')) {
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
    <AppLayout :title="'Quản lý Quiz: ' + post.title">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quản lý câu hỏi cho Quiz: <span class="font-bold">{{ post.title || 'Bài kiểm tra' }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Link :href="route('topics.show', post.topic_id)" class="text-sm text-blue-600 hover:text-blue-800 mb-4 inline-block">
                    &larr; Quay lại Topic
                </Link>
                
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <p class="font-bold">Đã có lỗi xảy ra:</p>
                    <ul class="list-disc list-inside">
                        <li v-for="(error, key) in $page.props.errors" :key="key">
                            {{ error }}
                        </li>
                    </ul>
                </div>

                <div class="mb-6">
                    <div class="sm:hidden">
                        <label for="tabs" class="sr-only">Select a tab</label>
                        <select id="tabs" v-model="currentTab" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="manual">Chọn thủ công</option>
                            <option value="random">Lấy ngẫu nhiên</option>
                        </select>
                    </div>
                    <div class="hidden sm:block">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button
                                    @click="currentTab = 'manual'"
                                    :class="[currentTab === 'manual' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    1. Chọn thủ công
                                </button>
                                <button
                                    @click="currentTab = 'random'"
                                    :class="[currentTab === 'random' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    2. Lấy ngẫu nhiên & Cài đặt
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>

                <div v-if="currentTab === 'manual'" class="space-y-6"> 
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="bg-white shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                                Câu hỏi trong bài Quiz ({{ quizQuestions.length }})
                            </h3>
                            <ul class="divide-y divide-gray-200">
                                <li v-if="quizQuestions.length === 0" class="py-4 text-gray-500">Chưa có câu hỏi nào.</li>
                                <li v-for="question in quizQuestions" :key="'quiz-' + question.id" class="py-4 flex items-center justify-between">
                                    <div class="flex-1 pr-4">
                                        <p class="font-medium">{{ question.question_text }}</p>
                                        <p class="text-xs text-gray-500 truncate" :title="getSummary(question.options)">{{ getSummary(question.options) }}</p>
                                    </div>
                                    <Link as="button" method="delete" :data="{ question_id: question.id }" :href="route('post.quiz.detach', post.id)" class="flex-shrink-0 px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full hover:bg-red-200" preserve-scroll>
                                        Gỡ
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-white shadow-xl sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                                Ngân hàng câu hỏi
                            </h3>

                            <div class="space-y-4 mb-4 p-4 border rounded-md bg-gray-50">
                                <VueMultiselect v-model="manualFilterForm.filter_subject" :options="props.subjects" label="name" track-by="id" placeholder="Lọc theo Môn học..." />
                                <VueMultiselect v-model="manualFilterForm.filter_tags" :options="props.tags" :multiple="true" label="name" track-by="id" placeholder="Lọc theo Thẻ..." />
                                <TextInput v-model="manualFilterForm.filter_search" type="text" class="w-full" placeholder="Tìm theo nội dung câu hỏi..." />
                            </div>

                            <h4 class="font-semibold mb-2">
                                Kết quả ({{ availableQuestions.total }})
                            </h4>
                            <ul class="divide-y divide-gray-200 max-h-screen-70 overflow-y-auto">
                                <li v-if="availableQuestions.data.length === 0" class="py-4 text-gray-500">
                                    Không tìm thấy câu hỏi nào.
                                </li>
                                <li v-for="question in availableQuestions.data" :key="'bank-' + question.id" class="py-4 flex items-center justify-between">
                                    <div class="flex-1 pr-4">
                                        <div class="mb-1 flex flex-wrap gap-1">
                                            <span v-if="question.subject" class="px-2 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">{{ question.subject.name }}</span>
                                            <span v-for="tag in question.tags" :key="tag.id" class="px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">{{ tag.name }}</span>
                                        </div>
                                        <p class="font-medium">{{ question.question_text }}</p>
                                        <img v-if="question.image_path" :src="'/storage/' + question.image_path" class="mt-2 w-full max-w-xs rounded-md border">
                                        <p class="text-xs text-gray-500 truncate" :title="getSummary(question.options)">{{ getSummary(question.options) }}</p>
                                    </div>
                                    <Link as="button" method="post" :data="{ question_id: question.id }" :href="route('post.quiz.attach', post.id)" class="flex-shrink-0 px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full hover:bg-green-200" preserve-scroll>
                                        Thêm
                                    </Link>
                                </li>
                            </ul>
                            
                            <Pagination :links="availableQuestions.links" class="mt-6" />
                        </div>
                    </div>

                    <form @submit.prevent="submitManualSettings">
                        <div class="p-6 bg-white shadow-xl sm:rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-6">A. Cài đặt Chung</h3>
                            <label class="flex items-center">
                                <Checkbox v-model:checked="manualSettingsForm.settings.shuffle" />
                                <span class="ms-2 text-sm text-gray-600">Đảo thứ tự câu hỏi khi học sinh làm bài</span>
                            </label>
                        </div>

                        <div class="p-6 bg-white shadow-xl sm:rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-6">B. Giao bài</h3>
                            <div class="space-y-2 mb-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="manualSettingsForm.assignment.assign_mode" value="all" class="form-radio text-indigo-600">
                                    <span class="ms-2">Giao cho Cả lớp ({{ students.length }} học sinh)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="manualSettingsForm.assignment.assign_mode" value="specific" class="form-radio text-indigo-600">
                                    <span class="ms-2">Giao cho học sinh cụ thể...</span>
                                </label>
                            </div>
                            <div v-if="manualSettingsForm.assignment.assign_mode === 'specific'">
                                <VueMultiselect
                                    v-model="manualSettingsForm.assignment.assigned_users"
                                    :options="props.students"
                                    :multiple="true"
                                    label="name" track-by="id"
                                    placeholder="Chọn 1 hoặc nhiều học sinh"
                                />
                                <InputError :message="manualSettingsForm.errors['assignment.assigned_users']" class="mt-2" />
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 shadow-xl sm:rounded-lg mt-6 flex justify-end items-center">
                            <ActionMessage :on="manualSettingsForm.recentlySuccessful" class="me-3">
                                Đã lưu.
                            </ActionMessage>
                            <PrimaryButton :class="{ 'opacity-25': manualSettingsForm.processing }" :disabled="manualSettingsForm.processing">
                                Lưu Cài đặt (Thủ công)
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div v-if="currentTab === 'random'" class="space-y-6">
                    
                    <div class="p-6 bg-white shadow-xl sm:rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Sử dụng Cấu hình Mẫu</h3>
                        <div class="flex items-end space-x-2">
                            <div class="flex-1">
                                <InputLabel value="Chọn một mẫu đã lưu" />
                                <VueMultiselect
                                    v-model="selectedTemplate"
                                    :options="props.templates"
                                    label="name" track-by="id"
                                    placeholder="Chọn một mẫu để tải cài đặt"
                                />
                            </div>
                            <SecondaryButton v-if="selectedTemplate" @click="deleteTemplate(selectedTemplate.id)" class="bg-red-100 text-red-700">
                                Xóa mẫu này
                            </SecondaryButton>
                        </div>
                    </div>

                    <form @submit.prevent="submitGenerate">
                        <div class="p-6 bg-white shadow-xl sm:rounded-lg">
                            <h3 class="text-lg font-semibold mb-6">A. Cài đặt Đề thi</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel value="1. Lấy câu hỏi từ Môn học (Bắt buộc)" />
                                    <VueMultiselect
                                        v-model="generateForm.settings.subject_id"
                                        :options="props.subjects"
                                        label="name" track-by="id"
                                        placeholder="Chọn 1 môn học"
                                    />
                                    <InputError :message="generateForm.errors['settings.subject_id']" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel value="2. Lọc theo Thẻ (Tùy chọn)" />
                                    <VueMultiselect
                                        v-model="generateForm.settings.tags"
                                        :options="props.tags"
                                        :multiple="true"
                                        label="name" track-by="id"
                                        placeholder="Chọn 1 hoặc nhiều thẻ"
                                    />
                                    <InputError :message="generateForm.errors['settings.tags']" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel value="3. Số lượng câu hỏi cần lấy" />
                                    <TextInput v-model="generateForm.settings.count" type="number" min="1" class="w-full" />
                                    <InputError :message="generateForm.errors['settings.count']" class="mt-2" />
                                </div>
                                
                                <div>
                                    <InputLabel value="4. Điểm cho mỗi câu" />
                                    <TextInput v-model="generateForm.settings.points" type="number" min="0.1" step="0.1" class="w-full" />
                                    <InputError :message="generateForm.errors['settings.points']" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="generateForm.settings.shuffle" />
                                    <span class="ms-2 text-sm text-gray-600">Đảo thứ tự câu hỏi khi học sinh làm bài</span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6 bg-white shadow-xl sm:rounded-lg mt-6">
                            <h3 class="text-lg font-semibold mb-6">B. Giao bài</h3>
                            
                            <div class="space-y-2 mb-4">
                                <label class="flex items-center">
                                    <input type="radio" v-model="generateForm.assignment.assign_mode" value="all" class="form-radio text-indigo-600">
                                    <span class="ms-2">Giao cho Cả lớp ({{ students.length }} học sinh)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" v-model="generateForm.assignment.assign_mode" value="specific" class="form-radio text-indigo-600">
                                    <span class="ms-2">Giao cho học sinh cụ thể...</span>
                                </label>
                            </div>
                            
                            <div v-if="generateForm.assignment.assign_mode === 'specific'">
                                <VueMultiselect
                                    v-model="generateForm.assignment.assigned_users"
                                    :options="props.students"
                                    :multiple="true"
                                    label="name" track-by="id"
                                    placeholder="Chọn 1 hoặc nhiều học sinh"
                                />
                                <InputError :message="generateForm.errors['assignment.assigned_users']" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="p-6 bg-gray-50 shadow-xl sm:rounded-lg mt-6 flex justify-end items-center space-x-4">
                            <ActionMessage :on="templateForm.recentlySuccessful" class="me-3">
                                Đã lưu mẫu.
                            </ActionMessage>

                            <SecondaryButton type="button" @click="saveAsTemplate" :disabled="templateForm.processing">
                                Lưu làm Mẫu...
                            </SecondaryButton>
                            
                            <ActionMessage :on="generateForm.recentlySuccessful" class="me-3">
                                Đã tạo.
                            </ActionMessage>

                            <PrimaryButton :class="{ 'opacity-25': generateForm.processing }" :disabled="generateForm.processing">
                                Tạo đề tự động & Giao bài
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AppLayout>
</template>