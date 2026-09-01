<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";

defineProps({
    page: { type: Object, required: true },
    heroImage: { type: String, default: null },
});

// Named inertiaPage so it does not collide with the CMS `page` prop above.
const inertiaPage = usePage();
const settings = computed(() => inertiaPage.props.webSettings || {});
</script>

<template>
    <MainLayout>
        <Head :title="page.title" />

        <header class="border-b border-gray-100 bg-cocov-card px-5 md:px-8 lg:px-[126px] py-20">
            <div class="mx-auto max-w-full text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-cocov-gold">Coco Craft</p>
                <h1 class="mt-5 font-heading text-5xl uppercase leading-tight text-cocov-text md:text-6xl">
                    {{ page.title }}
                </h1>
                <div class="mx-auto mt-8 h-px w-24 bg-cocov-gold"></div>
            </div>
        </header>

        <main class="mx-auto max-w-full px-5 md:px-8 lg:px-[126px] py-16 md:py-20">
            <div
                class="gap-12 md:gap-16"
                :class="heroImage ? 'md:grid md:grid-cols-[660px_1fr] md:items-start' : ''"
            >
                <article class="page-content text-base leading-8 text-gray-700">
                    <div class="rich-text" v-html="page.content"></div>
                </article>

                <!-- Mirrors the homepage About Cococraft visual: arch framed photo
                     with the gold call-us badge overlaid. -->
                <div v-if="heroImage" class="relative mx-auto mt-12 w-full max-w-[430px] md:mt-0">
                    <div class="overflow-hidden rounded-t-[215px] shadow-[inset_2px_4px_4px_rgba(115,57,40,0.3)]">
                        <img :src="heroImage" :alt="page.title" class="h-[560px] w-full object-cover md:h-[640px]" />
                    </div>
                    <div class="absolute bottom-[16%] right-0 w-[296px] max-w-[85%] bg-cocov-gold/95 p-2">
                        <div class="border border-[#f9e00c] px-4 py-3 text-center text-white">
                            <p class="text-[16px] leading-[26px]">If need custom chocolate<br />CALL US</p>
                            <p class="mt-1 text-[22px] font-medium leading-[26px] md:text-[24px]">{{ settings.phone || '+88 01886 831 130' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-14 border-t border-gray-100 pt-8">
                <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-[0.24em] text-cocov-text transition hover:text-cocov-gold">
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
    font-family: "Oswald", sans-serif;
    text-transform: uppercase;
    font-size: 2rem;
    line-height: 1.2;
    color: #484747;
}

.page-content :deep(h3) {
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #484747;
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
    color: #F69521;
    text-decoration: underline;
    text-underline-offset: 4px;
}
</style>
