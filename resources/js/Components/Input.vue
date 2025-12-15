<template>
  <div class="mb-5">
    <label :for="id" class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">
      {{ label }}
    </label>
    <div class="relative group">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg blur opacity-0 group-hover:opacity-30 transition duration-500"></div>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="relative w-full px-4 py-3 rounded-lg bg-slate-900/80 border border-white/10 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all duration-300 shadow-inner"
            :class="[error ? 'border-red-500/50 focus:ring-red-500' : '', className]"
            v-bind="$attrs"
        />
    </div>
    <p v-if="error" class="mt-1.5 text-sm text-red-400 flex items-center gap-1 ml-1">
        <span class="inline-block w-1 h-1 rounded-full bg-red-400"></span> {{ error }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  id: String,
  error: String,
  type: {
    type: String,
    default: 'text'
  },
  modelValue: [String, Number],
  className: {
    type: String,
    default: ''
  }
});
</script>