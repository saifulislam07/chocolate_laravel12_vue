<script setup>
import { SunIcon, MoonIcon } from "@heroicons/vue/24/outline";
import { useTheme } from "@/composables/useTheme";

const { isDark, toggle } = useTheme();
</script>

<template>
    <button
        type="button"
        @click="toggle"
        class="text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream dark:hover:text-godiva-gold"
        :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
    >
        <SunIcon v-if="isDark" class="h-5 w-5" />
        <MoonIcon v-else class="h-5 w-5" />
    </button>
</template>
