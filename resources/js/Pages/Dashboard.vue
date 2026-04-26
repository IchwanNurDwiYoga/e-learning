<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tasks: {
        type: Object,
        default: () => ({
            ongoing: [],
            upcoming: [],
            completed: [],
        }),
    },
});

const submissionForms = ref({});
const uploadPanelState = ref({});
const confirmFinalTaskId = ref(null);
const assessmentModal = ref(null);
const assessmentResultModal = ref(null);
const assessmentForms = ref({});
const page = usePage();
const nowTick = ref(Date.now());
let timer = null;

const assessmentItems = [
    { type: 'task', label: 'Asesmen Task' },
    { type: 'product', label: 'Asesmen Produk' },
    { type: 'product_presentation', label: 'Asesmen Presentasi Produk' },
];

const taskRubricCriteria = [
    {
        section: 'A. Merumuskan Masalah',
        statement: 'Merumuskan masalah berbasis etnosains',
        scales: {
            4: 'Masalah dirumuskan jelas, spesifik, dan eksplisit bersumber dari praktik/pengetahuan lokal yang nyata dan relevan',
            3: 'Masalah dirumuskan jelas dan berbasis praktik lokal, namun konteks atau fokusnya belum spesifik',
            2: 'Masalah bersifat umum/kontekstual, tetapi keterkaitannya dengan praktik lokal kurang jelas',
            1: 'Masalah tidak berbasis pengetahuan lokal dan tidak relevan',
        },
    },
    {
        section: 'A. Merumuskan Masalah',
        statement: 'Keterkaitan masalah dengan konsep sains',
        scales: {
            4: 'Masalah dikaitkan secara logis dan tepat dengan konsep sains yang relevan',
            3: 'Masalah dikaitkan dengan konsep sains, namun penjelasan belum mendalam',
            2: 'Keterkaitan dengan konsep sains lemah atau kurang tepat',
            1: 'Tidak menunjukkan keterkaitan dengan konsep sains',
        },
    },
    {
        section: 'B. Mengolah Data',
        statement: 'Integrasi data ilmiah dan pengetahuan etnosains',
        scales: {
            4: 'Data ilmiah dan pengetahuan lokal diolah secara sistematis dan saling menguatkan dalam membangun konsep',
            3: 'Data ilmiah dan pengetahuan lokal diolah cukup sistematis, namun integrasinya belum optimal',
            2: 'Data diolah secara terpisah dan integrasi sains-etnosains lemah',
            1: 'Tidak menunjukkan integrasi data ilmiah dan pengetahuan lokal',
        },
    },
    {
        section: 'B. Mengolah Data',
        statement: 'Analisis kesesuaian konsep sains dan praktik etnosains',
        scales: {
            4: 'Mampu menjelaskan kesesuaian dan/atau perbedaan sains-etnosains secara ilmiah dan logis',
            3: 'Analisis cukup logis, namun belum mendalam',
            2: 'Analisis bersifat deskriptif dan dangkal',
            1: 'Tidak mampu menganalisis kesesuaian konsep',
        },
    },
    {
        section: 'C. Merencanakan Solusi',
        statement: 'Perencanaan solusi berbasis konsep sains dan etnosains',
        scales: {
            4: 'Solusi dirancang dengan mengintegrasikan konsep sains, teknologi, dan praktik etnosains secara tepat',
            3: 'Solusi berbasis sains dan etnosains, namun kurang optimal',
            2: 'Solusi hanya sebagian berbasis konsep sains atau praktik etnosains',
            1: 'Solusi tidak berbasis konsep sains maupun praktik lokal',
        },
    },
    {
        section: 'C. Merencanakan Solusi',
        statement: 'Argumentasi ilmiah dalam pemilihan solusi',
        scales: {
            4: 'Alasan pemilihan solusi didukung argumen ilmiah yang kuat dalam konteks etnosains',
            3: 'Argumen cukup logis, namun belum didukung kajian ilmiah yang kuat',
            2: 'Argumen lemah dan kurang berbasis data',
            1: 'Tidak mampu memberikan argumentasi ilmiah',
        },
    },
    {
        section: 'D. Refleksi',
        statement: 'Refleksi kontribusi etnosains terhadap pemahaman konsep sains',
        scales: {
            4: 'Refleksi mendalam menunjukkan peran etnosains dalam memperkuat pemahaman konsep sains',
            3: 'Refleksi cukup tepat, namun belum mendalam',
            2: 'Refleksi bersifat umum dan deskriptif',
            1: 'Tidak menunjukkan refleksi yang relevan',
        },
    },
    {
        section: 'D. Refleksi',
        statement: 'Refleksi keterbatasan praktik etnosains',
        scales: {
            4: 'Menjelaskan keterbatasan etnosains secara kritis berdasarkan kajian ilmiah',
            3: 'Menyebutkan keterbatasan etnosains namun belum dianalisis mendalam',
            2: 'Menyebutkan keterbatasan secara umum tanpa dasar ilmiah',
            1: 'Tidak menyebutkan keterbatasan praktik etnosains',
        },
    },
    {
        section: 'E. Evaluasi dan Pengambilan Keputusan',
        statement: 'Evaluasi solusi berdasarkan sains, budaya, dan keberlanjutan',
        scales: {
            4: 'Evaluasi solusi mempertimbangkan data ilmiah, nilai budaya, dan keberlanjutan praktik lokal',
            3: 'Evaluasi mempertimbangkan sains dan budaya, namun aspek keberlanjutan belum kuat',
            2: 'Evaluasi terbatas pada satu aspek saja',
            1: 'Tidak melakukan evaluasi yang bermakna',
        },
    },
    {
        section: 'E. Evaluasi dan Pengambilan Keputusan',
        statement: 'Pengambilan keputusan berbasis integrasi sains-etnosains',
        scales: {
            4: 'Keputusan akhir logis, konsisten, dan berbasis integrasi sains serta kearifan lokal',
            3: 'Keputusan cukup logis, namun integrasi belum optimal',
            2: 'Keputusan kurang didukung pertimbangan ilmiah',
            1: 'Keputusan tidak berdasar',
        },
    },
];

