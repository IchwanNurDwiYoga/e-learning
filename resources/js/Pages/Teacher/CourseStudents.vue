<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    students: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    username: '',
    email: '',
});

const students = computed(() => props.students ?? []);

const submitForm = () => {
    form.post(route('teacher.students.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Students" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Students</h2>
                    <p class="mt-2 text-sm text-gray-600">Kelola data student di satu tempat. Penempatan ke grup dilakukan dari halaman course detail.</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Create Student</h3>
                    </div>

                    <div class="px-4 py-6 sm:px-6 space-y-6">
                        <form @submit.prevent="submitForm" class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="username" value="Username (NIP)" />
                                <TextInput
                                    id="username"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.username"
                                    required
                                    placeholder="10-18 digits"
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.username" />
                            </div>

                            <div class="sm:col-span-2">
                                <InputLabel for="email" value="Email (Optional)" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    autocomplete="email"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="sm:col-span-2">
                                <PrimaryButton type="submit">
                                    Create Student
                                </PrimaryButton>
                                <p class="mt-2 text-sm text-slate-600">Default password: <strong>akunpelajar</strong></p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Course Students List -->
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">All Students ({{ students.length }})</h3>
                    </div>

                    <div v-if="students.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <p class="text-sm text-gray-500">Belum ada student. Tambahkan student baru dari form di atas.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="student in students"
                            :key="student.id"
                            class="flex items-center justify-between px-4 py-4 sm:px-6"
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ student.name }}</p>
                                <p class="text-sm text-gray-500">{{ student.username }}</p>
                                <p class="text-xs text-gray-400">{{ student.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
