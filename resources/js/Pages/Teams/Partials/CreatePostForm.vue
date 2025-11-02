<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    team: Object,
    topic: Object,
    canManageTopics: Boolean, // <-- THÊM PROP NÀY ĐỂ NHẬN QUYỀN
});

// 'text', 'poll', 'material', 'assignment'
const postType = ref('text'); 

const switchTo = (type) => {
    postType.value = type;
    form.post_type = type;
    form.clearErrors();
};

const form = useForm({
    post_type: 'text',
    content: '',
    poll_options: ['', ''],
    title: '',
    due_date: '',
    max_points: 100, 
    files: [], 
});

// --- Logic Poll Options ---
const addPollOption = () => {
    if (form.poll_options.length < 10) {
        form.poll_options.push('');
    }
};
const removePollOption = (index) => {
    form.poll_options.splice(index, 1);
};
// --- Hết Logic Poll ---

// --- Logic File Upload ---
const fileInput = ref(null); 

const handleFileChange = (event) => {
    form.files = Array.from(event.target.files);
};

const removeFile = (index) => {
    form.files.splice(index, 1);
    if (form.files.length === 0 && fileInput.value) {
        fileInput.value.value = null;
    }
};

const clearFiles = () => {
    form.files = [];
    if (fileInput.value) {
        fileInput.value.value = null;
    }
}
// --- Hết Logic File Upload ---


const createPost = () => {
    form.post_type = postType.value;

    form.post(route('posts.store', props.topic), {
        errorBag: 'createPost',
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.poll_options = ['', ''];
            clearFiles(); 
        },
    });
};
</script>