const presentationRubricByTask = {
    'Task 1': [
        {
            statement: 'Menjelaskan solusi pencemaran sesuai konsep ilmiah',
            scales: {
                4: 'Sangat tepat, lengkap, dan sesuai konsep ilmiah',
                3: 'Cukup tepat namun kurang lengkap',
                2: 'Kurang tepat',
                1: 'Tidak sesuai',
            },
        },
        {
            statement: 'Menjelaskan alasan solusi berdasarkan konsep ilmiah',
            scales: {
                4: 'Alasan sangat logis dan berbasis konsep ilmiah',
                3: 'Alasan cukup logis',
                2: 'Alasan lemah',
                1: 'Tidak ada alasan',
            },
        },
        {
            statement: 'Mengaitkan solusi dengan lubuk larangan',
            scales: {
                4: 'Sangat relevan, jelas, dan mendalam',
                3: 'Cukup relevan, jelas dan kurang mendalam',
                2: 'Kurang relevan',
                1: 'Tidak dikaitkan',
            },
        },
        {
            statement: 'Menyampaikan presentasi secara runtut dan jelas',
            scales: {
                4: 'Sangat runtut, jelas, dan mudah dipahami',
                3: 'Cukup runtut',
                2: 'Kurang runtut',
                1: 'Tidak runtut',
            },
        },
        {
            statement: 'Menggunakan proposal untuk mendukung penjelasan',
            scales: {
                4: 'Proposal digunakan secara aktif (ditunjukkan, dijelaskan, dan mendukung pemahaman)',
                3: 'Proposal digunakan tetapi kurang optimal',
                2: 'Proposal hanya ditunjukkan tanpa penjelasan',
                1: 'Proposal tidak digunakan',
            },
        },
        {
            statement: 'Menjawab pertanyaan dengan tepat',
            scales: {
                4: 'Jawaban sangat tepat dan berbasis konsep',
                3: 'Cukup tepat',
                2: 'Kurang tepat',
                1: 'Tidak tepat',
            },
        },
    ],
    'Task 2': [
        {
            statement: 'Menjelaskan isi poster berdasarkan konsep pemanasan global',
            scales: {
                4: 'Penjelasan sangat tepat, lengkap, dan menggunakan konsep pemanasan global secara benar',
                3: 'Penjelasan cukup tepat namun belum lengkap',
                2: 'Penjelasan kurang tepat',
                1: 'Penjelasan tidak sesuai konsep',
            },
        },
        {
            statement: 'Menjelaskan hubungan sebab-akibat pemanasan global secara ilmiah',
            scales: {
                4: 'Menjelaskan hubungan sebab-akibat secara jelas, logis, dan ilmiah',
                3: 'Penjelasan cukup logis namun belum mendalam',
                2: 'Penjelasan kurang jelas',
                1: 'Tidak mampu menjelaskan',
            },
        },
        {
            statement: 'Mengaitkan pemanasan global dengan praktik atau kearifan lokal serta menjelaskan hubungan ilmiahnya',
            scales: {
                4: 'Mengaitkan secara sangat jelas dengan praktik/kearifan lokal serta menjelaskan hubungan ilmiahnya secara tepat',
                3: 'Mengaitkan dengan praktik lokal dan cukup relevan, namun penjelasan ilmiah kurang mendalam',
                2: 'Mengaitkan tetapi tidak jelas hubungan ilmiahnya',
                1: 'Tidak mengaitkan dengan praktik/kearifan lokal',
            },
        },
        {
            statement: 'Menyampaikan isi poster secara jelas, runtut, dan menarik',
            scales: {
                4: 'Penyampaian sangat jelas, runtut, menarik, dan mudah dipahami',
                3: 'Penyampaian cukup jelas',
                2: 'Penyampaian kurang runtut',
                1: 'Penyampaian tidak jelas',
            },
        },
        {
            statement: 'Menggunakan poster secara aktif untuk memperjelas penjelasan (menunjukkan, merujuk, dan menjelaskan isi poster)',
            scales: {
                4: 'Poster digunakan sangat aktif (ditunjukkan, dirujuk, dan dijelaskan) sehingga sangat membantu pemahaman',
                3: 'Poster digunakan tetapi belum maksimal',
                2: 'Poster hanya ditunjukkan tanpa penjelasan',
                1: 'Poster tidak digunakan',
            },
        },
        {
            statement: 'Menjawab pertanyaan secara tepat dan logis',
            scales: {
                4: 'Jawaban sangat tepat, logis, dan berbasis konsep',
                3: 'Jawaban cukup tepat',
                2: 'Jawaban kurang tepat',
                1: 'Tidak mampu menjawab',
            },
        },
    ],
    'Task 3': [
        {
            statement: 'Menjelaskan program pelestarian berdasarkan konsep ekologi (keseimbangan ekosistem, konservasi, biodiversitas)',
            scales: {
                4: 'Penjelasan sangat tepat, lengkap, dan menggunakan konsep pemanasan global secara benar',
                3: 'Penjelasan cukup tepat namun belum lengkap',
                2: 'Penjelasan kurang tepat',
                1: 'Penjelasan tidak sesuai konsep',
            },
        },
        {
            statement: 'Menjelaskan alasan program berdasarkan hubungan sebab-akibat ekologis',
            scales: {
                4: 'Menjelaskan hubungan sebab-akibat secara jelas, logis, dan ilmiah',
                3: 'Penjelasan cukup logis namun belum mendalam',
                2: 'Penjelasan kurang jelas',
                1: 'Tidak mampu menjelaskan',
            },
        },
        {
            statement: 'Mengaitkan program dengan praktik hutan larangan adat serta menjelaskan fungsi ekologisnya',
            scales: {
                4: 'Mengaitkan secara sangat jelas dengan praktik/kearifan lokal serta menjelaskan hubungan ilmiahnya secara tepat',
                3: 'Mengaitkan dengan praktik lokal dan cukup relevan, namun penjelasan ilmiah kurang mendalam',
                2: 'Mengaitkan tetapi tidak jelas hubungan ilmiahnya',
                1: 'Tidak mengaitkan dengan praktik/kearifan lokal',
            },
        },
        {
            statement: 'Menyampaikan program secara runtut, jelas, dan terstruktur (tujuan, langkah, dampak)',
            scales: {
                4: 'Penyampaian sangat jelas, runtut, menarik, dan mudah dipahami',
                3: 'Penyampaian cukup jelas',
                2: 'Penyampaian kurang runtut',
                1: 'Penyampaian tidak jelas',
            },
        },
        {
            statement: 'Menggunakan rancangan program secara aktif untuk memperjelas penjelasan (menunjukkan, merujuk, menjelaskan isi program)',
            scales: {
                4: 'Poster digunakan sangat aktif (ditunjukkan, dirujuk, dan dijelaskan) sehingga sangat membantu pemahaman',
                3: 'Poster digunakan tetapi belum maksimal',
                2: 'Poster hanya ditunjukkan tanpa penjelasan',
                1: 'Poster tidak digunakan',
            },
        },
        {
            statement: 'Menjawab pertanyaan secara tepat, logis, dan berbasis konsep ekologi',
            scales: {
                4: 'Jawaban sangat tepat, logis, dan berbasis konsep',
                3: 'Jawaban cukup tepat',
                2: 'Jawaban kurang tepat',
                1: 'Tidak mampu menjawab',
            },
        },
    ],
};

