import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

console.log('--- 1. Bắt đầu khởi tạo Echo ---');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1',
    forceTLS: true,
    
    // Cấu hình Authorization Tương Đối (Relative Path)
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                console.log('--- 2. Đang xin quyền (Authorizing) ---');
                console.log('Channel:', channel.name);
                console.log('SocketID:', socketId);

                // Dùng đường dẫn tương đối chuẩn
                axios.post('/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    console.log('--- 3. Xin quyền THÀNH CÔNG (Auth Success) ---');
                    callback(false, response.data);
                })
                .catch(error => {
                    console.error('--- 3. Xin quyền THẤT BẠI (Auth Failed) ---');
                    console.error(error);
                    callback(true, error);
                });
            }
        };
    },
});