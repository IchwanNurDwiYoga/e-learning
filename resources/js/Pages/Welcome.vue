<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';

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
</script>

<template>

    <Head title="E-PASTE" />
    <div class="min-h-screen overflow-hidden bg-[#f7f1e8] text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute left-[-8rem] top-[-7rem] h-72 w-72 rounded-full bg-[#f59e0b]/20 blur-3xl"></div>
            <div class="absolute right-[-6rem] top-24 h-80 w-80 rounded-full bg-[#0f766e]/20 blur-3xl"></div>
            <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-[#2563eb]/10 blur-3xl"></div>
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.9),_rgba(247,241,232,0.55)_45%,_rgba(247,241,232,1)_75%)]">
            </div>
            <div
                class="absolute inset-0 hidden bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.85),_rgba(2,6,23,0.95)_65%,_rgba(2,6,23,1)_80%)] dark:block">
            </div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <img src="/app_logo.png" alt="E-PASTE Logo" class="h-12 w-auto object-contain">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-teal-800 dark:text-teal-300">
                            Platform Pembelajaran</p>
                        <h1 class="text-lg font-black tracking-tight text-slate-900 dark:text-slate-100">E-PASTE</h1>
                    </div>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3">
                    <ThemeSwitcher />

                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:text-white">
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link :href="route('login')"
                            class="rounded-full border border-slate-300 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:text-white">
                            Masuk
                        </Link>

                        <Link v-if="canRegister" :href="route('register')"
                            class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Daftar
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex flex-1 items-center justify-center py-8 lg:py-10">
                <div
                    class="w-full max-w-4xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white/85 p-4 shadow-2xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900/80">
                    <img src="/landing_imange.jpg" alt="Landing Image"
                        class="mx-auto max-h-[calc(100vh-13rem)] w-full rounded-[1.4rem] object-contain">
                </div>
            </main>
        </div>
    </div>
</template>