<template>
    <FormSection @submitted="createPost">
        <template #title>
            Đăng bài mới
        </template>

        <template #description>
            Tạo thông báo, tài liệu, bài tập hoặc cuộc bình chọn mới.
        </template>

        <template #form>
            
            <!-- Tabs Lựa chọn (ĐÃ THÊM v-if) -->
            <div class="col-span-6 sm:col-span-4 mb-4">
                <div class="flex flex-wrap gap-2">
                    <!-- 1. Tab Thông báo (Luôn hiển thị) -->
                    <button
                        type="button"
                        @click="switchTo('text')"
                        :class="postType === 'text' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        📝 Thông báo
                    </button>
                    <!-- 2. Tab Tài liệu (Chỉ Teacher thấy) -->
                    <button
                        v-if="props.canManageTopics"
                        type="button"
                        @click="switchTo('material')"
                        :class="postType === 'material' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        📚 Tài liệu
                    </button>
                    <!-- 3. Tab Bài tập (Chỉ Teacher thấy) -->
                    <button
                        v-if="props.canManageTopics"
                        type="button"
                        @click="switchTo('assignment')"
                        :class="postType === 'assignment' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        🧑‍💻 Bài tập
                    </button>
                    <!-- 4. Tab Bình chọn (Luôn hiển thị) -->
                    <button
                        type="button"
                        @click="switchTo('poll')"
                        :class="postType === 'poll' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        🗳️ Bình chọn
                    </button>
                </div>
                <input type="hidden" v-model="form.post_type" />
            </div>

            <!-- 1. Form cho THÔNG BÁO (text) -->
            <div v-if="postType === 'text'" class="col-span-6 sm:col-span-4 space-y-4">
                <div>
                    <InputLabel for="content_text" value="Nội dung thông báo" />
                    <TextArea
                        id="content_text"
                        v-model="form.content"
                        class="mt-1 block w-full"
                        rows="5"
                    />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>
            </div>

            <!-- 2. Form cho TÀI LIỆU (material) (Thêm v-if) -->
            <div v-if="postType === 'material' && props.canManageTopics" class="col-span-6 sm:col-span-4 space-y-4">
                <div>
                    <InputLabel for="content_material" value="Mô tả tài liệu" />
                    <TextArea
                        id="content_material"
                        v-model="form.content"
                        class="mt-1 block w-full"
                        rows="3"
                        placeholder="Ví dụ: Slide bài giảng chương 1, video hướng dẫn..."
                    />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>
                <!-- VÙNG UPLOAD FILE -->
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel value="Đính kèm file (Video, PDF, Word...)" />
                    <input 
                        ref="fileInput"
                        type="file" 
                        multiple
                        class="mt-1 block w-full text-sm text-gray-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100"
                        @change="handleFileChange"
                    >
                    <InputError :message="form.errors.files" class="mt-2" />
                    
                    <!-- Sửa lỗi startsWith -->
                    <template v-for="(error, index) in form.errors" :key="index">
                        <InputError
                            v-if="typeof index === 'string' && index.startsWith('files.')"
                            :message="error"
                            class="mt-2"
                        />
                    </template>
                    
                    <!-- Hiển thị danh sách file đã chọn -->
                    <div v-if="form.files.length > 0" class="mt-2 space-y-1">
                        <div v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center text-sm">
                            <span>{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                            <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">Xóa</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Form cho BÀI TẬP (assignment) (Thêm v-if) -->
            <div v-if="postType === 'assignment' && props.canManageTopics" class="col-span-6 sm:col-span-4 space-y-4">
                <div>
                    <InputLabel for="title_assignment" value="Tiêu đề bài tập" />
                    <TextInput
                        id="title_assignment"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Ví dụ: Bài tập lớn cuối kỳ"
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="content_assignment" value="Hướng dẫn / Mô tả" />
                    <TextArea
                        id="content_assignment"
                        v-model="form.content"
                        class="mt-1 block w-full"
                        rows="5"
                    />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>

                <!-- VÙNG UPLOAD FILE -->
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel value="File đính kèm (nếu có)" />
                     <input 
                        ref="fileInput"
                        type="file" 
                        multiple
                        class="mt-1 block w-full text-sm text-gray-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100"
                        @change="handleFileChange"
                    >
                    <InputError :message="form.errors.files" class="mt-2" />
                    
                    <!-- Sửa lỗi startsWith -->
                    <template v-for="(error, index) in form.errors" :key="index">
                        <InputError
                            v-if="typeof index === 'string' && index.startsWith('files.')"
                            :message="error"
                            class="mt-2"
                        />
                    </template>

                    <div v-if="form.files.length > 0" class="mt-2 space-y-1">
                        <div v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center text-sm">
                            <span>{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                            <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">Xóa</button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="due_date" value="Ngày hết hạn (Tùy chọn)" />
                        <TextInput
                            id="due_date"
                            v-model="form.due_date"
                            type="datetime-local"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.due_date" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="max_points" value="Điểm tối đa" />
                        <TextInput
                            id="max_points"
                            v-model="form.max_points"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.max_points" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- 4. Form cho BÌNH CHỌN (poll) -->
            <div v-if="postType === 'poll'" class="col-span-6 sm:col-span-4 space-y-4">
                <div>
                    <InputLabel for="content_poll" value="Câu hỏi bình chọn" />
                    <TextArea
                        id="content_poll"
                        v-model="form.content"
                        class="mt-1 block w-full"
                        rows="3"
                        placeholder="Ví dụ: Cả lớp có đồng ý dời lịch thi sang tuần sau không?"
                    />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>
                
                <div>
                    <InputLabel value="Các lựa chọn (Tối thiểu 2)" />
                    <div v-for="(option, index) in form.poll_options" :key="index" class="flex items-center mt-2">
                        <TextInput
                            :id="'option_' + index"
                            v-model="form.poll_options[index]"
                            type="text"
                            class="block w-full"
                            :placeholder="'Lựa chọn ' + (index + 1)"
                        />
                        <DangerButton
                            type="button"
                            class="ml-2"
                            @click="removePollOption(index)"
                            v-if="form.poll_options.length > 2"
                        >
                            Xóa
                        </DANGERBUTTON>
                    </div>
                    
                    <InputError :message="form.errors.poll_options" class="mt-2" />
                    <template v-for="(error, index) in form.errors" :key="index">
                        <InputError
                            v-if="typeof index === 'string' && index.startsWith('poll_options.')"
                            :message="error"
                            class="mt-2"
                        />
                    </template>

                    <SecondaryButton
                        type="button"
                        @click="addPollOption"
                        class="mt-2"
                        v-if="form.poll_options.length < 10"
                    >
                        Thêm lựa chọn
                    </SecondaryButton>
                </div>
            </div>

        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Đã đăng.
            </ActionMessage>

            <span v-if="form.hasErrors" class="text-sm text-red-600 mr-3">
                Đã có lỗi xảy ra. Vui lòng kiểm tra lại.
            </span>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Đăng bài
            </PrimaryButton>
        </template>
    </FormSection>
</template>

