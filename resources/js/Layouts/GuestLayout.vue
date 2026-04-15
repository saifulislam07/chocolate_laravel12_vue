<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-godiva-pink py-12 sm:justify-center px-6"
    >
        <div class="mb-10 text-center">
            <Link href="/" class="flex flex-col items-center gap-4">
                <img src="/images/godiva/logo.png" alt="SweetChocholate" class="h-16 w-16" />
                <span class="font-serif text-3xl font-bold tracking-tight text-godiva-charcoal">SweetChocholate</span>
            </Link>
        </div>

        <div
            class="w-full overflow-hidden bg-white px-10 py-12 shadow-[0_20px_50px_rgba(0,0,0,0.05)] sm:max-w-md border border-gray-100"
        >
            <slot />
        </div>
        
        <p class="mt-10 text-[10px] uppercase tracking-[0.2em] text-godiva-charcoal/40 font-bold">
            © 2026 SweetChocholate Belgian Excellence
        </p>
    </div>
</template>
