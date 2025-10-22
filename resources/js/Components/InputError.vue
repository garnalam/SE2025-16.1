<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs font-medium text-rose-400">
            {{ message }}
        </p>
    </div>
</template>
