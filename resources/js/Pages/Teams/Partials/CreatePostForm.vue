<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({ team: Object, topic: Object, canManageTopics: Boolean });

const postType = ref('text'); 
const fileInput = ref(null);

const form = useForm({
    post_type: 'text', 
    content: '', 
    poll_options: ['', ''], 
    title: '', 
    due_date: '', 
    max_points: '100', 
    files: [], 
});

// --- LOGIC ---
const switchTo = (type) => {
    postType.value = type;
    form.post_type = type;
    form.clearErrors();
};

const addPollOption = () => { if (form.poll_options.length < 10) form.poll_options.push(''); };
const removePollOption = (index) => { form.poll_options.splice(index, 1); };

const handleFileChange = (event) => { form.files = [...form.files, ...Array.from(event.target.files)]; };
const removeFile = (index) => {
    form.files.splice(index, 1);
    if (form.files.length === 0 && fileInput.value) fileInput.value.value = null;
};
const triggerFileInput = () => fileInput.value.click();

const createPost = () => {
    form.post_type = postType.value;
    if (form.post_type === 'quiz') form.max_points = null; 
    form.post(route('posts.store', props.topic), {
        errorBag: 'createPost',
        preserveScroll: true,
        onSuccess: () => { 
            form.reset(); 
            form.poll_options = ['', '']; 
            form.files = [];
            if(fileInput.value) fileInput.value.value = null;
        },
    });
};

