<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { computed } from "vue";

const page = usePage();
const settings = computed(() => page.props.webSettings || {});

const form = useForm({
    name: "",
    email: "",
    phone: "",
    subject: "",
    message: "",
});

function submit() {
    form.post(route("contact.store"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Contact Us" />

    <MainLayout>
        <header class="border-b border-gray-100 bg-cocov-card px-6 py-20">
            <div class="mx-auto max-w-full text-center">
                <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-cocov-gold">Coco Craft</p>
                <h1 class="mt-5 font-heading text-5xl uppercase leading-tight text-cocov-text md:text-6xl">Contact Us</h1>
                <div class="mx-auto mt-8 h-px w-24 bg-cocov-gold"></div>
                <p class="mx-auto mt-6 max-w-xl text-sm leading-7 text-cocov-text/70">
                    Have a question about an order, a bulk gifting request, or just want to say hello? Send us a message and our team will get back to you soon.
                </p>
            </div>
        </header>

        <section class="px-6 py-16 md:py-20">
            <div class="mx-auto grid max-w-4xl gap-12 md:grid-cols-[1fr_1.3fr]">
                <div class="space-y-8">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-cocov-gold">Email</p>
                        <a :href="`mailto:${settings.email || 'info@cococraft.com.bd'}`" class="mt-2 block text-sm text-cocov-text/80 transition hover:text-cocov-gold">
                            {{ settings.email || "info@cococraft.com.bd" }}
                        </a>
                    </div>
                    <div v-if="settings.phone">
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-cocov-gold">Phone</p>
                        <a :href="`tel:${settings.phone}`" class="mt-2 block text-sm text-cocov-text/80 transition hover:text-cocov-gold">{{ settings.phone }}</a>
                    </div>
                    <div v-if="settings.address">
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-cocov-gold">Address</p>
                        <p class="mt-2 text-sm leading-7 text-cocov-text/80">{{ settings.address }}</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4 rounded-[3px] border border-cocov-line bg-white p-6 md:p-8">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <input v-model="form.name" type="text" placeholder="Your Name *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <input v-model="form.email" type="email" placeholder="Your Email *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <input v-model="form.phone" type="tel" placeholder="Phone Number (optional)" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <input v-model="form.subject" type="text" placeholder="Subject (optional)" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                            <p v-if="form.errors.subject" class="mt-1 text-xs text-red-500">{{ form.errors.subject }}</p>
                        </div>
                    </div>
                    <div>
                        <textarea v-model="form.message" rows="5" placeholder="Your Message *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none"></textarea>
                        <p v-if="form.errors.message" class="mt-1 text-xs text-red-500">{{ form.errors.message }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full rounded-[3px] bg-cocov-gold py-3 text-sm font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a] disabled:opacity-60">
                        {{ form.processing ? "Sending..." : "Send Message" }}
                    </button>
                </form>
            </div>
        </section>
    </MainLayout>
</template>
