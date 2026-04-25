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
</script>

<template>
    <Head title="E-Learning" />
    <div class="min-h-screen overflow-hidden bg-[#f7f1e8] text-slate-900">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute left-[-8rem] top-[-7rem] h-72 w-72 rounded-full bg-[#f59e0b]/20 blur-3xl"></div>
            <div class="absolute right-[-6rem] top-24 h-80 w-80 rounded-full bg-[#0f766e]/20 blur-3xl"></div>
            <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-[#2563eb]/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.9),_rgba(247,241,232,0.55)_45%,_rgba(247,241,232,1)_75%)]"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-lg font-black text-[#f7f1e8] shadow-lg shadow-slate-900/15">
                        EL
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-teal-800">Platform Pembelajaran</p>
                        <h1 class="text-lg font-black tracking-tight text-slate-900">E-Learning</h1>
                    </div>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-full border border-slate-300 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-full border border-slate-300 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900"
                        >
                            Masuk
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"
                        >
                            Daftar
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex flex-1 items-center py-12 lg:py-16">
                <div class="grid w-full gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <section>
                        <div class="inline-flex items-center rounded-full border border-[#0f766e]/20 bg-white/70 px-4 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-teal-800">
                            Kolaborasi, asesmen, dan progress belajar
                        </div>

                        <h2 class="mt-6 max-w-4xl font-serif text-5xl font-black leading-[1.05] text-slate-900 sm:text-6xl">
                            Satu ruang belajar untuk tugas, kelompok, dan asesmen berbasis performa.
                        </h2>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                            Kelola pembelajaran dengan alur yang rapi: teacher membagikan task, student mengirim first dan final submission, lalu asesmen diri, peer, dan guru tercatat dalam satu sistem.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="rounded-full bg-[#0f766e] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-900/20 transition hover:bg-[#115e59]"
                            >
                                Buka Dashboard
                            </Link>
                            <Link
                                v-else-if="canLogin"
                                :href="route('login')"
                                class="rounded-full bg-[#0f766e] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-900/20 transition hover:bg-[#115e59]"
                            >
                                Mulai Masuk
                            </Link>
                            <Link
                                v-if="!$page.props.auth.user && canRegister"
                                :href="route('register')"
                                class="rounded-full border border-slate-300 bg-white/80 px-6 py-3 text-sm font-semibold text-slate-800 transition hover:border-slate-900"
                            >
                                Buat Akun
                            </Link>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/60 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-3xl font-black text-slate-900">3x</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Jenis asesmen dalam satu alur: task, produk, dan presentasi produk.</p>
                            </div>
                            <div class="rounded-3xl border border-white/60 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-3xl font-black text-slate-900">2 tahap</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Submission bertahap untuk memberi ruang perbaikan sebelum final.</p>
                            </div>
                            <div class="rounded-3xl border border-white/60 bg-white/70 p-5 shadow-sm backdrop-blur">
                                <p class="text-3xl font-black text-slate-900">1 sistem</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Semua progress kelompok dan hasil asesmen tersimpan rapi.</p>
                            </div>
                        </div>
                    </section>

                    <section class="relative">
                        <div class="rounded-[2rem] border border-slate-200 bg-slate-950 p-5 text-white shadow-2xl shadow-slate-900/20">
                            <div class="rounded-[1.5rem] bg-[linear-gradient(135deg,#134e4a_0%,#0f172a_45%,#1d4ed8_100%)] p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-white/60">Live Workflow</p>
                                        <h3 class="mt-2 text-2xl font-bold">Pembelajaran Berbasis Task</h3>
                                    </div>
                                    <div class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">
                                        Teacher & Student
                                    </div>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                                        <p class="text-sm font-semibold text-amber-300">01. Teacher menyiapkan task</p>
                                        <p class="mt-2 text-sm leading-6 text-white/80">Task, deadline, file materi, dan rubrik asesmen disusun dari satu halaman course.</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                                        <p class="text-sm font-semibold text-emerald-300">02. Student mengirim submission</p>
                                        <p class="mt-2 text-sm leading-6 text-white/80">Kelompok mengunggah first submission, revisi, lalu final submission dengan status yang jelas.</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                                        <p class="text-sm font-semibold text-sky-300">03. Asesmen langsung terbuka</p>
                                        <p class="mt-2 text-sm leading-6 text-white/80">Setelah final submission, hasil penilaian pribadi, peer, dan guru bisa ditinjau dari dashboard.</p>
                                    </div>
                                </div>

                                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-white px-4 py-4 text-slate-900">
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Role</p>
                                        <p class="mt-2 text-lg font-bold">Teacher</p>
                                        <p class="mt-1 text-sm text-slate-600">Kelola course, learning group, task, review, dan asesmen.</p>
                                    </div>
                                    <div class="rounded-2xl bg-[#f59e0b] px-4 py-4 text-slate-950">
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-800/70">Role</p>
                                        <p class="mt-2 text-lg font-bold">Student</p>
                                        <p class="mt-1 text-sm text-slate-800/80">Akses tugas, submission kelompok, dan lihat hasil asesmen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>

            <section class="grid gap-4 pb-10 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Task Flow</p>
                    <h3 class="mt-3 text-xl font-bold text-slate-900">Struktur kerja kelompok lebih jelas</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Setiap task punya timeline, file pendukung, status review, dan riwayat first-final submission.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-700">Assessment</p>
                    <h3 class="mt-3 text-xl font-bold text-slate-900">Rubrik tersimpan per indikator</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Skor dan komentar rubric direkam per butir sehingga hasil penilaian lebih detail dan mudah dibaca.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white/70 p-6 shadow-sm backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-amber-700">Platform</p>
                    <h3 class="mt-3 text-xl font-bold text-slate-900">Dibangun untuk kelas yang aktif</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Cocok untuk pembelajaran berbasis proyek, kolaborasi kelompok, dan penilaian performa yang berulang.</p>
                </div>
            </section>

            <footer class="border-t border-slate-300/60 py-6 text-sm text-slate-500">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p>E-Learning untuk manajemen task, submission, dan asesmen kelompok.</p>
                    <p>Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}</p>
                </div>
            </footer>
        </div>
            </div>
</template>
