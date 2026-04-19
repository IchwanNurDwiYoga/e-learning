<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
});

const showCourseModal = ref(false);
const createCourseForm = useForm({
    title: '',
    description: '',
});

const submitCourse = () => {
    createCourseForm.post(route('teacher.courses.store'), {
        onSuccess: () => {
            createCourseForm.reset();
            showCourseModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Manage Courses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        My Courses
                    </h2>
                    <p class="text-sm text-gray-600">
                        Click a course to manage learning groups and students.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-transparent bg-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                    @click="showCourseModal = true"
                >
                    + Add New Course
                </button>
            </div>
        </template>

        <div class="py-12 space-y-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Your Courses</h3>
                    </div>

                    <div v-if="props.courses.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No courses yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create your first course with the button above.</p>
                    </div>

                    <div v-else class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="course in props.courses"
                            :key="course.id"
                            :href="route('teacher.courses.show', { course: course.id })"
                            class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-indigo-300 hover:shadow-md"
                        >
                            <div class="flex flex-col gap-3">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 group-hover:text-indigo-600">{{ course.title }}</h4>
                                    <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ course.description }}</p>
                                </div>
                                <div class="flex items-center justify-between pt-4">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                        {{ course.learning_groups_count || 0 }} groups
                                    </span>
                                    <span class="text-sm font-medium text-indigo-600">Open →</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showCourseModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Create New Course</h3>
                        <p class="mt-1 text-sm text-gray-600">Enter course details and save to publish.</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200"
                        @click="showCourseModal = false"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCourse" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="course-title" value="Course Title" />
                        <TextInput
                            id="course-title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createCourseForm.title"
                            required
                        />
                        <InputError class="mt-2" :message="createCourseForm.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="course-description" value="Description" />
                        <TextInput
                            id="course-description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="createCourseForm.description"
                        />
                        <InputError class="mt-2" :message="createCourseForm.errors.description" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            @click="showCourseModal = false"
                        >
                            Cancel
                        </button>
                        <PrimaryButton type="submit">Save Course</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
