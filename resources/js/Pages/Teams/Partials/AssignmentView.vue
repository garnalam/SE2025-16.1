<script setup>

import { ref, computed } from 'vue'; // THAY ĐỔI: Thêm computed
import { useForm, Link } from '@inertiajs/vue3';

import PrimaryButton from '@/Components/PrimaryButton.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue';

import InputError from '@/Components/InputError.vue';

import InputLabel from '@/Components/InputLabel.vue';

import TextArea from '@/Components/TextArea.vue';

import ActionMessage from '@/Components/ActionMessage.vue';



const props = defineProps({

    post: Object, // Thông tin bài tập (assignment post)

    canManageTopics: Boolean, // Quyền của giáo viên

    userSubmission: Object, // Bài nộp của sinh viên hiện tại (nếu có)

});

// THÊM MỚI: Computed property để kiểm tra nộp muộn
const isLate = computed(() => {
    // Nếu không có bài nộp hoặc không có hạn chót, thì không muộn
    if (!props.userSubmission || !props.post.due_date) {
        return false;
    }

    const submissionTime = new Date(props.userSubmission.submitted_at);
    const dueDate = new Date(props.post.due_date);

    return submissionTime > dueDate;
});

// Form nộp bài

const form = useForm({

    content: props.userSubmission?.content ?? '', // Lấy lại nội dung cũ nếu đã nộp

    files: [],

});



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



// Hàm submit

const submitAssignment = () => {

    form.post(route('submissions.store', props.post.id), {

        preserveScroll: true,

        onSuccess: () => {

            clearFiles();

            // Không reset content vì user có thể muốn giữ lại text

        },

    });

};

</script>



<template>

    <div class="mt-4 p-4 md:p-6 bg-white border border-gray-200 rounded-lg shadow-sm">

        

        <div class="flex justify-between items-start">

            <div>

                <h3 class="text-xl font-bold text-indigo-700">{{ post.title }}</h3>

                <p class="text-sm text-gray-500 mt-1">

                    Hết hạn: 

                    <span v-if="post.due_date" class="font-medium text-red-600">{{ new Date(post.due_date).toLocaleString('vi-VN') }}</span>

                    <span v-else>Không có</span>

                </p>

            </div>

            <span class="text-lg font-semibold text-gray-700">{{ post.max_points }} điểm</span>

        </div>

        

        <div v-if="post.content" class="mt-4 prose max-w-none" v-html="post.content"></div>

        
                <div v-if="post.attachments && post.attachments.length > 0" class="mt-4">
            <strong class="text-sm font-medium text-gray-700">File bài tập đính kèm:</strong>
            <ul class="list-disc list-inside pl-2 mt-2 space-y-1">
                <li v-for="file in post.attachments" :key="file.id" class="text-sm">
                    <a 
                        :href="'/storage/' + file.path" 
                        target="_blank" 
                        class="text-blue-600 hover:underline hover:text-blue-800"
                    >
                        {{ file.original_name }}
                    </a>
                </li>
            </ul>
        </div>
                <hr class="my-5">

        <div v-if="props.canManageTopics">

            <h4 class="text-md font-semibold text-gray-800">Khu vực giáo viên</h4>

            <p class="text-sm text-gray-600 mb-3">Xem tiến độ và chấm bài của sinh viên.</p>

            <Link :href="route('submissions.index', post.id)">

                <PrimaryButton class="bg-blue-600 hover:bg-blue-700">

                    Xem danh sách nộp bài

                </PrimaryButton>

            </Link>

        </div>



        <div v-else>

            <h4 class="text-md font-semibold text-gray-800">Bài nộp của bạn</h4>

            
            <!-- THAY ĐỔI: Khối hiển thị bài nộp, thêm logic isLate -->
            <div v-if="userSubmission" class="p-3 my-3 border rounded-md" :class="{
                'bg-green-50 border-green-200': !isLate,
                'bg-red-50 border-red-200': isLate
            }">
                <p class="font-semibold" :class="{ 'text-green-800': !isLate, 'text-red-800': isLate }">
                    <span v-if="isLate">
                        ❌ Nộp muộn lúc: {{ new Date(userSubmission.submitted_at).toLocaleString('vi-VN') }}
                    </span>
                    <span v-else>
                        ✔️ Đã nộp lúc: {{ new Date(userSubmission.submitted_at).toLocaleString('vi-VN') }}
                    </span>
                </p>

                <!-- THÊM MỚI: Hiển thị thông báo nộp muộn so với hạn -->
                <p v-if="isLate" class="text-sm text-red-700 mt-1">
                    (Đã nộp sau hạn chót: {{ new Date(post.due_date).toLocaleString('vi-VN') }})
                </p>

                

                <ul v-if="userSubmission.files && userSubmission.files.length > 0" class="mt-2 text-sm list-disc list-inside text-gray-700">

                    <li v-for="file in userSubmission.files" :key="file.id">

                        {{ file.original_name }}

                    </li>

                </ul>

                <div v-if="userSubmission.grade !== null" class="mt-3 pt-3" :class="{ 'border-t border-green-200': !isLate, 'border-t border-red-200': isLate }">
                    <p class="text-lg font-bold text-blue-800">

                        Điểm số: {{ userSubmission.grade }} / {{ post.max_points }}

                    </p>

                    <div v-if="userSubmission.feedback" class="mt-1">

                        <p class="font-semibold text-gray-700">Nhận xét của giáo viên:</p>

                        <p class="text-gray-600 p-2 bg-gray-50 rounded border">{{ userSubmission.feedback }}</p>

                    </div>

                </div>

            </div>
            <!-- KẾT THÚC THAY ĐỔI -->




            <form @submit.prevent="submitAssignment" class="mt-4">

                <div class="space-y-4">

                    <div>

                        <InputLabel for="submission_content" value="Nội dung trả lời (nếu có)" />

                        <TextArea

                            id="submission_content"

                            v-model="form.content"

                            class="mt-1 block w-full"
                            rows="3"
                             placeholder="Nhập câu trả lời của bạn tại đây..."
                        />

                        <InputError :message="form.errors.content" class="mt-2" />

                    </div>

                    <div>

                        <InputLabel value="Đính kèm file bài nộp" />

                        <input 

                            ref="fileInput"

                            type="file" 

                            multiple

                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"

                            @change="handleFileChange"

                        >

                        <InputError :message="form.errors.files" class="mt-2" />

                        <template v-for="(error, index) in form.errors" :key="index">

                            <InputError

                                v-if="typeof index === 'string' && index.startsWith('files.')"

                                :message="error"

                                class="mt-2"

                            />

                        </template>

                         <div v-if="form.files.length > 0" class="mt-2 space-y-1">

                            <div v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">

                                <span>{{ file.name }}</span>

                                <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 font-medium">Xóa</button>

                          </div>

                        </div>

                    </div>



                    <div class="flex items-center gap-4">

                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">

                            {{ userSubmission ? 'Nộp lại bài' : 'Nộp bài' }}

                       </PrimaryButton>

                        

                        <ActionMessage :on="form.recentlySuccessful" class="mr-3">

                            Đã lưu.

                        </ActionMessage>

                    </div>

                </div>

            </form>

        </div>

    </div>
</template>
