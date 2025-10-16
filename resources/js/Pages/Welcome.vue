<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const heroHighlights = [
    {
        title: 'Kết nối thời gian thực',
        description: 'Điểm danh, gửi thông báo và phản hồi tức thời giúp tiết kiệm 40% thời gian quản lý lớp học hàng tuần.',
    },
    {
        title: 'Không gian học tập sống động',
        description: 'Tài liệu, timeline, diễn đàn và bảng điều khiển cá nhân hòa làm một trải nghiệm mượt mà cho từng vai trò.',
    },
    {
        title: 'Phân tích thông minh',
        description: 'Biểu đồ tương tác đa chiều giúp phát hiện sớm những học sinh đang gặp khó khăn và tối ưu kế hoạch dạy học.',
    },
];

const heroQuickFeatures = [
    {
        title: 'Lịch học đồng bộ',
        description: 'Đồng bộ Google Calendar, gửi nhắc tự động và tránh xung đột lịch thi.',
    },
    {
        title: 'Rubrics minh bạch',
        description: 'Biểu mẫu chấm điểm công khai giúp học sinh hiểu rõ kỳ vọng.',
    },
    {
        title: 'Biểu đồ trực quan',
        description: 'Heatmap, radar và biểu đồ tiến độ theo tuần luôn cập nhật realtime.',
    },
];

const roles = [
    {
        name: 'Học sinh',
        gradient: 'from-sky-500/30 via-sky-400/20 to-transparent',
        description: 'Trung tâm của trải nghiệm học tập với bảng điều khiển cá nhân, lịch tự động, ghi chú trực tiếp trên tài liệu và diễn đàn tương tác.',
        features: [
            'Bảng Việc Cần Làm hợp nhất hạn nộp, thông báo và nhắc nhở thông minh.',
            'Kho học liệu đầy đủ với khả năng highlight, ghi chú cá nhân và xem lại lịch sử nộp bài.',
            'Biểu đồ tiến độ giúp so sánh ẩn danh với mặt bằng chung của lớp.',
        ],
    },
    {
        name: 'Giáo viên',
        gradient: 'from-amber-400/30 via-amber-300/20 to-transparent',
        description: 'Điều phối toàn bộ hành trình học tập: tổ chức nội dung theo chủ đề, giao bài đa dạng và chấm điểm minh bạch với rubrics.',
        features: [
            'Tạo lớp học nhiều phong cách, mời học sinh bằng email hoặc mã lớp.',
            'Ngân hàng câu hỏi tái sử dụng, hỗ trợ trắc nghiệm tự chấm, tự luận, bài nhóm.',
            'Chấm bài với phản hồi chi tiết, ghi âm, kiểm tra đạo văn ngay trong một bước.',
        ],
    },
    {
        name: 'Admin',
        gradient: 'from-violet-500/30 via-violet-400/20 to-transparent',
        description: 'Quản trị hệ thống "Lớp Học Tương Tác" với tầm nhìn chiến lược, đảm bảo an toàn dữ liệu và mở rộng chức năng hiệu quả.',
        features: [
            'Cấu hình vai trò, phân quyền chi tiết và theo dõi nhật ký hoạt động.',
            'Quản lý thương hiệu lớp học với cover, màu chủ đạo và chủ đề theo mùa.',
            'Giám sát hiệu suất toàn tổ chức bằng dashboard hợp nhất.',
        ],
    },
];

const studentInsights = [
    {
        title: 'Theo dõi tiến độ 360°',
        description: 'Biểu đồ dạng radar, heatmap và đường thời gian giúp học sinh nắm rõ điểm mạnh yếu, chủ động điều chỉnh chiến lược học.',
    },
    {
        title: 'Nộp bài đa định dạng',
        description: 'Upload file, ghi âm, ghi chú trực tiếp hoặc viết văn bản – mọi thứ đều được lưu phiên bản và phản hồi rõ ràng.',
    },
    {
        title: 'Tương tác cộng đồng',
        description: 'Diễn đàn lớp học, bình chọn nhanh, khen thưởng ngang hàng tạo nên văn hóa học tập tích cực và gắn kết.',
    },
];

