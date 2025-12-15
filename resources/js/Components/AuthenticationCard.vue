<template>
    <div class="relative min-h-screen overflow-hidden bg-gray-50 text-gray-900 font-sans">
        <!-- Background Pattern (Optional) -->
        <div class="absolute inset-0 z-0 opacity-40">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px]"></div>
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-6 py-16 lg:px-10">
            <div
                class="flex w-full flex-col items-center gap-0 rounded-2xl bg-white shadow-xl overflow-hidden transition lg:flex-row lg:items-stretch"
                :class="$slots.promo ? 'max-w-5xl' : 'max-w-md'"
            >
                <!-- Promo Section (Left) -->
                <div v-if="$slots.promo" class="hidden lg:block w-1/2 bg-gray-100 relative overflow-hidden">
                    <slot name="promo" />
                </div>

                <!-- Form Section (Right) -->
                <div class="w-full lg:w-1/2 p-8 lg:p-12 flex flex-col justify-center">
                    <div class="flex justify-center mb-8">
                        <slot name="logo" />
                    </div>
                    <div class="w-full max-w-sm mx-auto">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
