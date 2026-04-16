<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-godiva-charcoal/40">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
