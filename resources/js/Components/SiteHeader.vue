<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    MagnifyingGlassIcon,
    HeartIcon,
    UserIcon,
    ShoppingBagIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon,
} from "@heroicons/vue/24/outline";
import { menuHref } from "@/composables/useMenuHref";

const page = usePage();
const settings = computed(() => page.props.webSettings || {});
const cartCount = computed(() => page.props.cartCount || 0);
const wishlistCount = computed(() => page.props.wishlistCount || 0);
const isMobileMenuOpen = ref(false);

// ---- Navigation (live menu with Figma fallback) ----
const staticLeft = [
    { id: "l1", name: "Home", url: "/" },
    { id: "l2", name: "About CocoCraft", url: "about-us", children: [] },
    { id: "l3", name: "Chocolates", url: "/shop", children: [] },
];
const staticRight = [
    { id: "r1", name: "Offers", url: "/shop" },
    { id: "r2", name: "Gifting", url: "/shop" },
    { id: "r3", name: "Contact", url: "contact-us" },
];
const mainMenu = computed(() => page.props.mainMenu || []);
const leftMenu = computed(() =>
    mainMenu.value.length ? mainMenu.value.slice(0, Math.ceil(mainMenu.value.length / 2)) : staticLeft
);
const rightMenu = computed(() =>
    mainMenu.value.length ? mainMenu.value.slice(Math.ceil(mainMenu.value.length / 2)) : staticRight
);
const allMenu = computed(() => [...leftMenu.value, ...rightMenu.value]);
const isActive = (url) => {
    const href = menuHref(url);
    return href === "/" ? page.url === "/" : page.url.startsWith(href);
};
</script>

<template>
    <!-- ================= HEADER ================= -->
    <header class="sticky top-0 z-50 bg-white">
        <div class="relative mx-auto grid h-[70px] max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center px-5 md:h-[110px] md:px-8 lg:px-[126px]">
            <!-- Left nav -->
            <div class="flex items-center gap-8">
                <button class="text-cocov-text md:hidden" aria-label="Menu" @click="isMobileMenuOpen = true">
                    <Bars3Icon class="h-7 w-7" />
                </button>
                <nav class="hidden items-center gap-6 font-heading text-[17px] font-normal uppercase leading-none md:flex">
                    <component
                        v-for="m in leftMenu"
                        :key="m.id"
                        :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                        :href="menuHref(m.url)"
                        class="flex items-center gap-1 whitespace-nowrap transition hover:text-cocov-gold"
                        :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                    >
                        {{ m.name }}
                        <ChevronDownIcon v-if="m.children?.length" class="h-3.5 w-3.5 stroke-[2.5]" />
                    </component>
                </nav>
            </div>

            <!-- Center logo (protrudes over banner) -->
            <Link href="/" class="relative z-10 flex justify-center self-stretch">
                <span class="absolute left-1/2 top-1/2 flex h-[120px] w-[120px] -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] md:h-[176px] md:w-[176px]">
                    <img :src="settings.logo || '/images/cococraft-v2/logo.png'" :alt="settings.site_name || 'CocoCraft'" class="h-auto w-[74px] object-contain md:w-[120px]" />
                </span>
            </Link>

            <!-- Right nav + icons -->
            <div class="flex items-center justify-end gap-6">
                <nav class="hidden items-center gap-6 font-heading text-[17px] font-normal uppercase leading-none lg:flex">
                    <component
                        v-for="m in rightMenu"
                        :key="m.id"
                        :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                        :href="menuHref(m.url)"
                        class="flex items-center gap-1 whitespace-nowrap transition hover:text-cocov-gold"
                        :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                    >
                        {{ m.name }}
                        <ChevronDownIcon v-if="m.children?.length" class="h-3.5 w-3.5 stroke-[2.5]" />
                    </component>
                </nav>
                <div class="flex items-center gap-4 text-cocov-text md:gap-5">
                    <Link :href="route('products.index')" class="transition hover:text-cocov-gold" aria-label="Search"><MagnifyingGlassIcon class="h-6 w-6" /></Link>
                    <Link :href="route('wishlist.index')" class="relative transition hover:text-cocov-gold" aria-label="Wishlist">
                        <HeartIcon class="h-6 w-6" />
                        <span v-if="wishlistCount > 0" class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-cocov-text text-[9px] font-bold text-white">{{ wishlistCount }}</span>
                    </Link>
                    <Link :href="page.props.auth?.user ? route('customer.dashboard') : route('login')" class="transition hover:text-cocov-gold" aria-label="Account"><UserIcon class="h-6 w-6" /></Link>
                    <Link :href="route('cart.index')" class="relative transition hover:text-cocov-gold" aria-label="Cart">
                        <ShoppingBagIcon class="h-6 w-6" />
                        <span class="absolute -right-2 -top-2 flex h-[22px] w-[22px] items-center justify-center rounded-full bg-cocov-gold text-[11px] font-bold text-white">{{ cartCount }}</span>
                    </Link>
                </div>
            </div>
        </div>
        <div class="h-[6px] w-full bg-cocov-line md:h-[8px]"></div>
    </header>

    <!-- Mobile menu -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[100] bg-white md:hidden">
        <div class="flex items-center justify-between border-b border-cocov-line px-5 py-4">
            <img :src="settings.logo || '/images/cococraft-v2/logo.png'" :alt="settings.site_name || 'CocoCraft'" class="h-12 w-auto" />
            <button aria-label="Close" @click="isMobileMenuOpen = false"><XMarkIcon class="h-7 w-7 text-cocov-text" /></button>
        </div>
        <nav class="flex flex-col gap-6 px-8 py-10 font-heading text-2xl uppercase text-cocov-text">
            <component
                v-for="m in allMenu"
                :key="m.id"
                :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                :href="menuHref(m.url)"
                class="border-b border-cocov-line pb-4 transition hover:text-cocov-gold"
                @click="isMobileMenuOpen = false"
            >{{ m.name }}</component>
        </nav>
    </div>
</template>
