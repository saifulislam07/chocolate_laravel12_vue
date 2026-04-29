<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

defineProps({
    page: { type: Object, required: true },
});
</script>

<template>
    <MainLayout>
        <Head :title="page.title" />

        <header class="border-b border-gray-100 bg-godiva-cream px-6 py-20">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-godiva-gold">SweetChocholate</p>
                <h1 class="mt-5 font-serif text-5xl font-light leading-tight text-godiva-charcoal md:text-6xl">
                    {{ page.title }}
                </h1>
                <div class="mx-auto mt-8 h-px w-24 bg-godiva-gold"></div>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-16 md:py-20">
            <article class="page-content text-base leading-8 text-gray-700">
                <div v-html="page.content"></div>
            </article>

            <div class="mt-14 border-t border-gray-100 pt-8">
                <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-[0.24em] text-godiva-charcoal transition hover:text-godiva-gold">
                    Back To Shop
                </Link>
            </div>
        </main>
    </MainLayout>
</template>

<style scoped>
.page-content :deep(h2) {
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    font-family: "Cormorant Garamond", serif;
    font-size: 2rem;
    line-height: 1.2;
    color: #1C1C1C;
}

.page-content :deep(h3) {
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #1C1C1C;
}

.page-content :deep(p) {
    margin-bottom: 1.25rem;
}

.page-content :deep(ul) {
    margin-bottom: 1.5rem;
    padding-left: 1.25rem;
    list-style: disc;
}

.page-content :deep(li) {
    margin-bottom: 0.5rem;
}

.page-content :deep(a) {
    color: #8C733E;
    text-decoration: underline;
    text-underline-offset: 4px;
}
</style>
