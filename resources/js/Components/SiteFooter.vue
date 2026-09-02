<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const settings = computed(() => page.props.webSettings || {});

// Published pages from Admin > Static Pages. Deactivating one drops it from here.
const footerPages = computed(() => page.props.footerPages || []);

// Social platforms configured on Admin > Settings. Only those with a link set
// are rendered; an empty field drops the icon from the footer.
const socialPlatforms = [
    { key: 'facebook_url', label: 'Facebook', color: '#1877F2', path: 'M13.5 21v-8h2.6l.4-3h-3V8.1c0-.9.3-1.5 1.6-1.5H17V4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8.2v3h2.6v8h2.7z' },
    { key: 'instagram_url', label: 'Instagram', color: '#E4405F', path: 'M12 8.9A3.1 3.1 0 1 0 12 15a3.1 3.1 0 0 0 0-6.1zm0 5.1a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm4-5.2a.72.72 0 1 1-1.44 0 .72.72 0 0 1 1.44 0zM18 9.06c-.05-1-.28-1.87-1-2.6-.72-.72-1.6-.95-2.6-1-1-.06-4.1-.06-5.1 0-1 .05-1.87.28-2.6 1-.72.72-.95 1.6-1 2.6-.06 1-.06 4.1 0 5.1.05 1 .28 1.87 1 2.6.73.72 1.6.95 2.6 1 1 .06 4.1.06 5.1 0 1-.05 1.88-.28 2.6-1 .72-.73.95-1.6 1-2.6.06-1 .06-4.1 0-5.1zm-1.3 6.17a2 2 0 0 1-1.13 1.14c-.79.31-2.65.24-3.52.24-.87 0-2.74.07-3.52-.24a2 2 0 0 1-1.14-1.14c-.31-.78-.24-2.65-.24-3.52 0-.87-.07-2.73.24-3.52A2 2 0 0 1 8.47 7.7c.79-.31 2.65-.24 3.52-.24.87 0 2.74-.07 3.52.24a2 2 0 0 1 1.14 1.14c.31.79.24 2.65.24 3.52 0 .87.07 2.73-.24 3.52z' },
    { key: 'youtube_url', label: 'YouTube', color: '#FF0000', path: 'M21.6 8.2s-.2-1.4-.8-2c-.8-.8-1.6-.8-2-.9-2.8-.2-7-.2-7-.2h-.02s-4.2 0-7 .2c-.4.05-1.2.1-2 .9-.6.6-.8 2-.8 2S1 9.8 1 11.5v1c0 1.7.2 3.3.2 3.3s.2 1.4.8 2c.8.8 1.8.8 2.3.9 1.7.16 7 .2 7 .2s4.2 0 7-.22c.4-.05 1.2-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3v-1c0-1.7-.2-3.3-.2-3.3zM9.9 14.6V9.4l5.4 2.6-5.4 2.6z' },
    { key: 'whatsapp_url', label: 'WhatsApp', color: '#25D366', path: 'M17.5 6.5A7.7 7.7 0 0 0 4.4 12a7.6 7.6 0 0 0 1 3.8L4.3 20l4.3-1.1a7.7 7.7 0 0 0 3.7.9 7.7 7.7 0 0 0 5.2-13.3zM12 18.3a6.4 6.4 0 0 1-3.3-.9l-.24-.14-2.5.66.67-2.44-.16-.25a6.4 6.4 0 1 1 5.7 3.06zm3.5-4.8c-.2-.1-1.14-.56-1.3-.62-.18-.06-.3-.1-.44.1-.13.2-.5.62-.62.75-.1.13-.23.14-.43.05a5.2 5.2 0 0 1-1.53-.95 5.7 5.7 0 0 1-1.06-1.32c-.1-.2 0-.3.1-.4l.3-.35c.1-.13.14-.2.2-.35.07-.13.03-.25 0-.35-.05-.1-.44-1.06-.6-1.45-.16-.38-.32-.33-.44-.33l-.37-.01c-.13 0-.35.05-.53.25-.18.2-.7.68-.7 1.65 0 .97.72 1.92.82 2.05.1.13 1.4 2.15 3.4 3 .48.2.85.33 1.14.42.48.15.9.13 1.25.08.38-.06 1.14-.47 1.3-.92.17-.45.17-.83.12-.92-.05-.08-.18-.13-.38-.23z' },
    { key: 'tiktok_url', label: 'TikTok', color: '#010101', path: 'M16.5 3c.3 2 1.5 3.6 3.5 3.9v2.4c-1.2.1-2.4-.2-3.5-.8v5.9a5.6 5.6 0 1 1-5.6-5.6c.3 0 .5 0 .8.06v2.5a3.1 3.1 0 1 0 2.2 3V3h2.6z' },
    { key: 'linkedin_url', label: 'LinkedIn', color: '#0A66C2', path: 'M6.9 8.2H4.2V20h2.7V8.2zM5.55 4a1.57 1.57 0 1 0 0 3.14 1.57 1.57 0 0 0 0-3.14zM20 20v-6.5c0-3.2-1.7-4.7-4-4.7-1.8 0-2.6 1-3.1 1.7V8.2H9.9V20h2.7v-6.2c0-.3 0-.65.13-.9.27-.65.86-1.32 1.86-1.32 1.3 0 1.83 1 1.83 2.47V20H20z' },
    { key: 'pinterest_url', label: 'Pinterest', color: '#E60023', path: 'M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.6l-.2.9c-.1.3-.3.4-.6.2-1.2-.5-1.8-1.9-1.8-3.4 0-2.6 2.2-5.7 6.5-5.7 3.5 0 5.8 2.5 5.8 5.2 0 3.6-2 6.3-4.9 6.3-1 0-1.9-.5-2.2-1.1l-.6 2.4c-.2.8-.7 1.7-1.1 2.4A10 10 0 1 0 12 2z' },
];

