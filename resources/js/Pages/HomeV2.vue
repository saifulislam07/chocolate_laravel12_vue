<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted } from "vue";
import {
    HeartIcon,
    ShoppingBagIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/outline";
import MainLayout from "@/Layouts/MainLayout.vue";
import { menuHref } from "@/composables/useMenuHref";

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    featuredItems: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
    aboutContent: { type: String, default: "" },
    testimonials: { type: Array, default: () => [] },
});

const page = usePage();
const settings = computed(() => page.props.webSettings || {});

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

const fallbackAboutContent = `
    <p>Chocolate is not just a delicious treat; it is a unique expression of joy, celebration, and love. For the past decade, our chocolate company, "CocoCraft," has been winning hearts by crafting world-class premium chocolates. Our primary mission is to sweeten your every moment with chocolates made from entirely pure ingredients.</p>
    <p>We source high-quality cocoa beans directly from the finest natural farms in West Africa and Latin America. Every chocolate bar is then crafted under the strict supervision of our expert chocolatiers, combining modern, hygienic technology. Our special collection features rich dark chocolate, creamy milk chocolate, and mouth-watering chocolates infused with various nuts and fruits. To maintain the highest quality, we do not use any artificial preservatives.</p>
    <p>Beyond business success, we are deeply committed to eco-friendly packaging and fair trade practices, which help improve the livelihoods of marginalized cocoa farmers. Whether it is a birthday, wedding, or any festive occasion, our customized gift boxes are the perfect choice for gifting something special to your loved ones. Join us today to experience the true, royal taste of authentic chocolate.</p>
`;

// ---- Hero banner (admin-managed via Admin > Sliders) ----
const heroFallback = {
    id: "fallback",
    title: "",
    description: "",
    image: "/images/cococraft-v2/banner.jpg",
    button_text: "",
    button_link: "",
};
const heroSlides = computed(() => (props.sliders.length ? props.sliders : [heroFallback]));
const activeSlide = ref(0);
let heroTimer = null;
function goToSlide(i) {
    activeSlide.value = i;
}
onMounted(() => {
    if (heroSlides.value.length > 1) {
        heroTimer = setInterval(() => {
            activeSlide.value = (activeSlide.value + 1) % heroSlides.value.length;
        }, 6000);
    }
});
onUnmounted(() => {
    if (heroTimer) clearInterval(heroTimer);
});

// ---- Testimonials (admin-managed via Admin > Testimonials) ----
const testimonialFallback = [
    {
        id: "fallback",
        quote: "Best chocolate I've ever had!!!! It is one of the best sweet treat experiences that you will ever have. The problem is, you will never...",
        customer_name: "Jannatul Khatun",
        location: "Mohammadpur, Dhaka",
    },
];
const testimonialSlides = computed(() => (props.testimonials.length ? props.testimonials : testimonialFallback));
const activeTestimonial = ref(0);
let testimonialTimer = null;
onMounted(() => {
    if (testimonialSlides.value.length > 1) {
        testimonialTimer = setInterval(() => {
            activeTestimonial.value = (activeTestimonial.value + 1) % testimonialSlides.value.length;
        }, 7000);
    }
});
onUnmounted(() => {
    if (testimonialTimer) clearInterval(testimonialTimer);
});
</script>

