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
        class="border-0 border-b border-cocov-text/10 bg-transparent py-3 px-0 focus:ring-0 focus:border-cocov-gold placeholder:text-cocov-text/20 text-cocov-text text-sm transition-all duration-300"
        v-model="model"
        ref="input"
    />
</template>
