<template>
    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-white/6 px-6 py-8 backdrop-blur-2xl shadow-[0_35px_120px_-45px_rgba(79,70,229,0.65)]">
        <div class="absolute -top-24 -right-32 h-64 w-64 rounded-full bg-gradient-to-br from-indigo-500/25 via-purple-500/20 to-fuchsia-500/15 blur-[160px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(129,140,248,0.45),transparent_55%)]"></div>

        <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.45em] text-indigo-200/80">SmartClass AI</p>
                <h1 class="mt-3 text-4xl font-bold leading-tight text-white md:text-[2.75rem]">
                    Bảng điều khiển giáo viên
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300/90">
                    Theo dõi tiến độ lớp học, kích hoạt điểm danh 4.0 và phối hợp cùng trợ lý AI để tối ưu hóa trải nghiệm giảng dạy của bạn.
                </p>
            </div>

            <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/10 p-5">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 text-2xl shadow-[0_10px_35px_-20px_rgba(129,140,248,0.9)]">
                    ⚡
                </div>
                <div class="text-sm text-slate-200">
                    <p class="text-xs uppercase tracking-[0.35em] text-indigo-200/70">Điểm danh nhanh</p>
                    <p class="mt-1 font-semibold">Khởi động phiên chỉ với một cú chạm</p>
                </div>
            </div>
        </div>
    </div>
</template>