const productRubricByTask = {
    'Task 1': [
        {
            statement: 'Solusi sesuai dengan konsep pencemaran lingkungan',
            scales: {
                4: 'Solusi sesuai dengan konsep pencemaran lingkungan',
                3: 'Solusi cukup tepat dan sebagian besar sesuai konsep',
                2: 'Solusi kurang tepat dan hanya sebagian sesuai',
                1: 'Solusi tidak sesuai dengan konsep',
            },
        },
        {
            statement: 'Produk memuat komponen lengkap',
            scales: {
                4: 'Memuat masalah, solusi, dan langkah secara lengkap dan sistematis',
                3: 'Memuat komponen cukup lengkap namun kurang sistematis',
                2: 'Komponen tidak lengkap',
                1: 'Tidak memuat komponen utama',
            },
        },
        {
            statement: 'Integrasi lubuk larangan',
            scales: {
                4: 'Integrasi sangat jelas, relevan, dan mendalam',
                3: 'Integrasi cukup jelas',
                2: 'Integrasi kurang tepat',
                1: 'Tidak ada integrasi',
            },
        },
        {
            statement: 'Kelayakan solusi',
            scales: {
                4: 'Solusi sangat realistis dan dapat diterapkan',
                3: 'Solusi cukup realistis',
                2: 'Solusi sulit diterapkan',
                1: 'Solusi tidak realistis',
            },
        },
        {
            statement: 'Argumentasi ilmiah',
            scales: {
                4: 'Argumentasi sangat kuat, logis, dan berbasis konsep ilmiah',
                3: 'Argumentasi cukup logis',
                2: 'Argumentasi lemah',
                1: 'Tidak ada argumentasi',
            },
        },
    ],
    'Task 2': [
        {
            statement: 'Menjelaskan penyebab pemanasan global dengan benar',
            scales: {
                4: 'Penjelasan sangat tepat dan ilmiah',
                3: 'Penjelasan cukup tepat',
                2: 'Penjelasan kurang tepat',
                1: 'Tidak sesuai',
            },
        },
        {
            statement: 'Menjelaskan dampak secara jelas',
            scales: {
                4: 'Dampak dijelaskan lengkap dan mendalam',
                3: 'Dampak cukup jelas',
                2: 'Dampak kurang jelas',
                1: 'Tidak ada dampak',
            },
        },
        {
            statement: 'Menyajikan solusi yang tepat',
            scales: {
                4: 'Solusi sangat tepat dan aplikatif',
                3: 'Solusi cukup tepat',
                2: 'Solusi kurang tepat',
                1: 'Solusi tidak relevan',
            },
        },
        {
            statement: 'Tampilan mudah dipahami',
            scales: {
                4: 'Sangat jelas, menarik dan mudah dipahami',
                3: 'Cukup jelas',
                2: 'Kurang menarik',
                1: 'Tidak jelas',
            },
        },
        {
            statement: 'Memuat ajakan perubahan perilaku',
            scales: {
                4: 'Ajakan sangat kuat dan persuasif',
                3: 'Ajakan cukup jelas',
                2: 'Ajakan lemah',
                1: 'Tidak ada unsur mengajak',
            },
        },
    ],
    'Task 3': [
        {
            statement: 'Konsep pelestarian',
            scales: {
                4: 'Sangat tepat dan berbasis konsep ilmiah',
                3: 'Cukup tepat',
                2: 'Kurang tepat',
                1: 'Tidak tepat',
            },
        },
        {
            statement: 'Integrasi etnosains',
            scales: {
                4: 'Integrasi sangat kuat dan relevan',
                3: 'Integrasi cukup jelas',
                2: 'Integrasi lemah',
                1: 'Tidak terintegrasi',
            },
        },
        {
            statement: 'Langkah program',
            scales: {
                4: 'Sangat jelas, sistematis, dan rinci',
                3: 'Cukup jelas',
                2: 'Kurang sistematis',
                1: 'Tidak jelas',
            },
        },
        {
            statement: 'Kelayakan',
            scales: {
                4: 'Sangat realistis',
                3: 'Cukup realistis',
                2: 'Kurang realistis',
                1: 'Tidak realistis',
            },
        },
        {
            statement: 'Argumentasi',
            scales: {
                4: 'Sangat kuat dan ilmiah',
                3: 'Cukup logis',
                2: 'Lemah',
                1: 'Tidak ada',
            },
        },
    ],
};

// Untuk Task 1, 2, dan 3: isi rubric disamakan.
const assessmentCriteria = {
    task: taskRubricCriteria,
    product: taskRubricCriteria,
    product_presentation: taskRubricCriteria,
};

const resolveTaskLabel = (task) => {
    if (!task) return '';

    if (task.label && presentationRubricByTask[task.label]) {
        return task.label;
    }

    const title = String(task.title || '');
    const match = title.match(/task\s*(1|2|3)/i);
    if (match) {
        return `Task ${match[1]}`;
    }

    return '';
};

const flashSuccess = computed(() => page.props.flash?.success || '');
const flashError = computed(() => page.props.flash?.error || '');

const taskGroups = computed(() => ([
    {
        key: 'ongoing',
        title: 'Tugas Sedang Berjalan',
        emptyText: 'Belum ada tugas yang sedang berjalan.',
    },
    {
        key: 'upcoming',
        title: 'Tugas Akan Datang',
        emptyText: 'Belum ada tugas yang akan datang.',
    },
    {
        key: 'completed',
        title: 'Tugas Selesai',
        emptyText: 'Belum ada tugas yang selesai.',
    },
]));

const getForm = (taskId) => {
    if (!submissionForms.value[taskId]) {
        submissionForms.value[taskId] = useForm({
            description: '',
            file: null,
            confirm_final_submission: false,
        });
    }

    return submissionForms.value[taskId];
};

const onFileChange = (taskId, event) => {
    const file = event.target.files[0] || null;
    getForm(taskId).file = file;
};

const submitTask = (taskId, isFinalSubmission = false) => {
    const form = getForm(taskId);
    form.confirm_final_submission = isFinalSubmission;

    form.post(route('student.task-submissions.store', taskId), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            if (isFinalSubmission) {
                confirmFinalTaskId.value = null;
            }

            form.file = null;
            form.confirm_final_submission = false;
        },
        onFinish: () => {
            form.confirm_final_submission = false;
        },
    });
};

const requiresFinalConfirmation = (task) => {
    return Boolean(task.first_submission && !task.final_submission);
};

const requestSubmit = (task) => {
    if (requiresFinalConfirmation(task)) {
        confirmFinalTaskId.value = task.id;
        return;
    }

    submitTask(task.id, false);
};

const confirmFinalSubmission = () => {
    if (!confirmFinalTaskId.value) return;
    submitTask(confirmFinalTaskId.value, true);
};

const cancelFinalSubmission = () => {
    confirmFinalTaskId.value = null;
};

const getTaskById = (taskId) => {
    const allTasks = [
        ...(props.tasks.ongoing || []),
        ...(props.tasks.upcoming || []),
        ...(props.tasks.completed || []),
    ];

    return allTasks.find((task) => task.id === taskId) || null;
};

const finalSubmissionTask = computed(() => {
    return confirmFinalTaskId.value ? getTaskById(confirmFinalTaskId.value) : null;
});

const submissionStageLabel = (submissionLabel) => {
    if (submissionLabel === 'final_submit') return 'Final Submission';
    return 'First Submission';
};

const getAssessmentKey = (taskId, scope, type, stage) => {
    return `${taskId}-${scope}-${type}-${stage}`;
};

