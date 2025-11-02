<script setup>
import { useForm } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { watch, nextTick, ref } from 'vue';

// Nhận prop 'show' để điều khiển bật/tắt
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

// Gửi sự kiện 'close' để báo cho component cha
const emit = defineEmits(['close']);

const form = useForm({
    join_code: '',
});

// Tự động focus vào ô input khi modal mở
const joinCodeInput = ref(null);

watch(() => props.show, (show) => {
    if (show) {
        nextTick(() => {
            joinCodeInput.value?.focus();
        });
    }
});

// Hàm gửi form
const submitJoinClassroom = () => {
    form.post(route('classrooms.join'), {
        preserveScroll: true, // Không cuộn trang lên đầu
        onError: () => {
            // InputError sẽ tự hiển thị lỗi
            joinCodeInput.value?.focus();
        },
        onSuccess: () => {
            // Thành công, đóng modal
            closeModal();
            // Trang sẽ tự tải lại với thông báo 'status'
        },
    });
};

// Hàm đóng modal
const closeModal = () => {
    form.reset(); // Xóa dữ liệu đã nhập
    form.clearErrors(); // Xóa thông báo lỗi
    emit('close'); // Báo cho cha (AppLayout) là đã đóng
};
</script>

<template>
    <DialogModal :show="show" @close="closeModal">
        <template #title>
            Tham gia Lớp học mới
        </template>

        <template #content>
            <!-- Đặt ID cho form để nút submit bên ngoài có thể kích hoạt -->
            <form @submit.prevent="submitJoinClassroom" id="join-classroom-form">
                <div>
                    <InputLabel for="join_code_modal" value="Mã Lớp (Join Code)" />
                    <TextInput
                        id="join_code_modal"
                        ref="joinCodeInput"
                        v-model="form.join_code"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="join_code"
                        placeholder="kpd-yyy-4xo"
                    />
                    <InputError class="mt-2" :message="form.errors.join_code" />
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Hủy bỏ
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                form="join-classroom-form"
                type="submit"
            >
                Tham gia
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
