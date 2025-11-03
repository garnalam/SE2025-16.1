<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    post: Object, // Bài tập đang chấm
    submissions: Array, // Mảng data (student, submission, status) từ controller
});

const selectedSubmissionData = ref(null);
const showGradeModal = ref(false);

// Form để chấm điểm
const form = useForm({
    grade: '',
    feedback: '',
});

// THÊM MỚI: Hàm kiểm tra nộp muộn
const isSubmissionLate = (submission) => {
    // Nếu không có bài nộp hoặc không có hạn chót, thì không muộn
    if (!submission || !props.post.due_date) {
        return false;
    }
    const submissionTime = new Date(submission.submitted_at);
    const dueDate = new Date(props.post.due_date);
    return submissionTime > dueDate;
};


// Hàm mở Modal
const openGradeModal = (submissionData) => {
    selectedSubmissionData.value = submissionData;
    const submission = submissionData.submission;
    
    // Điền thông tin cũ (nếu đã chấm)
    form.grade = submission?.grade ?? '';
    form.feedback = submission?.feedback ?? '';
    
    showGradeModal.value = true;
};

// Hàm đóng Modal
const closeModal = () => {
    showGradeModal.value = false;
    form.reset();
    form.clearErrors();
    selectedSubmissionData.value = null;
};

// Hàm submit điểm
const submitGrade = () => {
    if (!selectedSubmissionData.value?.submission) return;

    // Gọi route 'submissions.grade' (PUT)
    form.put(route('submissions.grade', selectedSubmissionData.value.submission.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(), // Đóng modal sau khi thành công
    });
};

// Hàm để download file (bạn sẽ cần tạo route này)
// Tạm thời, chúng ta sẽ link trực tiếp đến storage nếu bạn dùng public disk
// Hoặc bạn cần tạo một route bảo mật để xử lý việc download
const getFileUrl = (filePath) => {
    // Giả sử bạn đang dùng 'storage:link' và file nằm trong 'public/'
    // return '/storage/' + filePath; 
    
    // Tạm thời (vì chúng ta lưu file trong 'storage/app/submissions...')
    // bạn sẽ cần một route mới để download, ví dụ:
    // return route('submissions.downloadFile', fileId);
    alert('Bạn cần tạo route để download file an toàn. Đường dẫn: ' + filePath);
    return '#'; // Placeholder
}
</script>

<template>
    <AppLayout :title="'Chấm bài: ' + post.title">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chấm bài: {{ post.title }}
            </h2>
            <Link 
                :href="route('topics.show', post.topic_id)" 
                class="text-sm text-indigo-600 hover:text-indigo-800"
            >
                &larr; Quay lại chủ đề
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-4 md:p-6">
                    
                    <div class="mb-4">
                        <h3 class="text-lg font-medium">Tổng quan</h3>
                        <p class="text-sm text-gray-600">Điểm tối đa: <span class="font-bold">{{ post.max_points }}</span></p>
                        <!-- THÊM MỚI: Hiển thị hạn nộp ở đây cho rõ -->
                        <p class="text-sm text-gray-600">
                            Hết hạn: 
                            <span v-if="post.due_date" class="font-bold text-red-600">{{ new Date(post.due_date).toLocaleString('vi-VN') }}</span>
                            <span v-else class="font-bold">Không có</span>
                        </p>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Học sinh</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian nộp</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Điểm số</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="submissions.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Không có học sinh nào trong lớp này.</td>
                                </tr>
                                <tr v-for="data in submissions" :key="data.student.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-10 w-10 rounded-full" :src="data.student.profile_photo_url" :alt="data.student.name">
                                            <div class="ml-4 text-sm font-medium text-gray-900">{{ data.student.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-red-100 text-red-800': data.status === 'Not Submitted',
                                            'bg-yellow-100 text-yellow-800': data.status === 'Submitted',
                                            'bg-green-100 text-green-800': data.status === 'Graded',
                                        }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ data.status === 'Not Submitted' ? 'Chưa nộp' : (data.status === 'Submitted' ? 'Đã nộp' : 'Đã chấm') }}
                                        </span>
                                    </td>
                                    
                                    <!-- THAY ĐỔI: Cột thời gian nộp, bổ sung logic kiểm tra nộp muộn -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div v-if="data.submission">
                                            <span>{{ new Date(data.submission.submitted_at).toLocaleString('vi-VN') }}</span>
                                            
                                            <!-- THÊM MỚI: Tag "Nộp muộn" -->
                                            <span v-if="isSubmissionLate(data.submission)" 
                                                  class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Nộp muộn
                                            </span>
                                        </div>
                                        <span v-else>N/A</span>
                                    </td>
                                    <!-- KẾT THÚC THAY ĐỔI -->
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <span v-if="data.status === 'Graded'">
                                            {{ data.submission.grade }} / {{ post.max_points }}
                                        </span>
                                        <span v-else>Chưa chấm</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <PrimaryButton @click="openGradeModal(data)" :disabled="!data.submission">
                                            {{ data.status === 'Graded' ? 'Sửa điểm' : 'Chấm bài' }}
                                        </PrimaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showGradeModal" @close="closeModal">
            <form @submit.prevent="submitGrade" class="p-6">

                <!-- THAY ĐỔI: Bổ sung tag nộp muộn trong tiêu đề Modal -->
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">
                        Chấm bài cho: {{ selectedSubmissionData?.student.name }}
                    </h2>
                    <span v-if="selectedSubmissionData?.submission && isSubmissionLate(selectedSubmissionData.submission)"
                          class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        NỘP MUỘN
                    </span>
                </div>
                <!-- KẾT THÚC THAY ĐỔI -->


                <div class="mt-4 space-y-3">
                    <div>
                        <InputLabel value="Nội dung học sinh nộp:" />
                        <p class="text-sm text-gray-700 p-3 border rounded-md bg-gray-50 min-h-[60px]">
                            {{ selectedSubmissionData?.submission?.content || '(Không có nội dung text)' }}
                        </p>
                    </div>
                    
                    <div>
                        <InputLabel value="Files học sinh nộp:" />
                        <ul v-if="selectedSubmissionData?.submission?.files.length > 0" class="list-disc list-inside mt-1 space-y-1">
                            <li v-for="file in selectedSubmissionData.submission.files" :key="file.id">
                                <a 
                                    :href="route('submissions.downloadFile', file.id)" class="text-indigo-600 hover:underline" 
                                    target="_blank" >
                                    {{ file.original_name }}
                                </a>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">(Không có file đính kèm)</p>
                    </div>
                </div>

                <hr class="my-6">

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-1">
                        <InputLabel for="grade" value="Điểm số" />
                        <TextInput
                            id="grade"
                            v-model="form.grade"
                            type="number"
                            class="mt-1 block w-full"
                            step="0.1"
                            :max="post.max_points"
                            min="0"
                            required
                        />
                    </div>
                     <div class="col-span-2 pt-8 text-lg font-semibold text-gray-700">
                        / {{ post.max_points }}
                    </div>
                </div>
                <InputError :message="form.errors.grade" class="mt-2" />
                
                <div class="mt-4">
                    <InputLabel for="feedback" value="Nhận xét (Feedback)" />
                    <TextArea
                        id="feedback"
                        v-model="form.feedback"
                        class="mt-1 block w-full"
                        rows="4"
                        placeholder="Gửi nhận xét cho học sinh..."
                    />
                    <InputError :message="form.errors.feedback" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Hủy </SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Lưu điểm
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
