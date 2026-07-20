<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    MagnifyingGlassIcon,
    HeartIcon,
    UserIcon,
    ShoppingBagIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    featuredItems: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
});

const page = usePage();
const settings = computed(() => page.props.webSettings || {});
const cartCount = computed(() => page.props.cartCount || 0);
const wishlistCount = computed(() => page.props.wishlistCount || 0);
const isMobileMenuOpen = ref(false);

// ---- Navigation (live menu with Figma fallback) ----
const staticLeft = [
    { id: "l1", name: "Home", url: "/" },
    { id: "l2", name: "About CocoCraft", url: "/p/about-us" },
    { id: "l3", name: "Chocolates", url: "/shop", children: true },
];
const staticRight = [
    { id: "r1", name: "Offers", url: "/shop" },
    { id: "r2", name: "Gifting", url: "/shop" },
    { id: "r3", name: "Contact", url: "/p/contact" },
];
const mainMenu = computed(() => page.props.mainMenu || []);
const leftMenu = computed(() =>
    mainMenu.value.length ? mainMenu.value.slice(0, Math.ceil(mainMenu.value.length / 2)) : staticLeft
);
const rightMenu = computed(() =>
    mainMenu.value.length ? mainMenu.value.slice(Math.ceil(mainMenu.value.length / 2)) : staticRight
);
const allMenu = computed(() => [...leftMenu.value, ...rightMenu.value]);
const menuHref = (url) => (!url ? "#" : url.startsWith("http") || url.startsWith("/") ? url : `/p/${url}`);
const isActive = (url) => {
    const href = menuHref(url);
    return href === "/" ? page.url === "/" : page.url.startsWith(href);
};

// ---- Helpers ----
const fallbackImage = "/images/godiva/product_default.png";
function formatMoney(amount) {
    return `${Number(amount || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}৳`;
}
function addToCart(id) {
    router.post(route("cart.store"), { product_id: id, quantity: 1 }, { preserveScroll: true });
}
function toggleWishlist(id) {
    router.post(route("wishlist.toggle", id), {}, { preserveScroll: true });
}

// ---- New arrivals carousel ----
const arrivalsTrack = ref(null);
function scrollArrivals(dir) {
    const el = arrivalsTrack.value;
    if (!el) return;
    el.scrollBy({ left: dir * (el.clientWidth * 0.8), behavior: "smooth" });
}

const sectionDesc =
    "Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits.";
</script>

