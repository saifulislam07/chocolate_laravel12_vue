<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import { 
    ChevronDownIcon,
    ArrowRightIcon,
    HeartIcon
} from "@heroicons/vue/24/outline";

// Import Swiper components
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Autoplay, EffectFade, Navigation } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';

const modules = [Pagination, Autoplay, EffectFade, Navigation];

const activeSlideIndex = ref(0);
function onSlideChange(swiper) {
    activeSlideIndex.value = swiper.realIndex;
}

defineProps({
    sliders: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    categorySections: { type: Array, default: () => [] },
    featuredItems: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
    discountItems: { type: Array, default: () => [] },
    bundleItems: { type: Array, default: () => [] },
});

function getDiscountPercent(price, comparePrice) {
    if (!comparePrice || comparePrice <= price) return null;
    return Math.round(((comparePrice - price) / comparePrice) * 100);
}

const fallbackImage = "/images/godiva/product_default.png";

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(amount) {
    return moneyFormatter.format(Number(amount || 0));
}

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}

function toggleWishlist(productId) {
    router.post(route("wishlist.toggle", productId), {}, { preserveScroll: true });
}
</script>

<template>
    <MainLayout>
        <Head title="Coco Craft | Premium Belgian Chocolate" />

        <!-- Hero Slider Section -->
        <section class="relative overflow-hidden">
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{ delay: 6000, pauseOnMouseEnter: true }"
                :pagination="{ el: '.hero-progress', type: 'progressbar' }"
                :navigation="{ prevEl: '.hero-nav-prev', nextEl: '.hero-nav-next' }"
                effect="fade"
                class="h-[80vh] md:h-[90vh]"
                @slide-change="onSlideChange"
            >
                <SwiperSlide v-for="slider in sliders" :key="slider.id">
                    <div
                        class="h-full w-full transition-colors duration-1000"
                        :style="{ backgroundColor: slider.bg_color || '#FBEBD9', color: slider.text_color || '#4B2E1E' }"
                    >
                        <div class="mx-auto grid h-full max-w-7xl grid-cols-1 items-center gap-8 px-6 relative md:grid-cols-[1.1fr_1fr] md:gap-4">
                            <!-- Background Text (Decorative) -->
                            <div
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 select-none opacity-[0.03] pointer-events-none"
                                :style="{ color: slider.text_color || '#4B2E1E' }"
                            >
                                <span class="font-serif text-[20vw] whitespace-nowrap leading-none uppercase tracking-tighter">COCOCRAFT</span>
                            </div>

                            <!-- Text Block (always left) -->
                            <div class="z-10 order-2 flex flex-col justify-center py-10 md:order-1 md:py-0">
                                <div class="overflow-hidden mb-6">
                                    <p class="slide-up text-[11px] font-bold uppercase tracking-[0.4em] opacity-80"
                                       :style="{ borderLeft: `2px solid ${slider.text_color === '#FFFFFF' ? '#E89A50' : slider.text_color}`, animationDelay: '200ms' }">
                                        <span class="ml-4">{{ slider.subtitle }}</span>
                                    </p>
                                </div>

                                <h1
                                    class="slide-up font-serif font-light leading-[0.9] uppercase tracking-tighter mb-8 text-5xl md:text-[5.5rem]"
                                    :style="{ animationDelay: '400ms' }"
                                    v-html="slider.title"
                                ></h1>

                                <div class="overflow-hidden mb-12">
                                    <p class="slide-up mt-2 max-w-md text-sm leading-loose tracking-widest opacity-80 font-light"
                                       :style="{ animationDelay: '600ms' }">
                                        {{ slider.description }}
                                    </p>
                                </div>

                                <div class="slide-up" :style="{ animationDelay: '800ms' }">
                                    <Link
                                        :href="slider.button_link || '#'"
                                        class="group relative inline-flex items-center gap-4 overflow-hidden border px-12 py-5 text-[10px] font-bold uppercase tracking-[0.2em] transition-all duration-500"
                                        :style="{
                                            borderColor: slider.text_color === '#FFFFFF' ? 'rgba(255,255,255,0.3)' : 'rgba(75,46,30,0.25)',
                                            color: slider.text_color
                                        }"
                                    >
                                        <span class="relative z-10">{{ slider.button_text }}</span>
                                        <ArrowRightIcon class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-2" />
                                        <div
                                            class="absolute inset-0 -translate-x-full transition-transform duration-500 group-hover:translate-x-0"
                                            :style="{ backgroundColor: slider.text_color === '#FFFFFF' ? '#FFFFFF' : '#4B2E1E' }"
                                        ></div>
                                        <span
                                            class="absolute inset-0 z-1 flex -translate-x-full items-center gap-4 px-12 py-5 text-[10px] font-bold uppercase tracking-[0.2em] transition-transform duration-500 group-hover:translate-x-0 group-hover:text-gold"
                                            :style="{ color: slider.text_color === '#FFFFFF' ? '#4B2E1E' : '#FFFFFF' }"
                                        >
                                            {{ slider.button_text }} <ArrowRightIcon class="h-4 w-4" />
                                        </span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Image Block (always right, full-bleed with Ken Burns zoom) -->
                            <div class="relative order-1 flex h-[45vh] w-full items-center justify-center md:order-2 md:h-full">
                                <div
                                    class="absolute inset-0 rounded-full border opacity-10 animate-pulse-slow scale-75 md:scale-100"
                                    :style="{ borderColor: slider.text_color }"
                                ></div>

                                <div class="relative h-full w-full overflow-hidden">
                                    <img
                                        :src="slider.image"
                                        :alt="slider.subtitle"
                                        class="hero-img absolute inset-0 h-full w-full object-contain z-20"
                                        :style="{
                                            filter: slider.bg_color === '#4B2E1E' ? 'drop-shadow(0 25px 50px rgba(255,255,255,0.1))' : 'drop-shadow(0 25px 50px rgba(0,0,0,0.15))'
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>

                <!-- Custom controls: progress bar + arrows + slide counter -->
                <div class="pointer-events-none absolute inset-x-0 bottom-0 z-30 pb-8">
                    <div class="mx-auto flex max-w-7xl items-center gap-6 px-6">
                        <span class="pointer-events-auto font-serif text-sm tabular-nums opacity-70">
                            {{ String(activeSlideIndex + 1).padStart(2, '0') }} / {{ String(sliders.length).padStart(2, '0') }}
                        </span>
                        <div class="hero-progress pointer-events-auto relative h-[2px] flex-1 overflow-hidden bg-white/25"></div>
                        <div class="pointer-events-auto flex items-center gap-3">
                            <button type="button" class="hero-nav-prev flex h-9 w-9 items-center justify-center rounded-full border border-current/25 transition hover:border-current/60" aria-label="Previous slide">
                                <ChevronDownIcon class="h-3.5 w-3.5 rotate-90" />
                            </button>
                            <button type="button" class="hero-nav-next flex h-9 w-9 items-center justify-center rounded-full border border-current/25 transition hover:border-current/60" aria-label="Next slide">
                                <ChevronDownIcon class="h-3.5 w-3.5 -rotate-90" />
                            </button>
                        </div>
                    </div>
                </div>
            </Swiper>
        </section>

        <!-- Shop by Category -->
        <section class="border-y border-gray-100 py-20 dark:border-white/10">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 text-center">
                    <h2 class="font-serif text-[11px] font-bold uppercase tracking-[0.4em] text-godiva-gold">World of Godiva</h2>
                    <p class="mt-4 font-serif text-4xl italic tracking-wide">Shop by Collection</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="route('categories.show', cat.slug)"
                        class="group cursor-pointer"
                    >
                        <div class="relative h-[360px] overflow-hidden bg-godiva-cream sm:h-[420px]">
                            <img
                                :src="cat.image || fallbackImage"
                                :alt="cat.name"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-godiva-charcoal/55 via-transparent to-transparent opacity-80"></div>
                            <div class="absolute left-4 top-4 bg-white/95 px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal shadow-sm">
                                {{ cat.products_count }} items
                            </div>
                            <div class="absolute inset-x-0 bottom-0 p-6 text-center text-white">
                                <h3 class="text-xs font-bold uppercase tracking-[0.22em] transition group-hover:text-godiva-gold">{{ cat.name }}</h3>
                                <p class="mt-3 text-[10px] uppercase leading-5 tracking-[0.18em] text-white/85">{{ cat.description || 'Explore this collection' }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Bundle Items Section -->
        <section v-if="bundleItems.length" class="bg-white py-20 dark:bg-godiva-charcoal">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6 dark:border-white/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Curated Sets</p>
                        <h2 class="mt-3 font-serif text-3xl italic">Bundle Offers</h2>
                    </div>
                    <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">
                        Shop More
                    </Link>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="product in bundleItems" :key="product.id" class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
                        <div class="relative aspect-square overflow-hidden bg-godiva-cream/40 p-4">
                            <span class="absolute left-4 top-4 z-10 bg-godiva-charcoal px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-white">
                                {{ product.bundle_items_count }} items
                            </span>
                            <button
                                type="button"
                                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal dark:text-godiva-cream"
                                :class="{ 'text-red-500': product.is_wishlisted }"
                                @click="toggleWishlist(product.id)"
                            >
                                <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                            </button>
                            <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                            <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4">
                                <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">Add to Bag</button>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6 text-center">
                            <h3 class="font-serif text-lg uppercase leading-tight tracking-tight">
                                <Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition">{{ product.name }}</Link>
                            </h3>
                            <p v-if="product.bundle_note" class="mt-3 line-clamp-2 text-xs leading-5 tracking-wide text-gray-500">{{ product.bundle_note }}</p>
                            <div class="mt-4 flex flex-col items-center justify-center gap-1">
                                <span v-if="product.compare_at_price > product.price" class="text-xs text-gray-400 line-through tracking-widest">{{ formatMoney(product.compare_at_price) }}</span>
                                <span class="font-serif text-xl" :class="product.compare_at_price > product.price ? 'text-red-600' : 'text-godiva-charcoal'">{{ formatMoney(product.price) }}</span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Category-wise Products -->
        <section v-for="section in categorySections" :key="section.id" class="mx-auto max-w-7xl px-6 py-16 border-b border-gray-100 dark:border-white/10">
            <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Category</p>
                    <h2 class="mt-3 font-serif text-3xl italic">{{ section.name }}</h2>
                    <p v-if="section.description" class="mt-2 max-w-xl text-sm leading-7 text-gray-500 dark:text-gray-400">{{ section.description }}</p>
                </div>
                <Link :href="route('categories.show', section.slug)" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">
                    View All
                </Link>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="product in section.products" :key="product.id" class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
                    <div class="relative aspect-square overflow-hidden bg-white p-4 dark:bg-godiva-prefooter">
                        <button
                            type="button"
                            class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal dark:text-godiva-cream"
                            :class="{ 'text-red-500': product.is_wishlisted }"
                            @click="toggleWishlist(product.id)"
                        >
                            <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                        </button>
                        <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4">
                            <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">Add to Bag</button>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6 text-center">
                        <h3 class="font-serif text-lg tracking-tight uppercase leading-tight">
                            <Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition">{{ product.name }}</Link>
                        </h3>
                        <div class="mt-4 flex flex-col items-center justify-center gap-1">
                            <span v-if="product.compare_at_price > product.price" class="text-xs text-gray-400 line-through tracking-widest">{{ formatMoney(product.compare_at_price) }}</span>
                            <span class="font-serif text-xl" :class="product.compare_at_price > product.price ? 'text-red-600' : 'text-godiva-charcoal'">{{ formatMoney(product.price) }}</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- New Arrivals Section -->
        <section v-if="newArrivals.length" class="mx-auto max-w-7xl px-6 py-20">
            <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6 dark:border-white/10">
                <h2 class="font-serif text-3xl italic">New Arrivals</h2>
                <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">
                    Discover New
                </Link>
            </div>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="product in newArrivals" :key="product.id" class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
                    <div class="relative aspect-square overflow-hidden bg-white p-4 dark:bg-godiva-prefooter">
                        <span class="absolute top-4 left-4 z-10 bg-godiva-pink px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal">New</span>
                        <button
                            type="button"
                            class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal dark:text-godiva-cream"
                            :class="{ 'text-red-500': product.is_wishlisted }"
                            @click="toggleWishlist(product.id)"
                        >
                            <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                        </button>
                        <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4 text-center">
                            <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">Add to Bag</button>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6 text-center text-left-sm">
                        <h3 class="font-serif text-lg tracking-tight"><Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition uppercase leading-tight">{{ product.name }}</Link></h3>
                        <p class="mt-4 font-serif text-xl">{{ formatMoney(product.price) }}</p>
                    </div>
                </article>
            </div>
        </section>

        <!-- Featured Items Section -->
        <section v-if="featuredItems.length" class="bg-godiva-cream/30 py-20 dark:bg-white/5">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6 dark:border-white/10">
                    <h2 class="font-serif text-3xl italic">Signature Featured</h2>
                    <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition text-left-sm">View Collections</Link>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="product in featuredItems" :key="product.id" class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
                        <div class="relative aspect-square overflow-hidden bg-white p-4 dark:bg-godiva-prefooter">
                            <button
                                type="button"
                                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal dark:text-godiva-cream"
                                :class="{ 'text-red-500': product.is_wishlisted }"
                                @click="toggleWishlist(product.id)"
                            >
                                <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                            </button>
                            <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                            <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4">
                                <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">Add to Bag</button>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6 text-center">
                            <h3 class="font-serif text-lg tracking-tight uppercase leading-tight">
                                <Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition">{{ product.name }}</Link>
                            </h3>
                            <p class="mt-4 font-serif text-xl">{{ formatMoney(product.price) }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Discount/Sale Items Section -->
        <section v-if="discountItems.length" class="mx-auto max-w-7xl px-6 py-20">
            <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6 dark:border-white/10">
                <h2 class="font-serif text-3xl italic">Sweet Offers & Sale</h2>
                <Link :href="route('products.index')" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">Shop All Sale</Link>
            </div>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="product in discountItems" :key="product.id" class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
                    <div class="relative aspect-square overflow-hidden bg-white p-4 dark:bg-godiva-prefooter">
                        <span class="absolute top-4 left-4 z-10 bg-red-600 px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-white">Save {{ getDiscountPercent(product.price, product.compare_at_price) }}%</span>
                        <button
                            type="button"
                            class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal dark:text-godiva-cream"
                            :class="{ 'text-red-500': product.is_wishlisted }"
                            @click="toggleWishlist(product.id)"
                        >
                            <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                        </button>
                        <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4">
                            <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">Add to Bag</button>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6 text-center">
                        <h3 class="font-serif text-lg uppercase leading-tight tracking-tight">
                            <Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition">{{ product.name }}</Link>
                        </h3>
                        <div class="mt-4 flex flex-col items-center justify-center gap-1">
                            <span class="text-xs text-gray-400 line-through tracking-widest">{{ formatMoney(product.compare_at_price) }}</span>
                            <span class="font-serif text-xl text-red-600">{{ formatMoney(product.price) }}</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- Newsletter -->
        <section class="mx-auto max-w-4xl px-6 py-24 text-center">
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Join our gold rewards</p>
            <h2 class="mt-8 font-serif text-5xl leading-tight">More Chocolate In Your Inbox!</h2>
            <form class="mx-auto mt-12 flex max-w-md items-center border-b border-godiva-charcoal pb-4 dark:border-godiva-cream">
                <input type="email" placeholder="Your Email Address" class="w-full bg-transparent px-2 py-2 text-sm focus:outline-none dark:placeholder:text-gray-400" />
                <button type="button" class="text-godiva-charcoal transition hover:text-godiva-gold dark:text-godiva-cream">
                    <ArrowRightIcon class="h-6 w-6" />
                </button>
            </form>
        </section>
    </MainLayout>
</template>

<style>
/* Custom Slider Animations */
.swiper-slide-active .slide-up {
    animation: slideUp 1s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

.slide-up {
    opacity: 0;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.swiper-slide-active .hero-img {
    animation: kenBurns 7s ease-out forwards;
    opacity: 1;
}

.hero-img {
    opacity: 0;
    transform: scale(1.06);
}

@keyframes kenBurns {
    0% { opacity: 0; transform: scale(1.12); }
    15% { opacity: 1; }
    100% { opacity: 1; transform: scale(1); }
}

.animate-pulse-slow {
    animation: pulseSlow 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulseSlow {
    0%, 100% { opacity: 0.1; transform: scale(1); }
    50% { opacity: 0.2; transform: scale(1.05); }
}

/* Swiper Styling: slim progress-bar pagination + arrow nav */
.hero-progress {
    color: inherit;
}

.hero-progress .swiper-pagination-progressbar-fill {
    background: currentColor !important;
    opacity: 0.9;
}

.hero-nav-prev,
.hero-nav-next {
    color: inherit;
}

</style>
