<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
});

const expandedGroups = ref({});
const courseGroups = computed(() => props.course.learning_groups ?? []);
const courseTasks = computed(() => props.course.tasks ?? []);

const toggleGroup = (groupId) => {
    expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

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

const getRoleBadgeColor = (role) => {
    return role === 'leader' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`${course.title} - Details`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <Link href="/student/courses" class="text-sm text-indigo-600 hover:text-indigo-700">Courses</Link>
                        <span class="text-sm text-gray-400">/</span>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ course.title }}</h2>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ course.description }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Course Info Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Course Information</h3>
                    </div>
                    <div class="px-4 py-6 sm:px-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Instructor</label>
                                <p class="mt-1 text-sm text-gray-900">{{ course.teacher.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <p class="mt-1 text-sm text-gray-600">{{ course.description || 'No description provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Groups Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">My Learning Groups</h3>
                    </div>

                    <div v-if="courseGroups.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <p class="text-sm text-gray-500">You are not part of any learning group in this course yet.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="group in courseGroups" :key="group.id" class="px-4 py-6 sm:px-6">
                            <button
                                type="button"
                                @click="toggleGroup(group.id)"
                                class="flex w-full items-center justify-between text-left hover:bg-gray-50 -mx-4 -my-6 px-4 py-6 sm:-mx-6 sm:px-6"
                            >
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ group.name }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ group.members.length }} members</p>
                                </div>
                                <svg
                                    :class="{
                                        'rotate-180': expandedGroups[group.id],
                                    }"
                                    class="h-5 w-5 transform transition-transform text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                    />
                                </svg>
                            </button>

                            <!-- Members List -->
                            <div v-if="expandedGroups[group.id]" class="mt-4 border-t border-gray-200 pt-4">
                                <h5 class="mb-4 text-sm font-semibold text-gray-900">Group Members</h5>
                                <div class="space-y-3">
                                    <div v-for="member in group.members" :key="member.id" class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ member.name }}</p>
                                            <p class="text-xs text-gray-500">@{{ member.username }}</p>
                                        </div>
                                        <span :class="getRoleBadgeColor(member.pivot.role)" class="inline-block rounded-full px-3 py-1 text-xs font-semibold">
                                            {{ member.pivot.role }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Course Tasks</h3>
                    </div>

                    <div v-if="courseTasks.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <p class="text-sm text-gray-500">No tasks assigned for this course yet.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="task in courseTasks" :key="task.id" class="px-4 py-6 sm:px-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <span v-if="task.label" class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ task.label }}
                                    </span>
                                    <h4 class="text-base font-semibold text-gray-900">{{ task.title }}</h4>
                                    <p v-if="task.description" class="mt-2 text-sm text-gray-600">{{ task.description }}</p>
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Start Date</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(task.start_date) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Deadline</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(task.deadline) }}</p>
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
