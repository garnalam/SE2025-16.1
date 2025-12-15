<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4 bg-[#0f172a] text-white">
            <div class="text-lg font-bold">
                <slot name="title" />
            </div>

            <div class="mt-4 text-sm text-slate-400">
                <slot name="content" />
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-slate-900/50 border-t border-white/5 text-end">
            <slot name="footer" />
        </div>
    </Modal>
</template>