const getAssessmentForm = (taskId, scope, type, stage) => {
    const key = getAssessmentKey(taskId, scope, type, stage);

    if (!assessmentForms.value[key]) {
        const initialState = {
            assessment_scope: scope,
            assessment_type: type,
            submission_stage: stage,
            target_group_id: '',
            confirm_irreversible: false,
        };

        // Form disiapkan fleksibel agar bisa menampung rubric dengan jumlah indikator berbeda-beda.
        for (let i = 1; i <= 20; i += 1) {
            initialState[`score_${i}`] = null;
            initialState[`comment_${i}`] = '';
        }

        assessmentForms.value[key] = useForm({
            ...initialState,
        });
    }

    return assessmentForms.value[key];
};

const getAssessmentStatus = (task, scope, type, stage) => {
    return (task.assessment_statuses || []).find((assessment) => {
        return assessment.assessment_scope === scope && assessment.assessment_type === type && assessment.submission_stage === stage;
    }) || null;
};

const getSubmittedAssessment = (task, scope, type, stage) => {
    return (task.submitted_assessments || []).find((assessment) => {
        return assessment.assessment_scope === scope && assessment.assessment_type === type && assessment.submission_stage === stage;
    }) || null;
};

const getReceivedPeerAssessments = (task, type, stage) => {
    return (task.received_peer_assessments || []).filter((assessment) => {
        return assessment.assessment_type === type && assessment.submission_stage === stage;
    });
};

const getReceivedTeacherAssessments = (task, type, stage) => {
    return (task.received_teacher_assessments || []).filter((assessment) => {
        return assessment.assessment_type === type && assessment.submission_stage === stage;
    });
};

const hasAssessmentSubmitted = (task, scope, type, stage) => {
    return Boolean(getAssessmentStatus(task, scope, type, stage));
};

const hasPeerGroupOptions = (task) => {
    return (task.course_groups || []).some((group) => group.id !== task.learning_group?.id);
};

const openAssessmentModal = (task, scope, type, stage) => {
    if (hasAssessmentSubmitted(task, scope, type, stage)) {
        return;
    }

    const form = getAssessmentForm(task.id, scope, type, stage);
    form.assessment_scope = scope;
    form.assessment_type = type;
    form.submission_stage = stage;

    if (scope === 'personal_group') {
        form.target_group_id = task.learning_group?.id || '';
    } else {
        const peerGroups = (task.course_groups || []).filter((group) => group.id !== task.learning_group?.id);
        form.target_group_id = peerGroups[0]?.id || '';
    }

    assessmentModal.value = {
        taskId: task.id,
        scope,
        type,
        stage,
    };
};

const closeAssessmentModal = () => {
    assessmentModal.value = null;
};

const openAssessmentResultModal = (task, scope, type, mode, stage) => {
    let results = [];

    if (mode === 'submitted') {
        const submitted = getSubmittedAssessment(task, scope, type, stage);
        results = submitted ? [submitted] : [];
    } else if (mode === 'received_peer') {
        results = getReceivedPeerAssessments(task, type, stage);
    } else if (mode === 'received_teacher') {
        results = getReceivedTeacherAssessments(task, type, stage);
    }

    assessmentResultModal.value = {
        taskId: task.id,
        scope,
        type,
        mode,
        stage,
        results,
    };
};

const closeAssessmentResultModal = () => {
    assessmentResultModal.value = null;
};

const currentAssessmentTask = computed(() => {
    if (!assessmentModal.value) return null;
    return getTaskById(assessmentModal.value.taskId);
});

const currentAssessmentResultTask = computed(() => {
    if (!assessmentResultModal.value) return null;
    return getTaskById(assessmentResultModal.value.taskId);
});

const currentAssessmentItem = computed(() => {
    if (!assessmentModal.value) return null;
    return assessmentItems.find((item) => item.type === assessmentModal.value.type) || null;
});

const currentAssessmentCriteria = computed(() => {
    if (!assessmentModal.value) return [];

    if (assessmentModal.value.type === 'product') {
        const task = currentAssessmentTask.value;
        const taskLabel = resolveTaskLabel(task);

        if (taskLabel && productRubricByTask[taskLabel]) {
            return productRubricByTask[taskLabel];
        }
    }

    if (assessmentModal.value.type === 'product_presentation') {
        const task = currentAssessmentTask.value;
        const taskLabel = resolveTaskLabel(task);

        if (taskLabel && presentationRubricByTask[taskLabel]) {
            return presentationRubricByTask[taskLabel];
        }
    }

    return assessmentCriteria[assessmentModal.value.type] || [];
});

const currentResultCriteria = computed(() => {
    if (!assessmentResultModal.value) return [];

    const task = currentAssessmentResultTask.value;
    const type = assessmentResultModal.value.type;
    if (!task) return [];

    if (type === 'product') {
        const taskLabel = resolveTaskLabel(task);
        if (taskLabel && productRubricByTask[taskLabel]) {
            return productRubricByTask[taskLabel];
        }
    }

    if (type === 'product_presentation') {
        const taskLabel = resolveTaskLabel(task);
        if (taskLabel && presentationRubricByTask[taskLabel]) {
            return presentationRubricByTask[taskLabel];
        }
    }

    return assessmentCriteria[type] || [];
});

const currentRubricTitle = computed(() => {
    if (!assessmentModal.value) return '';

    if (assessmentModal.value.type === 'product') {
        const taskLabel = resolveTaskLabel(currentAssessmentTask.value);
        if (taskLabel) {
            return `ASESMEN PRODUCT PADA PERFORMANCE ${taskLabel.toUpperCase()}`;
        }

        return 'ASESMEN PRODUCT';
    }

    if (assessmentModal.value.type === 'product_presentation') {
        const taskLabel = resolveTaskLabel(currentAssessmentTask.value);
        if (taskLabel) {
            return `ASESMEN PRESENTASI PRODUCT ${taskLabel.toUpperCase()}`;
        }

        return 'ASESMEN PRESENTASI PRODUCT';
    }

    return 'ASESMEN TASK 123';
});

