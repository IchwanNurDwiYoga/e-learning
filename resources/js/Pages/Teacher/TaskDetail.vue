<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
    course: {
        type: Object,
        required: true,
    },
    submissions: {
        type: Array,
        default: () => [],
    },
    teacher_assessments: {
        type: Array,
        default: () => [],
    },
    learning_groups: {
        type: Array,
        default: () => [],
    },
    available_students: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success || '');
const flashError = computed(() => page.props.flash?.error || '');

const expandedSubmissions = ref({});
const assessmentModal = ref(null);   // { groupId, groupName, type }
const resultModal = ref(null);       // { assessment }
const assessmentForms = ref({});
const showGroupModal = ref(false);
const expandedGroups = ref({});
const studentForms = ref({});
const leaderForms = ref({});
const removeConfirmModal = ref({
    open: false,
    groupId: null,
    userId: null,
    memberName: '',
});
const removingMember = ref(null);

const taskLearningGroups = computed(() => props.learning_groups ?? []);

const createGroupForm = useForm({
    name: '',
    task_id: props.task.id,
});

const statusForm = useForm({
    status: '',
    teacher_notes: '',
});

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

const toggleGroup = (groupId) => {
    expandedGroups.value[groupId] = !expandedGroups.value[groupId];
};

const submitGroup = () => {
    createGroupForm.post(route('teacher.learning-groups.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createGroupForm.reset();
            createGroupForm.task_id = props.task.id;
            showGroupModal.value = false;
        },
    });
};

