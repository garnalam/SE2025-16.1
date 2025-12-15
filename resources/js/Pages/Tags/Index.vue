<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    tags: Array,
});

const createForm = useForm({
    name: '',
});

const updateForm = useForm({
    name: '',
});

const editingId = ref(null);

const editTag = (tag) => {
    editingId.value = tag.id;
    updateForm.name = tag.name;
};

const cancelEdit = () => {
    editingId.value = null;
    updateForm.reset();
};

const submitCreate = () => {
    createForm.post(route('tags.store'), {
        onSuccess: () => createForm.reset(),
        preserveScroll: true,
    });
};

const submitUpdate = (id) => {
    updateForm.put(route('tags.update', id), {
        onSuccess: () => cancelEdit(),
        preserveScroll: true,
    });
};

const deleteTag = (id) => {
    if (confirm('WARNING: Removing metadata tag. This will detach it from all questions. Confirm?')) {
        useForm({}).delete(route('tags.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Nhãn">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('questions.index')" class="p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-black text-xl text-white uppercase tracking-wide font-exo">
                    Nhãn
                </h2>
            </div>
        </template>

        <div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- CREATE CARD -->
            <div class="bg-[#0f172a] border border-purple-500/30 rounded-3xl p-6 shadow-[0_0_30px_rgba(168,85,247,0.15)] relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <h3 class="text-sm font-bold text-purple-400 font-exo uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                    Define New Metadata
                </h3>

                <form @submit.prevent="submitCreate" class="flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex-1 w-full">
                        <TextInput
                            v-model="createForm.name"
                            type="text"
                            class="w-full bg-slate-900 border-slate-700 text-white focus:border-purple-500 focus:ring-purple-500/50 placeholder-slate-600 font-mono text-sm"
                            placeholder="Tag Label (e.g. Chapter 1, Midterm)"
                        />
                        <InputError :message="createForm.errors.name" class="mt-2" />
                    </div>
                    <PrimaryButton :disabled="createForm.processing" class="whitespace-nowrap !bg-purple-600 hover:!bg-purple-500">
                        Generate Tag
                    </PrimaryButton>
                </form>
            </div>

            <!-- LIST CARD -->
            <div class="bg-[#0f172a] border border-slate-800 rounded-3xl p-6 shadow-xl relative">
                <h3 class="text-xs font-bold text-slate-500 font-mono uppercase tracking-[0.2em] mb-6">Available Metadata</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div v-if="tags.length === 0" class="col-span-full py-8 text-center border border-dashed border-slate-800 rounded-xl bg-slate-900/30">
                        <p class="text-slate-500 font-mono text-sm">>> NO TAGS FOUND.</p>
                    </div>
                    
                    <div v-for="tag in tags" :key="tag.id" 
                        class="group p-3 bg-slate-900/50 border border-slate-800 rounded-xl hover:border-purple-500/30 transition-all duration-200">
                        
                        <div v-if="editingId !== tag.id" class="flex items-center justify-between">
                            <span class="text-slate-300 font-mono text-xs font-bold truncate flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_5px_#a855f7]"></span>
                                {{ tag.name }}
                            </span>
                            
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editTag(tag)" class="p-1 text-slate-500 hover:text-white transition">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteTag(tag.id)" class="p-1 text-slate-500 hover:text-rose-500 transition">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                        
                        <form v-else @submit.prevent="submitUpdate(tag.id)" class="flex gap-2 items-center">
                            <TextInput
                                v-model="updateForm.name"
                                type="text"
                                class="w-full bg-slate-950 border-purple-500 text-white focus:ring-purple-500/50 font-mono text-xs h-8 px-2"
                                autofocus
                            />
                            <button type="button" @click="cancelEdit" class="text-slate-500 hover:text-white"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            <button type="submit" class="text-purple-500 hover:text-purple-400"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>