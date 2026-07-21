<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-[3px] border border-cocov-text/20 bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-cocov-text transition duration-150 ease-in-out hover:border-cocov-gold hover:text-cocov-gold focus:outline-none focus:ring-2 focus:ring-cocov-gold focus:ring-offset-2 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
