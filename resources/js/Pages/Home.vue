<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import ProductCard from "@/Components/ProductCard.vue";
import {
    ChevronDownIcon,
    ArrowRightIcon,
    TruckIcon,
    ShoppingBagIcon,
    GiftTopIcon,
    ChatBubbleBottomCenterTextIcon,
} from "@heroicons/vue/24/outline";

// Import Swiper components
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade, Navigation } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';

const heroModules = [Autoplay, EffectFade, Navigation];
const arrivalsModules = [Navigation];

const activeSlideIndex = ref(0);
function onSlideChange(swiper) {
    activeSlideIndex.value = swiper.realIndex;
}

defineProps({
    sliders: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    featuredItems: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
});

const fallbackImage = "/images/godiva/product_default.png";

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}

function toggleWishlist(productId) {
    router.post(route("wishlist.toggle", productId), {}, { preserveScroll: true });
}

const features = [
    { icon: TruckIcon, title: 'Free Delivery', subtitle: 'Over 3,000 Taka Order' },
    { icon: ShoppingBagIcon, title: 'Collect Your Chocolate', subtitle: 'Cococraft' },
    { icon: GiftTopIcon, title: 'Best Selling Gifts Box', subtitle: 'Cococraft' },
];

const testimonial = {
    quote: "Best chocolate I've ever had!!! It is one of the best sweet treat experiences that you will ever have. The problem is, you will never...",
    name: 'Jannatul Khatun',
    location: 'Mohammadpur, Dhaka',
};
</script>

