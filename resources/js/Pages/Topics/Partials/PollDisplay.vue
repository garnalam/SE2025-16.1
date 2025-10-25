<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    post: Object,
    authUserId: Number,
});

// === TÍNH TOÁN KẾT QUẢ ===

// 1. Tổng số phiếu bầu của poll này
const totalVotes = computed(() => {
    return props.post.poll_options.reduce((total, option) => total + option.votes.length, 0);
});

// 2. Tính toán các lựa chọn (thêm % và kiểm tra user)
const pollOptions = computed(() => {
    const total = totalVotes.value;

    return props.post.poll_options.map(option => {
        const votesCount = option.votes.length;
        // Tính % (tránh chia cho 0)
        const percentage = (total > 0) ? ((votesCount / total) * 100) : 0;
        
        // Kiểm tra xem user hiện tại đã vote cho lựa chọn NÀY chưa
        const userVotedThis = option.votes.some(vote => vote.user_id === props.authUserId);

        return {
            ...option, // id, text, post_id, etc.
            votesCount,
            percentage,
            userVotedThis,
        };
    });
});

// 3. Kiểm tra xem user đã vote cho poll NÀY (bất kỳ lựa chọn nào) chưa
const userHasVoted = computed(() => {
    return pollOptions.value.some(option => option.userVotedThis);
});

// === LOGIC BÌNH CHỌN (SẼ DÙNG Ở BƯỚC 7.7) ===
const voteForm = useForm({});

const castVote = (optionId) => {
    voteForm.post(route('poll-votes.store', optionId), {
        preserveScroll: true,
        // (Chúng ta sẽ tạo route 'poll-votes.store' ở bước 7.7)
    });
};

</script>

<template>
    <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ post.content }}</h4>

    <div class="space-y-3">
        
        <div v-for="option in pollOptions" :key="option.id">

            <button
                v-if="!userHasVoted"
                type="button"
                @click="castVote(option.id)"
                class="w-full text-left px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                :class="{ 'opacity-25': voteForm.processing }"
                :disabled="voteForm.processing"
            >
                {{ option.text }}
            </button>

            <div v-else class="relative border border-gray-300 rounded-lg p-3 overflow-hidden">
                <div 
                    class="absolute top-0 left-0 h-full bg-indigo-100" 
                    :style="{ width: option.percentage + '%' }"
                ></div>
                
                <div class="relative flex justify-between items-center">
                    <div class="font-semibold" :class="{ 'text-indigo-800': option.userVotedThis }">
                        {{ option.text }}
                        <span v-if="option.userVotedThis"> (✓ Đã chọn)</span>
                    </div>
                    <div class="text-sm text-gray-700 font-medium">
                        {{ option.votesCount }} phiếu ({{ option.percentage.toFixed(0) }}%)
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <div v-if="userHasVoted" class="text-sm text-gray-600 mt-3">
        Tổng số: {{ totalVotes }} phiếu bầu.
    </div>
</template>