<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3'; 
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    subjects: Array,
});

const createForm = useForm({
    name: '',
});

const updateForm = useForm({
    name: '',
});

const editingId = ref(null);

const editSubject = (subject) => {
    editingId.value = subject.id;
    updateForm.name = subject.name;
};

const cancelEdit = () => {
    editingId.value = null;
    updateForm.reset();
};

const submitCreate = () => {
    createForm.post(route('subjects.store'), {
        onSuccess: () => createForm.reset(),
        preserveScroll: true,
    });
};

const submitUpdate = (id) => {
    updateForm.put(route('subjects.update', id), {
        onSuccess: () => cancelEdit(),
        preserveScroll: true,
    });
};

const deleteSubject = (id) => {
    if (confirm('Bạn đã chắc chắn chưa? Hành động này không thể hoàn tác.')) {
        useForm({}).delete(route('subjects.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Môn học">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('questions.index')" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                    Môn học 
                </h2>
            </div>
        </template>

        <div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- CREATE CARD -->
            <div class="bg-[#0f172a] border border-cyan-500/30 rounded-3xl p-6 shadow-[0_0_30px_rgba(6,182,212,0.15)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <h3 class="text-sm font-bold text-cyan-400 font-exo uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Tạo môn học mới
                </h3>

                <form @submit.prevent="submitCreate" class="flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex-1 w-full">
                        <TextInput
                            v-model="createForm.name"
                            type="text"
                            class="w-full bg-slate-900 border-slate-700 text-white focus:border-cyan-500 focus:ring-cyan-500/50 placeholder-slate-600 font-mono text-sm"
                            placeholder="Mô tả tên môn học (ví dụ:  Toán học, Vật lý, Hóa học...)"
                        />
                        <InputError :message="createForm.errors.name" class="mt-2" />
                    </div>
                    <PrimaryButton :disabled="createForm.processing" class="whitespace-nowrap">
                        Tạo môn học
                    </PrimaryButton>
                </form>
            </div>

            <!-- LIST CARD -->
            <div class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl relative">
                <h3 class="text-xs font-bold text-slate-500 font-mono uppercase tracking-[0.2em] mb-6">Các môn học hiện có</h3>
                
                <ul class="space-y-3">
                    <li v-if="subjects.length === 0" class="py-8 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/30">
                        <p class="text-slate-500 font-mono text-sm">>> Chưa có môn học nào.</p>
                    </li>
                    
                    <li v-for="subject in subjects" :key="subject.id" 
                        class="group flex flex-col md:flex-row items-center justify-between p-4 bg-slate-900/50 border border-slate-800 rounded-xl hover:border-cyan-500/30 transition-all duration-300 hover:shadow-lg">
                        
                        <div v-if="editingId !== subject.id" class="flex items-center gap-4 w-full">
                            <div class="w-10 h-10 rounded-lg bg-cyan-900/20 border border-cyan-500/20 flex items-center justify-center text-cyan-500 font-bold font-mono">
                                {{ subject.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-slate-200 font-exo font-bold tracking-wide">{{ subject.name }}</span>
                            
                            <div class="ml-auto flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editSubject(subject)" class="p-2 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-lg transition border border-white/5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteSubject(subject.id)" class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition border border-white/5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                        
                        <form v-else @submit.prevent="submitUpdate(subject.id)" class="w-full flex flex-col md:flex-row gap-3 items-center">
                            <div class="w-10 h-10 rounded-lg bg-indigo-900/20 border border-indigo-500/20 flex items-center justify-center text-indigo-500 font-bold">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </div>
                            <TextInput
                                v-model="updateForm.name"
                                type="text"
                                class="flex-1 w-full bg-slate-950 border-indigo-500 text-white focus:ring-indigo-500/50 font-mono text-sm"
                                autofocus
                            />
                            <div class="flex gap-2">
                                <SecondaryButton @click="cancelEdit" class="!py-2">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="updateForm.processing" class="!bg-indigo-600 hover:!bg-indigo-500 !py-2">Update</PrimaryButton>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>