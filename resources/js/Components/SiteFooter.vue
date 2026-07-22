<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { menuHref } from "@/composables/useMenuHref";

const page = usePage();
const settings = computed(() => page.props.webSettings || {});
</script>

<template>
    <!-- ================= FOOTER ================= -->
    <footer class="bg-cocov-brown text-white">
        <div class="mx-auto flex max-w-[1350px] flex-col items-center gap-6 px-5 py-10 text-center md:flex-row md:justify-between md:px-8 md:text-left lg:px-[126px]">
            <img :src="settings.footer_logo || '/images/cococraft-logo-light.svg'" alt="" class="h-10 w-auto" />

            <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[15px] text-white/90">
                <Link :href="menuHref('about-us')" class="transition hover:text-cocov-gold">About Us</Link>
                <Link :href="route('products.index')" class="transition hover:text-cocov-gold">Products</Link>
                <Link :href="menuHref('privacy-policy')" class="transition hover:text-cocov-gold">Privacy Policy</Link>
                <Link :href="menuHref('terms-of-service')" class="transition hover:text-cocov-gold">Terms of Service</Link>
                <a :href="`mailto:${settings.email || 'info@cococraft.com.bd'}`" class="transition hover:text-cocov-gold">Contact</a>
            </nav>

            <div class="flex items-center gap-5 text-white">
                <a :href="settings.facebook_url || '#'" aria-label="Facebook" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M13.5 21v-8h2.6l.4-3h-3V8.1c0-.9.3-1.5 1.6-1.5H17V4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8.2v3h2.6v8h2.7z"/></svg></a>
                <a :href="settings.twitter_url || '#'" aria-label="X" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-[18px] w-[18px] fill-current"><path d="M18.9 2H22l-7 8 8.2 12h-6.5l-5-6.6L5.9 22H2.8l7.5-8.6L2.4 2h6.7l4.6 6.1L18.9 2zm-1.1 18h1.7L7.3 3.8H5.5L17.8 20z"/></svg></a>
                <a :href="settings.pinterest_url || '#'" aria-label="Pinterest" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.6l-.2.9c-.1.3-.3.4-.6.2-1.2-.5-1.8-1.9-1.8-3.4 0-2.6 2.2-5.7 6.5-5.7 3.5 0 5.8 2.5 5.8 5.2 0 3.6-2 6.3-4.9 6.3-1 0-1.9-.5-2.2-1.1l-.6 2.4c-.2.8-.7 1.7-1.1 2.4A10 10 0 1 0 12 2z"/></svg></a>
            </div>
        </div>

        <div class="border-t border-white/10 py-5 text-center text-[14px] text-white/70">
            Copyright © {{ new Date().getFullYear() }} {{ settings.site_name || 'Cococraft' }} - All Rights Reserved.
        </div>
    </footer>
</template>
