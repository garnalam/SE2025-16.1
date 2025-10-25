<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Lấy thông báo lỗi và thành công từ session (qua HandleInertiaRequests.php)
const { props } = usePage();

// Sử dụng useForm của Inertia
const form = useForm({
    join_code: '',
});

const submitJoinClassroom = () => {
    form.post(route('classrooms.join'), {
        onError: () => {
            // Lỗi sẽ tự động được hiển thị bởi component InputError
            // Bạn có thể thêm thông báo toast ở đây nếu muốn
        },
        onSuccess: () => {
            // Khi thành công, form sẽ được reset
            form.reset();
            // Trang sẽ tự động tải lại (do Redirect từ controller)
            // và hiển thị thông báo 'status'
        },
        onFinish: () => {
            // Bất cứ khi nào request kết thúc
        },
    });
};
</script>

<template>
    <AppLayout title="Student Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard (Học sinh)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Hiển thị thông báo tham gia thành công -->
                <div v-if="props.status" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ props.status }}
                </div>

                <!-- Form Tham Gia Lớp Học -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Tham gia Lớp học mới
                    </h3>
                    
                    <form @submit.prevent="submitJoinClassroom" class="max-w-lg">
                        <div>
                            <InputLabel for="join_code" value="Mã Lớp (Join Code)" />
                            <TextInput
                                id="join_code"
                                v-model="form.join_code"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                autocomplete="join_code"
                                placeholder="kpd-yyy-4xo"
                            />
                            <!-- Hiển thị lỗi từ backend (ví dụ: mã sai, đã tham gia) -->
                            <InputError class="mt-2" :message="form.errors.join_code" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Tham gia
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <!-- Bạn có thể thêm các component khác cho dashboard học sinh ở đây -->
                <!-- Ví dụ: Danh sách các lớp đã tham gia, bài tập sắp đến hạn... -->

            </div>
        </div>
    </AppLayout>
</template>