<template>
    <MainLayout>
        <Head title="Coco Craft | A Bite of Love" />

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <Swiper
                :modules="heroModules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{ delay: 6000, pauseOnMouseEnter: true }"
                :navigation="{ prevEl: '.hero-nav-prev', nextEl: '.hero-nav-next' }"
                effect="fade"
                class="h-[70vh] md:h-[85vh]"
                @slide-change="onSlideChange"
            >
                <SwiperSlide v-for="slider in sliders" :key="slider.id">
                    <div class="relative h-full w-full">
                        <img :src="slider.image" :alt="slider.subtitle || 'Coco Craft'" class="absolute inset-0 h-full w-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

                        <div class="relative mx-auto flex h-full max-w-screen-2xl items-end px-6 pb-16 md:pb-24">
                            <div class="max-w-lg text-white">
                                <h1 class="font-sans text-5xl font-bold uppercase leading-[1.05] tracking-tight md:text-6xl" v-html="slider.title"></h1>
                                <p v-if="slider.description" class="mt-6 max-w-md text-sm leading-loose tracking-wide text-white/85">{{ slider.description }}</p>
                                <div class="mt-9">
                                    <Link
                                        :href="slider.button_link || route('products.index')"
                                        class="group inline-flex items-center gap-3 bg-godiva-gold px-10 py-4 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal transition hover:bg-white"
                                    >
                                        {{ slider.button_text || 'Shop Now' }}
                                        <ArrowRightIcon class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>

                <div class="pointer-events-none absolute inset-x-0 bottom-0 z-30 pb-6">
                    <div class="mx-auto flex max-w-screen-2xl items-center justify-end gap-3 px-6">
                        <span class="pointer-events-auto mr-auto font-serif text-sm tabular-nums text-white/80">
                            {{ String(activeSlideIndex + 1).padStart(2, '0') }} / {{ String(sliders.length).padStart(2, '0') }}
                        </span>
                        <button type="button" class="hero-nav-prev flex h-9 w-9 items-center justify-center rounded-full border border-white/40 text-white transition hover:border-white" aria-label="Previous slide">
                            <ChevronDownIcon class="h-3.5 w-3.5 rotate-90" />
                        </button>
                        <button type="button" class="hero-nav-next flex h-9 w-9 items-center justify-center rounded-full border border-white/40 text-white transition hover:border-white" aria-label="Next slide">
                            <ChevronDownIcon class="h-3.5 w-3.5 -rotate-90" />
                        </button>
                    </div>
                </div>
            </Swiper>

            <!-- Feature strip -->
            <div class="relative z-10 mx-auto -mt-8 max-w-5xl px-6 md:-mt-10">
                <div class="grid grid-cols-1 divide-y divide-godiva-gold/25 border border-godiva-gold/25 bg-godiva-cream shadow-xl sm:grid-cols-3 sm:divide-x sm:divide-y-0 dark:bg-godiva-prefooter">
                    <div v-for="feature in features" :key="feature.title" class="flex items-center justify-center gap-4 px-6 py-6">
                        <component :is="feature.icon" class="h-8 w-8 shrink-0 text-godiva-gold" />
                        <div class="text-left">
                            <p class="text-sm font-bold uppercase tracking-wide text-godiva-charcoal dark:text-godiva-cream">{{ feature.title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ feature.subtitle }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Shop by Collection -->
        <section class="py-20 md:py-28">
            <div class="mx-auto max-w-screen-2xl px-6">
                <div class="mb-14 text-center">
                    <p class="font-script text-2xl text-godiva-gold">world of cococraft</p>
                    <h2 class="mt-2 font-serif text-4xl font-semibold text-godiva-charcoal dark:text-godiva-cream">Shop by Collection</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-gray-500 dark:text-gray-400">
                        Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits.
                    </p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(cat, index) in categories" :key="cat.id" class="group relative h-[420px] overflow-hidden bg-godiva-cream">
                        <img :src="cat.image || fallbackImage" :alt="cat.name" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 flex flex-col items-center gap-5 p-8 text-center text-white">
                            <h3 class="font-serif text-2xl font-semibold">{{ cat.name }}</h3>
                            <Link
                                :href="route('categories.show', cat.slug)"
                                class="px-8 py-3 text-[11px] font-bold uppercase tracking-[0.2em] transition"
                                :class="index === 1
                                    ? 'bg-godiva-gold text-godiva-charcoal hover:bg-white'
                                    : 'border border-white/70 text-white hover:bg-white hover:text-godiva-charcoal'"
                            >
                                View All Collections
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Signature Featured -->
        <section v-if="featuredItems.length" class="bg-godiva-cream/40 py-20 md:py-28 dark:bg-white/5">
            <div class="mx-auto max-w-screen-2xl px-6">
                <div class="mb-14 text-center">
                    <p class="font-script text-2xl text-godiva-gold">great cococraft taste</p>
                    <h2 class="mt-2 font-serif text-4xl font-semibold text-godiva-charcoal dark:text-godiva-cream">Signature Featured</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-gray-500 dark:text-gray-400">
                        Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits.
                    </p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <ProductCard
                        v-for="product in featuredItems"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="addToCart"
                        @toggle-wishlist="toggleWishlist"
                    />
                </div>
                <div class="mt-14 text-center">
                    <Link :href="route('products.index')" class="inline-block border border-godiva-charcoal px-10 py-4 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal transition hover:bg-godiva-charcoal hover:text-white dark:border-godiva-cream dark:text-godiva-cream dark:hover:bg-godiva-cream dark:hover:text-godiva-charcoal">
                        More Product
                    </Link>
                </div>
            </div>
        </section>

        <!-- New Arrivals -->
        <section v-if="newArrivals.length" class="py-20 md:py-28">
            <div class="mx-auto max-w-screen-2xl px-6">
                <div class="mb-14 text-center">
                    <p class="font-script text-2xl text-godiva-gold">pure nature, bold innovation</p>
                    <h2 class="mt-2 font-serif text-4xl font-semibold text-godiva-charcoal dark:text-godiva-cream">New Arrivals</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-gray-500 dark:text-gray-400">
                        Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits.
                    </p>
                </div>

                <div class="relative">
                    <Swiper
                        :modules="arrivalsModules"
                        :slides-per-view="1.3"
                        :space-between="24"
                        :navigation="{ prevEl: '.arrivals-nav-prev', nextEl: '.arrivals-nav-next' }"
                        :breakpoints="{
                            640: { slidesPerView: 2.2 },
                            1024: { slidesPerView: 4 },
                        }"
                    >
                        <SwiperSlide v-for="product in newArrivals" :key="product.id">
                            <ProductCard
                                :product="product"
                                @add-to-cart="addToCart"
                                @toggle-wishlist="toggleWishlist"
                            />
                        </SwiperSlide>
                    </Swiper>

                    <button type="button" class="arrivals-nav-prev absolute left-0 top-1/2 z-10 hidden h-11 w-11 -translate-x-5 -translate-y-1/2 items-center justify-center rounded-full border border-godiva-charcoal/20 bg-white text-godiva-charcoal shadow-md transition hover:border-godiva-gold hover:text-godiva-gold lg:flex dark:bg-godiva-prefooter dark:text-godiva-cream" aria-label="Previous">
                        <ChevronDownIcon class="h-4 w-4 rotate-90" />
                    </button>
                    <button type="button" class="arrivals-nav-next absolute right-0 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 translate-x-5 items-center justify-center rounded-full border border-godiva-charcoal/20 bg-white text-godiva-charcoal shadow-md transition hover:border-godiva-gold hover:text-godiva-gold lg:flex dark:bg-godiva-prefooter dark:text-godiva-cream" aria-label="Next">
                        <ChevronDownIcon class="h-4 w-4 -rotate-90" />
                    </button>
                </div>
            </div>
        </section>

        <!-- About Cococraft -->
        <section class="py-20 md:py-28">
            <div class="mx-auto grid max-w-screen-2xl gap-14 px-6 md:grid-cols-2 md:items-center md:gap-20">
                <div>
                    <p class="font-script text-2xl text-godiva-gold">the story behind</p>
                    <h2 class="mt-2 font-serif text-4xl font-semibold text-godiva-charcoal dark:text-godiva-cream">About Cococraft</h2>
                    <p class="mt-7 text-sm leading-8 text-gray-600 dark:text-gray-400">
                        Chocolate is not just a delicious treat; it is a unique expression of joy, celebration, and love. For the past decade, our chocolate company, "CocoCraft," has been winning hearts by crafting world-class premium chocolates. Our primary mission is to sweeten your every moment with chocolates made from entirely pure ingredients.
                    </p>
                    <p class="mt-5 text-sm leading-8 text-gray-600 dark:text-gray-400">
                        We source high-quality cocoa beans directly from the finest natural farms in West Africa and Latin America. Every chocolate bar is then crafted under the strict supervision of our expert chocolatiers, combining modern, hygienic technology. Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits. To maintain the highest quality, we do not use any artificial preservatives.
                    </p>
                    <div class="mt-9">
                        <Link :href="route('page.public', 'about-us')" class="inline-block border border-godiva-charcoal px-10 py-4 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal transition hover:bg-godiva-charcoal hover:text-white dark:border-godiva-cream dark:text-godiva-cream dark:hover:bg-godiva-cream dark:hover:text-godiva-charcoal">
                            Details
                        </Link>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-sm">
                    <img
                        src="/images/cococraft/about.jpg"
                        alt="About Coco Craft"
                        class="h-[480px] w-full rounded-t-[9999px] object-cover"
                    />
                    <div class="absolute -bottom-6 left-1/2 w-[85%] -translate-x-1/2 bg-godiva-gold px-6 py-5 text-center shadow-xl">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal">If need custom chocolate</p>
                        <p class="mt-1 font-serif text-xl font-semibold text-godiva-charcoal">CALL US {{ $page.props.webSettings?.phone || '+88 01886 831130' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="relative overflow-hidden bg-godiva-cream py-24 md:py-28 dark:bg-white/5">
            <svg viewBox="0 0 220 260" class="pointer-events-none absolute -left-6 top-10 hidden h-64 w-56 text-godiva-gold/40 md:block" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M20 230 C10 160 30 80 90 30 C130 -2 170 20 150 60 C130 100 70 90 60 130 C50 170 90 190 120 170" />
                <path d="M40 210 C60 160 55 110 90 80" />
                <path d="M70 150 C100 140 120 110 115 70" />
            </svg>

            <div class="mx-auto grid max-w-screen-2xl items-center gap-12 px-6 md:grid-cols-[1.2fr_1fr]">
                <div class="text-center md:text-left">
                    <p class="font-script text-2xl text-godiva-gold">Testimonials</p>
                    <h2 class="mt-2 font-serif text-4xl font-semibold text-godiva-charcoal">What Our Guest Are Saying</h2>
                    <ChatBubbleBottomCenterTextIcon class="mx-auto mt-8 h-9 w-9 text-godiva-gold md:mx-0" />
                    <p class="mx-auto mt-6 max-w-xl font-serif text-xl italic leading-relaxed text-godiva-charcoal/90 md:mx-0">
                        "{{ testimonial.quote }}"
                    </p>
                    <p class="mt-8 text-sm font-bold uppercase tracking-[0.2em] text-godiva-charcoal">{{ testimonial.name }}</p>
                    <p class="mt-1 text-xs uppercase tracking-[0.15em] text-gray-500">{{ testimonial.location }}</p>
                </div>
                <div class="mx-auto h-72 w-72 overflow-hidden rounded-full shadow-xl md:h-80 md:w-80">
                    <img src="/images/cococraft/testimonial.jpg" alt="Happy Coco Craft guest" class="h-full w-full object-cover" />
                </div>
            </div>

            <svg viewBox="0 0 1440 90" preserveAspectRatio="none" class="absolute -bottom-px left-0 h-16 w-full text-godiva-charcoal" aria-hidden="true">
                <path fill="currentColor" d="M0,58 C240,0 480,90 720,66 C960,42 1200,0 1440,50 L1440,90 L0,90 Z" />
            </svg>
        </section>
    </MainLayout>
</template>