<template>
    <div class="min-h-screen bg-white font-body text-cocov-text antialiased">
        <Head :title="(settings.site_name || 'CocoCraft') + ' | A Bite of Love'" />

        <!-- ================= HEADER ================= -->
        <header class="sticky top-0 z-50 bg-white">
            <div class="relative mx-auto grid h-[70px] max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center px-5 md:h-[110px] md:px-8 lg:px-[126px]">
                <!-- Left nav -->
                <div class="flex items-center gap-8">
                    <button class="text-cocov-text md:hidden" aria-label="Menu" @click="isMobileMenuOpen = true">
                        <Bars3Icon class="h-7 w-7" />
                    </button>
                    <nav class="hidden items-center gap-8 font-heading text-[24px] font-normal uppercase leading-none md:flex">
                        <component
                            v-for="m in leftMenu"
                            :key="m.id"
                            :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                            :href="menuHref(m.url)"
                            class="flex items-center gap-1 whitespace-nowrap transition hover:text-cocov-gold"
                            :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                        >
                            {{ m.name }}
                            <ChevronDownIcon v-if="m.children" class="h-3.5 w-3.5 stroke-[2.5]" />
                        </component>
                    </nav>
                </div>

                <!-- Center logo (protrudes over banner) -->
                <Link href="/" class="relative z-10 flex justify-center self-stretch">
                    <span class="absolute left-1/2 top-1/2 flex h-[120px] w-[120px] -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] md:h-[176px] md:w-[176px]">
                        <img src="/images/cococraft-v2/logo.png" :alt="settings.site_name || 'CocoCraft'" class="h-auto w-[74px] object-contain md:w-[120px]" />
                    </span>
                </Link>

                <!-- Right nav + icons -->
                <div class="flex items-center justify-end gap-6">
                    <nav class="hidden items-center gap-8 font-heading text-[24px] font-normal uppercase leading-none lg:flex">
                        <component
                            v-for="m in rightMenu"
                            :key="m.id"
                            :is="menuHref(m.url).startsWith('http') ? 'a' : Link"
                            :href="menuHref(m.url)"
                            class="flex items-center gap-1 whitespace-nowrap transition hover:text-cocov-gold"
                            :class="isActive(m.url) ? 'text-cocov-gold' : 'text-cocov-text'"
                        >
                            {{ m.name }}
                            <ChevronDownIcon v-if="m.children" class="h-3.5 w-3.5 stroke-[2.5]" />
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
                <img src="/images/cococraft-v2/logo.png" alt="CocoCraft" class="h-12 w-auto" />
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

        <!-- ================= HERO BANNER ================= -->
        <section class="w-full">
            <img src="/images/cococraft-v2/banner.jpg" alt="CocoCraft — A Bite of Love" class="block w-full object-cover" />
        </section>

        <!-- ================= OFFER BAR (flush under banner) ================= -->
        <section class="w-full px-5 md:px-8 lg:px-[126px]">
            <div class="mx-auto flex max-w-[1350px] flex-col divide-y divide-cocov-muted/50 bg-cocov-offer py-6 md:flex-row md:items-center md:divide-x md:divide-y-0 md:py-0">
                <div v-for="(o, i) in [
                    { icon: '/images/cococraft-v2/icon_delivery.png', title: 'FREE DELIVERY', sub: 'Over 3,000 taka order' },
                    { icon: '/images/cococraft-v2/icon_collect.png', title: 'COLLECT YOUR CHOCOLATE', sub: 'Cococraft' },
                    { icon: '/images/cococraft-v2/icon_gifts.png', title: 'BEST SELLING GIFTS Box', sub: 'Cococraft' },
                ]" :key="i" class="flex flex-1 items-center justify-center gap-4 px-6 py-6 md:py-[42px]">
                    <img :src="o.icon" alt="" class="h-16 w-16 shrink-0 object-contain" />
                    <div class="text-left">
                        <p class="font-heading text-[22px] uppercase leading-tight text-cocov-text md:text-[24px]">{{ o.title }}</p>
                        <p class="text-[16px] capitalize text-cocov-text">{{ o.sub }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= SHOP BY COLLECTION ================= -->
        <section class="px-5 py-16 md:px-8 lg:px-[126px] md:py-24">
            <div class="mx-auto max-w-[1350px]">
                <div class="mb-12 text-center">
                    <p class="font-corinthia text-[46px] lowercase leading-none text-cocov-gold md:text-[50px]">world of cococraft</p>
                    <h2 class="mt-1 font-heading text-3xl uppercase leading-tight text-cocov-text md:text-[48px]">Shop by Collection</h2>
                    <p class="mx-auto mt-4 max-w-[760px] text-[15px] leading-[25px] text-black/80">{{ sectionDesc }}</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-[31px]">
                    <div v-for="(cat, index) in categories.slice(0, 3)" :key="cat.id" class="group relative h-[380px] overflow-hidden rounded-[4px] bg-black md:h-[435px]">
                        <img :src="cat.image || fallbackImage" :alt="cat.name" class="h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105 group-hover:opacity-90" />
                        <div class="absolute inset-0 flex items-center justify-center px-6 text-center">
                            <h3 class="font-heading text-[36px] capitalize leading-[1.15] text-white drop-shadow-[2px_4px_4px_rgba(0,0,0,0.75)] md:text-[44px]">{{ cat.name }}</h3>
                        </div>
                        <Link
                            :href="route('categories.show', cat.slug)"
                            class="absolute bottom-5 left-1/2 flex h-[52px] w-[280px] max-w-[85%] -translate-x-1/2 items-center justify-center rounded-[3px] text-[15px] capitalize transition md:h-[60px] md:w-[320px]"
                            :class="index === 1
                                ? 'bg-cocov-gold text-white hover:bg-[#e0851a]'
                                : 'border border-cocov-gold text-cocov-gold hover:bg-cocov-gold hover:text-white'"
                        >View all collections</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= SIGNATURE FEATURED ================= -->
        <section v-if="featuredItems.length" class="px-5 py-16 md:px-8 lg:px-[126px] md:py-20">
            <div class="mx-auto max-w-[1350px]">
                <div class="mb-12 text-center">
                    <p class="font-corinthia text-[46px] lowercase leading-none text-cocov-gold md:text-[50px]">great cococraft taste</p>
                    <h2 class="mt-1 font-heading text-3xl uppercase leading-tight text-cocov-text md:text-[48px]">Signature Featured</h2>
                    <p class="mx-auto mt-4 max-w-[760px] text-[15px] leading-[25px] text-black/80">{{ sectionDesc }}</p>
                </div>
                <div class="grid grid-cols-2 gap-5 lg:grid-cols-4 lg:gap-[35px]">
                    <article v-for="product in featuredItems" :key="product.id" class="group relative flex flex-col overflow-hidden bg-cocov-card">
                        <div class="relative aspect-square overflow-hidden">
                            <Link :href="route('products.show', product.slug)">
                                <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            </Link>
                            <button
                                type="button"
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-cocov-gold text-white opacity-0 shadow transition group-hover:opacity-100 hover:bg-[#e0851a]"
                                :class="{ 'opacity-100': product.is_wishlisted }"
                                aria-label="Wishlist"
                                @click="toggleWishlist(product.id)"
                            ><HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" /></button>
                        </div>
                        <div class="relative flex min-h-[102px] flex-col px-4 pt-4">
                            <h3 class="font-heading text-[18px] capitalize leading-[24px] text-cocov-text md:text-[20px] md:leading-[28px]">
                                <Link :href="route('products.show', product.slug)" class="transition hover:text-cocov-gold">{{ product.name }}</Link>
                            </h3>
                            <div class="mt-3 flex items-baseline gap-2 text-[15px]">
                                <span v-if="product.compare_at_price > product.price" class="text-cocov-muted line-through">{{ formatMoney(product.compare_at_price) }}</span>
                                <span v-if="product.compare_at_price > product.price" class="text-cocov-muted">|</span>
                                <span class="text-[18px] font-semibold text-cocov-gold md:text-[20px]">{{ formatMoney(product.price) }}</span>
                            </div>
                            <!-- default compact cart button -->
                            <button
                                type="button"
                                class="absolute bottom-0 right-0 flex h-[44px] w-[73px] items-center justify-center bg-cocov-gold text-white transition group-hover:opacity-0"
                                aria-label="Add to cart"
                                @click="addToCart(product.id)"
                            ><ShoppingBagIcon class="h-5 w-5" /></button>
                            <!-- hover full bar -->
                            <button
                                type="button"
                                class="absolute inset-x-0 bottom-0 flex h-[44px] translate-y-full items-center justify-center gap-2 bg-cocov-gold text-[15px] capitalize text-white opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100"
                                @click="addToCart(product.id)"
                            ><ShoppingBagIcon class="h-5 w-5" /> Add to cart</button>
                        </div>
                    </article>
                </div>
                <div class="mt-12 text-center">
                    <Link :href="route('products.index')" class="inline-flex h-[50px] w-[161px] items-center justify-center rounded-[3px] border border-cocov-gold text-[15px] capitalize text-cocov-gold transition hover:bg-cocov-gold hover:text-white">more product</Link>
                </div>
            </div>
        </section>

        <!-- ================= NEW ARRIVALS ================= -->
        <section v-if="newArrivals.length" class="px-5 py-16 md:px-8 lg:px-[126px] md:py-20">
            <div class="mx-auto max-w-[1350px]">
                <div class="mb-12 text-center">
                    <p class="font-corinthia text-[46px] lowercase leading-none text-cocov-gold md:text-[50px]">pure nature, bold innovation</p>
                    <h2 class="mt-1 font-heading text-3xl uppercase leading-tight text-cocov-text md:text-[48px]">New Arrivals</h2>
                    <p class="mx-auto mt-4 max-w-[760px] text-[15px] leading-[25px] text-black/80">{{ sectionDesc }}</p>
                </div>
                <div class="relative">
                    <button type="button" class="absolute -left-2 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-cocov-gold bg-white text-cocov-gold shadow transition hover:bg-cocov-gold hover:text-white lg:flex" aria-label="Previous" @click="scrollArrivals(-1)"><ChevronLeftIcon class="h-5 w-5" /></button>
                    <button type="button" class="absolute -right-2 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-cocov-gold bg-white text-cocov-gold shadow transition hover:bg-cocov-gold hover:text-white lg:flex" aria-label="Next" @click="scrollArrivals(1)"><ChevronRightIcon class="h-5 w-5" /></button>

                    <div ref="arrivalsTrack" class="flex snap-x gap-5 overflow-x-auto scroll-smooth pb-2 lg:gap-[35px] [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        <article v-for="product in newArrivals" :key="product.id" class="group relative flex w-[70%] shrink-0 snap-start flex-col overflow-hidden bg-cocov-card sm:w-[45%] lg:w-[calc((100%-105px)/4)]">
                            <div class="relative aspect-square overflow-hidden">
                                <Link :href="route('products.show', product.slug)">
                                    <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                </Link>
                                <button type="button" class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-cocov-gold text-white opacity-0 shadow transition group-hover:opacity-100 hover:bg-[#e0851a]" :class="{ 'opacity-100': product.is_wishlisted }" aria-label="Wishlist" @click="toggleWishlist(product.id)"><HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" /></button>
                            </div>
                            <div class="relative flex min-h-[102px] flex-col px-4 pt-4">
                                <h3 class="font-heading text-[18px] capitalize leading-[24px] text-cocov-text md:text-[20px] md:leading-[28px]">
                                    <Link :href="route('products.show', product.slug)" class="transition hover:text-cocov-gold">{{ product.name }}</Link>
                                </h3>
                                <div class="mt-3 flex items-baseline gap-2 text-[15px]">
                                    <span v-if="product.compare_at_price > product.price" class="text-cocov-muted line-through">{{ formatMoney(product.compare_at_price) }}</span>
                                    <span v-if="product.compare_at_price > product.price" class="text-cocov-muted">|</span>
                                    <span class="text-[18px] font-semibold text-cocov-gold md:text-[20px]">{{ formatMoney(product.price) }}</span>
                                </div>
                                <button type="button" class="absolute bottom-0 right-0 flex h-[44px] w-[73px] items-center justify-center bg-cocov-gold text-white transition group-hover:opacity-0" aria-label="Add to cart" @click="addToCart(product.id)"><ShoppingBagIcon class="h-5 w-5" /></button>
                                <button type="button" class="absolute inset-x-0 bottom-0 flex h-[44px] translate-y-full items-center justify-center gap-2 bg-cocov-gold text-[15px] capitalize text-white opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100" @click="addToCart(product.id)"><ShoppingBagIcon class="h-5 w-5" /> Add to cart</button>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= ABOUT ================= -->
        <section class="px-5 py-16 md:px-8 lg:px-[126px] md:py-20">
            <div class="mx-auto grid max-w-[1350px] gap-12 md:grid-cols-[660px_1fr] md:items-start md:gap-16">
                <div>
                    <p class="text-center font-corinthia text-[46px] lowercase leading-none text-cocov-gold md:text-left md:text-[50px]">the story behind</p>
                    <h2 class="mt-1 text-center font-heading text-3xl uppercase leading-tight text-cocov-text md:text-left md:text-[48px]">About Cococraft</h2>
                    <div class="mt-6 space-y-4 text-justify text-[15px] leading-[25px] text-black/85">
                        <p>Chocolate is not just a delicious treat; it is a unique expression of joy, celebration, and love. For the past decade, our chocolate company, "CocoCraft," has been winning hearts by crafting world-class premium chocolates. Our primary mission is to sweeten your every moment with chocolates made from entirely pure ingredients.</p>
                        <p>We source high-quality cocoa beans directly from the finest natural farms in West Africa and Latin America. Every chocolate bar is then crafted under the strict supervision of our expert chocolatiers, combining modern, hygienic technology. Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits. To maintain the highest quality, we do not use any artificial preservatives.</p>
                        <p>Beyond business success, we are deeply committed to eco-friendly packaging and fair trade practices, which help improve the livelihoods of marginalized cocoa farmers. Whether it is a birthday, wedding, or any festive occasion, our customized gift boxes are the perfect choice for gifting something special to your loved ones. Join us today to experience the true, royal taste of authentic chocolate.</p>
                    </div>
                    <div class="mt-8 text-center md:text-left">
                        <Link :href="menuHref('about-us')" class="inline-flex h-[50px] w-[161px] items-center justify-center rounded-[3px] border border-cocov-gold text-[15px] capitalize text-cocov-gold transition hover:bg-cocov-gold hover:text-white">Details</Link>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-[430px]">
                    <div class="overflow-hidden rounded-t-[215px] shadow-[inset_2px_4px_4px_rgba(115,57,40,0.3)]">
                        <img src="/images/cococraft-v2/about_arch.jpg" alt="About CocoCraft" class="h-[560px] w-full object-cover md:h-[640px]" />
                    </div>
                    <div class="absolute bottom-[16%] right-0 w-[296px] max-w-[85%] bg-cocov-gold/95 p-2">
                        <div class="border border-[#f9e00c] px-4 py-3 text-center text-white">
                            <p class="text-[16px] leading-[26px]">If need custom chocolate<br />CALL US</p>
                            <p class="mt-1 text-[22px] font-medium leading-[26px] md:text-[24px]">{{ settings.phone || '+88 01886 831 130' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= TESTIMONIALS ================= -->
        <section class="relative overflow-hidden bg-[#f7f2ec]">
            <img src="/images/cococraft-v2/testi_cocoa.png" alt="" class="pointer-events-none absolute bottom-0 left-0 hidden w-[31.5%] lg:block" />
            <img src="/images/cococraft-v2/testi_woman.jpg" alt="Happy CocoCraft guest" class="pointer-events-none absolute bottom-0 right-0 hidden w-[25%] lg:block" />

            <div class="relative z-10 mx-auto max-w-[1350px] px-5 py-16 md:px-8 lg:px-[126px] md:py-24">
                <div class="mx-auto max-w-[780px] text-center lg:mr-[440px]">
                    <p class="font-corinthia text-[46px] leading-none text-cocov-gold md:text-[50px]">Testimonials</p>
                    <h2 class="mt-1 font-heading text-3xl uppercase leading-tight text-cocov-text md:text-[48px]">What Our Guest Are Saying</h2>
                    <svg viewBox="0 0 64 64" class="mx-auto mt-8 h-14 w-14 text-cocov-gold" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                        <path d="M20 44a12 12 0 1 1 12-12" stroke="#484747" />
                        <path d="M14 20h8v8" stroke="currentColor" />
                        <path d="M26 20h8v8" stroke="currentColor" />
                    </svg>
                    <p class="mx-auto mt-6 max-w-[771px] text-[20px] font-medium leading-[36px] text-cocov-text">
                        &ldquo;Best chocolate I've ever had!!!! It is one of the best sweet treat experiences that you will ever have. The problem is, you will never...&rdquo;
                    </p>
                    <p class="mt-8 text-[20px] font-bold uppercase leading-[32px] text-cocov-text">Jannatul Khatun</p>
                    <p class="text-[16px] leading-[32px] text-cocov-text">Mohammadpur, Dhaka</p>
                </div>
            </div>

            <!-- woman on mobile / tablet -->
            <img src="/images/cococraft-v2/testi_woman.jpg" alt="" class="mx-auto block w-full max-w-[420px] object-cover lg:hidden" />
        </section>

        <!-- Dune transition (exact Figma band: cream -> tan waves -> brown) -->
        <img src="/images/cococraft-v2/dune_band.jpg" alt="" aria-hidden="true" class="hidden w-full lg:block" />
        <!-- simple dune for mobile/tablet -->
        <svg viewBox="0 0 800 90" preserveAspectRatio="none" class="block h-12 w-full bg-[#f7f2ec] lg:hidden" aria-hidden="true">
            <path fill="#f2c98c" d="M0,38 C130,10 260,50 400,36 C540,22 660,52 800,30 L800,90 L0,90 Z" />
            <path fill="#6B2410" d="M0,62 C150,38 300,72 450,58 C580,46 690,70 800,52 L800,90 L0,90 Z" />
        </svg>

        <!-- ================= FOOTER ================= -->
        <footer class="relative -mt-px bg-cocov-brown text-white">
            <!-- subtle cocoa texture overlay -->
            <div class="pointer-events-none absolute inset-0 z-0 opacity-[0.10]" aria-hidden="true"
                 style="background-image: url(&quot;data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'><g fill='none' stroke='%23f7e6d0' stroke-width='2'><ellipse cx='40' cy='40' rx='18' ry='11' transform='rotate(-25 40 40)'/><path d='M28 44 L52 36'/><rect x='110' y='30' width='34' height='26' rx='3'/><path d='M110 43 h34 M121 30 v26 M133 30 v26'/><ellipse cx='150' cy='120' rx='16' ry='10' transform='rotate(30 150 120)'/><path d='M140 116 L160 124'/><circle cx='70' cy='130' r='4'/><circle cx='90' cy='120' r='3'/><rect x='20' y='120' width='30' height='22' rx='3' transform='rotate(-12 20 120)'/></g></svg>&quot;); background-size: 180px 180px;"></div>

            <div class="relative z-10">
                <div class="mx-auto grid max-w-[1350px] gap-12 px-5 pb-14 pt-7 md:grid-cols-2 md:px-8 lg:grid-cols-4 lg:px-[126px]">
                    <!-- Sign up -->
                    <div>
                        <h4 class="font-heading text-[24px] leading-[32px] text-white">Sign Up and Save</h4>
                        <p class="mt-6 max-w-[307px] text-[16px] leading-[25px] text-white/90">Subscribe to get special offers, free giveaways, &amp; once-in-a-lifetime deals.</p>
                        <form class="mt-6 max-w-[314px]" @submit.prevent>
                            <input type="email" placeholder="Email here" class="w-full border-0 border-b border-cocov-gold bg-transparent px-1 py-2 text-[14px] text-cocov-gold placeholder:text-cocov-gold focus:border-cocov-gold focus:ring-0" />
                            <button type="submit" class="mt-6 flex h-[50px] w-[133px] items-center justify-center rounded-[3px] border border-cocov-gold text-[16px] text-cocov-gold transition hover:bg-cocov-gold hover:text-white">Submit</button>
                        </form>
                    </div>
                    <!-- Company -->
                    <div>
                        <h4 class="font-heading text-[24px] leading-[32px] text-white">Company Information</h4>
                        <div class="mt-6 flex flex-col text-[16px] leading-[37px] text-white/90">
                            <Link :href="menuHref('about-us')" class="transition hover:text-cocov-gold">About Us</Link>
                            <Link :href="menuHref('employment')" class="transition hover:text-cocov-gold">Employment</Link>
                            <Link :href="menuHref('retail-store-locations')" class="transition hover:text-cocov-gold">Retail Store</Link>
                            <Link :href="menuHref('terms-of-service')" class="transition hover:text-cocov-gold">Terms of Service</Link>
                            <Link :href="menuHref('wholesale')" class="transition hover:text-cocov-gold">Wholesale</Link>
                        </div>
                    </div>
                    <!-- Useful -->
                    <div>
                        <h4 class="font-heading text-[24px] leading-[32px] text-white">Useful Links</h4>
                        <div class="mt-6 flex flex-col text-[16px] leading-[37px] text-white/90">
                            <Link :href="route('products.index')" class="transition hover:text-cocov-gold">Products</Link>
                            <Link :href="menuHref('privacy-policy')" class="transition hover:text-cocov-gold">Privacy Policy</Link>
                            <Link :href="menuHref('refund-policy')" class="transition hover:text-cocov-gold">Refund and Returns</Link>
                            <Link :href="route('customer.dashboard')" class="transition hover:text-cocov-gold">Order Status</Link>
                            <Link :href="menuHref('become-an-affiliate')" class="transition hover:text-cocov-gold">Become an Affiliate</Link>
                        </div>
                    </div>
                    <!-- Contact -->
                    <div>
                        <h4 class="font-heading text-[24px] leading-[32px] text-white">Contact Information</h4>
                        <div class="mt-6 flex flex-col gap-3 text-[16px] text-white/90">
                            <a :href="`tel:${settings.phone || '+8801886831130'}`" class="flex items-center gap-3 transition hover:text-cocov-gold"><PhoneIcon class="h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.phone || '+88 01886 831 130' }}</a>
                            <a :href="`mailto:${settings.email || 'info@cococraft.com.bd'}`" class="flex items-center gap-3 transition hover:text-cocov-gold"><EnvelopeIcon class="h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.email || 'info@cococraft.com.bd' }}</a>
                            <span class="flex items-start gap-3"><MapPinIcon class="mt-1 h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.address || 'House 7, Road 4, Mirpur 7, Dhaka' }}</span>
                        </div>
                        <div class="mt-6 flex items-center gap-5 text-white">
                            <a :href="settings.facebook_url || '#'" aria-label="Facebook" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-6 w-6 fill-current"><path d="M13.5 21v-8h2.6l.4-3h-3V8.1c0-.9.3-1.5 1.6-1.5H17V4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8.2v3h2.6v8h2.7z"/></svg></a>
                            <a :href="settings.twitter_url || '#'" aria-label="X" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M18.9 2H22l-7 8 8.2 12h-6.5l-5-6.6L5.9 22H2.8l7.5-8.6L2.4 2h6.7l4.6 6.1L18.9 2zm-1.1 18h1.7L7.3 3.8H5.5L17.8 20z"/></svg></a>
                            <a :href="settings.pinterest_url || '#'" aria-label="Pinterest" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-6 w-6 fill-current"><path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.6l-.2.9c-.1.3-.3.4-.6.2-1.2-.5-1.8-1.9-1.8-3.4 0-2.6 2.2-5.7 6.5-5.7 3.5 0 5.8 2.5 5.8 5.2 0 3.6-2 6.3-4.9 6.3-1 0-1.9-.5-2.2-1.1l-.6 2.4c-.2.8-.7 1.7-1.1 2.4A10 10 0 1 0 12 2z"/></svg></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 bg-cocov-brown-dark py-8 text-center text-[16px] text-white/90">
                Copyright © {{ new Date().getFullYear() }} {{ settings.site_name || 'Cococraft' }} - All Rights Reserved.
            </div>
        </footer>
    </div>
</template>
