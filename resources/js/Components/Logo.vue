<template>
  <div :class="['flex items-center gap-3 font-bold text-white', className]">
    <div class="relative group">
      <div class="absolute -inset-1 bg-gradient-to-r from-sky-500 to-indigo-600 rounded-lg blur opacity-40 group-hover:opacity-75 transition duration-200"></div>
      <div class="relative bg-slate-900 ring-1 ring-white/10 p-2 rounded-lg text-white">
        <BookOpen :size="iconSize" />
      </div>
    </div>
    <span class="text-xl tracking-tight font-display">
      Smart<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-indigo-400">Class</span> AI
    </span>
  </div>
</template>

<script setup>
import { BookOpen } from 'lucide-vue-next';

defineProps({
  className: {
    type: String,
    default: ''
  },
  iconSize: {
    type: Number,
    default: 32
  }
});
</script>