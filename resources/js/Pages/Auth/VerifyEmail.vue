<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-6">
            <h2 class="text-2xl font-black tracking-tight text-slate-900">Verifikasi Email</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Konfirmasi email dulu sebelum melanjutkan ke dashboard.</p>
        </div>

        <div class="mb-4 text-sm text-slate-600">
            Terima kasih sudah mendaftar. Cek email kamu dan klik link verifikasi yang kami kirim. Kalau belum menerima, kirim ulang dari tombol di bawah.
        </div>

        <div
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700"
            v-if="verificationLinkSent"
        >
            Link verifikasi baru sudah dikirim ke email yang kamu daftarkan.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim Ulang Verifikasi
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm font-medium text-slate-600 underline decoration-slate-400 underline-offset-2 transition hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                    >Keluar</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
