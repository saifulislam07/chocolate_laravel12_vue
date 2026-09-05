<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Settings drive the artwork and the words; an unset one leaves the page saying
// what it has always said, so the screen works before anything is filled in.
const settings = computed(() => usePage().props.webSettings || {});

const coverImage = computed(() => settings.value.login_image || '/images/godiva/auth_bg.png');
const coverTitle = computed(() => settings.value.login_cover_title || 'Mastering the Art of Indulgence');
const coverText = computed(() => settings.value.login_cover_text || 'A Bite of Love');
const siteName = computed(() => settings.value.site_name || 'Coco Craft');
</script>

<template>
    <div class="flex min-h-screen bg-cocov-card overflow-hidden">
        <!-- Left Side: Immersive Image -->
        <div class="hidden lg:block w-1/2 relative h-screen">
            <div 
                class="absolute inset-0 bg-cover bg-center animate-subtle-zoom"
                :style="{ backgroundImage: `url('${coverImage}')` }"
            ></div>
            <!-- Overlay to ensure text readability if needed, though here we want the image clear -->
            <div class="absolute inset-0 bg-gradient-to-t from-cocov-text/60 via-transparent to-transparent"></div>
            
            <div class="absolute bottom-20 left-20 right-20 text-white">
                <h2 class="font-heading text-5xl mb-4 leading-tight">{{ coverTitle }}</h2>
                <p v-if="coverText" class="text-xs tracking-[0.3em] uppercase opacity-70">{{ coverText }}</p>
            </div>
        </div>

        <!-- Right Side: Auth Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-white lg:bg-transparent">
            <div class="flex-1 flex flex-col justify-center px-6 sm:px-16 lg:px-24 py-12 overflow-y-auto relative">
                <div class="absolute top-12 left-6 sm:left-16 lg:left-24">
                    <Link 
                        href="/" 
                        class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.3em] font-bold text-cocov-text/30 hover:text-cocov-gold transition-all duration-300 group"
                    >
                        <span class="transform group-hover:-translate-x-1 transition-transform">←</span>
                        Back to Home
                    </Link>
                </div>

                <div class="mb-16">
                    <Link href="/" class="mt-8 inline-flex items-center gap-3">
                        <img :src="settings.logo || '/images/cococraft-logo.svg'" :alt="siteName" class="h-12 w-12 object-contain" />
                        <span class="font-heading text-3xl font-bold tracking-tight text-cocov-text">{{ siteName }}</span>
                    </Link>
                </div>

                <div class="w-full max-w-sm">
                    <slot />
                </div>
            </div>

            <!-- Footer for Auth Pages -->
            <div class="px-6 lg:px-24 py-8 border-t border-cocov-text/5 lg:border-none">
                <p class="text-[10px] uppercase tracking-[0.3em] text-cocov-text/40 font-bold">
                    © {{ new Date().getFullYear() }} {{ siteName }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes subtleZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.1); }
}
.animate-subtle-zoom {
    animation: subtleZoom 30s infinite alternate ease-in-out;
}
</style>
