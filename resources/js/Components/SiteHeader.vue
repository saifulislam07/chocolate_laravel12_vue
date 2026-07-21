<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, nextTick, ref, watch } from "vue";
import axios from "axios";
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
const expandedMobileId = ref(null);

function toggleMobileSubmenu(id) {
    expandedMobileId.value = expandedMobileId.value === id ? null : id;
}

// ---- Live product search ----
const fallbackImage = "/images/godiva/product_default.png";
const isSearchOpen = ref(false);
const searchInput = ref(null);
const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
let searchDebounce = null;
let searchRequestToken = 0;

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});
function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function openSearch() {
    isSearchOpen.value = true;
    nextTick(() => searchInput.value?.focus());
}

function closeSearch() {
    isSearchOpen.value = false;
    searchQuery.value = "";
    searchResults.value = [];
}

watch(searchQuery, (value) => {
    clearTimeout(searchDebounce);

    const query = value.trim();
    if (query.length < 2) {
        searchResults.value = [];
        isSearching.value = false;
        return;
    }

    isSearching.value = true;
    searchDebounce = setTimeout(async () => {
        const token = ++searchRequestToken;
        try {
            const { data } = await axios.get(route("products.search"), { params: { q: query } });
            if (token === searchRequestToken) {
                searchResults.value = data.results || [];
            }
        } finally {
            if (token === searchRequestToken) {
                isSearching.value = false;
            }
        }
    }, 350);
});

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
                    <div v-for="m in leftMenu" :key="m.id" class="group relative">
                        <component
                            :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                            :href="menuHref(m.url)"
                            class="flex items-center gap-1 whitespace-nowrap py-3 transition hover:text-cocov-gold"
                            :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                        >
                            {{ m.name }}
                            <ChevronDownIcon v-if="m.children?.length" class="h-3.5 w-3.5 stroke-[2.5] transition-transform duration-200 group-hover:rotate-180" />
                        </component>

                        <div
                            v-if="m.children?.length"
                            class="invisible absolute left-0 top-full z-50 min-w-[220px] -translate-y-1 rounded-sm border border-cocov-line bg-white py-2 opacity-0 shadow-xl transition-all duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                        >
                            <Link
                                v-for="child in m.children"
                                :key="child.id"
                                :href="menuHref(child.url)"
                                class="block whitespace-nowrap px-5 py-2.5 text-sm font-normal normal-case tracking-normal text-cocov-text transition hover:bg-cocov-card hover:text-cocov-gold"
                            >{{ child.name }}</Link>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Center logo (protrudes over banner) -->
            <Link href="/" class="relative z-10 flex justify-center self-stretch">
                <span class="absolute left-1/2 top-1/2 flex h-20 w-20 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] sm:h-24 sm:w-24 md:h-[176px] md:w-[176px]">
                    <img :src="settings.logo || '/images/cococraft-v2/logo.png'" :alt="settings.site_name || 'CocoCraft'" class="h-auto w-12 object-contain sm:w-14 md:w-[120px]" />
                </span>
            </Link>

            <!-- Right nav + icons -->
            <div class="flex items-center justify-end gap-6">
                <nav class="hidden items-center gap-6 font-heading text-[17px] font-normal uppercase leading-none lg:flex">
                    <div v-for="m in rightMenu" :key="m.id" class="group relative">
                        <component
                            :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                            :href="menuHref(m.url)"
                            class="flex items-center gap-1 whitespace-nowrap py-3 transition hover:text-cocov-gold"
                            :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                        >
                            {{ m.name }}
                            <ChevronDownIcon v-if="m.children?.length" class="h-3.5 w-3.5 stroke-[2.5] transition-transform duration-200 group-hover:rotate-180" />
                        </component>

                        <div
                            v-if="m.children?.length"
                            class="invisible absolute right-0 top-full z-50 min-w-[220px] -translate-y-1 rounded-sm border border-cocov-line bg-white py-2 opacity-0 shadow-xl transition-all duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                        >
                            <Link
                                v-for="child in m.children"
                                :key="child.id"
                                :href="menuHref(child.url)"
                                class="block whitespace-nowrap px-5 py-2.5 text-sm font-normal normal-case tracking-normal text-cocov-text transition hover:bg-cocov-card hover:text-cocov-gold"
                            >{{ child.name }}</Link>
                        </div>
                    </div>
                </nav>
                <div class="flex items-center gap-3 text-cocov-text sm:gap-4 md:gap-5">
                    <button type="button" class="transition hover:text-cocov-gold" aria-label="Search" @click="openSearch"><MagnifyingGlassIcon class="h-6 w-6" /></button>
                    <Link :href="route('wishlist.index')" class="relative hidden transition hover:text-cocov-gold sm:block" aria-label="Wishlist">
                        <HeartIcon class="h-6 w-6" />
                        <span v-if="wishlistCount > 0" class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-cocov-text text-[9px] font-bold text-white">{{ wishlistCount }}</span>
                    </Link>
                    <div class="group relative">
                        <Link :href="page.props.auth?.user ? route('customer.dashboard') : route('login')" class="flex transition hover:text-cocov-gold" aria-label="Account"><UserIcon class="h-6 w-6" /></Link>

                        <div
                            v-if="page.props.auth?.user"
                            class="invisible absolute right-0 top-full z-50 min-w-[220px] -translate-y-1 rounded-sm border border-cocov-line bg-white py-2 normal-case tracking-normal opacity-0 shadow-xl transition-all duration-200 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                        >
                            <p class="truncate px-5 pb-2 pt-1 text-sm font-bold text-cocov-text">{{ page.props.auth.user.name }}</p>
                            <Link :href="route('customer.dashboard')" class="block px-5 py-2.5 text-sm text-cocov-text transition hover:bg-cocov-card hover:text-cocov-gold">My Account</Link>
                            <Link :href="route('profile.edit')" class="block px-5 py-2.5 text-sm text-cocov-text transition hover:bg-cocov-card hover:text-cocov-gold">My Profile</Link>
                            <Link :href="route('wishlist.index')" class="block px-5 py-2.5 text-sm text-cocov-text transition hover:bg-cocov-card hover:text-cocov-gold">Wishlist</Link>
                            <Link :href="route('logout')" method="post" as="button" class="block w-full border-t border-cocov-line px-5 pb-1 pt-2.5 text-left text-sm text-red-500 transition hover:bg-cocov-card">Log Out</Link>
                        </div>
                    </div>
                    <Link :href="route('cart.index')" class="relative transition hover:text-cocov-gold" aria-label="Cart">
                        <ShoppingBagIcon class="h-6 w-6" />
                        <span class="absolute -right-2 -top-2 flex h-[22px] w-[22px] items-center justify-center rounded-full bg-cocov-gold text-[11px] font-bold text-white">{{ cartCount }}</span>
                    </Link>
                </div>
            </div>
        </div>
        <div class="h-[6px] w-full bg-cocov-line md:h-[8px]"></div>

        <!-- Live search overlay -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="isSearchOpen" class="absolute inset-x-0 top-full z-50 border-b border-cocov-line bg-white shadow-2xl">
                <div class="mx-auto max-w-[1600px] px-5 py-6 md:px-8 lg:px-[126px]">
                    <div class="flex items-center gap-4">
                        <MagnifyingGlassIcon class="h-6 w-6 shrink-0 text-cocov-gold" />
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search chocolate, SKU, flavor..."
                            class="flex-1 border-0 bg-transparent p-0 font-heading text-lg uppercase tracking-wide text-cocov-text placeholder:text-cocov-text/30 focus:outline-none focus:ring-0 md:text-xl"
                            @keyup.escape="closeSearch"
                        />
                        <button type="button" class="shrink-0 text-cocov-text transition hover:text-cocov-gold" aria-label="Close search" @click="closeSearch">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <div v-if="searchQuery.trim().length >= 2" class="mt-6 border-t border-cocov-line pt-6">
                        <p v-if="isSearching" class="text-sm text-cocov-text/50">Searching...</p>
                        <template v-else>
                            <div v-if="searchResults.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <Link
                                    v-for="product in searchResults"
                                    :key="product.id"
                                    :href="route('products.show', product.slug)"
                                    class="group flex items-center gap-4 rounded-sm p-2 transition hover:bg-cocov-card"
                                    @click="closeSearch"
                                >
                                    <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-cocov-card">
                                        <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-300 group-hover:scale-105" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-heading text-sm uppercase text-cocov-text group-hover:text-cocov-gold">{{ product.name }}</span>
                                        <span class="mt-1 flex items-baseline gap-2 text-sm">
                                            <span v-if="product.compare_at_price > product.price" class="text-cocov-muted line-through">{{ formatMoney(product.compare_at_price) }}</span>
                                            <span class="font-semibold text-cocov-gold">{{ formatMoney(product.price) }}</span>
                                        </span>
                                    </span>
                                </Link>
                            </div>
                            <p v-else class="text-sm text-cocov-text/50">No products found for &ldquo;{{ searchQuery }}&rdquo;.</p>
                        </template>

                        <div class="mt-6 text-center">
                            <Link
                                :href="route('products.index', { q: searchQuery })"
                                class="inline-flex h-[46px] items-center justify-center rounded-[3px] border border-cocov-gold px-8 text-[13px] font-bold uppercase tracking-widest text-cocov-gold transition hover:bg-cocov-gold hover:text-white"
                                @click="closeSearch"
                            >
                                View all results
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </header>

    <!-- Search backdrop -->
    <div v-if="isSearchOpen" class="fixed inset-0 z-40 bg-cocov-brown-dark/40" @click="closeSearch"></div>

    <!-- Mobile menu -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[100] bg-white md:hidden">
        <div class="flex items-center justify-between border-b border-cocov-line px-5 py-4">
            <img :src="settings.logo || '/images/cococraft-v2/logo.png'" :alt="settings.site_name || 'CocoCraft'" class="h-12 w-auto" />
            <button aria-label="Close" @click="isMobileMenuOpen = false"><XMarkIcon class="h-7 w-7 text-cocov-text" /></button>
        </div>
        <nav class="flex flex-col gap-6 px-8 py-10 font-heading text-2xl uppercase text-cocov-text">
            <div v-for="m in allMenu" :key="m.id" class="border-b border-cocov-line pb-4">
                <div class="flex items-center justify-between gap-3">
                    <component
                        :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                        :href="menuHref(m.url)"
                        class="transition hover:text-cocov-gold"
                        @click="isMobileMenuOpen = false"
                    >{{ m.name }}</component>
                    <button
                        v-if="m.children?.length"
                        type="button"
                        aria-label="Toggle submenu"
                        @click="toggleMobileSubmenu(m.id)"
                    >
                        <ChevronDownIcon
                            class="h-5 w-5 stroke-[2.5] transition-transform duration-200"
                            :class="{ 'rotate-180': expandedMobileId === m.id }"
                        />
                    </button>
                </div>

                <div v-if="m.children?.length && expandedMobileId === m.id" class="mt-4 flex flex-col gap-4 pl-4 text-base normal-case tracking-normal text-cocov-text/80">
                    <Link
                        v-for="child in m.children"
                        :key="child.id"
                        :href="menuHref(child.url)"
                        class="transition hover:text-cocov-gold"
                        @click="isMobileMenuOpen = false"
                    >{{ child.name }}</Link>
                </div>
            </div>
        </nav>

        <div class="flex flex-col gap-4 border-t border-cocov-line px-8 py-8 text-base uppercase tracking-wide text-cocov-text">
            <Link :href="route('wishlist.index')" class="flex items-center gap-2 transition hover:text-cocov-gold" @click="isMobileMenuOpen = false">
                <HeartIcon class="h-5 w-5" /> Wishlist
                <span v-if="wishlistCount > 0" class="text-cocov-gold">({{ wishlistCount }})</span>
            </Link>
            <template v-if="page.props.auth?.user">
                <p class="truncate font-heading text-lg normal-case tracking-normal text-cocov-text/60">{{ page.props.auth.user.name }}</p>
                <Link :href="route('customer.dashboard')" class="transition hover:text-cocov-gold" @click="isMobileMenuOpen = false">My Account</Link>
                <Link :href="route('profile.edit')" class="transition hover:text-cocov-gold" @click="isMobileMenuOpen = false">My Profile</Link>
                <Link :href="route('logout')" method="post" as="button" class="text-left text-red-500" @click="isMobileMenuOpen = false">Log Out</Link>
            </template>
            <template v-else>
                <Link :href="route('login')" class="transition hover:text-cocov-gold" @click="isMobileMenuOpen = false">Login</Link>
                <Link :href="route('register')" class="transition hover:text-cocov-gold" @click="isMobileMenuOpen = false">Register</Link>
            </template>
        </div>
    </div>
</template>
