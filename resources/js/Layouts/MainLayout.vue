<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { computed, nextTick, ref, watch } from "vue";
import {
    ShoppingBagIcon,
    UserIcon,
    MagnifyingGlassIcon,
    HeartIcon,
    EnvelopeIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon,
    CheckCircleIcon
} from "@heroicons/vue/24/outline";
import ThemeToggle from "@/Components/ThemeToggle.vue";
import BackToTop from "@/Components/BackToTop.vue";

const page = usePage();
const isMobileMenuOpen = ref(false);
const isMessengerOpen = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const isSearchOpen = ref(false);
const searchQuery = ref("");
const searchInput = ref(null);

const toggleSearch = async () => {
    isSearchOpen.value = !isSearchOpen.value;
    if (isSearchOpen.value) {
        await nextTick();
        searchInput.value?.focus();
    }
};

const submitSearch = () => {
    const q = searchQuery.value.trim();
    if (!q) {
        searchInput.value?.focus();
        return;
    }
    isSearchOpen.value = false;
    router.get(route('products.index'), { q });
};

const cartCount = computed(() => page.props.cartCount || 0);
const wishlistCount = computed(() => page.props.wishlistCount || 0);
const flash = computed(() => page.props.flash || {});
const messengerSettings = computed(() => page.props.webSettings || {});
const shouldShowMessenger = computed(() => Boolean(
    messengerSettings.value?.messenger_enabled && messengerSettings.value?.messenger_page_id
));
const messengerLink = computed(() => `https://m.me/${messengerSettings.value.messenger_page_id}`);

const socialPlatforms = [
    { key: 'facebook_url', label: 'Facebook', badge: 'f' },
    { key: 'instagram_url', label: 'Instagram', badge: 'Ig' },
    { key: 'youtube_url', label: 'YouTube', badge: 'Yt' },
    { key: 'whatsapp_url', label: 'WhatsApp', badge: 'Wa' },
    { key: 'tiktok_url', label: 'TikTok', badge: 'Tt' },
    { key: 'linkedin_url', label: 'LinkedIn', badge: 'In' },
    { key: 'pinterest_url', label: 'Pinterest', badge: 'Pi' },
];

const socialLinks = computed(() => socialPlatforms
    .map((platform) => ({ ...platform, url: page.props.webSettings?.[platform.key] }))
    .filter((platform) => Boolean(platform.url)));

const menuHref = (url) => {
    if (!url) {
        return '#';
    }

    if (url.startsWith('http') || url.startsWith('/')) {
        return url;
    }

    return `/p/${url}`;
};

const menuComponent = (url) => url?.startsWith('http') ? 'a' : (url ? Link : 'span');

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