const teacherInsights = [
    {
        title: 'Quy trình giao – chấm – trả bài liền mạch',
        description: 'Thiết lập rubrics minh bạch, giao bài theo nhóm, gợi ý phản hồi và trả điểm chỉ trong một giao diện duy nhất.',
    },
    {
        title: 'Bài giảng được hệ thống hóa',
        description: 'Chuyển đổi giáo án thành module với video, tài liệu, quiz tương tác và đường dẫn tới công cụ bên ngoài.',
    },
    {
        title: 'Phân tích lớp học theo thời gian thực',
        description: 'Heatmap hoàn thành bài tập, biểu đồ phổ điểm, cảnh báo sớm học sinh nguy cơ giúp giáo viên phản ứng kịp thời.',
    },
];

const roadmap = [
    {
        title: 'Gamification',
        description: 'Điểm kinh nghiệm, huy hiệu, bảng xếp hạng tùy bật/tắt để tạo động lực học tập bền vững.',
    },
    {
        title: 'Cổng thông tin phụ huynh',
        description: 'Liên kết tài khoản học sinh với phụ huynh để theo dõi điểm số, chuyên cần và phản hồi riêng.',
    },
    {
        title: 'Tích hợp công cụ bên ngoài',
        description: 'Nhúng quiz từ Kahoot!, Quizlet, video Edpuzzle và đồng bộ Google Calendar chỉ với vài cú nhấp.',
    },
    {
        title: 'Peer Review ẩn danh',
        description: 'Học sinh đánh giá chéo theo rubrics có sẵn, giúp nâng cao kỹ năng phản biện và học hỏi lẫn nhau.',
    },
    {
        title: 'Không gian dự án nhóm',
        description: 'Kanban, chat riêng, timeline tiến độ giúp mỗi nhóm vận hành như một team chuyên nghiệp.',
    },
];

const ctaHighlights = [
    {
        title: 'Onboarding 1-1',
        description: 'Chuyên gia đồng hành giúp giáo viên chuẩn hóa lớp học chỉ trong 3 giờ.',
    },
    {
        title: 'Kho mẫu sẵn sàng',
        description: 'Template timeline, rubrics và diễn đàn mẫu sẵn dùng cho từng môn học.',
    },
    {
        title: 'Hỗ trợ nâng cấp liên tục',
        description: 'Bản cập nhật hàng tháng cùng workshop online để tối ưu trải nghiệm.',
    },
];

const stats = [
    { label: 'Lớp học đang hoạt động', value: '128', accent: 'from-sky-400 to-sky-600' },
    { label: 'Tài liệu số hóa', value: '2.4K+', accent: 'from-emerald-400 to-emerald-600' },
    { label: 'Phản hồi giáo viên mỗi tuần', value: '5.3K', accent: 'from-amber-400 to-amber-600' },
    { label: 'Mức độ hài lòng người dùng', value: '96%', accent: 'from-violet-400 to-violet-600' },
];
</script>

