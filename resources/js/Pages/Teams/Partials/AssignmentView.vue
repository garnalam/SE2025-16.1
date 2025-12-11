<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import ActionMessage from '@/Components/ActionMessage.vue';

const props = defineProps({
    post: Object, // Thông tin bài tập
    canManageTopics: Boolean, // Quyền giáo viên
    userSubmission: Object, // Bài nộp của học sinh
});

// Kiểm tra nộp muộn
const isLate = computed(() => {
    if (!props.userSubmission || !props.post.due_date) return false;
    return new Date(props.userSubmission.submitted_at) > new Date(props.post.due_date);
});

// Format điểm số (ví dụ: 8.00 -> 8)
const formattedScore = computed(() => {
    if (props.userSubmission?.grade === null) return null;
    return Number(props.userSubmission.grade).toString();
});

const form = useForm({
    content: props.userSubmission?.content ?? '',
    files: [],
});

const fileInput = ref(null);

const handleFileChange = (event) => {
    form.files = Array.from(event.target.files);
};

const removeFile = (index) => {
    form.files.splice(index, 1);
    if (form.files.length === 0 && fileInput.value) fileInput.value.value = null;
};

const clearFiles = () => {
    form.files = [];
    if (fileInput.value) fileInput.value.value = null;
}

const submitAssignment = () => {
    form.post(route('submissions.store', props.post.id), {
        preserveScroll: true,
        onSuccess: () => clearFiles(),
    });
};
</script>

<template>
    <div class="mt-4 p-4 md:p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold text-indigo-700 flex items-center">
                    📝 {{ post.title || 'Bài tập' }}
                    <span v-if="post.due_date" class="ml-3 text-xs font-medium px-2 py-1 rounded bg-gray-100 text-gray-600">
                        Hạn: {{ new Date(post.due_date).toLocaleString('vi-VN') }}
                    </span>
                </h3>
            </div>
            <span class="text-lg font-bold text-gray-700">{{ post.max_points }} điểm</span>
        </div>
        
        <div class="mt-3 text-gray-800 whitespace-pre-wrap">{{ post.content }}</div>

        <div v-if="post.attachments && post.attachments.length > 0" class="mt-4 p-3 bg-gray-50 rounded border">
            <strong class="text-xs font-bold text-gray-500 uppercase">Tài liệu đính kèm:</strong>
            <ul class="list-disc list-inside mt-1 space-y-1">
                <li v-for="file in post.attachments" :key="file.id" class="text-sm">
                    <a :href="'/storage/' + file.path" target="_blank" class="text-blue-600 hover:underline">
                        {{ file.original_name }}
                    </a>
                </li>
            </ul>
        </div>

        <hr class="my-5 border-gray-200">

        <div v-if="props.canManageTopics">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="font-bold text-gray-800">Quản lý bài nộp</h4>
                    <p class="text-sm text-gray-500">Xem danh sách học sinh nộp bài và chấm điểm.</p>
                </div>
                <Link :href="route('submissions.index', post.id)">
                    <PrimaryButton>👀 Xem & Chấm bài</PrimaryButton>
                </Link>
            </div>
        </div>

        <div v-else>
            
            <div v-if="userSubmission && userSubmission.grade !== null" class="bg-green-50 border border-green-200 rounded-lg p-5">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-bold text-lg text-green-800 flex items-center">
                            🎉 Kết quả bài làm
                            <span v-if="isLate" class="ml-2 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold">NỘP MUỘN</span>
                        </h4>
                        <p class="text-xs text-green-600 mt-1">
                            Chấm lúc: {{ new Date(userSubmission.graded_at).toLocaleString('vi-VN') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="block text-4xl font-black text-green-600">{{ formattedScore }}</span>
                        <span class="text-sm text-gray-500 font-medium">/ {{ post.max_points }} điểm</span>
                    </div>
                </div>

                <div v-if="userSubmission.feedback" class="bg-white p-4 rounded border border-green-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">Lời phê của giáo viên:</p>
                    <p class="text-gray-800 text-sm whitespace-pre-wrap leading-relaxed">{{ userSubmission.feedback }}</p>
                </div>
            </div>

            <div v-else-if="userSubmission" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">✅</span>
                    <div>
                        <h4 class="font-bold text-yellow-800">Đã nộp bài thành công</h4>
                        <p class="text-xs text-yellow-700">
                            Nộp lúc: {{ new Date(userSubmission.submitted_at).toLocaleString('vi-VN') }}
                            <span v-if="isLate" class="font-bold text-red-600 ml-1">(Nộp muộn)</span>
                        </p>
                    </div>
                </div>
                
                <div class="mt-2 text-sm text-gray-600 pl-9">
                    <p v-if="userSubmission.content" class="italic mb-1">"{{ userSubmission.content }}"</p>
                    <ul v-if="userSubmission.files && userSubmission.files.length" class="list-disc pl-4">
                        <li v-for="f in userSubmission.files" :key="f.id">{{ f.original_name }}</li>
                    </ul>
                </div>
            </div>

            <form @submit.prevent="submitAssignment" class="mt-4 pt-4 border-t border-gray-100">
                <h4 class="font-bold text-gray-700 mb-3">
                    {{ userSubmission ? 'Nộp lại bài làm:' : 'Nộp bài làm:' }}
                </h4>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Nội dung trả lời" />
                        <TextArea
                            v-model="form.content"
                            class="mt-1 block w-full"
                            rows="3"
                            placeholder="Nhập câu trả lời hoặc ghi chú..."
                        />
                        <InputError :message="form.errors.content" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Đính kèm file (Word, PDF, Ảnh...)" />
                        <div class="mt-1 flex items-center">
                            <input 
                                ref="fileInput"
                                type="file" 
                                multiple
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                @change="handleFileChange"
                            >
                        </div>
                        <InputError :message="form.errors.files" class="mt-2" />
                        
                        <div v-if="form.files.length > 0" class="mt-2 space-y-1">
                            <div v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                                <span class="truncate max-w-xs">{{ file.name }}</span>
                                <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 text-xs font-bold">Xóa</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <ActionMessage :on="form.recentlySuccessful">Đã nộp!</ActionMessage>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ userSubmission ? 'Cập nhật bài nộp' : 'Nộp bài' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>