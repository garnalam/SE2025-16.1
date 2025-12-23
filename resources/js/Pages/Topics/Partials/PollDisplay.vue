<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    post: Object,
    authUserId: Number,
});

const totalVotes = computed(() => {
    return props.post.poll_options.reduce((total, option) => total + option.votes.length, 0);
});

const pollOptions = computed(() => {
    const total = totalVotes.value;
    return props.post.poll_options.map(option => {
        const votesCount = option.votes.length;
        const percentage = (total > 0) ? ((votesCount / total) * 100) : 0;
        const userVotedThis = option.votes.some(vote => vote.user_id === props.authUserId);
        return { ...option, votesCount, percentage, userVotedThis };
    });
});

const userHasVoted = computed(() => {
    return pollOptions.value.some(option => option.userVotedThis);
});

const voteForm = useForm({});
const castVote = (optionId) => {
    voteForm.post(route('poll-votes.store', optionId), { preserveScroll: true });
};
</script>

<template>
    <div class="p-4 bg-pink-900/10 border border-pink-500/20 rounded-xl">
        <h4 class="text-sm font-bold text-pink-400 mb-3 font-exo flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            KHẢO SÁT Ý KIẾN
        </h4>
        <p class="text-slate-300 text-sm mb-4">{{ post.content }}</p>

        <div class="space-y-3">
            <div v-for="option in pollOptions" :key="option.id">
                <button
                    v-if="!userHasVoted"
                    type="button"
                    @click="castVote(option.id)"
                    class="w-full text-left px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg hover:border-pink-500 hover:shadow-[0_0_15px_rgba(236,72,153,0.3)] hover:text-white text-slate-300 transition-all duration-300 text-sm group relative overflow-hidden"
                    :class="{ 'opacity-50 cursor-not-allowed': voteForm.processing }"
                    :disabled="voteForm.processing"
                >
                    <span class="relative z-10">{{ option.text }}</span>
                    <div class="absolute inset-0 bg-pink-500/10 translate-x-[-100%] group-hover:translate-x-0 transition-transform duration-300"></div>
                </button>

                <div v-else class="relative bg-slate-900 border border-slate-800 rounded-lg p-3 overflow-hidden">
                    <!-- Progress Bar Background -->
                    <div 
                        class="absolute top-0 left-0 h-full bg-gradient-to-r from-pink-600/30 to-pink-500/20 transition-all duration-1000 ease-out" 
                        :style="{ width: option.percentage + '%' }"
                    ></div>
                    <div class="absolute top-0 left-0 h-full w-[2px] bg-pink-500" :style="{ left: option.percentage + '%' }"></div>
                    
                    <div class="relative flex justify-between items-center z-10">
                        <div class="text-sm font-medium flex items-center gap-2" :class="option.userVotedThis ? 'text-pink-400' : 'text-slate-400'">
                            {{ option.text }}
                            <svg v-if="option.userVotedThis" class="w-4 h-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="text-xs font-mono font-bold" :class="option.userVotedThis ? 'text-pink-400' : 'text-slate-500'">
                            {{ option.votesCount }} <span class="opacity-50">/</span> {{ option.percentage.toFixed(0) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="userHasVoted" class="text-[10px] text-slate-500 mt-3 font-mono text-right uppercase tracking-widest">
            Tổng lượt vote: {{ totalVotes }} lượt
        </div>
    </div>
</template>