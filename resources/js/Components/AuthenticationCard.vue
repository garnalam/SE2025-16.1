<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div class="pointer-events-none absolute inset-0 opacity-80">
            <div class="absolute -left-28 top-8 h-[360px] w-[360px] rounded-full bg-gradient-to-br from-sky-500/30 via-indigo-600/20 to-transparent blur-3xl"></div>
            <div class="absolute right-[-15%] top-[12%] h-[420px] w-[420px] rounded-full bg-gradient-to-br from-fuchsia-500/25 via-purple-600/20 to-transparent blur-3xl"></div>
            <div class="absolute bottom-[-20%] left-[18%] h-[520px] w-[520px] rounded-full bg-gradient-to-tr from-cyan-400/20 via-blue-500/20 to-transparent blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_rgba(2,6,23,0.85))]"></div>
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6 py-16 lg:px-10">
            <div
                class="flex w-full flex-col items-center gap-10 rounded-[36px] border border-white/10 bg-slate-950/70 p-8 shadow-[0_50px_160px_-80px_rgba(56,189,248,0.5)] backdrop-blur-2xl transition lg:p-12"
                :class="$slots.promo ? 'lg:flex-row lg:items-stretch' : 'lg:max-w-3xl'"
            >
                <div v-if="$slots.promo" class="w-full max-w-lg space-y-8 text-slate-100/85">
                    <slot name="promo" />
                </div>

                <div class="w-full max-w-md space-y-10">
                    <div class="flex justify-center">
                        <slot name="logo" />
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-slate-950/90 p-8 shadow-[0_40px_120px_-90px_rgba(56,189,248,0.7)]">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
