<script setup>
import { Link } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f7f1e8] px-4 py-10 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute left-[-6rem] top-[-5rem] h-72 w-72 rounded-full bg-[#f59e0b]/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-20 h-72 w-72 rounded-full bg-[#0f766e]/20 blur-3xl"></div>
            <div class="absolute bottom-[-6rem] left-1/3 h-72 w-72 rounded-full bg-[#2563eb]/10 blur-3xl"></div>
            <div class="absolute inset-0 hidden bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.85),_rgba(2,6,23,0.95)_70%)] dark:block"></div>
        </div>

        <div class="relative w-full max-w-6xl overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-700 dark:bg-slate-900/75">
            <div class="grid lg:grid-cols-2">
                <div class="order-1 bg-slate-100/70 p-4 sm:p-5 lg:p-6 dark:bg-slate-900/50">
                    <img src="/landing_imange.jpg" alt="Landing Image"
                        class="h-full min-h-[280px] w-full rounded-2xl object-cover lg:min-h-[520px]">
            </div>

                <div class="order-2 p-6 sm:p-8 lg:p-10">
                    <div class="mb-6 flex items-center justify-between">
                        <Link href="/" class="group inline-flex items-center gap-3">
                            <img src="/app_logo.png" alt="E-PASTE Logo" class="h-11 w-auto object-contain">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-800 dark:text-teal-300">E-PASTE</p>
                            </div>
                        </Link>
                        <div class="flex items-center gap-3">
                            <ThemeSwitcher />
                            <Link href="/" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                            Kembali ke Landing
                            </Link>
                        </div>
                    </div>

                    <div class="mx-auto w-full max-w-md overflow-hidden rounded-3xl border border-white/70 bg-white/90 px-6 py-7 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 sm:px-8 sm:py-8">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
