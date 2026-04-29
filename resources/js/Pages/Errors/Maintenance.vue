<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    status: { type: Number, default: 503 },
    title: { type: String, default: "We are polishing the shop" },
    message: { type: String, default: "SweetChocholate is temporarily unavailable while we make a few improvements." },
});
</script>

<template>
    <Head title="Maintenance | SweetChocholate" />

    <main class="min-h-screen bg-[#1c1c1c] text-white">
        <div class="relative min-h-screen overflow-hidden">
            <img src="/images/godiva/seasonal.png" alt="Chocolate collection" class="absolute right-[-10%] top-1/2 hidden h-[88vh] -translate-y-1/2 object-contain opacity-25 lg:block" />
            <div class="absolute inset-0 bg-gradient-to-r from-[#1c1c1c] via-[#1c1c1c]/95 to-[#1c1c1c]/50"></div>

            <div class="relative z-10 mx-auto flex min-h-screen max-w-6xl items-center px-6 py-16">
                <section class="max-w-2xl">
                    <Link href="/admin/login" class="inline-flex items-center gap-3">
                        <img src="/images/godiva/logo-cute.png" alt="SweetChocholate" class="h-14 w-14 object-contain" />
                        <span class="font-serif text-3xl font-bold">SweetChocholate</span>
                    </Link>

                    <p class="mt-16 text-[11px] font-bold uppercase tracking-[0.35em] text-godiva-gold">{{ status }} Maintenance</p>
                    <h1 class="mt-6 font-serif text-6xl italic leading-none lg:text-7xl">{{ title }}</h1>
                    <p class="mt-8 max-w-xl text-base leading-9 text-white/65">{{ message }}</p>

                    <div class="mt-12 flex flex-wrap gap-3">
                        <button type="button" class="bg-white px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-black transition hover:bg-godiva-gold" @click="location.reload()">
                            Check Again
                        </button>
                        <Link href="/admin/login" class="border border-white/15 px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-white/65 transition hover:border-godiva-gold hover:text-godiva-gold">
                            Admin Login
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </main>
</template>
