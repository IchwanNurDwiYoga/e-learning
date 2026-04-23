<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
    taskOptions: {
        type: Array,
        default: () => [],
    },
});

const fileInput = ref(null);
const fileName = ref('');

const form = useForm({
    course_id: props.course.id,
    title: '',
    description: '',
    file: null,
    start_date: '',
    deadline: '',
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;
        fileName.value = file.name;
    }
};

const clearFile = () => {
    form.file = null;
    fileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const selectedTaskOption = computed(() => {
    return props.taskOptions.find((option) => option.title === form.title) ?? null;
});

const submit = () => {
    form.post(route('teacher.tasks.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Create Task - ${course.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link href="/teacher/dashboard" class="text-sm text-indigo-600 hover:text-indigo-700">
                    Courses
                </Link>
                <span class="text-sm text-gray-400">/</span>
                <Link :href="route('teacher.courses.show', course.id)" class="text-sm text-indigo-600 hover:text-indigo-700">
                    {{ course.title }}
                </Link>
                <span class="text-sm text-gray-400">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Task</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Form Card -->
                <div class="rounded-lg bg-white px-6 py-8 shadow">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Task Topic Field -->
                        <div>
                            <InputLabel for="title" value="Task Topic" />
                            <select
                                id="title"
                                v-model="form.title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select task topic</option>
                                <option
                                    v-for="option in taskOptions"
                                    :key="option.label"
                                    :value="option.title"
                                >
                                    {{ option.title }}
                                </option>
                            </select>
                            <InputError :message="form.errors.title" class="mt-2" />
                            <InputError :message="form.errors.label" class="mt-2" />
                            <p v-if="selectedTaskOption" class="mt-2 text-sm text-gray-500">
                                Label yang akan disimpan: <span class="font-semibold text-gray-700">{{ selectedTaskOption.label }}</span>
                            </p>
                        </div>

                        <!-- Description Field -->
                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="4"
                                placeholder="Enter task description (optional)"
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- File Upload Field -->
                        <div>
                            <InputLabel for="file" value="Upload Task File" />
                            <div class="mt-2">
                                <input
                                    ref="fileInput"
                                    id="file"
                                    type="file"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.zip"
                                    @change="handleFileChange"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Allowed formats: PDF, DOC, DOCX, XLS, XLSX, ZIP (Max 10MB)
                                </p>
                            </div>

                            <!-- Selected File Display -->
                            <div v-if="fileName" class="mt-3 flex items-center gap-2 rounded-md bg-blue-50 px-3 py-2">
                                <svg
                                    class="h-5 w-5 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                <span class="flex-1 text-sm text-gray-700">{{ fileName }}</span>
                                <button
                                    type="button"
                                    @click="clearFile"
                                    class="text-red-600 hover:text-red-700"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                </button>
                            </div>

                            <InputError :message="form.errors.file" class="mt-2" />
                        </div>

                        <!-- Start Date Field -->
                        <div>
                            <InputLabel for="start_date" value="Start Date (When task becomes available)" />
                            <TextInput
                                id="start_date"
                                v-model="form.start_date"
                                type="datetime-local"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.start_date" class="mt-2" />
                        </div>

                        <!-- Deadline Field -->
                        <div>
                            <InputLabel for="deadline" value="Deadline (When task submission closes)" />
                            <TextInput
                                id="deadline"
                                v-model="form.deadline"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.deadline" class="mt-2" />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-4 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Task' }}
                            </PrimaryButton>
                            <Link
                                :href="route('teacher.courses.show', course.id)"
                                class="text-sm text-gray-600 underline hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
