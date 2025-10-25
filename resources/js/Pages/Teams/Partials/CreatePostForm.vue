<script setup>
import { ref } from 'vue'; // <-- THÊM ref
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue'; // <-- THÊM TextInput
import SecondaryButton from '@/Components/SecondaryButton.vue'; // <-- THÊM SecondaryButton
import DangerButton from '@/Components/DangerButton.vue'; // <-- THÊM DangerButton

const props = defineProps({
    team: Object,
    topic: Object,
});

// --- LOGIC MỚI CHO TABS ---
// 'text' (bài viết) hoặc 'poll' (bình chọn)
const postType = ref('text');

const switchTo = (type) => {
    postType.value = type;
    // Đồng bộ form khi chuyển tab
    form.post_type = type;
    form.clearErrors(); // Xóa lỗi cũ
};
// --- KẾT THÚC LOGIC TABS ---


const form = useForm({
    content: '',
    post_type: 'text', // <-- THÊM DÒNG NÀY
    // Dùng mảng để lưu các lựa chọn (bắt đầu với 2 ô)
    poll_options: ['', ''], // <-- THÊM DÒNG NÀY
});

// --- LOGIC MỚI CHO POLL OPTIONS ---
// Thêm một ô nhập lựa chọn
const addPollOption = () => {
    if (form.poll_options.length < 10) { // Giới hạn 10 lựa chọn
        form.poll_options.push('');
    }
};

// Xóa một ô nhập lựa chọn
const removePollOption = (index) => {
    form.poll_options.splice(index, 1);
};
// --- KẾT THÚC LOGIC POLL OPTIONS ---


const createPost = () => {
    // Đảm bảo post_type được cập nhật
    form.post_type = postType.value;

    form.post(route('posts.store', props.topic), {
        errorBag: 'createPost',
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Đặt lại poll options về 2 ô
            form.poll_options = ['', ''];
            // Giữ nguyên tab hiện tại, không reset về 'text'
            // postType.value = 'text'; 
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
            Tạo một thông báo (bài viết) hoặc một cuộc bình chọn (poll) mới cho chủ đề này.
        </template>

        <template #form>
            
            <div class="col-span-6 sm:col-span-4 mb-4">
                <div class="flex space-x-2">
                    <button
                        type="button"
                        @click="switchTo('text')"
                        :class="{
                            'bg-indigo-600 text-white': postType === 'text',
                            'bg-gray-200 text-gray-700 hover:bg-gray-300': postType !== 'text',
                        }"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        📝 Bài viết
                    </button>
                    <button
                        type="button"
                        @click="switchTo('poll')"
                        :class="{
                            'bg-indigo-600 text-white': postType === 'poll',
                            'bg-gray-200 text-gray-700 hover:bg-gray-300': postType !== 'poll',
                        }"
                        class="px-4 py-2 rounded-md font-semibold text-sm transition"
                    >
                        🗳️ Bình chọn
                    </button>
                </div>
                <input type="hidden" v-model="form.post_type" />
            </div>
            <div v-if="postType === 'text'" class="col-span-6 sm:col-span-4">
                <label for="content_text">Nội dung</label>
                <TextArea
                    id="content_text"
                    v-model="form.content"
                    class="mt-1 block w-full"
                    rows="5"
                />
                <InputError :message="form.errors.content" class="mt-2" />
            </div>


            <div v-if="postType === 'poll'" class="col-span-6 sm:col-span-4 space-y-4">
                <div>
                    <label for="content_poll">Câu hỏi bình chọn</label>
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
                    <label>Các lựa chọn (Tối thiểu 2)</label>
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
                        </DangerButton>
                    </div>
                    
                    <InputError :message="form.errors.poll_options" class="mt-2" />
                    <template v-for="(error, index) in form.errors" :key="index">
                        <InputError
                            v-if="index.startsWith('poll_options.')"
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

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Đăng bài
            </PrimaryButton>
        </template>
    </FormSection>
</template>