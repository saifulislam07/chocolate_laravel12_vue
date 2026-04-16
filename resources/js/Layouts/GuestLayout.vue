<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-godiva-cream overflow-hidden">
        <!-- Left Side: Immersive Image -->
        <div class="hidden lg:block w-1/2 relative h-screen">
            <div 
                class="absolute inset-0 bg-cover bg-center animate-subtle-zoom"
                style="background-image: url('/images/godiva/auth_bg.png')"
            ></div>
            <!-- Overlay to ensure text readability if needed, though here we want the image clear -->
            <div class="absolute inset-0 bg-gradient-to-t from-godiva-charcoal/60 via-transparent to-transparent"></div>
            
            <div class="absolute bottom-20 left-20 right-20 text-white">
                <h2 class="font-serif text-5xl mb-4 leading-tight">Mastering the Art <br/> of Indulgence</h2>
                <p class="font-sans text-xs tracking-[0.3em] uppercase opacity-70">Handcrafted Belgian Excellence</p>
            </div>
        </div>

        <!-- Right Side: Auth Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-white lg:bg-transparent">
            <div class="flex-1 flex flex-col justify-center px-6 sm:px-16 lg:px-24 py-12 overflow-y-auto relative">
                <div class="absolute top-12 left-6 sm:left-16 lg:left-24">
                    <Link 
                        href="/" 
                        class="inline-flex items-center gap-2 text-[10px] uppercase tracking-[0.3em] font-bold text-godiva-charcoal/30 hover:text-godiva-gold transition-all duration-300 group"
                    >
                        <span class="transform group-hover:-translate-x-1 transition-transform">←</span>
                        Back to Home
                    </Link>
                </div>

                <div class="mb-16">
                    <Link href="/" class="inline-block mt-8">
                        <span class="font-serif text-3xl font-bold tracking-tight text-godiva-charcoal">SweetChocholate</span>
                    </Link>
                </div>

                <div class="w-full max-w-sm">
                    <slot />
                </div>
            </div>

            <!-- Footer for Auth Pages -->
            <div class="px-6 lg:px-24 py-8 border-t border-godiva-charcoal/5 lg:border-none">
                <p class="text-[10px] uppercase tracking-[0.3em] text-godiva-charcoal/40 font-bold">
                    © 2026 SweetChocholate
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