const socialLinks = computed(() =>
    socialPlatforms
        .map((p) => ({ ...p, url: (settings.value[p.key] || '').trim() }))
        .filter((p) => p.url !== '')
);
</script>

<template>
    <!-- ================= FOOTER ================= -->
    <footer class="bg-cocov-brown text-white">
        <div class="mx-auto flex max-w-full flex-col items-center gap-6 px-5 py-10 text-center md:flex-row md:justify-between md:px-8 md:text-left lg:px-[126px]">
            <img :src="settings.footer_logo || '/images/cococraft-logo-light.svg'" alt="" class="h-16 w-auto" />

            <!-- Takes the space between logo and socials so the links sit centred. -->
            <div class="flex w-full justify-center md:flex-1">
                <!-- Five links per row on desktop, fewer as the footer narrows. -->
                <nav class="grid grid-cols-2 justify-items-center gap-x-6 gap-y-2 text-center text-[15px] text-white/90 sm:grid-cols-3 md:grid-cols-5">
                    <Link :href="route('products.index')" class="transition hover:text-cocov-gold">Products</Link>
                    <Link
                        v-for="item in footerPages"
                        :key="item.id"
                        :href="route('page.show', item.slug)"
                        class="transition hover:text-cocov-gold"
                    >{{ item.title }}</Link>
                </nav>
            </div>

            <div v-if="socialLinks.length" class="flex items-center gap-3">
                <a
                    v-for="social in socialLinks"
                    :key="social.key"
                    :href="social.url"
                    :aria-label="social.label"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-white transition hover:scale-110"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" :style="{ fill: social.color }"><path :d="social.path" /></svg>
                </a>
            </div>
        </div>

        <div class="border-t border-white/10 py-5 text-center text-[14px] text-white/70">
            Copyright © {{ new Date().getFullYear() }} {{ settings.site_name || 'Cococraft' }} - All Rights Reserved.
        </div>
    </footer>
</template>