const currentResultTitle = computed(() => {
    if (!assessmentResultModal.value) return '';

    const task = currentAssessmentResultTask.value;
    const taskLabel = resolveTaskLabel(task);

    if (assessmentResultModal.value.type === 'product_presentation') {
        if (assessmentResultModal.value.mode === 'received_teacher') {
            return taskLabel ? `HASIL ASESMEN GURU PRESENTASI PRODUCT ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN GURU PRESENTASI PRODUCT';
        }
        return taskLabel ? `HASIL ASESMEN PRESENTASI PRODUCT ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN PRESENTASI PRODUCT';
    }

    if (assessmentResultModal.value.type === 'product') {
        if (assessmentResultModal.value.mode === 'received_teacher') {
            return taskLabel ? `HASIL ASESMEN GURU PRODUCT ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN GURU PRODUCT';
        }
        return taskLabel ? `HASIL ASESMEN PRODUCT ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN PRODUCT';
    }

    if (assessmentResultModal.value.mode === 'received_teacher') {
        return taskLabel ? `HASIL ASESMEN GURU TASK ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN GURU TASK';
    }

    return taskLabel ? `HASIL ASESMEN TASK ${taskLabel.toUpperCase()}` : 'HASIL ASESMEN TASK';
});

const isScoreFilled = (value) => {
    return value !== null && value !== undefined && value !== '';
};

const isAssessmentReadyToSubmit = computed(() => {
    const form = currentAssessmentForm.value;
    const indicators = currentAssessmentCriteria.value;

    if (!form || indicators.length === 0) return false;

    const hasAllScores = indicators.every((_, idx) => {
        return isScoreFilled(form[`score_${idx + 1}`]);
    });

    return Boolean(form.confirm_irreversible) && Boolean(form.target_group_id) && hasAllScores;
});

const currentAssessmentForm = computed(() => {
    if (!assessmentModal.value) return null;
    return getAssessmentForm(
        assessmentModal.value.taskId,
        assessmentModal.value.scope,
        assessmentModal.value.type,
        assessmentModal.value.stage,
    );
});

const currentAssessmentScopeLabel = computed(() => {
    if (!assessmentModal.value) return '-';
    return assessmentModal.value.scope === 'peer_group'
        ? 'Penilaian Teman Sebaya (Kelompok)'
        : 'Penilaian Pribadi (Kelompok)';
});

const currentAssessmentModalId = computed(() => {
    if (!assessmentModal.value) return '-';
    return `assessment-modal-task-${assessmentModal.value.taskId}-${assessmentModal.value.scope}-${assessmentModal.value.type}`;
});

const currentTargetGroupOptions = computed(() => {
    const task = currentAssessmentTask.value;
    if (!task || !assessmentModal.value) return [];

    if (assessmentModal.value.scope === 'personal_group') {
        return (task.course_groups || []).filter((group) => group.id === task.learning_group?.id);
    }

    return (task.course_groups || []).filter((group) => group.id !== task.learning_group?.id);
});

const todayLabel = computed(() => {
    return new Date().toLocaleDateString('en-GB');
});

const assessmentTotalScore = computed(() => {
    const form = currentAssessmentForm.value;
    const indicators = currentAssessmentCriteria.value;
    if (!form || indicators.length === 0) return 0;

    return indicators.reduce((acc, _, idx) => {
        return acc + Number(form[`score_${idx + 1}`] || 0);
    }, 0);
});

const assessmentAverageScore = computed(() => {
    const indicators = currentAssessmentCriteria.value;
    if (indicators.length === 0) return 0;
    return Number((assessmentTotalScore.value / indicators.length).toFixed(2));
});

const submitAssessment = () => {
    if (!assessmentModal.value || !currentAssessmentTask.value || !currentAssessmentForm.value) {
        return;
    }

    const indicatorLength = currentAssessmentCriteria.value.length;
    const scores = [];
    const comments = [];

    for (let i = 1; i <= indicatorLength; i += 1) {
        scores.push(Number(currentAssessmentForm.value[`score_${i}`]));
        comments.push(currentAssessmentForm.value[`comment_${i}`] || '');
    }

    currentAssessmentForm.value.transform((data) => ({
        ...data,
        scores,
        comments,
    }));

    currentAssessmentForm.value.post(
        route('student.task-assessments.store', currentAssessmentTask.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                closeAssessmentModal();
            },
            onFinish: () => {
                currentAssessmentForm.value.transform((data) => data);
            },
        },
    );
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

const statusBadgeClass = (status) => {
    const styles = {
        submitted: 'bg-blue-100 text-blue-800',
        reviewed: 'bg-green-100 text-green-800',
        returned: 'bg-red-100 text-red-800',
    };

    return styles[status] || 'bg-gray-100 text-gray-700';
};

const statusLabel = (status) => {
    const labels = {
        submitted: 'Diserahkan',
        reviewed: 'Direview',
        returned: 'Harus Revisi',
    };

    return labels[status] || status;
};

const taskStatusBadgeClass = (task) => {
    if (!task.existing_submission) {
        return 'bg-slate-100 text-slate-800';
    }

    return statusBadgeClass(task.existing_submission.status);
};

const taskStatusLabel = (task) => {
    if (!task.existing_submission) {
        return 'Dibuat oleh Teacher';
    }

    return statusLabel(task.existing_submission.status);
};

const needsRevision = (task) => task.existing_submission?.status === 'returned';

const getRevisionNote = (task) => {
    const note = task.existing_submission?.teacher_notes;
    return note || 'Teacher meminta revisi, silakan perbaiki jawaban lalu upload ulang.';
};

const isUploadPanelOpen = (task) => {
    const panelState = uploadPanelState.value[task.id];

    if (typeof panelState === 'boolean') {
        return panelState;
    }

    return needsRevision(task);
};

const toggleUploadPanel = (taskId) => {
    uploadPanelState.value[taskId] = !uploadPanelState.value[taskId];
};

const getCountdown = (deadline) => {
    if (!deadline) return '-';

    const diffMs = new Date(deadline).getTime() - nowTick.value;
    if (diffMs <= 0) return 'Waktu habis';

    const totalMinutes = Math.floor(diffMs / 60000);
    const days = Math.floor(totalMinutes / 1440);
    const hours = Math.floor((totalMinutes % 1440) / 60);
    const minutes = totalMinutes % 60;

    if (days > 0) {
        return `${days} hari ${hours} jam ${minutes} menit`;
    }

    if (hours > 0) {
        return `${hours} jam ${minutes} menit`;
    }

    return `${minutes} menit`;
};

onMounted(() => {
    timer = window.setInterval(() => {
        nowTick.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (timer) {
        window.clearInterval(timer);
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Student</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div v-if="flashSuccess" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
                    {{ flashSuccess }}
                </div>

                <div v-if="flashError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200">
                    {{ flashError }}
                </div>

                <div v-for="group in taskGroups" :key="group.key" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ group.title }}</h3>
                    </div>

                    <div v-if="(tasks[group.key] || []).length === 0" class="px-6 py-8 text-sm text-gray-500">
                        {{ group.emptyText }}
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="task in tasks[group.key]" :key="task.id" class="space-y-5 px-6 py-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <span v-if="task.label" class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ task.label }}
                                    </span>
                                    <h4 class="text-base font-semibold text-gray-900">{{ task.title }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">{{ task.course?.title }}</p>
                                    <p v-if="task.description" class="mt-2 text-sm text-gray-600">{{ task.description }}</p>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div>Mulai: <span class="font-medium text-gray-800">{{ formatDate(task.start_date) }}</span></div>
                                    <div>Deadline: <span class="font-medium" :class="task.status_group === 'completed' ? 'text-red-600' : 'text-gray-800'">{{ formatDate(task.deadline) }}</span></div>
                                </div>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-gray-700">Status Tugas:</span>
                                    <span :class="['rounded px-2 py-1 text-xs font-semibold', taskStatusBadgeClass(task)]">
                                        {{ taskStatusLabel(task) }}
                                    </span>
                                </div>

                                <p v-if="task.can_access">Akses tugas tersedia.</p>
                                <p v-else>Akses tugas masih terkunci sampai waktu mulai.</p>

                                <p v-if="task.status_group === 'ongoing'" class="mt-2">
                                    Sisa waktu: <span class="font-semibold text-amber-700">{{ getCountdown(task.deadline) }}</span>
                                </p>

                                <p class="mt-2" v-if="task.learning_group">
                                    Grup: <span class="font-semibold">{{ task.learning_group.name }}</span>
                                    <span v-if="task.is_leader" class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Leader</span>
                                </p>

                                <p v-if="needsRevision(task)" class="mt-2 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    Catatan Revisi: {{ getRevisionNote(task) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <a
                                    v-if="task.file_path && task.can_access"
                                    :href="route('student.tasks.download', task.id)"
                                    class="font-semibold text-indigo-600 hover:text-indigo-700"
                                >
                                    Download File Tugas
                                </a>
                                <span v-else-if="task.file_path" class="text-gray-500">File tugas terkunci (belum mulai).</span>
                                <span v-else class="text-gray-500">Tidak ada file lampiran.</span>
                            </div>

                            <div v-if="task.existing_submission || task.first_submission || task.final_submission" class="rounded-lg border border-gray-200 p-4 text-sm space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">Submission Grup:</span>
                                    <span :class="['rounded px-2 py-1 text-xs font-semibold', statusBadgeClass(task.existing_submission.status)]">
                                        {{ statusLabel(task.existing_submission.status) }}
                                    </span>
                                    <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                        {{ submissionStageLabel(task.existing_submission.submission_label) }}
                                    </span>
                                </div>
                                <p class="mt-2 text-gray-600" v-if="task.existing_submission.description">{{ task.existing_submission.description }}</p>
                                <p class="mt-2 text-gray-600" v-if="task.existing_submission.submitted_by">Dikirim oleh: {{ task.existing_submission.submitted_by.name }}</p>

                                <div class="space-y-2 rounded-md bg-gray-50 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-medium text-gray-700">First Submission:</span>
                                        <a
                                            v-if="task.first_submission?.file_path"
                                            :href="route('student.task-submissions.download', task.first_submission.id)"
                                            class="font-semibold text-indigo-600 hover:text-indigo-700"
                                        >
                                            {{ task.first_submission.file_name }} (Download)
                                        </a>
                                        <span v-else class="text-gray-500">Belum upload</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-medium text-gray-700">Final Submission:</span>
                                        <a
                                            v-if="task.final_submission?.file_path"
                                            :href="route('student.task-submissions.download', task.final_submission.id)"
                                            class="font-semibold text-indigo-600 hover:text-indigo-700"
                                        >
                                            {{ task.final_submission.file_name }} (Download)
                                        </a>
                                        <span v-else class="text-gray-500">Belum upload</span>
                                    </div>
                                </div>
                            </div>

                            <template v-if="task.first_submission">
                                <!-- ── Asesmen First Submission ──────────────────── -->
                                <div class="rounded-lg border border-slate-300 bg-slate-50 p-4">
                                    <h6 class="text-sm font-semibold text-slate-900">Asesmen First Submission</h6>
                                    <p class="mt-1 text-xs text-slate-700">Setiap asesmen hanya bisa dikirim sekali dan tidak dapat diubah.</p>

                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-200 rounded-md border border-slate-200 bg-white text-sm">
                                            <thead class="bg-slate-100">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Jenis Asesmen</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Penilaian Pribadi (Kelompok)</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Penilaian Teman Sebaya (Kelompok)</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Asesmen Guru</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 text-gray-700">
                                                <tr v-for="(item, idx) in assessmentItems" :key="`${task.id}-first-${item.type}`">
                                                    <td class="px-4 py-2">{{ idx + 1 }}. {{ item.label }}</td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            type="button"
                                                            class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                                                            :class="hasAssessmentSubmitted(task, 'personal_group', item.type, 'first_submit')
                                                                ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-600'
                                                                : 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50'"
                                                            :disabled="hasAssessmentSubmitted(task, 'personal_group', item.type, 'first_submit')"
                                                            @click="openAssessmentModal(task, 'personal_group', item.type, 'first_submit')"
                                                        >
                                                            <span v-if="hasAssessmentSubmitted(task, 'personal_group', item.type, 'first_submit')">
                                                                Terkirim (Total {{ getAssessmentStatus(task, 'personal_group', item.type, 'first_submit')?.total_score }}, Rata-rata {{ getAssessmentStatus(task, 'personal_group', item.type, 'first_submit')?.average_score }})
                                                            </span>
                                                            <span v-else>Isi Asesmen</span>
                                                        </button>
                                                        <button
                                                            v-if="hasAssessmentSubmitted(task, 'personal_group', item.type, 'first_submit')"
                                                            type="button"
                                                            class="mt-2 block rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                            @click="openAssessmentResultModal(task, 'personal_group', item.type, 'submitted', 'first_submit')"
                                                        >
                                                            Lihat Hasil
                                                        </button>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            type="button"
                                                            class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                                                            :class="hasAssessmentSubmitted(task, 'peer_group', item.type, 'first_submit')
                                                                ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-600'
                                                                : 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50'"
                                                            :disabled="hasAssessmentSubmitted(task, 'peer_group', item.type, 'first_submit')"
                                                            @click="openAssessmentModal(task, 'peer_group', item.type, 'first_submit')"
                                                        >
                                                            <span v-if="hasAssessmentSubmitted(task, 'peer_group', item.type, 'first_submit')">
                                                                Terkirim (Total {{ getAssessmentStatus(task, 'peer_group', item.type, 'first_submit')?.total_score }}, Rata-rata {{ getAssessmentStatus(task, 'peer_group', item.type, 'first_submit')?.average_score }})
                                                            </span>
                                                            <span v-else>Isi Asesmen</span>
                                                        </button>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <button
                                                                v-if="hasAssessmentSubmitted(task, 'peer_group', item.type, 'first_submit')"
                                                                type="button"
                                                                class="rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                                @click="openAssessmentResultModal(task, 'peer_group', item.type, 'submitted', 'first_submit')"
                                                            >
                                                                Lihat Hasil Saya
                                                            </button>
                                                            <button
                                                                v-if="getReceivedPeerAssessments(task, item.type, 'first_submit').length > 0"
                                                                type="button"
                                                                class="rounded-md border border-sky-300 bg-white px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-50"
                                                                @click="openAssessmentResultModal(task, 'peer_group', item.type, 'received_peer', 'first_submit')"
                                                            >
                                                                Lihat Hasil Peer ke Kelompok Saya
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            v-if="getReceivedTeacherAssessments(task, item.type, 'first_submit').length > 0"
                                                            type="button"
                                                            class="rounded-md border border-violet-300 bg-white px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50"
                                                            @click="openAssessmentResultModal(task, 'teacher', item.type, 'received_teacher', 'first_submit')"
                                                        >
                                                            Lihat Hasil Guru
                                                        </button>
                                                        <span v-else class="text-xs text-gray-500">Belum ada hasil</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ── Asesmen Final Submission ───────────────────── -->
                                <div v-if="task.final_submission" class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20 p-4">
                                    <h6 class="text-sm font-semibold text-blue-900 dark:text-blue-200">Asesmen Final Submission</h6>
                                    <p class="mt-1 text-xs text-blue-800 dark:text-blue-300">Setiap asesmen hanya bisa dikirim sekali dan tidak dapat diubah.</p>

                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-800 rounded-md border border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-800/60 text-sm">
                                            <thead class="bg-blue-100 dark:bg-blue-900/30">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-semibold text-blue-900 dark:text-blue-200">Jenis Asesmen</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-blue-900 dark:text-blue-200">Penilaian Pribadi (Kelompok)</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-blue-900 dark:text-blue-200">Penilaian Teman Sebaya (Kelompok)</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-blue-900 dark:text-blue-200">Asesmen Guru</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-blue-100 dark:divide-blue-900/40 text-gray-700 dark:text-slate-300">
                                                <tr v-for="(item, idx) in assessmentItems" :key="`${task.id}-final-${item.type}`">
                                                    <td class="px-4 py-2">{{ idx + 1 }}. {{ item.label }}</td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            type="button"
                                                            class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                                                            :class="hasAssessmentSubmitted(task, 'personal_group', item.type, 'final_submit')
                                                                ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-600'
                                                                : 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50'"
                                                            :disabled="hasAssessmentSubmitted(task, 'personal_group', item.type, 'final_submit')"
                                                            @click="openAssessmentModal(task, 'personal_group', item.type, 'final_submit')"
                                                        >
                                                            <span v-if="hasAssessmentSubmitted(task, 'personal_group', item.type, 'final_submit')">
                                                                Terkirim (Total {{ getAssessmentStatus(task, 'personal_group', item.type, 'final_submit')?.total_score }}, Rata-rata {{ getAssessmentStatus(task, 'personal_group', item.type, 'final_submit')?.average_score }})
                                                            </span>
                                                            <span v-else>Isi Asesmen</span>
                                                        </button>
                                                        <button
                                                            v-if="hasAssessmentSubmitted(task, 'personal_group', item.type, 'final_submit')"
                                                            type="button"
                                                            class="mt-2 block rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                            @click="openAssessmentResultModal(task, 'personal_group', item.type, 'submitted', 'final_submit')"
                                                        >
                                                            Lihat Hasil
                                                        </button>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            type="button"
                                                            class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                                                            :class="hasAssessmentSubmitted(task, 'peer_group', item.type, 'final_submit')
                                                                ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-600'
                                                                : 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50'"
                                                            :disabled="hasAssessmentSubmitted(task, 'peer_group', item.type, 'final_submit')"
                                                            @click="openAssessmentModal(task, 'peer_group', item.type, 'final_submit')"
                                                        >
                                                            <span v-if="hasAssessmentSubmitted(task, 'peer_group', item.type, 'final_submit')">
                                                                Terkirim (Total {{ getAssessmentStatus(task, 'peer_group', item.type, 'final_submit')?.total_score }}, Rata-rata {{ getAssessmentStatus(task, 'peer_group', item.type, 'final_submit')?.average_score }})
                                                            </span>
                                                            <span v-else>Isi Asesmen</span>
                                                        </button>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <button
                                                                v-if="hasAssessmentSubmitted(task, 'peer_group', item.type, 'final_submit')"
                                                                type="button"
                                                                class="rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
                                                                @click="openAssessmentResultModal(task, 'peer_group', item.type, 'submitted', 'final_submit')"
                                                            >
                                                                Lihat Hasil Saya
                                                            </button>
                                                            <button
                                                                v-if="getReceivedPeerAssessments(task, item.type, 'final_submit').length > 0"
                                                                type="button"
                                                                class="rounded-md border border-sky-300 bg-white px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-50"
                                                                @click="openAssessmentResultModal(task, 'peer_group', item.type, 'received_peer', 'final_submit')"
                                                            >
                                                                Lihat Hasil Peer ke Kelompok Saya
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <button
                                                            v-if="getReceivedTeacherAssessments(task, item.type, 'final_submit').length > 0"
                                                            type="button"
                                                            class="rounded-md border border-violet-300 bg-white px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50"
                                                            @click="openAssessmentResultModal(task, 'teacher', item.type, 'received_teacher', 'final_submit')"
                                                        >
                                                            Lihat Hasil Guru
                                                        </button>
                                                        <span v-else class="text-xs text-gray-500">Belum ada hasil</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div v-else class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    Asesmen Final Submission tersedia setelah kelompok melakukan final submission.
                                </div>
                            </template>

                            <div v-if="task.can_submit" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <h5 class="text-sm font-semibold text-emerald-900">Upload Jawaban (Khusus Leader)</h5>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                        @click="toggleUploadPanel(task.id)"
                                    >
                                        {{ isUploadPanelOpen(task) ? 'Minimize Form' : 'Buka Form Upload' }}
                                    </button>
                                </div>

                                <form v-if="isUploadPanelOpen(task)" class="mt-4 space-y-4" @submit.prevent="requestSubmit(task)">
                                    <div>
                                        <label :for="`description-${task.id}`" class="block text-sm font-medium text-gray-700">Deskripsi Jawaban</label>
                                        <textarea
                                            :id="`description-${task.id}`"
                                            v-model="getForm(task.id).description"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                        />
                                        <InputError class="mt-2" :message="getForm(task.id).errors.description" />
                                    </div>

                                    <div>
                                        <label :for="`file-${task.id}`" class="block text-sm font-medium text-gray-700">File Jawaban</label>
                                        <input
                                            :id="`file-${task.id}`"
                                            type="file"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                                            @change="onFileChange(task.id, $event)"
                                        >
                                        <InputError class="mt-2" :message="getForm(task.id).errors.file" />
                                    </div>

                                    <PrimaryButton :disabled="getForm(task.id).processing">
                                        {{ getForm(task.id).processing ? 'Mengunggah...' : (task.is_final_submission_stage ? 'Upload Final Submission' : 'Upload First Submission') }}
                                    </PrimaryButton>
                                </form>
                            </div>

                            <div v-else-if="task.status_group === 'ongoing'" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                Hanya leader grup yang bisa upload submission.
                            </div>

                            <div v-else-if="task.status_group === 'completed'" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                Deadline sudah lewat. Upload submission ditutup.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="finalSubmissionTask"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Final Submission</h3>
                <p class="mt-3 text-sm text-gray-600">
                    Anda akan mengirim <span class="font-semibold">final submission</span> untuk tugas
                    <span class="font-semibold">{{ finalSubmissionTask.title }}</span>.
                    Final submission hanya bisa dilakukan satu kali dan setelah ini Anda tidak bisa upload lagi.
                </p>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="cancelFinalSubmission"
                    >
                        Batal
                    </button>
                    <PrimaryButton
                        :disabled="getForm(finalSubmissionTask.id).processing"
                        @click="confirmFinalSubmission"
                    >
                        {{ getForm(finalSubmissionTask.id).processing ? 'Mengunggah...' : 'Ya, Final Submission' }}
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <div
            v-if="currentAssessmentTask && currentAssessmentForm"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 px-4 py-6"
        >
            <div class="max-h-[95vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            Rubrik {{ currentAssessmentItem?.label }}
                        </h3>
                        <p class="mt-1 text-lg font-semibold text-gray-800">
                            {{ currentAssessmentTask.label || currentAssessmentTask.title }} - {{ currentAssessmentScopeLabel }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-gray-700">{{ currentRubricTitle }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">ID Modal: {{ currentAssessmentModalId }}</p>
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
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Siswa / Kelompok</label>
                            <input
                                v-if="assessmentModal.scope === 'personal_group'"
                                :value="currentAssessmentTask.learning_group?.name || '-'"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                            <select
                                v-else
                                v-model="currentAssessmentForm.target_group_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Pilih kelompok</option>
                                <option
                                    v-for="group in currentTargetGroupOptions"
                                    :key="group.id"
                                    :value="group.id"
                                >
                                    {{ group.name }}
                                </option>
                            </select>
                            <p
                                v-if="assessmentModal.scope === 'peer_group' && !hasPeerGroupOptions(currentAssessmentTask)"
                                class="mt-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"
                            >
                                Belum ada kelompok lain pada course ini, jadi penilaian teman sebaya belum bisa diisi.
                            </p>
                            <InputError class="mt-2" :message="currentAssessmentForm.errors.target_group_id" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <input
                                :value="currentAssessmentTask.course?.title || '-'"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input
                                :value="todayLabel"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Penilai</label>
                            <input
                                :value="currentAssessmentTask.assessor_name || '-'"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                        </div>
                    </div>

                    <template v-for="(criterion, idx) in currentAssessmentCriteria" :key="`criterion-${idx}`">
                        <div
                            v-if="criterion.section && (idx === 0 || currentAssessmentCriteria[idx - 1].section !== criterion.section)"
                            class="mt-4 border-b-2 border-indigo-200 pb-1"
                        >
                            <h4 class="text-sm font-bold uppercase tracking-wide text-indigo-700">{{ criterion.section }}</h4>
                        </div>
                    <div
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <h4 class="text-base font-semibold text-gray-900">{{ idx + 1 }}. {{ criterion.statement }}</h4>

                        <div class="mt-3 space-y-2 text-sm text-gray-700">
                            <label class="flex items-center gap-2">
                                <input v-model.number="currentAssessmentForm[`score_${idx + 1}`]" type="radio" :name="`score-${idx + 1}`" :value="4">
                                <span>4 - {{ criterion.scales[4] }}</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model.number="currentAssessmentForm[`score_${idx + 1}`]" type="radio" :name="`score-${idx + 1}`" :value="3">
                                <span>3 - {{ criterion.scales[3] }}</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model.number="currentAssessmentForm[`score_${idx + 1}`]" type="radio" :name="`score-${idx + 1}`" :value="2">
                                <span>2 - {{ criterion.scales[2] }}</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model.number="currentAssessmentForm[`score_${idx + 1}`]" type="radio" :name="`score-${idx + 1}`" :value="1">
                                <span>1 - {{ criterion.scales[1] }}</span>
                            </label>
                        </div>

                        <InputError class="mt-2" :message="currentAssessmentForm.errors[`score_${idx + 1}`]" />

                        <textarea
                            v-model="currentAssessmentForm[`comment_${idx + 1}`]"
                            rows="2"
                            class="mt-3 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Komentar penilai"
                        />
                    </div>
                    </template>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Skor</label>
                            <input
                                :value="assessmentTotalScore"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rata-rata Skor</label>
                            <input
                                :value="assessmentAverageScore"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            >
                        </div>
                    </div>

                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                        <label class="flex items-start gap-2">
                            <input v-model="currentAssessmentForm.confirm_irreversible" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500 dark:border-amber-700 dark:bg-slate-900 dark:text-amber-400 dark:focus:ring-amber-400">
                            <span>Saya memahami bahwa asesmen ini tidak bisa diubah dan hanya bisa dilakukan sekali.</span>
                        </label>
                        <InputError class="mt-2" :message="currentAssessmentForm.errors.confirm_irreversible" />
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
                            :disabled="currentAssessmentForm.processing || !isAssessmentReadyToSubmit"
                            :class="currentAssessmentForm.processing || !isAssessmentReadyToSubmit ? 'opacity-50 cursor-not-allowed' : ''"
                        >
                            {{ currentAssessmentForm.processing ? 'Menyimpan...' : 'Submit Asesmen' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="assessmentResultModal && currentAssessmentResultTask"
            class="fixed inset-0 z-[65] flex items-center justify-center bg-black/60 px-4 py-6"
        >
            <div class="max-h-[95vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ currentResultTitle }}</h3>
                        <p class="mt-1 text-sm font-semibold text-gray-700">
                            {{ currentAssessmentResultTask.label || currentAssessmentResultTask.title }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                        @click="closeAssessmentResultModal"
                    >
                        Tutup
                    </button>
                </div>

                <div v-if="assessmentResultModal.results.length === 0" class="mt-6 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                    Belum ada data hasil asesmen untuk ditampilkan.
                </div>

                <div v-else class="mt-6 space-y-6">
                    <div
                        v-for="result in assessmentResultModal.results"
                        :key="result.id"
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <div class="mb-4 grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-3 text-sm text-gray-700 md:grid-cols-2">
                            <div>
                                <span class="font-semibold">Tanggal:</span>
                                {{ formatDate(result.assessment_date) }}
                            </div>
                            <div>
                                <span class="font-semibold">Penilai:</span>
                                {{ result.assessor_name || '-' }}
                            </div>
                            <div>
                                <span class="font-semibold">Kelompok Penilai:</span>
                                {{ result.assessor_group?.name || '-' }}
                            </div>
                            <div>
                                <span class="font-semibold">Kelompok Dinilai:</span>
                                {{ result.target_group?.name || '-' }}
                            </div>
                        </div>

                        <template v-for="(criterion, idx) in currentResultCriteria" :key="`result-${result.id}-${idx}`">
                            <div
                                v-if="criterion.section && (idx === 0 || currentResultCriteria[idx - 1].section !== criterion.section)"
                                class="mt-3 border-b-2 border-slate-300 pb-1"
                            >
                                <h4 class="text-sm font-bold uppercase tracking-wide text-slate-600">{{ criterion.section }}</h4>
                            </div>
                            <div
                                class="mb-3 rounded-md border border-slate-200 p-3"
                            >
                                <p class="text-sm font-semibold text-slate-900">{{ idx + 1 }}. {{ criterion.statement }}</p>
                                <p class="mt-1 text-sm text-slate-700">
                                    Skor: <span class="font-semibold">{{ result.scores?.[idx] ?? '-' }}</span>
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Komentar: {{ result.comments?.[idx] || '-' }}
                                </p>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 gap-3 rounded-md bg-emerald-50 p-3 text-sm md:grid-cols-2">
                            <div>
                                <span class="font-semibold text-emerald-900">Total Skor:</span>
                                <span class="text-emerald-800"> {{ result.total_score }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-emerald-900">Rata-rata Skor:</span>
                                <span class="text-emerald-800"> {{ Number(result.average_score || 0).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
