<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import { 
    ChevronDownIcon,
    ArrowRightIcon
} from "@heroicons/vue/24/outline";

// Import Swiper components
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Autoplay, EffectFade } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

const modules = [Pagination, Autoplay, EffectFade];

defineProps({
    featuredItems: { type: Array, default: () => [] },
    newArrivals: { type: Array, default: () => [] },
    discountItems: { type: Array, default: () => [] },
});

function getDiscountPercent(price, comparePrice) {
    if (!comparePrice || comparePrice <= price) return null;
    return Math.round(((comparePrice - price) / comparePrice) * 100);
}

const categories = [
    {
        name: "Artisan Truffles",
        description: "Experience our signature velvety ganache in every bite.",
        image: "/images/godiva/truffles.png",
    },
    {
        name: "Gift Boxes",
        description: "Elegant collections for every cherished moment.",
        image: "/images/godiva/gift_boxes.png",
    },
    {
        name: "Seasonal Joy",
        description: "Celebrate the season with limited-edition creations.",
        image: "/images/godiva/seasonal.png",
    },
];

const fallbackImage = "/images/godiva/product_default.png";

const moneyFormatter = new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
});

function formatMoney(amount) {
    return moneyFormatter.format(Number(amount || 0));
}

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}
</script>

<template>
    <MainLayout>
        <Head title="SweetChocholate | Premium Belgian Chocolate" />

        <!-- Hero Slider Section -->
        <section class="relative bg-godiva-pink overflow-hidden">
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{ delay: 5000 }"
                :pagination="{ clickable: true }"
                effect="fade"
                class="h-[65vh] md:h-[80vh]"
            >
                <SwiperSlide>
                    <div class="mx-auto flex h-full max-w-7xl flex-col md:flex-row items-center px-6">
                        <div class="flex-1 text-left py-12 md:py-0">
                            <p class="text-[11px] font-bold uppercase tracking-[0.4em] text-godiva-charcoal/60 mb-6">New Centennial Collection</p>
                            <h1 class="font-serif text-5xl font-light leading-[0.9] text-godiva-charcoal md:text-[8rem] uppercase tracking-tighter">
                                Legacy made in <br/> <span class="font-normal italic">chocolate</span>
                            </h1>
                            <p class="mt-10 max-w-md text-xs leading-loose tracking-widest text-godiva-charcoal/70">
                                Since 1926, our passion for chocolate has been an endless pursuit of savours and sensations. Our Centennial Pralines are both sweetly nostalgic and at the cutting edge of chocolate design and innovation.
                            </p>
                            <div class="mt-12">
                                <a
                                    href="#"
                                    class="inline-block bg-godiva-charcoal px-12 py-5 text-[10px] font-bold uppercase tracking-[0.2em] text-white transition hover:bg-black"
                                >
                                    Discover the Collection
                                </a>
                            </div>
                        </div>
                        <div class="flex-1 relative h-full w-full flex items-center justify-center translate-x-10 translate-y-10">
                            <img
                                src="/images/godiva/hero_stack.png"
                                alt="Legacy Chocolate Collection"
                                class="h-[120%] w-auto object-contain drop-shadow-[-20px_20px_40px_rgba(0,0,0,0.1)]"
                            />
                        </div>
                    </div>
                </SwiperSlide>
                <SwiperSlide>
                    <div class="mx-auto flex h-full max-w-7xl flex-col md:flex-row-reverse items-center px-6">
                        <div class="flex-1 text-left py-12 md:py-0">
                            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-charcoal mb-4">Exquisite Gifting</p>
                            <h1 class="font-serif text-4xl font-light leading-tight text-godiva-charcoal sm:text-7xl uppercase">
                                Art of <br/> Belgian Gifting
                            </h1>
                            <p class="mt-6 max-w-lg text-sm leading-relaxed text-godiva-charcoal/80">
                                Delight your senses with our premium golden gift collections, crafted with the finest ingredients and century-old traditions.
                            </p>
                            <div class="mt-10">
                                <a
                                    href="#"
                                    class="inline-block bg-godiva-charcoal px-10 py-4 text-[11px] font-bold uppercase tracking-widest text-white transition hover:bg-black"
                                >
                                    Shop Gift Boxes
                                </a>
                            </div>
                        </div>
                        <div class="flex-1 relative h-full w-full flex items-center justify-center">
                            <img
                                src="/images/godiva/hero2.png"
                                alt="Golden Gift Box"
                                class="max-h-[80%] w-auto object-contain drop-shadow-2xl"
                            />
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>
        </section>

        <!-- Shop by Category -->
        <section class="border-y border-gray-100 py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 text-center">
                    <h2 class="font-serif text-[11px] font-bold uppercase tracking-[0.4em] text-godiva-gold">World of Godiva</h2>
                    <p class="mt-4 font-serif text-4xl italic tracking-wide">Shop by Collection</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-3">
                    <div v-for="cat in categories" :key="cat.name" class="group cursor-pointer overflow-hidden">
                        <div class="relative aspect-h-4 aspect-w-3 overflow-hidden bg-gray-100">
                            <img :src="cat.image" :alt="cat.name" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" />
                        </div>
                        <div class="mt-6 text-center">
                            <h3 class="text-xs font-bold uppercase tracking-widest transition group-hover:text-godiva-gold">{{ cat.name }}</h3>
                            <p class="mt-2 text-[10px] text-gray-500 uppercase tracking-widest">{{ cat.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- New Arrivals Section -->
        <section v-if="newArrivals.length" class="mx-auto max-w-7xl px-6 py-20">
            <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6">
                <h2 class="font-serif text-3xl italic">New Arrivals</h2>
                <a href="#" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">
                    Discover New
                </a>
            </div>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="product in newArrivals" :key="product.id" class="group relative flex flex-col bg-white">
                    <div class="relative aspect-square overflow-hidden bg-white p-4">
                        <span class="absolute top-4 left-4 z-10 bg-godiva-pink px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal">New</span>
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
        <section v-if="featuredItems.length" class="bg-godiva-cream/30 py-20">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6">
                    <h2 class="font-serif text-3xl italic">Signature Featured</h2>
                    <a href="#" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition text-left-sm">View Collections</a>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="product in featuredItems" :key="product.id" class="group relative flex flex-col bg-white">
                        <div class="relative aspect-square overflow-hidden bg-white p-4">
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
            <div class="mb-12 flex items-center justify-between border-b border-gray-200 pb-6">
                <h2 class="font-serif text-3xl italic">Sweet Offers & Sale</h2>
                <a href="#" class="text-[11px] font-bold uppercase tracking-widest hover:text-godiva-gold transition">Shop All Sale</a>
            </div>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="product in discountItems" :key="product.id" class="group relative flex flex-col bg-white">
                    <div class="relative aspect-square overflow-hidden bg-white p-4">
                        <span class="absolute top-4 left-4 z-10 bg-red-600 px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-white">Save {{ getDiscountPercent(product.price, product.compare_at_price) }}%</span>
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
            <form class="mx-auto mt-12 flex max-w-md items-center border-b border-godiva-charcoal pb-4">
                <input type="email" placeholder="Your Email Address" class="w-full bg-transparent px-2 py-2 text-sm focus:outline-none" />
                <button type="button" class="text-godiva-charcoal transition hover:text-godiva-gold">
                    <ArrowRightIcon class="h-6 w-6" />
                </button>
            </form>
        </section>
    </MainLayout>
</template>

<style>
/* Custom Pagination Styles to match Godiva */
.swiper-pagination-bullet {
    background: #000 !important;
    opacity: 0.3 !important;
    width: 8px !important;
    height: 8px !important;
}
.swiper-pagination-bullet-active {
    opacity: 1 !important;
    background: #000 !important;
}

.aspect-h-4 {
    padding-bottom: 133.333333%;
}
.aspect-w-3 {
    position: relative;
}
.aspect-h-4 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
</style>
