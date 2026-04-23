<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f7f1e8] px-4 py-10 sm:px-6 lg:px-8">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute left-[-6rem] top-[-5rem] h-72 w-72 rounded-full bg-[#f59e0b]/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-20 h-72 w-72 rounded-full bg-[#0f766e]/20 blur-3xl"></div>
            <div class="absolute bottom-[-6rem] left-1/3 h-72 w-72 rounded-full bg-[#2563eb]/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md">
            <div class="mb-6 flex items-center justify-between">
                <Link href="/" class="group inline-flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-sm font-black text-[#f7f1e8] transition group-hover:bg-slate-700">
                        EL
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-teal-800">E-Learning</p>
                        <p class="text-sm font-bold text-slate-900">Biologi</p>
                    </div>
                </Link>
                <Link href="/" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                    Kembali ke Landing
                </Link>
            </div>

            <div class="overflow-hidden rounded-3xl border border-white/70 bg-white/80 px-6 py-7 shadow-xl shadow-slate-900/10 backdrop-blur sm:px-8 sm:py-8">
                <slot />
            </div>
        </div>
    </div>
</template>
