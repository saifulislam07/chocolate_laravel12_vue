<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { 
    ShoppingBagIcon, 
    UserIcon, 
    MagnifyingGlassIcon, 
    Bars3Icon, 
    XMarkIcon,
    ChevronDownIcon,
    CheckCircleIcon
} from "@heroicons/vue/24/outline";

const page = usePage();
const isMobileMenuOpen = ref(false);
const showToast = ref(false);
const toastMessage = ref("");

const cartCount = computed(() => page.props.cartCount || 0);
const flash = computed(() => page.props.flash || {});

// Watch for flash messages to show toast
watch(() => flash.value.success, (message) => {
    if (message) {
        toastMessage.value = message;
        showToast.value = true;
        setTimeout(() => {
            showToast.value = false;
        }, 3000);
    }
}, { immediate: true });

</script>

<template>
    <div class="min-h-screen bg-white font-sans text-godiva-charcoal antialiased">
        <!-- Toast Notification -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed top-24 right-6 z-[100] w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black ring-opacity-5">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CheckCircleIcon class="h-6 w-6 text-green-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">{{ toastMessage }}</p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="showToast = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Announcement Bar -->
        <div class="bg-godiva-pink py-2 text-center text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal">
            Free Standard Shipping on Orders $60+ Code: FREESHIP60
        </div>

        <!-- Sticky Header -->
        <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                
                <!-- Logo & Brand (Left) -->
                <div class="flex flex-1 items-center gap-3">
                    <button @click="isMobileMenuOpen = true" class="text-godiva-charcoal md:hidden mr-2">
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                    <Link href="/" class="flex items-center gap-2">
                        <img src="/images/godiva/logo.png" alt="SweetChocholate" class="h-8 w-8 object-contain md:h-10 md:w-10" />
                        <span class="hidden font-serif text-xl font-bold tracking-tight md:block">SweetChocholate</span>
                    </Link>
                </div>

                <!-- Desktop Navigation (Center) -->
                <nav class="hidden md:flex flex-[2] justify-center items-center gap-8 text-[11px] font-bold uppercase tracking-[0.15em]">
                    <a href="#" class="flex items-center gap-1 transition hover:text-godiva-gold">
                        Mother's Day <ChevronDownIcon class="h-3 w-3" />
                    </a>
                    <a href="#" class="transition hover:text-godiva-gold">Best Sellers</a>
                    <a href="#" class="flex items-center gap-1 transition hover:text-godiva-gold">
                        Chocolate <ChevronDownIcon class="h-3 w-3" />
                    </a>
                    <a href="#" class="flex items-center gap-1 transition hover:text-godiva-gold">
                        Gifts <ChevronDownIcon class="h-3 w-3" />
                    </a>
                    <a href="#" class="flex items-center gap-1 transition hover:text-godiva-gold">
                        About us <ChevronDownIcon class="h-3 w-3" />
                    </a>
                </nav>

                <!-- Icons (Right) -->
                <div class="flex flex-1 justify-end items-center gap-4 md:gap-6">
                    <button class="text-godiva-charcoal transition hover:text-godiva-gold">
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </button>
                    <Link :href="route('profile.edit')" class="text-godiva-charcoal transition hover:text-godiva-gold">
                        <UserIcon class="h-5 w-5" />
                    </Link>
                    <Link :href="route('cart.index')" class="relative text-godiva-charcoal transition hover:text-godiva-gold">
                        <ShoppingBagIcon class="h-5 w-5" />
                        <span class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-godiva-charcoal text-[9px] font-bold text-white">{{ cartCount }}</span>
                    </Link>
                </div>
            </div>

            <!-- Mobile Navigation Overlay -->
            <div
                v-if="isMobileMenuOpen"
                class="fixed inset-0 z-[100] bg-white transition-all duration-300 md:hidden"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <Link href="/" @click="isMobileMenuOpen = false" class="flex items-center gap-2">
                        <img src="/images/godiva/logo.png" alt="SweetChocholate" class="h-8 w-8" />
                        <span class="font-serif text-xl font-bold">SweetChocholate</span>
                    </Link>
                    <button @click="isMobileMenuOpen = false" class="text-godiva-charcoal">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <nav class="flex flex-col items-center gap-8 py-12 text-sm font-bold uppercase tracking-[0.2em]">
                    <a href="#" @click="isMobileMenuOpen = false">Easter</a>
                    <a href="#" @click="isMobileMenuOpen = false">Best Sellers</a>
                    <a href="#" @click="isMobileMenuOpen = false">Chocolate</a>
                    <a href="#" @click="isMobileMenuOpen = false">Gifts</a>
                    <a href="#" @click="isMobileMenuOpen = false">About us</a>
                </nav>
            </div>
        </header>

        <main :class="{ 'blur-sm': isMobileMenuOpen }">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-godiva-charcoal text-white">
            <div class="bg-godiva-prefooter py-10 border-b border-white/5">
                <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row justify-between items-center gap-6">
                    <h3 class="font-serif text-3xl italic tracking-wide">Give the gift of SweetChocholate</h3>
                    <button class="bg-white text-godiva-charcoal px-10 py-3 text-[11px] font-bold uppercase tracking-widest transition hover:bg-gray-100">
                        Shop Now
                    </button>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-20">
                <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="text-center sm:text-left">
                        <Link href="/" class="flex flex-col items-center sm:items-start gap-4">
                            <img src="/images/godiva/logo.png" alt="SweetChocholate" class="h-16 w-16 brightness-0 invert" />
                            <span class="font-serif text-2xl font-bold tracking-tight">SweetChocholate</span>
                        </Link>
                        <p class="mt-6 text-[11px] leading-relaxed text-gray-400 tracking-wider">
                            Established in 1926 in Brussels, Belgium. The world's finest premium Belgian chocolate.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Resources</h4>
                        <div class="mt-8 flex flex-col gap-4 text-xs tracking-wide text-gray-400">
                            <a href="#" class="hover:text-white transition">Shipping Info</a>
                            <a href="#" class="hover:text-white transition">Return Policy</a>
                            <a href="#" class="hover:text-white transition">Gift Card Balance</a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Company</h4>
                        <div class="mt-8 flex flex-col gap-4 text-xs tracking-wide text-gray-400">
                            <a href="#" class="hover:text-white transition">Careers</a>
                            <a href="#" class="hover:text-white transition">Sweet News</a>
                            <a href="#" class="hover:text-white transition">Contact Us</a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Stay Updated</h4>
                        <form class="mt-8 relative max-w-xs">
                            <input type="email" placeholder="Your Email Address" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs focus:outline-none" />
                        </form>
                    </div>
                </div>

                <div class="mt-20 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-white/5 pt-10 text-[9px] tracking-[0.2em] text-gray-500 uppercase">
                    <div>© 2026 SweetChocholate, Inc. All Rights Reserved.</div>
                </div>
            </div>
        </footer>
    </div>
</template>
