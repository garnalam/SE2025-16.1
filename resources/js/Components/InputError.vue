<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs font-medium text-rose-400 font-mono mt-1">
            <span class="mr-1">⚠</span> {{ message }}
        </p>
    </div>
</template>