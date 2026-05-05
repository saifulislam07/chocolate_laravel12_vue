<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import { ChevronRightIcon, PlusIcon, MinusIcon } from "@heroicons/vue/20/solid";
import { HeartIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
});

const quantity = ref(1);
const selectedImage = ref(props.product.images?.[0] || "/images/godiva/product_default.png");

const moneyFormatter = new Intl.NumberFormat("en-BD", { 
    style: "currency", 
    currency: "BDT",
    minimumFractionDigits: 0,
});
const price = computed(() => Number(props.product.price || 0));
const comparePrice = computed(() => Number(props.product.compare_at_price || 0));

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function addToCart() {
    router.post(route("cart.store"), { 
        product_id: props.product.id, 
        quantity: quantity.value 
    }, { preserveScroll: true });
}

function toggleWishlist(productId = props.product.id) {
    router.post(route("wishlist.toggle", productId), {}, { preserveScroll: true });
}

function increment() {
    if (quantity.value < props.product.stock) quantity.value++;
}

function decrement() {
    if (quantity.value > 1) quantity.value--;
}
</script>

<template>
    <MainLayout>
        <Head :title="product.name" />

        <div class="bg-white">
            <!-- Breadcrumbs -->
            <nav class="mx-auto max-w-7xl px-6 py-4 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                <Link href="/" class="hover:text-godiva-gold">Home</Link>
                <ChevronRightIcon class="h-3 w-3" />
                <span class="text-godiva-charcoal">{{ product.category || 'Chocolate' }}</span>
                <ChevronRightIcon class="h-3 w-3" />
                <span class="text-godiva-gold">{{ product.name }}</span>
            </nav>

            <main class="mx-auto max-w-7xl px-6 py-12">
                <div class="grid gap-16 lg:grid-cols-2">
                    <!-- Image Gallery -->
                    <section class="space-y-4">
                        <div class="aspect-square w-full overflow-hidden bg-white border border-gray-100 flex items-center justify-center p-8">
                            <img :src="selectedImage" :alt="product.name" class="h-full w-full object-contain transition duration-500" />
                        </div>
                        <div class="grid grid-cols-4 gap-4" v-if="product.images?.length > 1">
                            <button
                                v-for="image in product.images"
                                :key="image"
                                type="button"
                                class="aspect-square border flex items-center justify-center p-2 rounded-sm transition"
                                :class="selectedImage === image ? 'border-godiva-gold' : 'border-gray-100'"
                                @click="selectedImage = image"
                            >
                                <img :src="image" :alt="product.name" class="h-full w-full object-contain" />
                            </button>
                        </div>
                    </section>

                    <!-- Product Details -->
                    <section class="flex flex-col">
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-gold">{{ product.category || "Signature Collection" }}</p>
                        <h1 class="mt-4 font-serif text-4xl lg:text-5xl italic tracking-tight text-godiva-charcoal">{{ product.name }}</h1>
                        
                        <div class="mt-8 flex items-baseline gap-4 border-b border-gray-100 pb-8">
                            <p class="text-3xl font-serif text-godiva-charcoal">{{ formatMoney(price) }}</p>
                            <p v-if="comparePrice > price" class="text-lg text-gray-400 line-through tracking-wider">{{ formatMoney(comparePrice) }}</p>
                        </div>

                        <div class="mt-8 space-y-6">
                            <p class="text-sm leading-8 text-gray-600 tracking-wide">{{ product.description }}</p>
                            <div v-if="product.is_bundle && product.bundle_note" class="border-l-2 border-godiva-gold bg-godiva-cream/30 px-5 py-4 text-sm leading-7 text-godiva-charcoal">
                                {{ product.bundle_note }}
                            </div>
                            <div v-if="product.is_bundle && product.bundle_items?.length" class="border border-gray-100 p-5">
                                <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.25em] text-godiva-gold">Included in this bundle</p>
                                <div class="space-y-3">
                                    <Link
                                        v-for="item in product.bundle_items"
                                        :key="item.id"
                                        :href="route('products.show', item.slug)"
                                        class="flex items-center gap-4 transition hover:text-godiva-gold"
                                    >
                                        <img :src="item.image || '/images/godiva/product_default.png'" :alt="item.name" class="h-12 w-12 object-contain bg-white">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold">{{ item.name }}</p>
                                            <p class="text-xs text-gray-500">{{ item.quantity }} x {{ formatMoney(item.price) }}</p>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                                <span class="text-gray-400">Availability:</span>
                                <span :class="product.stock > 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>
                        </div>

                        <!-- Add to Cart Controls -->
                        <div class="mt-12 flex flex-col sm:flex-row items-center gap-4">
                            <div class="flex h-12 w-full sm:w-32 items-center justify-between border border-gray-200 px-4">
                                <button @click="decrement" class="text-gray-500 hover:text-godiva-gold">
                                    <MinusIcon class="h-4 w-4" />
                                </button>
                                <span class="text-sm font-bold">{{ quantity }}</span>
                                <button @click="increment" class="text-gray-500 hover:text-godiva-gold">
                                    <PlusIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <button
                                type="button"
                                class="h-12 w-full flex-1 bg-godiva-charcoal text-[11px] font-bold uppercase tracking-[0.2em] text-white transition hover:bg-black disabled:bg-gray-300"
                                :disabled="product.stock <= 0"
                                @click="addToCart"
                            >
                                Add to Bag
                            </button>
                            <button
                                type="button"
                                class="flex h-12 w-full items-center justify-center gap-2 border border-gray-200 px-6 text-[11px] font-bold uppercase tracking-[0.2em] transition hover:border-red-200 hover:text-red-500 sm:w-auto"
                                :class="{ 'border-red-200 text-red-500': product.is_wishlisted }"
                                @click="toggleWishlist()"
                            >
                                <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                                Wishlist
                            </button>
                        </div>

                        <!-- Brand Promises -->
                        <div class="mt-12 grid grid-cols-2 gap-6 border-t border-gray-100 pt-10 text-[10px] font-bold uppercase tracking-widest text-godiva-charcoal/60">
                            <div class="flex items-center gap-3">
                                <div class="h-1 w-1 rounded-full bg-godiva-gold"></div>
                                Belgian Heritage
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-1 w-1 rounded-full bg-godiva-gold"></div>
                                Premium Ingredients
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Related Products -->
                <section v-if="relatedProducts.length" class="mt-24 border-t border-gray-100 pt-20">
                    <h2 class="font-serif text-3xl italic text-center mb-12">You may also like</h2>
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <article v-for="item in relatedProducts" :key="item.id" class="group relative flex flex-col bg-white">
                            <div class="relative aspect-square overflow-hidden bg-white p-4">
                                <img :src="item.image || '/images/godiva/product_default.png'" :alt="item.name" class="h-full w-full object-contain transition duration-500 group-hover:scale-105" />
                            </div>
                            <div class="flex flex-1 flex-col p-6 text-center">
                                <h3 class="font-serif text-lg tracking-tight uppercase">
                                    <Link :href="route('products.show', item.slug)" class="hover:text-godiva-gold transition">{{ item.name }}</Link>
                                </h3>
                                <p class="mt-4 font-serif text-xl">{{ formatMoney(item.price) }}</p>
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </MainLayout>
</template>
