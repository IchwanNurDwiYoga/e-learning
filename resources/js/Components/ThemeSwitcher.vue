<script setup>
import { useTheme } from '@/Composables/useTheme';

const { resolvedTheme, isSystemTheme, toggleTheme } = useTheme();
</script>

<template>
    <button
        type="button"
        class="group relative inline-flex h-8 w-16 items-center rounded-full border transition"
        :class="resolvedTheme === 'dark'
            ? 'border-slate-700 bg-slate-900'
            : 'border-amber-300 bg-amber-100'"
        :title="isSystemTheme ? 'Mode sistem aktif' : 'Mode manual aktif'"
        :aria-label="isSystemTheme ? 'Tema mengikuti sistem' : 'Tema manual'"
        :aria-pressed="resolvedTheme === 'dark'"
        @click="toggleTheme"
    >
        <span
            class="absolute inset-y-0 left-0 flex items-center ps-2 text-amber-500 transition-opacity"
            :class="resolvedTheme === 'dark' ? 'opacity-30' : 'opacity-100'"
            aria-hidden="true"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 3a1 1 0 011 1v1a1 1 0 11-2 0V4a1 1 0 011-1zm0 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm9-6a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM6 12a1 1 0 01-1 1H4a1 1 0 110-2h1a1 1 0 011 1zm11.364 6.95a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.05 8.05a1 1 0 010 1.414l-.707.707A1 1 0 115.93 8.757l.707-.707a1 1 0 011.414 0zm9.314-2.12a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.757 15.243a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
        </span>

        <span
            class="absolute inset-y-0 right-0 flex items-center pe-2 text-slate-300 transition-opacity"
            :class="resolvedTheme === 'dark' ? 'opacity-100' : 'opacity-30'"
            aria-hidden="true"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 14.5A8.5 8.5 0 119.5 3a1 1 0 01.79 1.613A6.5 6.5 0 1019.387 13.71 1 1 0 0121 14.5z" />
            </svg>
        </span>

        <span
            class="relative inline-flex h-6 w-6 transform items-center justify-center rounded-full bg-white text-slate-900 shadow transition duration-200"
            :class="resolvedTheme === 'dark' ? 'translate-x-8' : 'translate-x-1'"
        >
            <svg v-if="resolvedTheme === 'dark'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21 14.5A8.5 8.5 0 119.5 3a1 1 0 01.79 1.613A6.5 6.5 0 1019.387 13.71 1 1 0 0121 14.5z" />
            </svg>
            <svg v-else class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 3a1 1 0 011 1v1a1 1 0 11-2 0V4a1 1 0 011-1zm0 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm9-6a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM6 12a1 1 0 01-1 1H4a1 1 0 110-2h1a1 1 0 011 1zm11.364 6.95a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.05 8.05a1 1 0 010 1.414l-.707.707A1 1 0 115.93 8.757l.707-.707a1 1 0 011.414 0zm9.314-2.12a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.757 15.243a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
        </span>

        <span
            v-if="isSystemTheme"
            class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full border border-white bg-emerald-400"
            aria-hidden="true"
        ></span>
    </button>
</template>