// --- CONFIGURATION ---
const modes = [
    { id: 'text', label: 'Bài viết', icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3', color: 'indigo' },
    { id: 'assignment', label: 'Bài tập ', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', color: 'emerald', permission: true },
    { id: 'material', label: 'Tài liệu', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', color: 'cyan', permission: true },
    { id: 'quiz', label: 'Quiz', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0 1 18 0z', color: 'purple', permission: true },
    { id: 'poll', label: 'Khảo sát', icon: 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'pink' },
];

const currentMode = computed(() => modes.find(m => m.id === postType.value));

const buttonLabel = computed(() => {
     switch(postType.value) {
        case 'text': return 'Gửi';
        case 'material': return 'Tải lên';
        case 'assignment': return 'Đăng bài tập';
        case 'quiz': return 'Tạo';
        case 'poll': return 'Đăng khảo sát';
        default: return 'Gửi';
    }
});
</script>

<template>
    <div class="relative bg-[#0f172a] rounded-2xl overflow-hidden shadow-[0_0_30px_rgba(0,0,0,0.3)] border border-slate-800 transition-all duration-500 group max-w-5xl mx-auto">
        
        <!-- Ambient Glow based on mode -->
        <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r via-transparent to-transparent transition-colors duration-500"
             :class="`from-${currentMode.color}-500`"></div>

        <!-- COMPACT HEADER -->
        <div class="px-4 py-2 border-b border-white/5 bg-slate-900/50 backdrop-blur-sm flex items-center justify-between gap-2 overflow-x-auto custom-scrollbar h-12">
            <div class="flex gap-1">
                <template v-for="mode in modes" :key="mode.id">
                    <button 
                        v-if="!mode.permission || props.canManageTopics"
                        @click="switchTo(mode.id)"
                        type="button"
                        class="relative px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all duration-300 flex items-center gap-1.5 group/btn"
                        :class="postType === mode.id ? 'bg-white/10 text-white shadow-inner' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5'"
                    >
                        <svg class="w-3.5 h-3.5 transition-colors duration-300" :class="postType === mode.id ? `text-${mode.color}-400` : 'text-slate-600 group-hover/btn:text-slate-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mode.icon" /></svg>
                        <span>{{ mode.label }}</span>
                        <!-- Active Indicator -->
                        <div v-if="postType === mode.id" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 rounded-t-full transition-colors duration-300" :class="`bg-${mode.color}-500`"></div>
                    </button>
                </template>
            </div>
            
            <div class="hidden md:block text-[9px] font-mono uppercase tracking-widest opacity-50">
                <span :class="`text-${currentMode.color}-400`">●</span> Ready
            </div>
        </div>

        <!-- MAIN FORM BODY -->
        <form @submit.prevent="createPost" class="flex flex-col md:flex-row min-h-[320px]">
            
            <!-- LEFT COLUMN: CONTENT EDITOR (Denser padding) -->
            <div class="flex-1 p-5 flex flex-col gap-4 relative">
                
                <!-- Dynamic Title (Smaller Font) -->
                <div v-if="postType !== 'text' && postType !== 'poll'" class="relative group/title">
                    <input 
                        v-model="form.title" 
                        type="text" 
                        :placeholder="postType === 'quiz' ? 'Tiêu đề bài Quiz' : (postType === 'assignment' ? 'Tiêu đề bài tập' : 'Tiêu đề tài liệu')"
                        class="w-full bg-transparent border-none text-lg font-bold font-exo text-white placeholder-slate-700 focus:ring-0 px-0 py-1 transition-colors"
                    />
                    <div class="h-px w-8 group-focus-within/title:w-full bg-slate-700 group-focus-within/title:bg-white/20 transition-all duration-500"></div>
                    <InputError :message="form.errors.title" class="absolute top-full left-0" />
                </div>

                <!-- Main Text Area (Smaller text, less padding) -->
                <div class="flex-1 relative flex flex-col">
                    <TextArea
                        v-model="form.content"
                        class="flex-1 w-full bg-slate-800/20 hover:bg-slate-800/40 focus:bg-slate-800/60 border-none rounded-xl resize-none font-sans text-sm text-slate-300 placeholder-slate-600 p-3 transition-all duration-300 focus:ring-1 focus:ring-white/10 shadow-inner"
                        :placeholder="postType === 'poll' ? 'Hỏi điều gì đó...' : (postType === 'text' ? 'Viết điều gì đó...' : 'Viết điều gì đó...')"
                    />
                    <InputError :message="form.errors.content" class="mt-1" />
                </div>

                <!-- Poll Options -->
                <div v-if="postType === 'poll'" class="space-y-2 pl-3 border-l border-dashed border-pink-500/30">
                    <div v-for="(option, index) in form.poll_options" :key="index" class="flex items-center gap-2">
                        <span class="text-[9px] font-mono text-slate-600">0{{ index + 1 }}</span>
                        <TextInput v-model="form.poll_options[index]" type="text" class="flex-1 bg-slate-900/50 border-slate-700 rounded text-xs py-1 px-2 focus:border-pink-500 focus:ring-pink-500/20" :placeholder="'Lựa chọn ' + (index + 1)"/>
                        <button type="button" @click="removePollOption(index)" v-if="form.poll_options.length > 2" class="text-slate-600 hover:text-rose-500 transition">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <button type="button" @click="addPollOption" v-if="form.poll_options.length < 10" class="mt-1 text-[10px] font-bold text-pink-500 hover:text-white transition flex items-center gap-1">
                        + Add Option
                    </button>
                </div>
            </div>

            <!-- RIGHT COLUMN: CONFIG SIDEBAR (Narrower) -->
            <div class="w-full md:w-60 bg-black/20 border-l border-white/5 p-4 flex flex-col gap-5 backdrop-blur-sm">
                
                <!-- Section: Configuration -->
                <div v-if="['assignment', 'quiz'].includes(postType)">
                    <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 font-mono mb-2 border-b border-white/5 pb-1">Tùy chỉnh</h4>
                    
                    <div class="space-y-3">
                        <div class="group/input">
                            <InputLabel for="due_date" value="Deadline" />
                            <TextInput id="due_date" v-model="form.due_date" type="datetime-local" class="w-full text-[10px] py-1 font-mono bg-slate-900 border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20"/>
                            <InputError :message="form.errors.due_date" class="mt-1" />
                        </div>
                        
                        <div v-if="postType === 'assignment'" class="group/input">
                            <InputLabel for="max_points" value="Điểm tối đa" />
                            <div class="relative">
                                <TextInput id="max_points" v-model="form.max_points" type="number" min="0" class="w-full py-1 pl-2 pr-8 text-[10px] font-mono font-bold bg-slate-900 border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20"/>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-bold text-slate-500 group-focus-within/input:text-emerald-500 transition">PTS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Attachments -->
                <div v-if="['material', 'assignment'].includes(postType)" class="flex-1 flex flex-col">
                    <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 font-mono mb-2 border-b border-white/5 pb-1">Đính kèm file</h4>
                    
                    <div class="flex-1 flex flex-col gap-1.5">
                        <div v-for="(file, index) in form.files" :key="index" class="relative flex items-center justify-between p-2 bg-slate-800/50 border border-slate-700 rounded group hover:border-cyan-500/50 transition overflow-hidden">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <svg class="w-3 h-3 text-cyan-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span class="text-[10px] text-slate-300 truncate w-24">{{ file.name }}</span>
                            </div>
                            <button type="button" @click="removeFile(index)" class="text-slate-500 hover:text-rose-400">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <input ref="fileInput" type="file" multiple class="hidden" @change="handleFileChange">
                        <button type="button" @click="triggerFileInput" 
                                class="mt-1 w-full h-16 border border-dashed border-slate-700 rounded-lg flex flex-col items-center justify-center text-slate-500 hover:text-cyan-400 hover:border-cyan-500 hover:bg-cyan-500/5 transition-all duration-300 group/drop">
                            <svg class="w-4 h-4 mb-1 group-hover/drop:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span class="text-[8px] font-bold uppercase tracking-widest">Import từ thiết bị</span>
                        </button>
                    </div>
                </div>

                <div v-if="!['material', 'assignment', 'quiz'].includes(postType)" class="flex-1"></div>

                <!-- Submit Area -->
                <div class="pt-4 border-t border-white/5">
                    <PrimaryButton 
                        :class="[
                            { 'opacity-25': form.processing },
                            `w-full justify-center text-xs py-2 !bg-gradient-to-r !from-${currentMode.color}-600 !to-${currentMode.color}-500 hover:!shadow-[0_0_15px_rgba(0,0,0,0.3)] !border-none`
                        ]"
                        :disabled="form.processing"
                    >
                        {{ buttonLabel }}
                    </PrimaryButton>
                    <ActionMessage :on="form.recentlySuccessful" class="text-[9px] text-center mt-2 font-mono uppercase tracking-widest text-emerald-400">
                        Done.
                    </ActionMessage>
                </div>
            </div>

        </form>
    </div>
</template>