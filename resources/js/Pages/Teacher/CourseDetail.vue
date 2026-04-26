<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    availableStudents: {
        type: Array,
        default: () => [],
    },
});

const showGroupModal = ref(false);
const alertModal = ref({
    open: false,
    title: '',
    message: '',
});
const removeConfirmModal = ref({
    open: false,
    groupId: null,
    userId: null,
    memberName: '',
});
const expandedGroups = ref({});
const courseGroups = computed(() => props.course.learningGroups ?? props.course.learning_groups ?? []);
const courseTasks = computed(() => props.course.tasks ?? []);

const createGroupForm = useForm({
    name: '',
    course_id: props.course.id,
});

const studentForms = ref({});
const leaderForms = ref({});

const getStudentForm = (groupId) => {
    if (!studentForms.value[groupId]) {
        studentForms.value[groupId] = useForm({
            existing_student_id: '',
        });
    }

    return studentForms.value[groupId];
};

const getLeaderForm = (groupId, userId) => {
    const key = `${groupId}-${userId}`;
    if (!leaderForms.value[key]) {
        leaderForms.value[key] = useForm({});
    }

    return leaderForms.value[key];
};

const submitStudent = (groupId) => {
    const form = getStudentForm(groupId);

    if (!form.existing_student_id) {
        alertModal.value = {
            open: true,
            title: 'Pilih Student Dulu',
            message: 'Silakan pilih student sebelum menambahkan ke group.',
        };
        return;
    }

    form.post(route('teacher.learning-groups.members.store', { learningGroup: groupId }), {
        preserveScroll: true,
        onSuccess: () => {
            form.existing_student_id = '';
        },
        onError: (errors) => {
            alertModal.value = {
                open: true,
                title: 'Gagal Menambahkan Student',
                message: errors.existing_student_id || 'Terjadi kesalahan saat menambahkan student ke group.',
            };
        },
    });
};

