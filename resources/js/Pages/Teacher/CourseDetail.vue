<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
});

const courseTasks = computed(() => props.course.tasks ?? []);

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`${course.title} - Tasks`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <Link href="/teacher/dashboard" class="text-sm text-indigo-600 hover:text-indigo-700">Courses</Link>
                        <span class="text-sm text-gray-400">/</span>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ course.title }}</h2>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ course.description }}</p>
                </div>

                <Link
                    :href="route('teacher.tasks.create', course.id)"
                    class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                >
                    + Create Task
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Tasks</h3>
                        <p class="mt-1 text-sm text-gray-500">Create group sekarang dilakukan di halaman detail task.</p>
                    </div>

                    <div v-if="courseTasks.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a task to assign work to your students.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="task in courseTasks" :key="task.id" class="px-4 py-6 sm:px-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1">
                                    <span v-if="task.label" class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ task.label }}
                                    </span>
                                    <Link :href="route('teacher.tasks.show', task.id)" class="text-base font-semibold text-indigo-600 hover:text-indigo-700">
                                        {{ task.title }}
                                    </Link>
                                    <p v-if="task.description" class="mt-2 text-sm text-gray-600">{{ task.description }}</p>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-medium text-gray-500 uppercase">Available From</span>
                                            <span class="mt-1 text-sm text-gray-900">{{ formatDate(task.start_date) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-medium text-gray-500 uppercase">Deadline</span>
                                            <span class="mt-1 text-sm font-semibold" :class="new Date(task.deadline) < new Date() ? 'text-red-600' : 'text-gray-900'">
                                                {{ formatDate(task.deadline) }}
                                            </span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-medium text-gray-500 uppercase">File</span>
                                            <div class="mt-1">
                                                <a v-if="task.file_path" :href="route('teacher.tasks.download', task.id)" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                                    Download
                                                </a>
                                                <span v-else class="text-sm text-gray-500">No file</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
