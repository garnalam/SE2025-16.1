<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const notifications = ref([]);
const unreadCount = ref(0);
const isOpen = ref(false);

// Ref để tham chiếu đến nút bấm (để tính toán vị trí)
const buttonRef = ref(null);
const dropdownTop = ref(0);

// 1. Hàm lấy danh sách thông báo
const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data;
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error("Lỗi lấy thông báo:", error);
    }
};
const markAllAsRead = async () => {
    try {
        // 1. Gọi API xuống Backend
        await axios.post(route('notifications.readAll'));
        
        // 2. Cập nhật Frontend ngay lập tức
        unreadCount.value = 0;
        
        // 3. Cập nhật trạng thái từng thông báo trong danh sách hiện tại để UI đổi màu
        notifications.value.forEach(n => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
    } catch (error) {
        console.error("Lỗi khi đánh dấu tất cả đã đọc:", error);
    }
};

// 2. Hàm Toggle Menu (Tính toán vị trí trước khi mở)
const toggleMenu = () => {
    if (!isOpen.value) {
        // Tính toán vị trí nút bấm hiện tại trên màn hình
        if (buttonRef.value) {
            const rect = buttonRef.value.getBoundingClientRect();
            // Set vị trí top của modal bằng vị trí top của nút
            dropdownTop.value = rect.top; 
        }
        isOpen.value = true;
    } else {
        isOpen.value = false;
    }
};

// 3. Hàm xử lý khi bấm vào 1 thông báo
const markAsRead = async (notification) => {
    if (!notification.read_at) {
        await axios.post(`/notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
    isOpen.value = false;
    if (notification.data.url && notification.data.url !== '#') {
        window.location.href = notification.data.url;
    }
};

// 4. Realtime
onMounted(() => {
    fetchNotifications();
    if (page.props.auth.user) {
        const userId = page.props.auth.user.id;
        Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                unreadCount.value++;
                notifications.value.unshift({
                    id: notification.id,
                    data: { 
                        title: notification.title || 'Thông báo mới',
                        message: notification.message || '',
                        url: notification.url || '#',
                        icon: notification.icon || null,
                        team_name: notification.team_name || 'Hệ thống'
                    },
                    read_at: null,
                    created_at: new Date().toISOString()
                });
            });
    }
});
</script>

<template>
    <div class="relative w-full"> 
        
        <button ref="buttonRef" 
                @click="toggleMenu" 
                class="group flex items-center w-full px-3 py-3 text-sm font-medium rounded-r-md transition-all duration-200 focus:outline-none"
                :class="isOpen ? 'bg-indigo-600/10 text-indigo-300 border-l-2 border-indigo-500' : 'text-slate-400 hover:bg-white/5 hover:text-white border-l-2 border-transparent'">
            
            <svg class="mr-3 h-5 w-5 opacity-70 group-hover:opacity-100 transition-opacity" 
                 :class="isOpen ? 'text-indigo-400' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            
            <span class="flex-1 text-left">Thông báo</span>

            <span v-if="unreadCount > 0" 
                  class="ml-2 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-[0_0_10px_rgba(244,63,94,0.5)] animate-pulse">
                {{ unreadCount }}
            </span>
        </button>

        <Teleport to="body">
            <div v-if="isOpen" class="fixed inset-0 z-[9998]" @click="isOpen = false"></div>

            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95 -translate-x-2"
                enter-to-class="transform opacity-100 scale-100 translate-x-0"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100 translate-x-0"
                leave-to-class="transform opacity-0 scale-95 -translate-x-2"
            >
                <div v-if="isOpen" 
                     :style="{ top: dropdownTop + 'px' }"
                     class="fixed left-[18.5rem] w-96 rounded-xl bg-slate-900 border border-slate-700 shadow-[0_0_30px_rgba(0,0,0,0.6)] ring-1 ring-white/10 z-[9999] overflow-hidden flex flex-col max-h-[80vh]">
                    
                    <div class="px-4 py-3 border-b border-slate-800 bg-slate-900/95 backdrop-blur flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Thông báo</span>
                        <span v-if="unreadCount > 0" class="text-[10px] text-indigo-400 cursor-pointer hover:underline" @click="markAllAsRead">Đọc tất cả</span>
                    </div>
                    
                    <div class="overflow-y-auto custom-scrollbar flex-1">
                        <div v-if="notifications.length === 0" class="p-8 text-center">
                            <p class="text-slate-500 text-sm">Không có thông báo mới.</p>
                        </div>

                        <div v-for="notify in notifications" :key="notify.id" 
                             @click="markAsRead(notify)"
                             class="group px-4 py-3 border-b border-slate-800 cursor-pointer transition-colors duration-200 flex gap-3 relative"
                             :class="notify.read_at ? 'hover:bg-slate-800/50 bg-slate-900' : 'bg-indigo-900/10 hover:bg-indigo-900/20'">
                            
                            <div v-if="!notify.read_at" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 shadow-[0_0_10px_#6366f1]"></div>

                            <div class="shrink-0 pt-1">
                                <div class="h-8 w-8 rounded-full bg-indigo-500/20 flex items-center justify-center ring-1 ring-indigo-500/30 text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wide truncate pr-2">
                                        {{ notify.data.team_name }}
                                    </p>
                                    <span class="text-[10px] text-slate-600 whitespace-nowrap">
                                        {{ notify.created_at ? new Date(notify.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Mới' }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-200 group-hover:text-indigo-300 transition-colors leading-snug mb-1">
                                    {{ notify.data.title }}
                                </h4>
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ notify.data.message }} 
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </Teleport>
    </div>
</template>