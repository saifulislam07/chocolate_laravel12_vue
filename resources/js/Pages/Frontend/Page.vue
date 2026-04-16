<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    page: Object,
});
</script>

<template>
    <Head :title="page.title" />

    <!-- Assume there is a Navbar component, but let's build a simple one or just the layout -->
    <div class="min-h-screen bg-white">
        <!-- Main Navigation (Simplified for Demo) -->
        <nav class="border-b bg-white/80 backdrop-blur-md sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <Link :href="route('home')" class="text-2xl font-bold tracking-tighter text-black">
                    Sweet<span class="text-amber-800">Chocolate</span>
                </Link>
                <div class="hidden md:flex space-x-8 text-sm font-medium uppercase tracking-widest text-gray-600">
                    <Link :href="route('home')" class="hover:text-amber-800 transition">Shop</Link>
                    <Link :href="route('home')" class="hover:text-amber-800 transition">Best Sellers</Link>
                    <Link :href="route('page.public', 'about-us')" class="hover:text-amber-800 transition">About Us</Link>
                </div>
                <div class="flex items-center space-x-4">
                    <i class="fas fa-search cursor-pointer pr-2"></i>
                    <i class="fas fa-shopping-bag cursor-pointer"></i>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header class="bg-gray-50 py-20 px-6 border-b">
            <div class="container mx-auto max-w-4xl text-center">
                <h1 class="text-5xl md:text-6xl font-serif font-bold text-gray-900 mb-6 leading-tight">
                    {{ page.title }}
                </h1>
                <div class="w-24 h-1 bg-amber-800 mx-auto rounded-full"></div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="container mx-auto max-w-4xl px-6 py-20">
            <article class="prose prose-lg prose-amber max-w-none text-gray-700 leading-relaxed font-light">
                <div v-html="page.content"></div>
            </article>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-20 px-6 border-t border-gray-800">
            <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-2">
                    <h2 class="text-3xl font-bold mb-6 italic">Sweet Chocolate</h2>
                    <p class="text-gray-400 max-w-sm mb-6 font-light">
                        Premium handcrafted chocolates made with the finest organic cacao beans from around the world.
                    </p>
                    <div class="flex space-x-4">
                        <i class="fab fa-facebook-f text-xl hover:text-amber-600 transition cursor-pointer"></i>
                        <i class="fab fa-instagram text-xl hover:text-amber-600 transition cursor-pointer"></i>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-amber-600 uppercase tracking-widest text-sm mb-6">Quick Links</h3>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li><Link :href="route('home')" class="hover:text-white transition">Shop Now</Link></li>
                        <li><Link :href="route('page.public', 'about-us')" class="hover:text-white transition">About Us</Link></li>
                        <li><Link :href="route('page.public', 'contact-us')" class="hover:text-white transition">Contact Us</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-amber-600 uppercase tracking-widest text-sm mb-6">Policies</h3>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li><Link :href="route('page.public', 'privacy-policy')" class="hover:text-white transition">Privacy Policy</Link></li>
                        <li><Link :href="route('page.public', 'shipping-returns')" class="hover:text-white transition">Shipping & Returns</Link></li>
                    </ul>
                </div>
            </div>
            <div class="container mx-auto border-t border-gray-800 mt-20 pt-10 text-center text-gray-500 text-xs">
                &copy; 2026 Sweet Chocolate. Handcrafted with love.
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}

h1, h2, h3 {
    font-family: 'Playfair Display', serif;
}

/* Base Prose styles for user-generated content */
.prose p { margin-bottom: 1.5rem; }
.prose h2 { font-size: 2rem; margin-top: 3rem; margin-bottom: 1rem; color: #111; }
.prose ul { list-style-type: disc; padding-left: 2rem; margin-bottom: 1.5rem; }
.prose li { margin-bottom: 0.5rem; }
</style>