const toggleGroup = (groupId) => {
    expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

const submitGroup = () => {
    createGroupForm.post(route('teacher.learning-groups.store'), {
        onSuccess: () => {
            createGroupForm.reset();
            createGroupForm.course_id = props.course.id;
            showGroupModal.value = false;
        },
    });
};

const setLeader = (groupId, userId) => {
    const form = getLeaderForm(groupId, userId);
    form.post(route('teacher.learning-groups.members.leader', {
        learningGroup: groupId,
        user: userId,
    }), {
        preserveScroll: true,
    });
};

const removingMember = ref(null);

const removeMember = (groupId, userId, memberName) => {
    if (removingMember.value) return;
    removeConfirmModal.value = {
        open: true,
        groupId,
        userId,
        memberName,
    };
};

const confirmRemoveMember = () => {
    const { groupId, userId } = removeConfirmModal.value;
    if (!groupId || !userId || removingMember.value) return;

    removeConfirmModal.value.open = false;
    removingMember.value = { groupId, userId };
    router.delete(route('teacher.learning-groups.members.destroy', { learningGroup: groupId, user: userId }), {
        preserveScroll: true,
        onFinish: () => { removingMember.value = null; },
    });
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
</script>

<template>

    <Head :title="`${course.title} - Manage Groups`" />

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

                <div class="flex gap-2">
                    <Link
                        :href="route('teacher.tasks.create', course.id)"
                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        + Create Task
                    </Link>
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        @click="showGroupModal = true">
                        + Create Group
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Tasks Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Tasks</h3>
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
                                                    📎 Download
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

                <!-- Learning Groups Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-4 py-6 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Learning Groups</h3>
                    </div>

                    <div v-if="courseGroups.length === 0" class="px-4 py-12 text-center sm:px-6">
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No learning groups yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a learning group and start adding students to this
                            course.
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="group in courseGroups" :key="group.id"
                            class="border-b border-gray-100 last:border-b-0">
                            <!-- Group Header with Toggle -->
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <button type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors"
                                            @click="toggleGroup(group.id)">
                                            <svg class="h-5 w-5 transform transition-transform"
                                                :class="{ 'rotate-90': expandedGroups[group.id] }" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div>
                                            <h4 class="text-base font-medium text-gray-900">{{ group.name }}</h4>
                                            <p class="mt-1 text-sm text-gray-500">{{ group.members.length }} members</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expandable Content -->
                            <div v-if="expandedGroups[group.id]" class="bg-gray-50 px-4 py-6 sm:px-6 space-y-6">
                                <!-- Add Student Form -->
                                <div class="space-y-3 rounded-lg border border-slate-200 bg-white p-4">
                                    <h5 class="text-sm font-semibold text-slate-900">Manage Group Members</h5>
                                    <form
                                        @submit.prevent="submitStudent(group.id)"
                                        class="space-y-4"
                                    >
                                        <div>
                                            <InputLabel :for="`student-select-${group.id}`" value="Select Student" />
                                            <select
                                                :id="`student-select-${group.id}`"
                                                v-model="getStudentForm(group.id).existing_student_id"
                                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">Choose a student...</option>
                                                <option
                                                    v-for="student in props.availableStudents"
                                                    :key="student.id"
                                                    :value="student.id"
                                                >
                                                    {{ student.name }} - {{ student.username }}
                                                </option>
                                            </select>
                                            <InputError class="mt-2" :message="getStudentForm(group.id).errors.existing_student_id" />
                                            <p class="mt-2 text-sm text-slate-500">Pilih student yang sudah dibuat dari menu Students, lalu tambahkan ke grup ini.</p>
                                        </div>

                                        <div>
                                            <PrimaryButton type="submit">Add to Group</PrimaryButton>
                                        </div>
                                    </form>
                                </div>

                                <!-- Group Members -->
                                <div class="space-y-3">
                                    <h5 class="text-sm font-semibold text-slate-900">Group Members ({{
                                        group.members.length }})
                                    </h5>
                                    <div v-if="group.members.length === 0" class="rounded-lg bg-white p-4 text-center">
                                        <p class="text-sm text-gray-500">No members yet. Add your first student above.
                                        </p>
                                    </div>
                                    <div v-else class="space-y-3">
                                        <div v-for="member in group.members" :key="member.id"
                                            class="flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 text-sm font-medium text-slate-900">
                                                    {{ member.name }}
                                                    <span
                                                        class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">{{
                                                        member.username }}</span>
                                                </div>
                                                <p class="mt-1 text-sm text-slate-500">Role: {{ member.pivot.role }}</p>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button v-if="member.pivot.role !== 'leader'" type="button"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                                                    @click.prevent="setLeader(group.id, member.id)">
                                                    Set as Leader
                                                </button>
                                                <span v-else
                                                    class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700">
                                                    Group Leader
                                                </span>
                                                <div v-if="member.pivot.role !== 'leader'" class="flex flex-col items-end gap-0.5">
                                                    <button
                                                        type="button"
                                                        :disabled="removingMember?.groupId === group.id && removingMember?.userId === member.id"
                                                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50"
                                                        @click.prevent="removeMember(group.id, member.id, member.name)"
                                                    >
                                                        {{ removingMember?.groupId === group.id && removingMember?.userId === member.id ? 'Menghapus...' : 'Hapus dari Grup' }}
                                                    </button>
                                                </div>
                                                <div v-else class="flex flex-col items-end gap-0.5">
                                                    <span class="text-xs text-gray-400">Team leader tidak dapat dihapus</span>
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
        </div>

        <!-- Create Learning Group Modal -->
        <div v-if="showGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Create Learning Group</h3>
                        <p class="mt-1 text-sm text-gray-600">Add a group for your students in <strong>{{ course.title
                                }}</strong>.</p>
                    </div>
                    <button type="button" class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200"
                        @click="showGroupModal = false">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitGroup" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="group-name" value="Group Name" />
                        <TextInput id="group-name" type="text" class="mt-1 block w-full" v-model="createGroupForm.name"
                            required placeholder="e.g., Group A, Tuesday Class" />
                        <InputError class="mt-2" :message="createGroupForm.errors.name" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button"
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            @click="showGroupModal = false">
                            Cancel
                        </button>
                        <PrimaryButton type="submit">Create Group</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alert Modal -->
        <div v-if="alertModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">{{ alertModal.title }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ alertModal.message }}</p>

                <div class="mt-6 flex items-center justify-end">
                    <button
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        @click="alertModal.open = false"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>

        <!-- Remove Member Confirm Modal -->
        <div v-if="removeConfirmModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus Member</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Hapus <span class="font-semibold text-gray-900">{{ removeConfirmModal.memberName }}</span> dari group ini?
                </p>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                        @click="removeConfirmModal.open = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        @click="confirmRemoveMember"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
