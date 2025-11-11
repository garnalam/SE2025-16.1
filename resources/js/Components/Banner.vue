<script setup>
import { ref, watch } from 'vue'; // <-- Sửa 1: Dùng 'watch' thay vì 'watchEffect'
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false); // <-- Sửa 2: Bắt đầu bằng 'false' (ẩn)
const style = ref('success');
const message = ref('');
let timeout = null; // Biến để theo dõi auto-hide

// Sửa 3: Thay thế toàn bộ 'watchEffect' bằng 'watch'
watch(() => page.props.flash, (flash) => {
    // Xóa timeout cũ (nếu có)
    if (timeout) clearTimeout(timeout);

    // 1. Lắng nghe thông báo 'success' của chúng ta
    if (flash.success) {
        style.value = 'success';
        message.value = flash.success;
        show.value = true;
        // Tự động ẩn sau 5 giây
        timeout = setTimeout(() => show.value = false, 5000);
    } 
    // 2. Lắng nghe thông báo 'error' của chúng ta
    else if (flash.error) {
        style.value = 'danger';
        message.value = flash.error;
        show.value = true;
        // (Lỗi thì không tự động ẩn)
    }
    // 3. Lắng nghe thông báo 'banner' mặc định của Jetstream (để tương thích)
    else if (page.props.jetstream.flash?.banner) {
        style.value = page.props.jetstream.flash.bannerStyle || 'success';
        message.value = page.props.jetstream.flash.banner;
        show.value = true;
    }
    // 4. Nếu không có gì, ẩn đi
    else {
        show.value = false;
    }
}, {
    deep: true // Theo dõi các thay đổi bên trong object 'flash'
});
</script>