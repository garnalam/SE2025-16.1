<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import VueMultiselect from 'vue-multiselect';

const props = defineProps({
    subjects: Array,
    tags: Array,
});

const form = useForm({
    file: null,
    subject_id: null,
    tags: [],
});

function submitForm() {
    const payload = {
        ...form.data(),
        subject_id: form.subject_id ? form.subject_id.id : null,
        tags: form.tags.map(tag => tag.id),
    };

    useForm(payload).post(route('questions.import.store'), {
        onSuccess: () => form.reset('file'),
    });
}
</script>

<template>
    <AppLayout title="Data Uplink">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('questions.index')" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                    Bulk Data Uplink
                </h2>
            </div>
        </template>

        <div class="py-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0f172a] border border-emerald-500/30 rounded-3xl p-8 shadow-[0_0_50px_rgba(16,185,129,0.1)] relative overflow-hidden">
                
                <div class="absolute top-0 right-0 w-64 h-64 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 pointer-events-none"></div>

                <div class="mb-8 p-5 bg-emerald-900/20 border border-emerald-500/30 rounded-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-emerald-500/5 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-700"></div>
                    <h4 class="font-bold text-emerald-400 font-exo uppercase tracking-wide mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Hướng dẫn nhanh
                    </h4>
                    <ol class="list-decimal list-inside text-sm text-emerald-100/70 font-mono space-y-1 relative z-10">
                        <li>Hỗ trợ file: .xlsx (Excel) or .csv</li>
                        <li>Sử dụng template bên dưới để tạo câu hỏi.</li>
                    </ol>
                    <a 
                        :href="route('questions.import.template')" 
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase rounded-lg transition shadow-lg relative z-10"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download Matrix Template
                    </a>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div>
                        <InputLabel value="1. Môn học" />
                        <VueMultiselect
                            v-model="form.subject_id"
                            :options="props.subjects"
                            label="name" track-by="id"
                            placeholder="Chọn môn học"
                            class="custom-multiselect mt-1"
                        />
                        <InputError :message="form.errors.subject_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="2. Nhãn" />
                        <VueMultiselect
                            v-model="form.tags"
                            :options="props.tags"
                            :multiple="true"
                            label="name" track-by="id"
                            placeholder="Chọn nhãn"
                            class="custom-multiselect mt-1"
                        />
                        <InputError :message="form.errors.tags" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="3. Nguồn" />
                        <div class="mt-1 relative group">
                            <!-- Added pointer-events-none to prevent blocking clicks -->
                            <div class="absolute inset-0 bg-emerald-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition duration-500 pointer-events-none"></div>
                            
                            <!-- Added relative z-10 to ensure input is clickable -->
                            <input 
                                type="file" 
                                @change="form.file = $event.target.files[0]" 
                                class="relative z-10 block w-full text-sm text-slate-400
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-xl file:border-0
                                file:text-xs file:font-bold file:uppercase file:tracking-widest
                                file:bg-emerald-600 file:text-white
                                file:cursor-pointer hover:file:bg-emerald-500
                                cursor-pointer
                                bg-slate-900 border border-slate-700 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>
                        <div v-if="form.file" class="mt-2 text-xs text-emerald-400 font-mono">
                            >> Selected: {{ form.file.name }}
                        </div>
                        <div v-if="form.progress" class="w-full bg-slate-800 rounded-full h-2.5 mt-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300" :style="{ width: form.progress.percentage + '%' }"></div>
                        </div>
                        <InputError :message="form.errors.file" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-white/10">
                        <Link :href="route('questions.index')" class="text-xs font-bold text-slate-500 hover:text-white uppercase tracking-widest mr-6 transition">Hủy</Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="!bg-emerald-600 hover:!bg-emerald-500">
                            Nhập câu hỏi
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Reusing the same Multiselect overrides but targeting Emerald colors for highlight where appropriate */
.custom-multiselect .multiselect__tags {
    background-color: #0f172a;
    border-color: #334155;
    color: white;
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
    background-color: #10b981; /* Emerald */
    color: white;
}
</style>