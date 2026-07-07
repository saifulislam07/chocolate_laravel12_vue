<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { ChevronUpIcon } from "@heroicons/vue/24/outline";

const visible = ref(false);

function onScroll() {
    visible.value = window.scrollY > 400;
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="translate-y-3 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-3 opacity-0"
    >
        <button
            v-if="visible"
            type="button"
            @click="scrollToTop"
            class="fixed bottom-28 right-6 z-[80] flex h-11 w-11 items-center justify-center rounded-full bg-godiva-charcoal text-white shadow-xl transition hover:-translate-y-0.5 hover:bg-godiva-gold dark:bg-godiva-cream dark:text-godiva-charcoal"
            aria-label="Back to top"
        >
            <ChevronUpIcon class="h-5 w-5" />
        </button>
    </Transition>
</template>
