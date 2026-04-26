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
    ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#0f766e] text-sm font-semibold leading-5 text-slate-900 transition duration-150 ease-in-out focus:border-[#115e59] focus:outline-none dark:text-slate-100 dark:focus:border-teal-400'
    : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-900 focus:border-slate-300 focus:text-slate-900 focus:outline-none dark:text-slate-300 dark:hover:border-slate-600 dark:hover:text-white dark:focus:border-slate-600 dark:focus:text-white',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
