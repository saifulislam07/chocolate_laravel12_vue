<script setup>
import { Link } from "@inertiajs/vue3";
import { HeartIcon, ShoppingBagIcon } from "@heroicons/vue/24/outline";

defineProps({
    product: { type: Object, required: true },
});

const emit = defineEmits(["add-to-cart", "toggle-wishlist"]);

const fallbackImage = "/images/godiva/product_default.png";

function formatMoney(amount) {
    return `${Number(amount || 0).toFixed(2)}৳`;
}
</script>

<template>
    <article class="group relative flex flex-col bg-white dark:bg-godiva-prefooter">
        <div class="relative aspect-square overflow-hidden bg-godiva-cream/40">
            <button
                type="button"
                class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-godiva-charcoal shadow-sm transition hover:text-red-500 dark:bg-godiva-charcoal/90 dark:text-godiva-cream"
                :class="{ 'text-red-500': product.is_wishlisted }"
                @click="emit('toggle-wishlist', product.id)"
                aria-label="Toggle wishlist"
            >
                <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
            </button>
            <Link :href="route('products.show', product.slug)">
                <img :src="product.image || fallbackImage" :alt="product.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            </Link>
        </div>
        <div class="flex flex-1 flex-col items-center gap-3 px-4 py-6 text-center">
            <h3 class="font-serif text-base font-medium leading-snug text-godiva-charcoal dark:text-godiva-cream">
                <Link :href="route('products.show', product.slug)" class="transition hover:text-godiva-gold">{{ product.name }}</Link>
            </h3>
            <div class="flex items-center justify-center gap-4">
                <div class="flex items-baseline gap-2">
                    <span v-if="product.compare_at_price > product.price" class="text-xs text-gray-400 line-through">{{ formatMoney(product.compare_at_price) }}</span>
                    <span class="text-lg font-bold text-godiva-gold-dark">{{ formatMoney(product.price) }}</span>
                </div>
                <button
                    type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-godiva-gold text-white transition hover:bg-godiva-gold-dark"
                    @click="emit('add-to-cart', product.id)"
                    aria-label="Add to cart"
                >
                    <ShoppingBagIcon class="h-4 w-4" />
                </button>
            </div>
        </div>
    </article>
</template>
