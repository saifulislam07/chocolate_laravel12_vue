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
        class="border-0 border-b border-godiva-charcoal/10 bg-transparent py-3 px-0 focus:ring-0 focus:border-godiva-gold placeholder:text-godiva-charcoal/20 text-godiva-charcoal text-sm transition-all duration-300"
        v-model="model"
        ref="input"
    />
</template>
