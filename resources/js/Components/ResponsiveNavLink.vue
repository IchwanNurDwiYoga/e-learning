<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
    ? 'block w-full border-l-4 border-[#0f766e] bg-emerald-50 py-2 pe-4 ps-3 text-start text-base font-semibold text-[#0f766e] transition duration-150 ease-in-out focus:border-[#115e59] focus:bg-emerald-100 focus:text-[#115e59] focus:outline-none dark:border-teal-400 dark:bg-slate-800/70 dark:text-teal-300 dark:focus:border-teal-300 dark:focus:bg-slate-800 dark:focus:text-teal-200'
    : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-slate-600 transition duration-150 ease-in-out hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:border-slate-300 focus:bg-slate-50 focus:text-slate-900 focus:outline-none dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800/60 dark:hover:text-white dark:focus:border-slate-600 dark:focus:bg-slate-800 dark:focus:text-white',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
