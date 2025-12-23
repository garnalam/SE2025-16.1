<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import ActionMessage from '@/Components/ActionMessage.vue';

const props = defineProps({
    post: Object, 
    canManageTopics: Boolean, 
    userSubmission: Object, 
});

const isLate = computed(() => {
    if (!props.userSubmission || !props.post.due_date) return false;
    return new Date(props.userSubmission.submitted_at) > new Date(props.post.due_date);
});

const formattedScore = computed(() => {
    if (props.userSubmission?.grade === null) return null;
    return Number(props.userSubmission.grade).toString();
});

const form = useForm({
    content: props.userSubmission?.content ?? '',
    files: [],
});

const fileInput = ref(null);

const handleFileChange = (event) => { form.files = Array.from(event.target.files); };
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
    <div class="mt-2 relative">
        <!-- Decoration Line -->
        <div class="absolute left-0 top-0 bottom-0 w-px bg-gradient-to-b from-emerald-500/50 to-transparent"></div>

        <div class="pl-4 md:pl-6 space-y-6">
            
            <!-- Header Info -->
            <div class="flex flex-wrap gap-4 items-start justify-between bg-emerald-900/10 border border-emerald-500/20 rounded-xl p-4">
                <div>
                    <h3 class="text-lg font-bold text-white font-exo flex items-center gap-2">
                        <span class="text-emerald-400">BÀI TẬP</span>
                        <span class="text-slate-600">//</span>
                        <span>{{ post.title || 'Untitled Assignment' }}</span>
                    </h3>
                    
                    <div v-if="post.due_date" class="flex items-center gap-2 mt-2 text-[10px] font-mono uppercase tracking-widest text-slate-400">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Deadline: {{ new Date(post.due_date).toLocaleString('vi-VN') }}</span>
                    </div>
                </div>
                <div class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 rounded text-center">
                    <div class="text-xs font-mono text-emerald-500 uppercase tracking-wider">Điểm tối đa</div>
                    <div class="text-xl font-bold text-white font-exo">{{ post.max_points }} <span class="text-xs text-slate-500">PTS</span></div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="text-slate-300 whitespace-pre-wrap leading-relaxed text-sm font-sans pl-1">
                {{ post.content }}
            </div>

            <!-- Attachments (Teacher provided) -->
            <div v-if="post.attachments && post.attachments.length > 0" class="space-y-2">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest font-mono">Bài tập đính kèm</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a v-for="file in post.attachments" :key="file.id" :href="'/storage/' + file.path" target="_blank" 
                       class="flex items-center gap-3 p-2 bg-slate-900 border border-slate-700 hover:border-emerald-500 rounded transition group">
                        <div class="p-1.5 bg-slate-800 rounded text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <span class="text-xs text-slate-300 group-hover:text-white truncate font-mono">{{ file.original_name }}</span>
                    </a>
                </div>
            </div>

            <div class="w-full h-px bg-slate-800"></div>

            <!-- TEACHER VIEW -->
            <div v-if="props.canManageTopics" class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-white/5">
                <div>
                    <h4 class="text-sm font-bold text-white font-exo uppercase">Quản lý bài nộp</h4>
                    <p class="text-xs text-slate-400 font-mono mt-1">Review các bài nộp của sinh viên và chấm điểm.</p>
                </div>
                <Link :href="route('submissions.index', post.id)">
                    <PrimaryButton class="!bg-indigo-600 hover:!bg-indigo-500 text-xs uppercase tracking-widest">
                        TRUY CẬP
                    </PrimaryButton>
                </Link>
            </div>

            <!-- STUDENT VIEW -->
            <div v-else class="space-y-6">
                
                <!-- Status: Graded -->
                <div v-if="userSubmission && userSubmission.grade !== null" class="relative overflow-hidden bg-emerald-900/10 border border-emerald-500/30 rounded-xl p-5">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl"></div>
                    
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <h4 class="font-bold text-lg text-emerald-400 flex items-center gap-2 font-exo">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0 1 18 0z" /></svg>
                                GRADED
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-slate-400 font-mono">Evaluated: {{ new Date(userSubmission.graded_at).toLocaleString('vi-VN') }}</span>
                                <span v-if="isLate" class="text-[9px] bg-rose-500/20 text-rose-400 px-1.5 py-0.5 rounded border border-rose-500/30 uppercase font-bold">LATE SUBMISSION</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-3xl font-black text-white font-mono tracking-tight">{{ formattedScore }}</span>
                            <span class="text-[10px] text-emerald-500 font-bold uppercase tracking-wider">/ {{ post.max_points }} PTS</span>
                        </div>
                    </div>

                    <div v-if="userSubmission.feedback" class="mt-4 p-3 bg-black/40 rounded border border-emerald-500/20">
                        <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1 font-mono">Officer Feedback:</p>
                        <p class="text-slate-300 text-sm italic">"{{ userSubmission.feedback }}"</p>
                    </div>
                </div>

                <!-- Status: Submitted (Not Graded) -->
                <div v-else-if="userSubmission" class="bg-amber-900/10 border border-amber-500/30 rounded-xl p-4 flex items-start gap-4">
                    <div class="p-2 bg-amber-500/20 rounded-lg text-amber-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-400 font-exo uppercase tracking-wide">Submission Pending Review</h4>
                        <p class="text-xs text-slate-400 mt-1 font-mono">
                            Timestamp: {{ new Date(userSubmission.submitted_at).toLocaleString('vi-VN') }}
                            <span v-if="isLate" class="text-rose-400 font-bold ml-1">[LATE]</span>
                        </p>
                        <!-- Preview Content -->
                        <div class="mt-2 text-xs text-slate-500 border-l-2 border-slate-700 pl-3">
                            <p v-if="userSubmission.content" class="line-clamp-2">{{ userSubmission.content }}</p>
                            <div v-if="userSubmission.files && userSubmission.files.length" class="flex gap-2 mt-1">
                                <span v-for="f in userSubmission.files" :key="f.id" class="px-1.5 py-0.5 bg-slate-800 rounded text-[10px]">{{ f.original_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Form -->
                <form @submit.prevent="submitAssignment" class="bg-slate-900/50 border border-slate-700 rounded-xl p-4">
                    <h4 class="font-bold text-xs text-slate-400 uppercase tracking-[0.2em] mb-4 font-mono">
                        {{ userSubmission ? 'Update Upload' : 'Upload Solution' }}
                    </h4>
                    
                    <div class="space-y-4">
                        <div>
                            <InputLabel value="Text Response" class="!text-slate-500" />
                            <TextArea
                                v-model="form.content"
                                class="mt-1 block w-full bg-black/50 border-slate-700 text-white placeholder-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20 text-sm font-mono"
                                rows="3"
                                placeholder="// Enter text response..."
                            />
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Data Files" class="!text-slate-500" />
                            <div class="mt-1 relative group">
                                <input 
                                    ref="fileInput"
                                    type="file" 
                                    multiple
                                    class="block w-full text-xs text-slate-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-[10px] file:font-bold file:uppercase file:tracking-widest
                                    file:bg-slate-800 file:text-emerald-500
                                    hover:file:bg-slate-700 cursor-pointer border border-slate-700 rounded-lg bg-black/20 p-1"
                                    @change="handleFileChange"
                                >
                            </div>
                            <InputError :message="form.errors.files" class="mt-2" />
                            
                            <!-- File List Preview -->
                            <div v-if="form.files.length > 0" class="mt-2 space-y-1">
                                <div v-for="(file, index) in form.files" :key="index" class="flex justify-between items-center text-xs p-2 bg-slate-800/50 rounded border border-white/5 text-slate-300">
                                    <span class="truncate max-w-xs font-mono">{{ file.name }}</span>
                                    <button type="button" @click="removeFile(index)" class="text-rose-500 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-700 flex items-center justify-end gap-4">
                            <ActionMessage :on="form.recentlySuccessful" class="text-[10px] font-mono uppercase tracking-widest text-emerald-400">
                                Upload Complete
                            </ActionMessage>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="!bg-emerald-600 hover:!bg-emerald-500 text-xs uppercase tracking-widest">
                                {{ userSubmission ? 'Update' : 'Submit' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>