watch(shouldShowMessenger, (visible) => {
    if (!visible) {
        isMessengerOpen.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-godiva-charcoal antialiased dark:bg-godiva-charcoal dark:text-godiva-cream">
        <Head :title="($page.props.webSettings?.site_name || 'Coco Craft') + ' | Premium Belgian Chocolate'">
            <link v-if="$page.props.webSettings?.favicon" rel="icon" :href="$page.props.webSettings.favicon">
        </Head>

        <!-- Toast Notification -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed top-24 right-6 z-[100] w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black ring-opacity-5 dark:bg-godiva-prefooter dark:ring-white/10">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CheckCircleIcon class="h-6 w-6 text-green-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-godiva-cream">{{ toastMessage }}</p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="showToast = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none dark:bg-transparent dark:hover:text-gray-300">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Announcement Bar -->
        <div class="bg-godiva-pink py-2 text-center text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal dark:bg-godiva-gold-dark dark:text-godiva-cream">
            Free Standard Shipping on Orders $60+ Code: FREESHIP60
        </div>

        <!-- Sticky Header -->
        <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur-sm dark:border-white/10 dark:bg-godiva-charcoal/95">
            <div class="mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center px-6 pt-3 pb-2 md:pb-0">

                <!-- Mobile Menu Button (Left) -->
                <div class="flex items-center">
                    <button @click="isMobileMenuOpen = true" class="text-godiva-charcoal md:hidden dark:text-godiva-cream">
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                </div>

                <!-- Logo & Brand (Center) -->
                <Link href="/" class="justify-self-center px-4">
                    <img :src="$page.props.webSettings?.logo || '/images/cococraft-logo.svg'" :alt="$page.props.webSettings?.site_name || 'Coco Craft'" class="h-12 w-auto object-contain md:h-16" />
                    <span class="sr-only">{{ $page.props.webSettings?.site_name || 'Coco Craft' }}</span>
                </Link>

                <!-- Icons (Right) -->
                <div class="flex justify-end items-center gap-4 md:gap-6">
                    <button @click="toggleSearch" class="text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream" :class="{ 'text-godiva-gold': isSearchOpen }" aria-label="Search">
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </button>
                    <ThemeToggle />
                    <Link
                        :href="$page.props.auth?.user ? route('customer.dashboard') : route('login')"
                        class="text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream"
                        :aria-label="$page.props.auth?.user ? 'My Account' : 'Login'"
                    >
                        <UserIcon class="h-5 w-5" />
                    </Link>
                    <Link :href="route('wishlist.index')" class="relative text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream">
                        <HeartIcon class="h-5 w-5" />
                        <span v-if="wishlistCount > 0" class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-godiva-charcoal text-[9px] font-bold text-white dark:bg-godiva-gold dark:text-godiva-charcoal">{{ wishlistCount }}</span>
                    </Link>
                    <Link :href="route('cart.index')" class="relative text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream">
                        <ShoppingBagIcon class="h-5 w-5" />
                        <span v-if="cartCount > 0" class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-godiva-charcoal text-[9px] font-bold text-white dark:bg-godiva-gold dark:text-godiva-charcoal">{{ cartCount }}</span>
                    </Link>
                </div>
            </div>

            <!-- Desktop Navigation (Centered below logo) -->
            <nav class="mx-auto hidden max-w-7xl items-center justify-center gap-8 px-6 text-[13px] font-medium uppercase tracking-[0.18em] md:flex">
                <div v-for="menu in $page.props.mainMenu" :key="menu.id" class="group relative">
                    <component
                        :is="menuComponent(menu.url)"
                        :href="menuHref(menu.url)"
                        class="flex items-center gap-1.5 transition hover:text-godiva-gold py-3 cursor-pointer whitespace-nowrap"
                    >
                        {{ menu.name }}
                        <ChevronDownIcon v-if="menu.children.length > 0" class="h-3 w-3 stroke-[2.5]" />
                    </component>

                    <!-- Dropdown -->
                    <div v-if="menu.children.length > 0" class="invisible group-hover:visible absolute top-full left-1/2 -translate-x-1/2 bg-white shadow-xl border border-gray-100 min-w-[240px] py-4 transition-all opacity-0 group-hover:opacity-100 dark:bg-godiva-prefooter dark:border-white/10">
                        <component
                            v-for="child in menu.children"
                            :key="child.id"
                            :is="menuComponent(child.url)"
                            :href="menuHref(child.url)"
                            class="block px-6 py-2.5 hover:bg-gray-50 hover:text-godiva-gold font-medium text-[11px] uppercase tracking-[0.14em] dark:hover:bg-white/5"
                        >
                            {{ child.name }}
                        </component>
                    </div>
                </div>
            </nav>

            <!-- Search Bar -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="-translate-y-2 opacity-0"
            >
                <div v-if="isSearchOpen" class="border-t border-gray-100 bg-white dark:border-white/10 dark:bg-godiva-charcoal">
                    <form @submit.prevent="submitSearch" class="mx-auto flex max-w-3xl items-center gap-3 px-6 py-4">
                        <MagnifyingGlassIcon class="h-5 w-5 shrink-0 text-gray-400" />
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search chocolates, gifts, collections..."
                            class="w-full border-0 bg-transparent p-0 text-sm text-godiva-charcoal placeholder:text-gray-400 focus:border-0 focus:outline-none focus:ring-0 dark:text-godiva-cream"
                            @keydown.esc="isSearchOpen = false"
                        />
                        <button type="submit" class="shrink-0 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream">
                            Search
                        </button>
                        <button type="button" @click="isSearchOpen = false" class="shrink-0 text-gray-400 transition hover:text-godiva-charcoal dark:hover:text-godiva-cream" aria-label="Close search">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </form>
                </div>
            </Transition>

            <!-- Mobile Navigation Overlay -->
            <div
                v-if="isMobileMenuOpen"
                class="fixed inset-0 z-[100] bg-white transition-all duration-300 md:hidden dark:bg-godiva-charcoal"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <Link href="/" @click="isMobileMenuOpen = false" class="flex items-center gap-2">
                        <img :src="$page.props.webSettings?.logo || '/images/cococraft-logo.svg'" :alt="$page.props.webSettings?.site_name || 'Coco Craft'" class="h-10 w-auto object-contain" />
                        <span class="sr-only">{{ $page.props.webSettings?.site_name || 'Coco Craft' }}</span>
                    </Link>
                    <button @click="isMobileMenuOpen = false" class="text-godiva-charcoal dark:text-godiva-cream">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <nav class="flex flex-col gap-6 px-8 py-10 text-sm font-bold uppercase tracking-[0.18em] dark:text-godiva-cream">
                    <div v-for="menu in $page.props.mainMenu" :key="menu.id" class="border-b border-gray-100 pb-5 dark:border-white/10">
                        <component
                            :is="menuComponent(menu.url)"
                            :href="menuHref(menu.url)"
                            class="block"
                            @click="isMobileMenuOpen = false"
                        >
                            {{ menu.name }}
                        </component>
                        <div v-if="menu.children.length > 0" class="mt-4 flex flex-col gap-3 pl-4 text-[11px] font-medium tracking-[0.14em] text-gray-500">
                            <component
                                v-for="child in menu.children"
                                :key="child.id"
                                :is="menuComponent(child.url)"
                                :href="menuHref(child.url)"
                                @click="isMobileMenuOpen = false"
                            >
                                {{ child.name }}
                            </component>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <main :class="{ 'blur-sm': isMobileMenuOpen }">
            <slot />
        </main>

        <BackToTop />

        <div v-if="shouldShowMessenger" class="fixed bottom-6 right-6 z-[90] flex flex-col items-end gap-4">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="translate-y-3 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-3 opacity-0"
            >
                <div v-if="isMessengerOpen" class="w-[330px] overflow-hidden rounded-2xl bg-white text-godiva-charcoal shadow-2xl ring-1 ring-black/5 sm:w-[360px]">
                    <div class="flex items-center gap-4 border-b border-gray-100 px-5 py-4">
                        <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-full bg-godiva-cream p-1.5">
                            <img :src="$page.props.webSettings?.logo || '/images/cococraft-logo.svg'" :alt="$page.props.webSettings?.site_name || 'Coco Craft'" class="h-full w-full rounded-full object-contain" />
                            <span class="absolute bottom-1 right-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-400"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold leading-5">{{ $page.props.webSettings?.site_name || 'Coco Craft' }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">Online</p>
                        </div>
                        <button type="button" class="text-gray-400 transition hover:text-godiva-charcoal" aria-label="Close Messenger chat" @click="isMessengerOpen = false">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="min-h-[145px] px-5 py-4">
                        <p class="mb-4 text-center text-[11px] text-gray-300">{{ new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</p>
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-godiva-cream p-1">
                                <img :src="$page.props.webSettings?.logo || '/images/cococraft-logo.svg'" :alt="$page.props.webSettings?.site_name || 'Coco Craft'" class="h-full w-full rounded-full object-contain" />
                            </div>
                            <div class="rounded-full bg-gray-100 px-4 py-2 text-sm text-gray-700">
                                {{ messengerSettings.messenger_logged_in_greeting || 'Hi! How can we help you?' }}
                            </div>
                        </div>
                    </div>

                    <div class="px-5 pb-5 text-center">
                        <a
                            :href="messengerLink"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                            :style="{ backgroundColor: messengerSettings.messenger_theme_color || '#E89A50' }"
                        >
                            <svg viewBox="0 0 36 36" class="h-5 w-5 fill-current" aria-hidden="true">
                                <path d="M18 3.2C9.5 3.2 3 9.4 3 17.7c0 4.3 1.8 8 4.7 10.5v4.6l4.4-2.4c1.8.6 3.8.9 5.9.9 8.5 0 15-6.2 15-14.5S26.5 3.2 18 3.2Zm1.6 19.4-3.8-4-7.4 4 8.1-8.6 3.9 4 7.3-4-8.1 8.6Z" />
                            </svg>
                            Chat on Facebook
                        </a>
                    </div>
                </div>
            </Transition>

            <button
                type="button"
                class="relative flex h-16 w-16 items-center justify-center rounded-full text-white shadow-2xl transition hover:-translate-y-0.5"
                :style="{ backgroundColor: messengerSettings.messenger_theme_color || '#E89A50' }"
                aria-label="Open Messenger chat"
                @click="isMessengerOpen = !isMessengerOpen"
            >
                <span class="absolute right-0 top-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-red-500"></span>
                <svg viewBox="0 0 36 36" class="h-8 w-8 fill-current" aria-hidden="true">
                    <path d="M18 3.2C9.5 3.2 3 9.4 3 17.7c0 4.3 1.8 8 4.7 10.5v4.6l4.4-2.4c1.8.6 3.8.9 5.9.9 8.5 0 15-6.2 15-14.5S26.5 3.2 18 3.2Zm1.6 19.4-3.8-4-7.4 4 8.1-8.6 3.9 4 7.3-4-8.1 8.6Z" />
                </svg>
            </button>
        </div>

        <!-- Footer -->
        <footer class="bg-godiva-charcoal text-white">
            <div class="mx-auto max-w-7xl px-6 py-16 md:py-20">
                <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-[1.3fr_1.4fr_1.4fr_1fr] lg:gap-16">
                    <div>
                        <Link href="/" class="mb-8 inline-flex items-center">
                            <img
                                :src="$page.props.webSettings?.footer_logo || $page.props.webSettings?.logo || '/images/cococraft-logo-light.svg'"
                                :alt="$page.props.webSettings?.site_name || 'Coco Craft'"
                                class="h-16 w-auto object-contain"
                            />
                            <span class="sr-only">{{ $page.props.webSettings?.site_name || 'Coco Craft' }}</span>
                        </Link>

                        <h4 class="text-[12px] font-bold uppercase tracking-[0.45em] text-godiva-gold">Sign Up And Save</h4>
                        <p class="mt-6 max-w-xs text-sm leading-7 text-godiva-cream/85">
                            Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.
                        </p>

                        <form class="mt-8 max-w-sm">
                            <label class="sr-only" for="footer-email">Email address</label>
                            <div class="flex items-center border-b border-godiva-cream/70 pb-3">
                                <input
                                    id="footer-email"
                                    type="email"
                                    placeholder="Enter your email"
                                    class="w-full border-0 bg-transparent p-0 text-sm text-white placeholder:text-godiva-cream/80 focus:border-0 focus:ring-0"
                                />
                                <button type="submit" class="ml-4 text-godiva-cream transition hover:text-godiva-gold" aria-label="Subscribe">
                                    <EnvelopeIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </form>

                        <div class="mt-8 flex items-center gap-5">
                            <a
                                v-for="social in socialLinks"
                                :key="social.key"
                                :href="social.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-9 w-9 items-center justify-center border border-white/15 text-sm font-bold uppercase transition hover:border-godiva-gold hover:text-godiva-gold"
                                :aria-label="social.label"
                            >{{ social.badge }}</a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[12px] font-bold uppercase tracking-[0.45em] text-godiva-gold">Company Information</h4>
                        <div class="mt-7 flex flex-col gap-4 text-sm font-medium text-godiva-cream/90">
                            <Link :href="route('page.public', 'about-us')" class="transition hover:text-godiva-gold">About Us</Link>
                            <Link :href="route('page.public', 'employment')" class="transition hover:text-godiva-gold">Employment</Link>
                            <Link :href="route('page.public', 'retail-store-locations')" class="transition hover:text-godiva-gold">Retail Store Locations</Link>
                            <Link :href="route('page.public', 'factory-tours')" class="transition hover:text-godiva-gold">Factory Tours</Link>
                            <Link :href="route('page.public', 'terms-of-service')" class="transition hover:text-godiva-gold">Terms of Service</Link>
                            <Link :href="route('page.public', 'contact-us')" class="transition hover:text-godiva-gold">Contact Us</Link>
                            <Link :href="route('page.public', 'wholesale')" class="transition hover:text-godiva-gold">Wholesale</Link>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[12px] font-bold uppercase tracking-[0.45em] text-godiva-gold">Help & Information</h4>
                        <div class="mt-7 flex flex-col gap-4 text-sm font-medium text-godiva-cream/90">
                            <Link :href="route('page.public', 'faq')" class="transition hover:text-godiva-gold">FAQ</Link>
                            <Link :href="route('page.public', 'privacy-policy')" class="transition hover:text-godiva-gold">Privacy Policy</Link>
                            <Link :href="route('page.public', 'shipping-policy')" class="transition hover:text-godiva-gold">Shipping Policy</Link>
                            <Link :href="route('page.public', 'refund-policy')" class="transition hover:text-godiva-gold">Refund Policy</Link>
                            <Link :href="route('page.public', 'terms-of-service')" class="transition hover:text-godiva-gold">Terms of Service</Link>
                            <Link :href="route('page.public', 'factory-expansion')" class="transition hover:text-godiva-gold">Factory Expansion</Link>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[12px] font-bold uppercase tracking-[0.45em] text-godiva-gold">Shop</h4>
                        <div class="mt-7 flex flex-col gap-4 text-sm font-medium text-godiva-cream/90">
                            <Link :href="route('products.index')" class="transition hover:text-godiva-gold">Seasonal</Link>
                            <Link :href="route('products.index')" class="transition hover:text-godiva-gold">Shop Chocolates</Link>
                            <a href="/shop?q=gift" class="transition hover:text-godiva-gold">Gift Ideas</a>
                            <a href="/shop?sort=featured" class="transition hover:text-godiva-gold">Best Sellers</a>
                        </div>
                    </div>
                </div>

                <div class="mt-16 flex flex-col gap-4 border-t border-white/10 pt-8 text-[10px] uppercase tracking-[0.25em] text-godiva-cream/55 md:flex-row md:items-center md:justify-between">
                    <div>© 2026 {{ $page.props.webSettings?.site_name || 'Coco Craft' }}. All Rights Reserved.</div>
                    <div class="text-godiva-gold/80">Premium Chocolate Gifts & Collections</div>
                </div>
            </div>
        </footer>
    </div>
</template>
