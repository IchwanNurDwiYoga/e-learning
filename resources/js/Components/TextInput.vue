<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-slate-300 bg-white/90 text-slate-900 shadow-sm focus:border-[#0f766e] focus:ring-[#0f766e]"
        v-model="model"
        ref="input"
    />
</template>