<template>
    <MainLayout>
        <!-- ================= HERO BANNER (admin-managed: Admin > Sliders) ================= -->
        <section class="relative w-full overflow-hidden">
            <div class="relative w-full aspect-[1600/756]">
                <div
                    v-for="(slide, i) in heroSlides"
                    :key="slide.id"
                    class="absolute inset-0 transition-opacity duration-700"
                    :class="i === activeSlide ? 'opacity-100' : 'pointer-events-none opacity-0'"
                >
                    <img :src="slide.image || '/images/cococraft-v2/banner.jpg'" :alt="slide.subtitle || slide.title || (settings.site_name || 'CocoCraft') + ' — A Bite of Love'" class="h-full w-full object-cover" />
                    <div v-if="slide.title || slide.description" class="absolute inset-0 flex items-end bg-gradient-to-t from-black/65 via-black/10 to-transparent">
                        <div class="mx-auto w-full max-w-[1350px] px-5 pb-10 md:px-8 md:pb-16 lg:px-[126px]">
                            <div class="max-w-lg text-white">
                                <h1 v-if="slide.title" class="font-heading text-4xl uppercase leading-[1.05] md:text-6xl" v-html="slide.title"></h1>
                                <p v-if="slide.description" class="mt-4 max-w-md text-sm leading-relaxed text-white/85">{{ slide.description }}</p>
                                <component
                                    :is="menuHref(slide.button_link).startsWith('http') ? 'a' : Link"
                                    v-if="slide.button_text"
                                    :href="slide.button_link || route('products.index')"
                                    class="mt-6 inline-flex items-center bg-cocov-gold px-8 py-3 text-[13px] font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a]"
                                >{{ slide.button_text }}</component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="heroSlides.length > 1" class="absolute inset-x-0 bottom-4 z-10 flex justify-center gap-2">
                <button
                    v-for="(slide, i) in heroSlides"
                    :key="'dot-' + slide.id"
                    type="button"
                    class="h-2 rounded-full transition-all"
                    :class="i === activeSlide ? 'w-6 bg-cocov-gold' : 'w-2 bg-white/70 hover:bg-white'"
                    :aria-label="`Go to slide ${i + 1}`"
                    @click="goToSlide(i)"
                ></button>
            </div>
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
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 lg:gap-[35px]">
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
                    <div class="mt-6 space-y-4 text-justify text-[15px] leading-[25px] text-black/85" v-html="aboutContent || fallbackAboutContent"></div>
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
                <div class="mx-auto max-w-[830px] text-center lg:mr-[440px]">
                    <p class="font-corinthia text-[46px] leading-none text-cocov-gold md:text-[50px]">Testimonials</p>
                    <h2 class="mt-1 font-heading text-3xl uppercase leading-tight text-cocov-text md:text-[48px]">What Our Guest Are Saying</h2>
                    <svg viewBox="0 0 48 40" class="mx-auto mt-8 h-10 w-12 text-cocov-gold" fill="none" aria-hidden="true">
                        <rect x="1.5" y="1.5" width="45" height="29" rx="8" stroke="#484747" stroke-width="2" />
                        <path d="M14 34 8 39v-8" fill="#f7f2ec" stroke="#484747" stroke-width="2" stroke-linejoin="round" />
                        <path d="M9 12h9v9H9z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        <path d="M24 12h9v9h-9z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                    <Transition name="fade" mode="out-in">
                        <div :key="activeTestimonial">
                            <p class="mx-auto mt-6 max-w-[820px] text-[20px] font-medium leading-[36px] text-cocov-text">
                                &ldquo;{{ testimonialSlides[activeTestimonial].quote }}&rdquo;
                            </p>
                            <p class="mt-8 text-[20px] font-bold uppercase leading-[32px] text-cocov-text">{{ testimonialSlides[activeTestimonial].customer_name }}</p>
                            <p v-if="testimonialSlides[activeTestimonial].location" class="text-[16px] leading-[32px] text-cocov-text">{{ testimonialSlides[activeTestimonial].location }}</p>
                        </div>
                    </Transition>
                    <div v-if="testimonialSlides.length > 1" class="mt-6 flex justify-center gap-2">
                        <button
                            v-for="(t, i) in testimonialSlides"
                            :key="'tdot-' + t.id"
                            type="button"
                            class="h-2 rounded-full transition-all"
                            :class="i === activeTestimonial ? 'w-6 bg-cocov-gold' : 'w-2 bg-cocov-text/20 hover:bg-cocov-text/40'"
                            :aria-label="`Show testimonial ${i + 1}`"
                            @click="activeTestimonial = i"
                        ></button>
                    </div>
                </div>
            </div>

            <!-- woman on mobile / tablet -->
            <img src="/images/cococraft-v2/testi_woman.jpg" alt="" class="mx-auto block w-full max-w-[420px] object-cover lg:hidden" />
        </section>

    </MainLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.35s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
