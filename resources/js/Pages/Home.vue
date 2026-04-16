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
    sliders: { type: Array, default: () => [] },
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
        <section class="relative overflow-hidden">
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{ delay: 6000 }"
                :pagination="{ clickable: true }"
                effect="fade"
                class="h-[80vh] md:h-[90vh]"
            >
                <SwiperSlide v-for="(slider, index) in sliders" :key="slider.id">
                    <div 
                        class="h-full w-full transition-colors duration-1000"
                        :style="{ backgroundColor: slider.bg_color || '#FBE0E3', color: slider.text_color || '#1C1C1C' }"
                    >
                        <div class="mx-auto flex h-full max-w-7xl flex-col items-center px-6 relative">
                            <!-- Background Text (Decorative) -->
                            <div 
                                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 select-none opacity-[0.03] pointer-events-none"
                                :style="{ color: slider.text_color || '#1C1C1C' }"
                            >
                                <span class="font-serif text-[20vw] whitespace-nowrap leading-none uppercase tracking-tighter">GODIVA</span>
                            </div>

                            <div 
                                class="z-10 flex h-full w-full flex-col items-center justify-center py-16 md:py-0 gap-12 md:gap-24 md:flex-row"
                                :class="index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'"
                            >
                                <div class="flex-1 text-left flex flex-col justify-center max-w-2xl">
                                    <div class="overflow-hidden mb-6">
                                        <p class="slide-up text-[11px] font-bold uppercase tracking-[0.4em] opacity-80" 
                                           :style="{ borderLeft: `2px solid ${slider.text_color === '#FFFFFF' ? '#B99D4B' : slider.text_color}`, animationDelay: '200ms' }">
                                            <span class="ml-4">{{ slider.subtitle }}</span>
                                        </p>
                                    </div>
                                    
                                    <h1 
                                        class="slide-up font-serif font-light leading-[0.85] uppercase tracking-tighter mb-8"
                                        :class="index === 0 ? 'text-6xl md:text-[8rem]' : 'text-5xl md:text-[6rem]'"
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
                                                borderColor: slider.text_color === '#FFFFFF' ? 'rgba(255,255,255,0.3)' : 'rgba(28,28,28,0.2)',
                                                color: slider.text_color
                                            }"
                                        >
                                            <span class="relative z-10">{{ slider.button_text }}</span>
                                            <ArrowRightIcon class="relative z-10 h-4 w-4 transition-transform group-hover:translate-x-2" />
                                            <div 
                                                class="absolute inset-0 -translate-x-full transition-transform duration-500 group-hover:translate-x-0"
                                                :style="{ backgroundColor: slider.text_color === '#FFFFFF' ? '#FFFFFF' : '#1C1C1C' }"
                                            ></div>
                                            <span 
                                                class="absolute inset-0 z-1 flex -translate-x-full items-center gap-4 px-12 py-5 text-[10px] font-bold uppercase tracking-[0.2em] transition-transform duration-500 group-hover:translate-x-0 group-hover:text-gold"
                                                :style="{ color: slider.text_color === '#FFFFFF' ? '#1C1C1C' : '#FFFFFF' }"
                                            >
                                                {{ slider.button_text }} <ArrowRightIcon class="h-4 w-4" />
                                            </span>
                                        </Link>
                                    </div>
                                </div>

                                <div 
                                    class="flex-1 relative h-full w-full flex items-center justify-center p-8 md:p-0 md:min-w-[40%]"
                                >
                                    <div class="relative w-full aspect-square flex items-center justify-center scale-x-[-1] md:scale-x-[1]">
                                        <!-- Decorative Circle -->
                                        <div 
                                            class="absolute inset-0 rounded-full border opacity-10 animate-pulse-slow scale-75 md:scale-100"
                                            :style="{ borderColor: slider.text_color }"
                                        ></div>
                                        
                                        <img
                                            :src="slider.image"
                                            :alt="slider.subtitle"
                                            class="hero-img h-[100%] md:h-[120%] w-auto object-contain z-20 transition-all duration-1000"
                                            :style="{ 
                                                filter: slider.bg_color === '#1C1C1C' ? 'drop-shadow(0 25px 50px rgba(255,255,255,0.1))' : 'drop-shadow(0 25px 50px rgba(0,0,0,0.15))'
                                            }"
                                        />
                                    </div>
                                </div>
                            </div>
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
    animation: float 6s ease-in-out infinite;
    opacity: 1;
}

.hero-img {
    opacity: 0;
}

@keyframes float {
	0% { transform: translateY(0px) rotate(0deg); }
	50% { transform: translateY(-20px) rotate(2deg); }
	100% { transform: translateY(0px) rotate(0deg); }
}

.animate-pulse-slow {
    animation: pulseSlow 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulseSlow {
    0%, 100% { opacity: 0.1; transform: scale(1); }
    50% { opacity: 0.2; transform: scale(1.05); }
}

/* Swiper Styling */
.swiper-pagination-bullet {
    background: transparent !important;
    border: 1px solid currentColor !important;
    opacity: 0.5 !important;
    width: 10px !important;
    height: 10px !important;
    transition: all 0.3s ease !important;
}

.swiper-pagination-bullet-active {
    opacity: 1 !important;
    background: currentColor !important;
    transform: scale(1.2) !important;
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
