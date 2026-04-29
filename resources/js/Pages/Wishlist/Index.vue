<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { HeartIcon, ShoppingBagIcon, TrashIcon } from "@heroicons/vue/24/outline";

defineProps({
    items: { type: Array, default: () => [] },
});

const fallbackImage = "/images/godiva/product_default.png";

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}

function removeFromWishlist(productId) {
    router.post(route("wishlist.toggle", productId), {}, { preserveScroll: true });
}
</script>

<template>
    <MainLayout>
        <Head title="Wishlist | SweetChocholate" />

        <div class="bg-white">
            <section class="border-b border-[#eee4d8] bg-[#fcf8f3] py-16 text-center">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-red-500 shadow-sm">
                        <HeartIcon class="h-6 w-6 fill-current" />
                    </div>
                    <h1 class="mt-6 font-serif text-5xl italic tracking-tight text-godiva-charcoal">Wishlist</h1>
                    <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Saved sweets for later</p>
                </div>
            </section>

            <main class="mx-auto max-w-7xl px-6 py-12">
                <div v-if="items.length" class="grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="item in items" :key="item.id" class="group relative flex flex-col bg-white">
                        <div class="relative aspect-square overflow-hidden bg-white p-4">
                            <button
                                type="button"
                                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-red-500 shadow-sm transition hover:text-godiva-charcoal"
                                @click="removeFromWishlist(item.product.id)"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                            <img
                                :src="item.product.image || fallbackImage"
                                :alt="item.product.name"
                                class="h-full w-full object-contain transition duration-500 group-hover:scale-105"
                            />
                        </div>

                        <div class="flex flex-1 flex-col p-6 text-center">
                            <h3 class="font-serif text-lg uppercase leading-tight tracking-tight">
                                <Link :href="route('products.show', item.product.slug)" class="transition hover:text-godiva-gold">
                                    {{ item.product.name }}
                                </Link>
                            </h3>
                            <div class="mt-4 flex flex-col items-center justify-center gap-1">
                                <span v-if="item.product.compare_at_price > item.product.price" class="text-xs tracking-widest text-gray-400 line-through">
                                    {{ formatMoney(item.product.compare_at_price) }}
                                </span>
                                <span class="font-serif text-xl" :class="item.product.compare_at_price > item.product.price ? 'text-red-600' : 'text-godiva-charcoal'">
                                    {{ formatMoney(item.product.price) }}
                                </span>
                            </div>
                            <button
                                type="button"
                                class="mt-6 flex h-11 items-center justify-center gap-2 bg-godiva-charcoal px-4 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold disabled:bg-gray-300"
                                :disabled="item.product.stock <= 0"
                                @click="addToCart(item.product.id)"
                            >
                                <ShoppingBagIcon class="h-4 w-4" />
                                Add to Bag
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="mx-auto max-w-xl py-24 text-center">
                    <p class="font-serif text-3xl italic text-godiva-charcoal">Your wishlist is empty.</p>
                    <p class="mt-4 text-sm leading-7 text-gray-500">Save your favorite chocolates and come back when the craving calls.</p>
                    <Link :href="route('products.index')" class="mt-8 inline-flex bg-godiva-charcoal px-10 py-4 text-[11px] font-bold uppercase tracking-[0.2em] text-white transition hover:bg-godiva-gold">
                        Browse Chocolates
                    </Link>
                </div>
            </main>
        </div>
    </MainLayout>
</template>
