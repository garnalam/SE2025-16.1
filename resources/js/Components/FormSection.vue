<script setup>
import { computed, useSlots } from 'vue';
import SectionTitle from './SectionTitle.vue';

defineProps({
    title: String,
    description: String,
});

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <SectionTitle>
            <template #title>
                <slot name="title" />
            </template>
            <template #description>
                <slot name="description" />
            </template>
        </SectionTitle>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="$emit('submitted')">
                <div
                    class="px-4 py-5 bg-[#0f172a] border border-white/5 sm:p-6 shadow-xl"
                    :class="hasActions ? 'sm:rounded-t-xl' : 'sm:rounded-xl'"
                >
                    <div class="grid grid-cols-6 gap-6">
                        <slot name="form" />
                    </div>
                </div>

                <div v-if="hasActions" class="flex items-center justify-end px-4 py-3 bg-slate-900/50 border-x border-b border-white/5 text-end sm:px-6 sm:rounded-b-xl">
                    <slot name="actions" />
                </div>
            </form>
        </div>
    </div>
</template>