const submitStudent = (groupId) => {
    const form = getStudentForm(groupId);

    form.post(route('teacher.learning-groups.members.store', { learningGroup: groupId }), {
        preserveScroll: true,
        onSuccess: () => {
            form.existing_student_id = '';
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

    router.delete(route('teacher.learning-groups.members.destroy', {
        learningGroup: groupId,
        user: userId,
    }), {
        preserveScroll: true,
        onFinish: () => {
            removingMember.value = null;
        },
    });
};

// ─── Rubric Data (Guru) ────────────────────────────────────────────────────

const taskRubric = [
    {
        section: 'A. Invitasi/Inisiasi',
        statement: 'Merumuskan masalah berbasis etnosains',
        scales: { 4: 'Masalah dirumuskan jelas, spesifik, dan eksplisit bersumber dari praktik/pengetahuan lokal yang nyata dan relevan', 3: 'Masalah dirumuskan jelas dan berbasis praktik lokal, namun konteks atau fokusnya belum spesifik', 2: 'Masalah bersifat umum/kontekstual, tetapi keterkaitannya dengan praktik lokal kurang jelas', 1: 'Masalah tidak berbasis pengetahuan lokal dan tidak relevan' },
    },
    {
        section: 'A. Invitasi/Inisiasi',
        statement: 'Keterkaitan masalah dengan konsep sains',
        scales: { 4: 'Masalah dikaitkan secara logis dan tepat dengan konsep sains yang relevan', 3: 'Masalah dikaitkan dengan konsep sains, namun penjelasan belum mendalam', 2: 'Keterkaitan dengan konsep sains lemah atau kurang tepat', 1: 'Tidak menunjukkan keterkaitan dengan konsep sains' },
    },
    {
        section: 'B. Pembentukan Konsep',
        statement: 'Integrasi data ilmiah dan pengetahuan etnosains',
        scales: { 4: 'Data ilmiah dan pengetahuan lokal diolah secara sistematis dan saling menguatkan dalam membangun konsep', 3: 'Data ilmiah dan pengetahuan lokal diolah cukup sistematis, namun integrasinya belum optimal', 2: 'Data diolah secara terpisah dan integrasi sains-etnosains lemah', 1: 'Tidak menunjukkan integrasi data ilmiah dan pengetahuan lokal' },
    },
    {
        section: 'B. Pembentukan Konsep',
        statement: 'Analisis kesesuaian konsep sains dan praktik etnosains',
        scales: { 4: 'Mampu menjelaskan kesesuaian dan/atau perbedaan sains-etnosains secara ilmiah dan logis', 3: 'Analisis cukup logis, namun belum mendalam', 2: 'Analisis bersifat deskriptif dan dangkal', 1: 'Tidak mampu menganalisis kesesuaian konsep' },
    },
    {
        section: 'C. Aplikasi Konsep',
        statement: 'Perencanaan solusi berbasis konsep sains dan etnosains',
        scales: { 4: 'Solusi dirancang dengan mengintegrasikan konsep sains, teknologi, dan praktik etnosains secara tepat', 3: 'Solusi berbasis sains dan etnosains, namun kurang optimal', 2: 'Solusi hanya sebagian berbasis konsep sains atau praktik etnosains', 1: 'Solusi tidak berbasis konsep sains maupun praktik lokal' },
    },
    {
        section: 'C. Aplikasi Konsep',
        statement: 'Argumentasi ilmiah dalam pemilihan solusi',
        scales: { 4: 'Alasan pemilihan solusi didukung argumen ilmiah yang kuat dalam konteks etnosains', 3: 'Argumen cukup logis, namun belum didukung kajian ilmiah yang kuat', 2: 'Argumen lemah dan kurang berbasis data', 1: 'Tidak mampu memberikan argumentasi ilmiah' },
    },
    {
        section: 'D. Pemantapan Konsep',
        statement: 'Refleksi kontribusi etnosains terhadap pemahaman konsep sains',
        scales: { 4: 'Refleksi mendalam menunjukkan peran etnosains dalam memperkuat pemahaman konsep sains', 3: 'Refleksi cukup tepat, namun belum mendalam', 2: 'Refleksi bersifat umum dan deskriptif', 1: 'Tidak menunjukkan refleksi yang relevan' },
    },
    {
        section: 'D. Pemantapan Konsep',
        statement: 'Refleksi keterbatasan praktik etnosains',
        scales: { 4: 'Menjelaskan keterbatasan etnosains secara kritis berdasarkan kajian ilmiah', 3: 'Menyebutkan keterbatasan etnosains namun belum dianalisis mendalam', 2: 'Menyebutkan keterbatasan secara umum tanpa dasar ilmiah', 1: 'Tidak menyebutkan keterbatasan praktik etnosains' },
    },
    {
        section: 'E. Evaluasi',
        statement: 'Evaluasi solusi berdasarkan sains, budaya, dan keberlanjutan',
        scales: { 4: 'Evaluasi solusi mempertimbangkan data ilmiah, nilai budaya, dan keberlanjutan praktik lokal', 3: 'Evaluasi mempertimbangkan sains dan budaya, namun aspek keberlanjutan belum kuat', 2: 'Evaluasi terbatas pada satu aspek saja', 1: 'Tidak melakukan evaluasi yang bermakna' },
    },
    {
        section: 'E. Evaluasi',
        statement: 'Pengambilan keputusan berbasis integrasi sains-etnosains',
        scales: { 4: 'Keputusan akhir logis, konsisten, dan berbasis integrasi sains serta kearifan lokal', 3: 'Keputusan cukup logis, namun integrasi belum optimal', 2: 'Keputusan kurang didukung pertimbangan ilmiah', 1: 'Keputusan tidak berdasar' },
    },
];

const productRubricByTask = {
    'Task 1': [
        { statement: 'Solusi sesuai dengan konsep pencemaran lingkungan', scales: { 4: 'Solusi sesuai dengan konsep pencemaran lingkungan', 3: 'Solusi cukup tepat dan sebagian besar sesuai konsep', 2: 'Solusi kurang tepat dan hanya sebagian sesuai', 1: 'Solusi tidak sesuai dengan konsep' } },
        { statement: 'Produk memuat komponen lengkap', scales: { 4: 'Memuat masalah, solusi, dan langkah secara lengkap dan sistematis', 3: 'Memuat komponen cukup lengkap namun kurang sistematis', 2: 'Komponen tidak lengkap', 1: 'Tidak memuat komponen utama' } },
        { statement: 'Integrasi lubuk larangan', scales: { 4: 'Integrasi sangat jelas, relevan, dan mendalam', 3: 'Integrasi cukup jelas', 2: 'Integrasi kurang tepat', 1: 'Tidak ada integrasi' } },
        { statement: 'Kelayakan solusi', scales: { 4: 'Solusi sangat realistis dan dapat diterapkan', 3: 'Solusi cukup realistis', 2: 'Solusi sulit diterapkan', 1: 'Solusi tidak realistis' } },
        { statement: 'Argumentasi ilmiah', scales: { 4: 'Argumentasi sangat kuat, logis, dan berbasis konsep ilmiah', 3: 'Argumentasi cukup logis', 2: 'Argumentasi lemah', 1: 'Tidak ada argumentasi' } },
    ],
    'Task 2': [
        { statement: 'Menjelaskan penyebab pemanasan global dengan benar', scales: { 4: 'Penjelasan sangat tepat dan ilmiah', 3: 'Penjelasan cukup tepat', 2: 'Penjelasan kurang tepat', 1: 'Tidak sesuai' } },
        { statement: 'Menjelaskan dampak secara jelas', scales: { 4: 'Dampak dijelaskan lengkap dan mendalam', 3: 'Dampak cukup jelas', 2: 'Dampak kurang jelas', 1: 'Tidak ada dampak' } },
        { statement: 'Menyajikan solusi yang tepat', scales: { 4: 'Solusi sangat tepat dan aplikatif', 3: 'Solusi cukup tepat', 2: 'Solusi kurang tepat', 1: 'Solusi tidak relevan' } },
        { statement: 'Tampilan mudah dipahami', scales: { 4: 'Sangat jelas, menarik dan mudah dipahami', 3: 'Cukup jelas', 2: 'Kurang menarik', 1: 'Tidak jelas' } },
        { statement: 'Memuat ajakan perubahan perilaku', scales: { 4: 'Ajakan sangat kuat dan persuasif', 3: 'Ajakan cukup jelas', 2: 'Ajakan lemah', 1: 'Tidak ada unsur mengajak' } },
    ],
    'Task 3': [
        { statement: 'Konsep pelestarian', scales: { 4: 'Sangat tepat dan berbasis konsep ilmiah', 3: 'Cukup tepat', 2: 'Kurang tepat', 1: 'Tidak tepat' } },
        { statement: 'Integrasi etnosains', scales: { 4: 'Integrasi sangat kuat dan relevan', 3: 'Integrasi cukup jelas', 2: 'Integrasi lemah', 1: 'Tidak terintegrasi' } },
        { statement: 'Langkah program', scales: { 4: 'Sangat jelas, sistematis, dan rinci', 3: 'Cukup jelas', 2: 'Kurang sistematis', 1: 'Tidak jelas' } },
        { statement: 'Kelayakan', scales: { 4: 'Sangat realistis', 3: 'Cukup realistis', 2: 'Kurang realistis', 1: 'Tidak realistis' } },
        { statement: 'Argumentasi', scales: { 4: 'Sangat kuat dan ilmiah', 3: 'Cukup logis', 2: 'Lemah', 1: 'Tidak ada' } },
    ],
};

const presentationRubricByTask = {
    'Task 1': [
        { statement: 'Menjelaskan solusi pencemaran sesuai konsep ilmiah', scales: { 4: 'Sangat tepat, lengkap, dan sesuai konsep ilmiah', 3: 'Cukup tepat namun kurang lengkap', 2: 'Kurang tepat', 1: 'Tidak sesuai' } },
        { statement: 'Menjelaskan alasan solusi berdasarkan konsep ilmiah', scales: { 4: 'Alasan sangat logis dan berbasis konsep ilmiah', 3: 'Alasan cukup logis', 2: 'Alasan lemah', 1: 'Tidak ada alasan' } },
        { statement: 'Mengaitkan solusi dengan lubuk larangan', scales: { 4: 'Sangat relevan, jelas, dan mendalam', 3: 'Cukup relevan, jelas dan kurang mendalam', 2: 'Kurang relevan', 1: 'Tidak dikaitkan' } },
        { statement: 'Menyampaikan presentasi secara runtut dan jelas', scales: { 4: 'Sangat runtut, jelas, dan mudah dipahami', 3: 'Cukup runtut', 2: 'Kurang runtut', 1: 'Tidak runtut' } },
        { statement: 'Menggunakan proposal untuk mendukung penjelasan', scales: { 4: 'Proposal digunakan secara aktif (ditunjukkan, dijelaskan, dan mendukung pemahaman)', 3: 'Proposal digunakan tetapi kurang optimal', 2: 'Proposal hanya ditunjukkan tanpa penjelasan', 1: 'Proposal tidak digunakan' } },
        { statement: 'Menjawab pertanyaan dengan tepat', scales: { 4: 'Jawaban sangat tepat dan berbasis konsep', 3: 'Cukup tepat', 2: 'Kurang tepat', 1: 'Tidak tepat' } },
    ],
    'Task 2': [
        { statement: 'Menjelaskan isi poster berdasarkan konsep pemanasan global', scales: { 4: 'Penjelasan sangat tepat, lengkap, dan menggunakan konsep pemanasan global secara benar', 3: 'Penjelasan cukup tepat namun belum lengkap', 2: 'Penjelasan kurang tepat', 1: 'Penjelasan tidak sesuai konsep' } },
        { statement: 'Menjelaskan hubungan sebab-akibat pemanasan global secara ilmiah', scales: { 4: 'Menjelaskan hubungan sebab-akibat secara jelas, logis, dan ilmiah', 3: 'Penjelasan cukup logis namun belum mendalam', 2: 'Penjelasan kurang jelas', 1: 'Tidak mampu menjelaskan' } },
        { statement: 'Mengaitkan pemanasan global dengan praktik atau kearifan lokal serta menjelaskan hubungan ilmiahnya', scales: { 4: 'Mengaitkan secara sangat jelas dengan praktik/kearifan lokal serta menjelaskan hubungan ilmiahnya secara tepat', 3: 'Mengaitkan dengan praktik lokal dan cukup relevan, namun penjelasan ilmiah kurang mendalam', 2: 'Mengaitkan tetapi tidak jelas hubungan ilmiahnya', 1: 'Tidak mengaitkan dengan praktik/kearifan lokal' } },
        { statement: 'Menyampaikan isi poster secara jelas, runtut, dan menarik', scales: { 4: 'Penyampaian sangat jelas, runtut, menarik, dan mudah dipahami', 3: 'Penyampaian cukup jelas', 2: 'Penyampaian kurang runtut', 1: 'Penyampaian tidak jelas' } },
        { statement: 'Menggunakan poster secara aktif untuk memperjelas penjelasan', scales: { 4: 'Poster digunakan sangat aktif (ditunjukkan, dirujuk, dan dijelaskan) sehingga sangat membantu pemahaman', 3: 'Poster digunakan tetapi belum maksimal', 2: 'Poster hanya ditunjukkan tanpa penjelasan', 1: 'Poster tidak digunakan' } },
        { statement: 'Menjawab pertanyaan secara tepat dan logis', scales: { 4: 'Jawaban sangat tepat, logis, dan berbasis konsep', 3: 'Jawaban cukup tepat', 2: 'Jawaban kurang tepat', 1: 'Tidak mampu menjawab' } },
    ],
    'Task 3': [
        { statement: 'Menjelaskan program pelestarian berdasarkan konsep ekologi', scales: { 4: 'Penjelasan sangat tepat, lengkap, dan menggunakan konsep ekologi secara benar', 3: 'Penjelasan cukup tepat namun belum lengkap', 2: 'Penjelasan kurang tepat', 1: 'Penjelasan tidak sesuai konsep' } },
        { statement: 'Menjelaskan alasan program berdasarkan hubungan sebab-akibat ekologis', scales: { 4: 'Menjelaskan hubungan sebab-akibat secara jelas, logis, dan ilmiah', 3: 'Penjelasan cukup logis namun belum mendalam', 2: 'Penjelasan kurang jelas', 1: 'Tidak mampu menjelaskan' } },
        { statement: 'Mengaitkan program dengan praktik hutan larangan adat serta menjelaskan fungsi ekologisnya', scales: { 4: 'Mengaitkan secara sangat jelas dengan praktik/kearifan lokal serta menjelaskan hubungan ilmiahnya secara tepat', 3: 'Mengaitkan dengan praktik lokal dan cukup relevan, namun penjelasan ilmiah kurang mendalam', 2: 'Mengaitkan tetapi tidak jelas hubungan ilmiahnya', 1: 'Tidak mengaitkan dengan praktik/kearifan lokal' } },
        { statement: 'Menyampaikan program secara runtut, jelas, dan terstruktur', scales: { 4: 'Penyampaian sangat jelas, runtut, menarik, dan mudah dipahami', 3: 'Penyampaian cukup jelas', 2: 'Penyampaian kurang runtut', 1: 'Penyampaian tidak jelas' } },
        { statement: 'Menggunakan rancangan program secara aktif untuk memperjelas penjelasan', scales: { 4: 'Rancangan digunakan sangat aktif (ditunjukkan, dirujuk, dan dijelaskan) sehingga sangat membantu pemahaman', 3: 'Rancangan digunakan tetapi belum maksimal', 2: 'Rancangan hanya ditunjukkan tanpa penjelasan', 1: 'Rancangan tidak digunakan' } },
        { statement: 'Menjawab pertanyaan secara tepat, logis, dan berbasis konsep ekologi', scales: { 4: 'Jawaban sangat tepat, logis, dan berbasis konsep', 3: 'Jawaban cukup tepat', 2: 'Jawaban kurang tepat', 1: 'Tidak mampu menjawab' } },
    ],
};

const assessmentTypes = [
    { type: 'task', label: 'Asesmen Task' },
    { type: 'product', label: 'Asesmen Produk' },
    { type: 'product_presentation', label: 'Asesmen Presentasi Produk' },
];

// ─── Helpers ─────────────────────────────────────────────────────────────────

const resolveTaskLabel = () => {
    const label = props.task.label || '';
    if (label && (productRubricByTask[label] || presentationRubricByTask[label])) {
        return label;
    }
    const match = String(props.task.title || '').match(/task\s*(1|2|3)/i);
    return match ? `Task ${match[1]}` : '';
};

const getRubricForType = (type) => {
    const taskLabel = resolveTaskLabel();

    if (type === 'product' && taskLabel && productRubricByTask[taskLabel]) {
        return productRubricByTask[taskLabel];
    }
    if (type === 'product_presentation' && taskLabel && presentationRubricByTask[taskLabel]) {
        return presentationRubricByTask[taskLabel];
    }
    return taskRubric;
};

const getTypeLabel = (type) => {
    return assessmentTypes.find((t) => t.type === type)?.label || type;
};

const todayLabel = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

// ─── Teacher Assessment Submission ──────────────────────────────────────────

const getAssessmentFormKey = (groupId, type, stage) => `${groupId}-${type}-${stage}`;

const getAssessmentForm = (groupId, type, stage) => {
    const key = getAssessmentFormKey(groupId, type, stage);
    if (!assessmentForms.value[key]) {
        const initial = { assessment_type: type, target_group_id: groupId, submission_stage: stage, confirm_irreversible: false };
        for (let i = 1; i <= 20; i++) {
            initial[`score_${i}`] = null;
            initial[`comment_${i}`] = '';
        }
        assessmentForms.value[key] = useForm({ ...initial });
    }
    return assessmentForms.value[key];
};

const hasTeacherAssessment = (groupId, type, stage) => {
    return props.teacher_assessments.some(
        (a) => a.target_group_id === groupId && a.assessment_type === type && a.submission_stage === stage,
    );
};

const getTeacherAssessment = (groupId, type, stage) => {
    return props.teacher_assessments.find(
        (a) => a.target_group_id === groupId && a.assessment_type === type && a.submission_stage === stage,
    ) || null;
};

const openAssessmentModal = (groupId, groupName, type, stage) => {
    if (hasTeacherAssessment(groupId, type, stage)) return;

    const form = getAssessmentForm(groupId, type, stage);
    form.assessment_type = type;
    form.target_group_id = groupId;
    form.submission_stage = stage;

    assessmentModal.value = { groupId, groupName, type, stage };
};

const closeAssessmentModal = () => { assessmentModal.value = null; };

const openResultModal = (groupId, type, stage) => {
    const assessment = getTeacherAssessment(groupId, type, stage);
    if (assessment) {
        resultModal.value = { assessment, type, stage };
    }
};

const closeResultModal = () => { resultModal.value = null; };

const currentForm = computed(() => {
    if (!assessmentModal.value) return null;
    return getAssessmentForm(assessmentModal.value.groupId, assessmentModal.value.type, assessmentModal.value.stage);
});

const currentRubric = computed(() => {
    if (!assessmentModal.value) return [];
    return getRubricForType(assessmentModal.value.type);
});

const currentResultRubric = computed(() => {
    if (!resultModal.value) return [];
    return getRubricForType(resultModal.value.type);
});

const assessmentTotalScore = computed(() => {
    if (!currentForm.value || !currentRubric.value.length) return 0;
    return currentRubric.value.reduce((sum, _, idx) => {
        const score = Number(currentForm.value[`score_${idx + 1}`]);
        return sum + (score > 0 ? score : 0);
    }, 0);
});

const assessmentAverageScore = computed(() => {
    const filled = currentRubric.value.filter((_, idx) => Number(currentForm.value?.[`score_${idx + 1}`]) > 0).length;
    return filled ? (assessmentTotalScore.value / filled).toFixed(2) : '0.00';
});

const isReadyToSubmit = computed(() => {
    if (!currentForm.value || !currentRubric.value.length) return false;
    const allScored = currentRubric.value.every((_, idx) => Number(currentForm.value[`score_${idx + 1}`]) > 0);
    return allScored && Boolean(currentForm.value.confirm_irreversible);
});

const submitAssessment = () => {
    if (!assessmentModal.value || !currentForm.value) return;

    const scores = currentRubric.value.map((_, idx) => Number(currentForm.value[`score_${idx + 1}`]));
    const comments = currentRubric.value.map((_, idx) => currentForm.value[`comment_${idx + 1}`] || '');

    currentForm.value.transform((data) => ({ ...data, scores, comments }));

    currentForm.value.post(route('teacher.task-assessments.store', props.task.id), {
        preserveScroll: true,
        onSuccess: () => { closeAssessmentModal(); },
        onFinish: () => { currentForm.value.transform((data) => data); },
    });
};

// ─── Existing helpers ────────────────────────────────────────────────────────

const toggleSubmission = (submissionId) => {
    expandedSubmissions.value[submissionId] = !expandedSubmissions.value[submissionId];
};

const updateStatus = (submission, newStatus) => {
    statusForm.status = newStatus;
    statusForm.teacher_notes = submission.teacher_notes || '';
    statusForm.patch(route('teacher.task-submissions.update-status', submission.id), { preserveScroll: true });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (status) => {
    const colors = { submitted: 'bg-blue-100 text-blue-800', reviewed: 'bg-green-100 text-green-800', returned: 'bg-red-100 text-red-800' };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = { submitted: 'Submitted', reviewed: 'Reviewed', returned: 'Returned' };
    return labels[status] || status;
};

const getSubmissionLabel = (submissionLabel) => submissionLabel === 'final_submit' ? 'Final Submission' : 'First Submission';

const getFileName = (filePath) => {
    if (!filePath) return '-';
    return filePath.split('/').pop();
};
</script>

<template>
    <Head :title="`${task.label ? `${task.label} - ` : ''}${task.title} - Task Detail`" />

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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ task.label ? `${task.label} - ${task.title}` : task.title }}</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Flash messages -->
                <div v-if="flashSuccess" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
                    {{ flashSuccess }}
                </div>
                <div v-if="flashError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200">
                    {{ flashError }}
                </div>

                <!-- Task Information -->
                <div class="rounded-lg bg-white px-6 py-8 shadow">
                    <div class="flex flex-wrap items-center gap-3">
                        <span v-if="task.label" class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            {{ task.label }}
                        </span>
                        <h3 class="text-lg font-semibold text-gray-900">{{ task.title }}</h3>
                    </div>

                    <p v-if="task.description" class="mt-4 text-gray-600">{{ task.description }}</p>

                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-xs font-medium uppercase text-gray-500">Available From</div>
                            <div class="mt-2 text-sm font-medium text-gray-900">{{ formatDate(task.start_date) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-xs font-medium uppercase text-gray-500">Deadline</div>
                            <div class="mt-2 text-sm font-medium" :class="new Date(task.deadline) < new Date() ? 'text-red-600' : 'text-gray-900'">
                                {{ formatDate(task.deadline) }}
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-xs font-medium uppercase text-gray-500">Task File</div>
                            <div class="mt-2">
                                <a v-if="task.file_path" :href="route('teacher.tasks.download', task.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    📎 Download
                                </a>
                                <span v-else class="text-sm text-gray-500">No file</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Groups Section -->
                <div class="rounded-lg bg-white px-6 py-8 shadow">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-gray-900">Task Groups</h3>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            @click="showGroupModal = true"
                        >
                            + Create Group
                        </button>
                    </div>

                    <div v-if="taskLearningGroups.length === 0" class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-600">
                        Belum ada group untuk task ini.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="group in taskLearningGroups" :key="group.id" class="overflow-hidden rounded-lg border border-gray-200">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left"
                                @click="toggleGroup(group.id)"
                            >
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ group.name }}</h4>
                                    <p class="text-xs text-gray-500">{{ group.members.length }} members</p>
                                </div>
                                <svg class="h-5 w-5 transform text-gray-400 transition-transform" :class="{ 'rotate-90': expandedGroups[group.id] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <div v-if="expandedGroups[group.id]" class="space-y-4 px-4 py-4">
                                <form class="space-y-2" @submit.prevent="submitStudent(group.id)">
                                    <label class="block text-sm font-medium text-gray-700">Tambah Student</label>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <select
                                            v-model="getStudentForm(group.id).existing_student_id"
                                            class="block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Pilih student...</option>
                                            <option v-for="student in available_students" :key="student.id" :value="student.id">
                                                {{ student.name }} - {{ student.username }}
                                            </option>
                                        </select>
                                        <PrimaryButton type="submit">Add to Group</PrimaryButton>
                                    </div>
                                    <InputError class="mt-2" :message="getStudentForm(group.id).errors.existing_student_id" />
                                </form>

                                <div class="space-y-2">
                                    <h5 class="text-sm font-semibold text-slate-900">Group Members</h5>
                                    <div v-if="group.members.length === 0" class="rounded-md border border-dashed border-slate-300 px-3 py-3 text-sm text-slate-500">
                                        Belum ada member.
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div v-for="member in group.members" :key="member.id" class="flex flex-col gap-2 rounded-md border border-slate-200 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">{{ member.name }} <span class="text-xs text-slate-500">({{ member.username }})</span></p>
                                                <p class="text-xs text-slate-500">Role: {{ member.pivot.role }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button
                                                    v-if="member.pivot.role !== 'leader'"
                                                    type="button"
                                                    class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700"
                                                    @click.prevent="setLeader(group.id, member.id)"
                                                >
                                                    Set as Leader
                                                </button>
                                                <span v-else class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">Group Leader</span>
                                                <button
                                                    v-if="member.pivot.role !== 'leader'"
                                                    type="button"
                                                    :disabled="removingMember?.groupId === group.id && removingMember?.userId === member.id"
                                                    class="rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 disabled:opacity-50"
                                                    @click.prevent="removeMember(group.id, member.id, member.name)"
                                                >
                                                    {{ removingMember?.groupId === group.id && removingMember?.userId === member.id ? 'Menghapus...' : 'Hapus dari Grup' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submissions Section -->
                <div class="rounded-lg bg-white px-6 py-8 shadow">
                    <h3 class="mb-6 text-lg font-semibold text-gray-900">Group Submissions</h3>

                    <div v-if="submissions.length === 0" class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h4 class="mt-2 text-sm font-medium text-gray-900">No submissions yet</h4>
                        <p class="mt-1 text-sm text-gray-500">Team leaders can submit task submissions here.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="submission in submissions" :key="submission.id" class="overflow-hidden rounded-lg border border-gray-200">
                            <!-- Submission Header -->
                            <button
                                type="button"
                                class="flex w-full items-center justify-between px-4 py-4 text-left transition hover:bg-gray-50"
                                @click="toggleSubmission(submission.id)"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="mb-2 flex items-center gap-3">
                                        <h4 class="truncate font-semibold text-gray-900">{{ submission.learning_group?.name }}</h4>
                                        <span :class="['inline-block rounded px-2 py-1 text-xs font-medium', getStatusColor(submission.status)]">
                                            {{ getStatusLabel(submission.status) }}
                                        </span>
                                        <span v-if="submission.first_submission" class="inline-block rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            First Submission
                                        </span>
                                        <span v-if="submission.final_submission" class="inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                            Final Submission
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Submitted by <span class="font-medium">{{ submission.submitted_by?.name }}</span> on
                                        <span class="font-medium">{{ formatDate(submission.created_at) }}</span>
                                    </p>
                                </div>
                                <svg
                                    class="h-5 w-5 transform text-gray-400 transition-transform"
                                    :class="{ 'rotate-90': expandedSubmissions[submission.id] }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Submission Details -->
                            <div v-if="expandedSubmissions[submission.id]" class="space-y-4 border-t border-gray-200 bg-gray-50 px-4 py-4">
                                <!-- Description -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Description</label>
                                    <p v-if="submission.description" class="text-sm text-gray-600">{{ submission.description }}</p>
                                    <p v-else class="text-sm italic text-gray-500">No description provided</p>
                                </div>

                                <!-- Submitted Files -->
                                <div v-if="submission.first_submission || submission.final_submission" class="space-y-3">
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Submitted Files</label>

                                    <div v-if="submission.first_submission" class="rounded-md border border-slate-200 bg-white p-3">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="inline-block rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                {{ getSubmissionLabel(submission.first_submission.submission_label) }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(submission.first_submission.created_at) }}
                                            </span>
                                        </div>
                                        <div class="space-y-1">
                                            <a
                                                v-if="submission.first_submission.task_file_path"
                                                :href="route('teacher.task-submissions.download', { submission: submission.first_submission.id, fileType: 'task' })"
                                                class="inline-flex items-center gap-2 font-medium text-indigo-600 hover:text-indigo-700"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Task: {{ getFileName(submission.first_submission.task_file_path) }}
                                            </a>
                                            <a
                                                v-if="submission.first_submission.product_file_path"
                                                :href="route('teacher.task-submissions.download', { submission: submission.first_submission.id, fileType: 'product' })"
                                                class="inline-flex items-center gap-2 font-medium text-emerald-600 hover:text-emerald-700"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Product: {{ getFileName(submission.first_submission.product_file_path) }}
                                            </a>
                                            <p v-if="!submission.first_submission.task_file_path && !submission.first_submission.product_file_path" class="text-sm text-gray-500">Tidak ada file.</p>
                                        </div>
                                    </div>

                                    <div v-if="submission.final_submission" class="rounded-md border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/20 p-3">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                                {{ getSubmissionLabel(submission.final_submission.submission_label) }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ formatDate(submission.final_submission.created_at) }}
                                            </span>
                                        </div>
                                        <div class="space-y-1">
                                            <a
                                                v-if="submission.final_submission.task_file_path"
                                                :href="route('teacher.task-submissions.download', { submission: submission.final_submission.id, fileType: 'task' })"
                                                class="inline-flex items-center gap-2 font-medium text-indigo-600 hover:text-indigo-700"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Task: {{ getFileName(submission.final_submission.task_file_path) }}
                                            </a>
                                            <a
                                                v-if="submission.final_submission.product_file_path"
                                                :href="route('teacher.task-submissions.download', { submission: submission.final_submission.id, fileType: 'product' })"
                                                class="inline-flex items-center gap-2 font-medium text-emerald-600 hover:text-emerald-700"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Product: {{ getFileName(submission.final_submission.product_file_path) }}
                                            </a>
                                            <p v-if="!submission.final_submission.task_file_path && !submission.final_submission.product_file_path" class="text-sm text-gray-500">Tidak ada file.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Update -->
                                <div v-if="submission.first_submission || submission.final_submission" class="border-t border-gray-200 pt-4">
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Update Status</label>
                                    <div class="flex gap-2">
                                        <button
                                            class="rounded bg-green-600 px-3 py-1 text-sm text-white transition hover:bg-green-700"
                                            @click="updateStatus(submission, 'reviewed')"
                                        >
                                            Mark as Reviewed
                                        </button>
                                        <button
                                            v-if="!submission.final_submission"
                                            class="rounded bg-red-600 px-3 py-1 text-sm text-white transition hover:bg-red-700"
                                            @click="updateStatus(submission, 'returned')"
                                        >
                                            Return for Revision
                                        </button>
                                    </div>
                                </div>

                                <!-- Teacher Notes -->
                                <div v-if="submission.teacher_notes" class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                                    <p class="mb-1 text-xs font-semibold uppercase text-yellow-800">Teacher Notes</p>
                                    <p class="text-sm text-yellow-900">{{ submission.teacher_notes }}</p>
                                </div>

                                <!-- Last Updated -->
                                <div class="text-xs text-gray-500">Last updated: {{ formatDate(submission.updated_at) }}</div>

                                <!-- ─── Teacher Assessment Section (per kelompok) ─── -->
                                <template v-if="submission.first_submission">
                                    <div class="border-t border-slate-200 pt-4">
                                        <h5 class="mb-3 text-sm font-semibold text-slate-900">Asesmen Guru - First Submission ({{ submission.learning_group?.name }})</h5>
                                        <p class="mb-3 text-xs text-slate-700">Setiap asesmen hanya bisa dikirim sekali dan tidak dapat diubah.</p>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-slate-100 rounded-md border border-slate-200 bg-white text-sm">
                                                <thead class="bg-slate-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Jenis Asesmen</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Status</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50 text-gray-700">
                                                    <tr v-for="item in assessmentTypes" :key="`first-${item.type}`">
                                                        <td class="px-4 py-2 font-medium">{{ item.label }}</td>
                                                        <td class="px-4 py-2">
                                                            <span
                                                                v-if="hasTeacherAssessment(submission.learning_group_id, item.type, 'first_submit')"
                                                                class="inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800"
                                                            >
                                                                Terkirim (Total {{ getTeacherAssessment(submission.learning_group_id, item.type, 'first_submit')?.total_score }}, Rata-rata {{ Number(getTeacherAssessment(submission.learning_group_id, item.type, 'first_submit')?.average_score || 0).toFixed(2) }})
                                                            </span>
                                                            <span v-else class="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                                                Belum dinilai
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <div class="flex flex-wrap gap-2">
                                                                <button
                                                                    v-if="!hasTeacherAssessment(submission.learning_group_id, item.type, 'first_submit')"
                                                                    type="button"
                                                                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                                    @click="openAssessmentModal(submission.learning_group_id, submission.learning_group?.name, item.type, 'first_submit')"
                                                                >
                                                                    Isi Asesmen
                                                                </button>
                                                                <button
                                                                    v-if="hasTeacherAssessment(submission.learning_group_id, item.type, 'first_submit')"
                                                                    type="button"
                                                                    class="rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                                    @click="openResultModal(submission.learning_group_id, item.type, 'first_submit')"
                                                                >
                                                                    Lihat Hasil
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div v-if="submission.final_submission" class="mt-4 border-t border-indigo-200 dark:border-indigo-800 pt-4">
                                        <h5 class="mb-3 text-sm font-semibold text-indigo-900 dark:text-indigo-200">Asesmen Guru - Final Submission ({{ submission.learning_group?.name }})</h5>
                                        <p class="mb-3 text-xs text-indigo-700 dark:text-indigo-300">Setiap asesmen hanya bisa dikirim sekali dan tidak dapat diubah.</p>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-indigo-100 dark:divide-indigo-800 rounded-md border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-800/60 text-sm">
                                                <thead class="bg-indigo-50 dark:bg-indigo-900/30">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-indigo-900 dark:text-indigo-200">Jenis Asesmen</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-indigo-900 dark:text-indigo-200">Status</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-indigo-900 dark:text-indigo-200">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-indigo-50 dark:divide-indigo-900/40 text-gray-700 dark:text-slate-300">
                                                    <tr v-for="item in assessmentTypes" :key="`final-${item.type}`">
                                                        <td class="px-4 py-2 font-medium">{{ item.label }}</td>
                                                        <td class="px-4 py-2">
                                                            <span
                                                                v-if="hasTeacherAssessment(submission.learning_group_id, item.type, 'final_submit')"
                                                                class="inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800"
                                                            >
                                                                Terkirim (Total {{ getTeacherAssessment(submission.learning_group_id, item.type, 'final_submit')?.total_score }}, Rata-rata {{ Number(getTeacherAssessment(submission.learning_group_id, item.type, 'final_submit')?.average_score || 0).toFixed(2) }})
                                                            </span>
                                                            <span v-else class="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                                                Belum dinilai
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <div class="flex flex-wrap gap-2">
                                                                <button
                                                                    v-if="!hasTeacherAssessment(submission.learning_group_id, item.type, 'final_submit')"
                                                                    type="button"
                                                                    class="rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50"
                                                                    @click="openAssessmentModal(submission.learning_group_id, submission.learning_group?.name, item.type, 'final_submit')"
                                                                >
                                                                    Isi Asesmen
                                                                </button>
                                                                <button
                                                                    v-if="hasTeacherAssessment(submission.learning_group_id, item.type, 'final_submit')"
                                                                    type="button"
                                                                    class="rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                                    @click="openResultModal(submission.learning_group_id, item.type, 'final_submit')"
                                                                >
                                                                    Lihat Hasil
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div v-else class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                        Asesmen final submission tersedia setelah kelompok melakukan final submission.
                                    </div>
                                </template>
                                <div v-else class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    Asesmen guru tersedia setelah kelompok melakukan first submission.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Group Modal -->
        <div v-if="showGroupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Create Learning Group</h3>
                        <p class="mt-1 text-sm text-gray-600">Buat group untuk task <strong>{{ task.title }}</strong>.</p>
                    </div>
                    <button type="button" class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200" @click="showGroupModal = false">✕</button>
                </div>

                <form class="mt-6 space-y-6" @submit.prevent="submitGroup">
                    <div>
                        <label for="task-group-name" class="block text-sm font-medium text-gray-700">Group Name</label>
                        <input
                            id="task-group-name"
                            v-model="createGroupForm.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="e.g., Group A"
                        >
                        <InputError class="mt-2" :message="createGroupForm.errors.name" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="showGroupModal = false">
                            Cancel
                        </button>
                        <PrimaryButton type="submit" :disabled="createGroupForm.processing">
                            {{ createGroupForm.processing ? 'Menyimpan...' : 'Create Group' }}
                        </PrimaryButton>
                    </div>
                </form>
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

        <!-- ─── Assessment Modal ──────────────────────────────────── -->
        <div
            v-if="assessmentModal && currentForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6"
        >
            <div class="max-h-[95vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ getTypeLabel(assessmentModal.type) }}</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-700">Kelompok: {{ assessmentModal.groupName }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">Tanggal: {{ todayLabel }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                        @click="closeAssessmentModal"
                    >
                        Tutup
                    </button>
                </div>

                <form class="mt-6 space-y-6" @submit.prevent="submitAssessment">
                    <template v-for="(criterion, idx) in currentRubric" :key="`c-${idx}`">
                        <div
                            v-if="criterion.section && (idx === 0 || currentRubric[idx - 1].section !== criterion.section)"
                            class="mt-4 border-b-2 border-indigo-200 pb-1"
                        >
                            <h4 class="text-sm font-bold uppercase tracking-wide text-indigo-700">{{ criterion.section }}</h4>
                        </div>
                    <div
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <h4 class="text-base font-semibold text-gray-900">{{ idx + 1 }}. {{ criterion.statement }}</h4>

                        <div class="mt-3 space-y-2 text-sm text-gray-700">
                            <label v-for="score in [4, 3, 2, 1]" :key="score" class="flex items-center gap-2">
                                <input v-model.number="currentForm[`score_${idx + 1}`]" type="radio" :name="`score-${idx + 1}`" :value="score">
                                <span>{{ score }} – {{ criterion.scales[score] }}</span>
                            </label>
                        </div>

                        <InputError class="mt-2" :message="currentForm.errors[`score_${idx + 1}`]" />

                        <textarea
                            v-model="currentForm[`comment_${idx + 1}`]"
                            rows="2"
                            class="mt-3 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Komentar guru (opsional)"
                        />
                    </div>
                    </template>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Skor</label>
                            <input :value="assessmentTotalScore" type="text" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rata-rata Skor</label>
                            <input :value="assessmentAverageScore" type="text" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        </div>
                    </div>

                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                        <label class="flex items-start gap-2">
                            <input v-model="currentForm.confirm_irreversible" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500 dark:border-amber-700 dark:bg-slate-900 dark:text-amber-400 dark:focus:ring-amber-400">
                            <span>Saya memahami bahwa asesmen ini tidak bisa diubah dan hanya bisa dilakukan sekali.</span>
                        </label>
                        <InputError class="mt-2" :message="currentForm.errors.confirm_irreversible" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            @click="closeAssessmentModal"
                        >
                            Batal
                        </button>
                        <PrimaryButton
                            :disabled="!isReadyToSubmit || currentForm.processing"
                            :class="!isReadyToSubmit || currentForm.processing ? 'opacity-50 cursor-not-allowed' : ''"
                        >
                            {{ currentForm.processing ? 'Menyimpan...' : 'Submit Asesmen' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Result Modal ─────────────────────────────────────── -->
        <div
            v-if="resultModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6"
        >
            <div class="max-h-[95vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="text-xl font-bold text-gray-900">Hasil {{ getTypeLabel(resultModal.type) }}</h3>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                        @click="closeResultModal"
                    >
                        Tutup
                    </button>
                </div>

                <div class="mb-4 mt-4 grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-3 text-sm text-gray-700 md:grid-cols-2">
                    <div><span class="font-semibold">Tanggal:</span> {{ formatDate(resultModal.assessment.assessment_date) }}</div>
                    <div><span class="font-semibold">Total Skor:</span> {{ resultModal.assessment.total_score }}</div>
                    <div><span class="font-semibold">Rata-rata:</span> {{ Number(resultModal.assessment.average_score || 0).toFixed(2) }}</div>
                </div>

                <div class="mt-4 space-y-3">
                    <template v-for="(criterion, idx) in currentResultRubric" :key="`r-${idx}`">
                        <div
                            v-if="criterion.section && (idx === 0 || currentResultRubric[idx - 1].section !== criterion.section)"
                            class="mt-4 border-b-2 border-slate-300 pb-1"
                        >
                            <h4 class="text-sm font-bold uppercase tracking-wide text-slate-600">{{ criterion.section }}</h4>
                        </div>
                        <div class="rounded-md border border-slate-200 p-3">
                            <p class="text-sm font-semibold text-slate-900">{{ idx + 1 }}. {{ criterion.statement }}</p>
                            <p class="mt-1 text-sm text-slate-700">Skor: <span class="font-semibold">{{ resultModal.assessment.scores?.[idx] ?? '-' }}</span></p>
                            <p class="mt-1 text-sm text-slate-600">Komentar: {{ resultModal.assessment.comments?.[idx] || '-' }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
