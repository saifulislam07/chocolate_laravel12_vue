<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    status: { type: Number, required: true },
});

const copy = {
    404: {
        eyebrow: "Page missing",
        title: "This chocolate melted away",
        message: "The page you are looking for is not available or has been moved.",
        action: "Back to shop",
    },
    500: {
        eyebrow: "Server error",
        title: "Something cracked backstage",
        message: "We could not complete the request. Please try again shortly.",
        action: "Go home",
    },
    503: {
        eyebrow: "Temporarily unavailable",
        title: "We are polishing the shop",
        message: "SweetChocholate is temporarily unavailable while we make a few improvements.",
        action: "Go home",
    },
};

const page = copy[props.status] || copy[500];
</script>

<template>
    <Head :title="`${status} | SweetChocholate`" />

    <main class="min-h-screen bg-[#fcf8f3] text-godiva-charcoal">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-16">
            <div class="grid w-full gap-12 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <section>
                    <Link href="/" class="inline-flex items-center gap-3">
                        <img src="/images/godiva/logo-cute.png" alt="SweetChocholate" class="h-14 w-14 object-contain" />
                        <span class="font-serif text-3xl font-bold">SweetChocholate</span>
                    </Link>

                    <p class="mt-14 text-[11px] font-bold uppercase tracking-[0.35em] text-godiva-gold">{{ page.eyebrow }}</p>
                    <h1 class="mt-5 font-serif text-7xl italic leading-none lg:text-8xl">{{ status }}</h1>
                    <h2 class="mt-6 font-serif text-4xl leading-tight">{{ page.title }}</h2>
                    <p class="mt-5 max-w-md text-sm leading-8 text-godiva-charcoal/60">{{ page.message }}</p>

                    <div class="mt-10 flex flex-wrap gap-3">
                        <Link :href="status === 404 ? route('products.index') : route('home')" class="bg-godiva-charcoal px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-white transition hover:bg-godiva-gold">
                            {{ page.action }}
                        </Link>
                        <button type="button" class="border border-godiva-charcoal/15 px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] transition hover:border-godiva-gold hover:text-godiva-gold" @click="history.back()">
                            Go Back
                        </button>
                    </div>
                </section>

                <section class="relative hidden min-h-[520px] overflow-hidden bg-white lg:block">
                    <img src="/images/godiva/hero_stack.png" alt="Chocolate gift stack" class="absolute inset-0 h-full w-full object-contain p-14" />
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#fcf8f3] to-transparent p-10">
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-godiva-charcoal/45">Premium Belgian chocolate</p>
                    </div>
                </section>
            </div>
        </div>
    </main>
</template>