<template>
    <Head title="Lớp Học Tương Tác" />
    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-[-20%] top-[-10%] h-[420px] w-[420px] rounded-full bg-gradient-to-br from-sky-500/40 via-indigo-500/40 to-purple-500/20 blur-3xl"></div>
            <div class="absolute right-[-15%] top-[10%] h-[360px] w-[360px] rounded-full bg-gradient-to-br from-amber-400/30 via-rose-500/30 to-transparent blur-3xl"></div>
            <div class="absolute bottom-[-25%] left-[10%] h-[540px] w-[540px] rounded-full bg-gradient-to-br from-cyan-400/20 via-violet-500/30 to-transparent blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.18),_rgba(2,6,23,0))]"></div>
        </div>

        <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-24 px-6 py-16 lg:px-12">
            <header class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-10 shadow-[0_30px_120px_-70px_rgba(56,189,248,0.65)] backdrop-blur-xl lg:p-14">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-24 top-12 hidden h-48 w-48 rounded-full bg-gradient-to-br from-sky-400/30 via-cyan-300/20 to-transparent blur-3xl lg:block"></div>
                    <div class="absolute right-6 top-8 h-24 w-24 rounded-full bg-gradient-to-br from-sky-400/40 via-indigo-400/30 to-transparent blur-3xl"></div>
                    <div class="absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent opacity-60"></div>
                    <div class="absolute -bottom-32 left-24 h-64 w-64 rounded-full bg-gradient-to-tr from-violet-500/20 via-fuchsia-500/20 to-transparent blur-3xl"></div>
                    <div class="absolute right-10 bottom-10 h-24 w-24 rounded-full border border-white/10 opacity-40"></div>
                </div>
                <div class="relative flex flex-col gap-12 lg:flex-row lg:items-center lg:gap-16">
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.4em] text-sky-100/80">
                            <span class="size-2 rounded-full bg-sky-300"></span>
                            Nền tảng quản lý lớp học thế hệ mới
                        </div>
                        <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">Lớp Học Tương Tác</h1>
                        <p class="mt-6 max-w-2xl text-lg text-slate-200/90">
                            Biến mỗi giờ học thành trải nghiệm đa chiều với bảng điều khiển trực quan, giao tiếp thời gian thực và phân tích sâu sát. Được thiết kế cho giáo viên dẫn dắt, học sinh chủ động và admin quản trị tầm nhìn dài hạn.
                        </p>
                        <div class="mt-10 flex flex-wrap items-center gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="group inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-sky-400 via-indigo-500 to-violet-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:shadow-xl hover:shadow-sky-500/40"
                            >
                                Bắt đầu trải nghiệm
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white/90 transition hover:border-white/60 hover:bg-white/15"
                            >
                                Đăng nhập với tài khoản hiện có
                            </Link>
                        </div>
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="feature in heroQuickFeatures"
                                :key="feature.title"
                                class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-xl transition hover:-translate-y-1 hover:border-white/30"
                            >
                                <div class="absolute inset-0 bg-gradient-to-br from-white/5 via-transparent to-transparent opacity-0 transition group-hover:opacity-70"></div>
                                <h3 class="text-sm font-semibold text-white">{{ feature.title }}</h3>
                                <p class="mt-2 text-xs text-slate-200/80">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative flex-1">
                        <div class="absolute -top-10 right-0 h-24 w-24 rounded-full bg-gradient-to-br from-cyan-400/30 via-sky-300/40 to-transparent blur-3xl"></div>
                        <div class="absolute -left-8 top-1/2 hidden h-32 w-32 -translate-y-1/2 rounded-full bg-gradient-to-br from-indigo-400/20 via-white/10 to-transparent blur-3xl lg:block"></div>
                        <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60 p-8 shadow-2xl shadow-sky-900/30">
                            <div class="absolute -top-6 right-10 h-16 w-16 rounded-full border border-white/20 opacity-30"></div>
                            <div class="flex flex-wrap gap-4">
                                <div
                                    v-for="stat in stats"
                                    :key="stat.label"
                                    class="flex-1 min-w-[140px] rounded-2xl bg-white/5 p-4 text-sm text-slate-200/80"
                                >
                                    <p class="text-xs uppercase tracking-wide text-slate-400/80">{{ stat.label }}</p>
                                    <p class="mt-3 text-2xl font-semibold text-white">
                                        <span class="bg-clip-text text-transparent" :class="['bg-gradient-to-r', stat.accent]">{{ stat.value }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-950/60 p-6">
                                <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Điểm nhấn</p>
                                <ul class="mt-4 space-y-3 text-slate-200/90">
                                    <li v-for="highlight in heroHighlights" :key="highlight.title">
                                        <p class="font-medium text-white">{{ highlight.title }}</p>
                                        <p class="mt-1 text-sm text-slate-300/80">{{ highlight.description }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <section class="relative">
                <div class="absolute inset-0 -z-10 rounded-[36px] border border-white/5 bg-white/5 backdrop-blur-sm"></div>
                <div class="grid gap-10 rounded-[32px] p-8 lg:grid-cols-3 lg:p-12">
                    <div
                        v-for="role in roles"
                        :key="role.name"
                        class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/70 p-8 shadow-[0_40px_120px_-70px_rgba(22,101,216,0.45)] transition duration-500 hover:-translate-y-1 hover:border-white/20"
                    >
                        <div :class="['absolute inset-0 pointer-events-none opacity-70 bg-gradient-to-br', role.gradient]"></div>
                        <div class="relative">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-white/70">{{ role.name }}</p>
                            <h3 class="mt-4 text-xl font-semibold text-white">Trải nghiệm dành riêng</h3>
                            <p class="mt-4 text-sm leading-relaxed text-slate-200/85">{{ role.description }}</p>
                            <ul class="mt-6 space-y-3 text-sm text-slate-100/80">
                                <li v-for="feature in role.features" :key="feature" class="flex gap-2">
                                    <span class="mt-1 size-2 rounded-full bg-white/70"></span>
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-10 lg:grid-cols-2">
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-10 backdrop-blur-xl">
                    <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-gradient-to-br from-sky-400/40 via-sky-200/30 to-transparent blur-3xl"></div>
                    <p class="text-sm uppercase tracking-[0.4em] text-sky-200/80">Học sinh</p>
                    <h2 class="mt-4 text-3xl font-semibold text-white">Hành trình học tập chủ động</h2>
                    <p class="mt-4 text-slate-200/85">Từ bảng điều khiển cá nhân đến ghi chú ngay trên PDF, mỗi chi tiết hỗ trợ học sinh tự tin chinh phục mục tiêu học tập.</p>
                    <div class="mt-8 space-y-6">
                        <div v-for="item in studentInsights" :key="item.title" class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-inner shadow-sky-900/40">
                            <h3 class="text-lg font-semibold text-white">{{ item.title }}</h3>
                            <p class="mt-2 text-sm text-slate-200/80">{{ item.description }}</p>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-10 backdrop-blur-xl">
                    <div class="absolute -left-16 top-24 h-44 w-44 rounded-full bg-gradient-to-br from-amber-400/40 via-orange-300/30 to-transparent blur-3xl"></div>
                    <p class="text-sm uppercase tracking-[0.4em] text-amber-200/80">Giáo viên</p>
                    <h2 class="mt-4 text-3xl font-semibold text-white">Tổ chức và đánh giá tinh gọn</h2>
                    <p class="mt-4 text-slate-200/85">Mọi thao tác từ chuẩn bị nội dung đến phân tích kết quả đều tập trung trong một bộ công cụ hiện đại, tinh tế.</p>
                    <div class="mt-8 space-y-6">
                        <div v-for="item in teacherInsights" :key="item.title" class="rounded-2xl border border-white/10 bg-slate-900/60 p-6 shadow-inner shadow-amber-900/40">
                            <h3 class="text-lg font-semibold text-white">{{ item.title }}</h3>
                            <p class="mt-2 text-sm text-slate-200/80">{{ item.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-950/70 p-10 shadow-[0_60px_120px_-80px_rgba(244,114,182,0.6)]">
                <div class="absolute left-10 top-10 h-32 w-32 rounded-full bg-gradient-to-br from-rose-400/40 via-fuchsia-400/30 to-transparent blur-3xl"></div>
                <div class="absolute -right-32 bottom-0 h-72 w-72 rounded-full bg-gradient-to-tr from-sky-500/30 via-indigo-500/30 to-transparent blur-3xl"></div>
                <div class="relative">
                    <p class="text-sm uppercase tracking-[0.4em] text-rose-200/80">Lộ trình mở rộng</p>
                    <h2 class="mt-4 text-3xl font-semibold text-white">Khai phá khả năng vô hạn của lớp học</h2>
                    <p class="mt-4 max-w-3xl text-slate-200/85">Mỗi nâng cấp được thiết kế để tăng chiều sâu trải nghiệm, mở rộng cộng đồng và tạo nên nền tảng giáo dục toàn diện – phù hợp cả hiện tại lẫn tương lai.</p>
                    <div class="mt-12 grid gap-8 md:grid-cols-2">
                        <div v-for="item in roadmap" :key="item.title" class="relative rounded-2xl border border-white/10 bg-white/5 p-6">
                            <div class="absolute -left-6 top-6 size-3 rounded-full bg-gradient-to-r from-rose-400 to-fuchsia-500 shadow-lg shadow-rose-500/50"></div>
                            <h3 class="text-lg font-semibold text-white">{{ item.title }}</h3>
                            <p class="mt-2 text-sm text-slate-200/80">{{ item.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/8 p-10 text-slate-100 backdrop-blur-xl lg:p-14">
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-400/10 via-indigo-400/10 to-violet-500/10"></div>
                    <div class="absolute -left-24 top-1/4 h-52 w-52 rounded-full bg-gradient-to-tr from-sky-400/20 via-white/10 to-transparent blur-3xl"></div>
                    <div class="absolute -right-16 bottom-1/3 h-64 w-64 rounded-full bg-gradient-to-br from-violet-500/20 via-fuchsia-400/20 to-transparent blur-3xl"></div>
                    <div class="absolute inset-x-10 top-1/3 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent opacity-60"></div>
                </div>
                <div class="relative grid gap-10 lg:grid-cols-[2fr_1fr] lg:items-center">
                    <div class="space-y-6">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.4em] text-slate-200">
                            Sẵn sàng triển khai
                        </div>
                        <h2 class="text-3xl font-semibold text-white sm:text-4xl">Đưa “Lớp Học Tương Tác” vào đời sống giảng dạy</h2>
                        <p class="max-w-2xl text-slate-200/85">
                            Cùng tạo ra hành trình học tập truyền cảm hứng với phân quyền rõ ràng, dữ liệu tức thời và khả năng mở rộng không giới hạn. Mỗi giai đoạn triển khai đều có bộ công cụ, tài liệu và chuyên gia hỗ trợ.
                        </p>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div
                                v-for="item in ctaHighlights"
                                :key="item.title"
                                class="rounded-2xl border border-white/10 bg-white/10 p-5 text-sm text-slate-100/90 shadow-[0_20px_60px_-40px_rgba(56,189,248,0.6)]"
                            >
                                <p class="font-medium text-white">{{ item.title }}</p>
                                <p class="mt-2 text-xs text-slate-200/75">{{ item.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 rounded-3xl border border-white/10 bg-slate-950/70 p-8 text-sm shadow-[0_50px_120px_-80px_rgba(99,102,241,0.5)]">
                        <p class="text-xs uppercase tracking-[0.4em] text-slate-400">Bước tiếp theo</p>
                        <div class="space-y-3 text-slate-200/85">
                            <p>1. Tạo lớp mẫu và mời đồng nghiệp thử nghiệm.</p>
                            <p>2. Chuẩn hóa tài liệu, timeline và rubrics theo bộ khung có sẵn.</p>
                            <p>3. Kích hoạt diễn đàn lớp để khởi động tương tác đầu tiên.</p>
                        </div>
                        <div class="mt-6 flex flex-wrap items-center gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="group inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:shadow-xl hover:shadow-sky-500/40"
                            >
                                Tạo lớp học ngay
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white/90 transition hover:border-white/60 hover:bg-white/15"
                            >
                                Truy cập bảng điều khiển
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="pb-8 text-center text-xs text-slate-400/80">
                Nền tảng vận hành trên Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </footer>
        </div>
    </div>
</template>
