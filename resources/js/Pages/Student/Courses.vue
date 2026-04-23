<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="My Courses" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My Courses</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="courses.length === 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-12 text-center sm:px-6">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17s4.5 10.747 10 10.747c5.5 0 10-4.998 10-10.747S17.5 6.253 12 6.253z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses</h3>
                        <p class="mt-1 text-sm text-gray-500">You are not enrolled in any courses yet.</p>
                    </div>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="course in courses"
                        :key="course.id"
                        :href="route('student.courses.show', course.id)"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                {{ course.title }}
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 line-clamp-3">
                                {{ course.description }}
                            </p>
                            <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4">
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Teacher:</span> {{ course.teacher.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Groups:</span> {{ course.learning_groups_count }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
