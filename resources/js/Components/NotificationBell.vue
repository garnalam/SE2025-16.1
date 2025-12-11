<script setup>
    import { ref, onMounted } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import axios from 'axios';
    
    const page = usePage();
    const notifications = ref([]);
    const unreadCount = ref(0);
    const isOpen = ref(false);
    
    // 1. Hàm lấy danh sách thông báo từ Server
    const fetchNotifications = async () => {
        try {
            const response = await axios.get(route('notifications.index'));
            notifications.value = response.data;
            // Đếm số thông báo chưa đọc (read_at là null)
            unreadCount.value = notifications.value.filter(n => !n.read_at).length;
        } catch (error) {
            console.error("Lỗi lấy thông báo:", error);
        }
    };
    
    // 2. Hàm xử lý khi bấm vào 1 thông báo
    const markAsRead = async (notification) => {
        if (!notification.read_at) {
            await axios.post(`/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
        // Chuyển trang (nếu thông báo có link)
        if (notification.data.url && notification.data.url !== '#') {
            window.location.href = notification.data.url;
        }
    };
    
    // 3. Lắng nghe Realtime
    onMounted(() => {
        fetchNotifications();
    
        if (page.props.auth.user) {
            const userId = page.props.auth.user.id;
            console.log("Đang lắng nghe thông báo cho User ID:", userId);
    
            Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log("ĐÃ NHẬN ĐƯỢC THÔNG BÁO:", notification);
                    
                    unreadCount.value++;
                    notifications.value.unshift({
                        id: notification.id,
                        data: { 
                            title: notification.title || 'Thông báo mới',
                            message: notification.message || '',
                            url: notification.url || '#',
                            icon: notification.icon || null, // [MỚI] Lấy icon từ event
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
        <div class="relative">
            <button @click="isOpen = !isOpen" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                
                <div v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                    {{ unreadCount }}
                </div>
            </button>
    
            <div v-if="isOpen" class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none max-h-96 overflow-y-auto">
                <div class="px-4 py-2 border-b text-sm font-semibold text-gray-700">Thông báo</div>
                
                <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500 text-sm">
                    Không có thông báo mới.
                </div>
    
                <div v-for="notify in notifications" :key="notify.id" 
                     @click="markAsRead(notify)"
                     :class="{'bg-blue-50': !notify.read_at}"
                     class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b last:border-0 transition flex items-start">
                    
                    <div class="shrink-0 mr-3 pt-1">
                        <img v-if="notify.data.icon && notify.data.icon !== 'badges/default.png'" 
                             :src="'/storage/' + notify.data.icon" 
                             class="h-8 w-8 rounded-full bg-yellow-50 object-contain border border-yellow-200">
                        
                        <img v-else-if="notify.data.user_avatar" 
                             :src="notify.data.user_avatar" 
                             class="h-8 w-8 rounded-full object-cover">
                        
                        <span v-else class="flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100 text-indigo-600">
                            🔔
                        </span>
                    </div>
    
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase mb-0.5">
                            {{ notify.data.team_name || 'Hệ thống' }}
                        </p>
    
                        <p class="text-sm font-bold text-gray-900 leading-tight">
                            {{ notify.data.title }}
                        </p>
    
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                            {{ notify.data.message }} 
                        </p>
                        
                        <p class="text-[10px] text-gray-400 mt-1">
                            {{ notify.created_at ? new Date(notify.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'Vừa xong' }}
                        </p>
                    </div>
                </div>
            </div>
    
            <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40 h-full w-full cursor-default"></div>
        </div>
    </template>