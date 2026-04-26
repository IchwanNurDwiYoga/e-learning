<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-[#f7f1e8] text-slate-900 dark:bg-slate-950 dark:text-slate-100">
            <nav
                class="relative z-50 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="inline-flex items-center gap-2">
                                    <img src="/app_logo.png" alt="E-PASTE Logo" class="h-9 w-auto object-contain">
                                    <span class="text-sm font-bold tracking-tight text-slate-900 dark:text-slate-100">E-PASTE</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'student'"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <!-- Student Menu -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'student'"
                                    :href="route('student.courses.index')"
                                    :active="route().current('student.courses.index')"
                                >
                                    Courses
                                </NavLink>

                                <!-- Teacher Only Menu -->
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'teacher'"
                                    :href="route('teacher.dashboard')"
                                    :active="route().current('teacher.dashboard')"
                                >
                                    Courses
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user.role === 'teacher'"
                                    :href="route('teacher.students.index')"
                                    :active="route().current('teacher.students.index')"
                                >
                                    Students
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                            <ThemeSwitcher />

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-600 transition duration-150 ease-in-out hover:text-slate-900 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-600 focus:bg-slate-100 focus:text-slate-600 focus:outline-none dark:hover:bg-slate-800 dark:hover:text-slate-300 dark:focus:bg-slate-800 dark:focus:text-slate-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <!-- Student Menu (Mobile) -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'student'"
                            :href="route('student.courses.index')"
                            :active="route().current('student.courses.index')"
                        >
                            Courses
                        </ResponsiveNavLink>

                        <!-- Teacher Only Menu (Mobile) -->
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'teacher'"
                            :href="route('teacher.dashboard')"
                            :active="route().current('teacher.dashboard')"
                        >
                            Courses
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'teacher'"
                            :href="route('teacher.students.index')"
                            :active="route().current('teacher.students.index')"
                        >
                            Students
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-slate-200 pb-1 pt-4 dark:border-slate-800"
                    >
                        <div class="px-4">
                            <div class="mb-3">
                                <ThemeSwitcher />
                            </div>
                            <div
                                class="text-base font-medium text-slate-800 dark:text-slate-100"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="border-b border-slate-200 bg-white/70 backdrop-blur dark:border-slate-800 dark:bg-slate-900/60"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
