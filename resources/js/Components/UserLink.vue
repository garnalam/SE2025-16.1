<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    user: Object, // Object user bắt buộc phải có: id, name, profile_photo_url
    showAvatar: {
        type: Boolean,
        default: true
    },
    noLink: { // Tùy chọn: Nếu muốn hiển thị giống link nhưng không click được
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="inline-flex items-center gap-2 group">
        <div v-if="showAvatar" class="relative">
            <img :src="user.profile_photo_url" :alt="user.name" 
                 class="w-6 h-6 rounded-full border border-slate-600 group-hover:border-cyan-400 transition object-cover">
        </div>
        
        <Link v-if="!noLink" :href="route('profile.public', user.id)" 
              class="font-bold text-slate-300 group-hover:text-cyan-400 group-hover:underline transition font-exo text-sm truncate">
            {{ user.name }}
        </Link>
        
        <span v-else class="font-bold text-slate-300 font-exo text-sm truncate">
            {{ user.name }}
        </span>
    </div>
</template>