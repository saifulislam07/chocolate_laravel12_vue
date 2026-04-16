<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { XMarkIcon, PlusIcon, MinusIcon, ArrowRightIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    subtotal: {
        type: Number,
        default: 0,
    },
});

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(amount) {
    return moneyFormatter.format(Number(amount || 0));
}

function updateQuantity(item, quantity) {
    if (quantity < 1 || quantity > props.product?.stock || quantity === item.quantity) {
        return;
    }

    router.patch(route("cart.update", item.id), { quantity }, { preserveScroll: true });
}

function increment(item) {
    updateQuantity(item, item.quantity + 1);
}

function decrement(item) {
    if (item.quantity > 1) {
        updateQuantity(item, item.quantity - 1);
    }
}

function removeItem(itemId) {
    router.delete(route("cart.destroy", itemId), { preserveScroll: true });
}
</script>

<template>
    <MainLayout>
        <Head title="Your Shopping Bag | SweetChocholate" />

        <div class="bg-white py-12 sm:py-20 text-godiva-charcoal">
            <main class="mx-auto max-w-7xl px-6">
                <div class="flex items-baseline justify-between border-b border-gray-100 pb-10">
                    <h1 class="font-serif text-4xl italic tracking-tight">Your Shopping Bag</h1>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">{{ items.length }} Items</p>
                </div>

                <div v-if="!props.items.length" class="mt-20 flex flex-col items-center justify-center text-center">
                    <div class="h-24 w-24 rounded-full bg-godiva-pink/20 flex items-center justify-center mb-6">
                        <ShoppingBagIcon class="h-10 w-10 text-godiva-gold" />
                    </div>
                    <p class="font-serif text-2xl italic">Your bag is currently empty.</p>
                    <Link
                        :href="route('home')"
                        class="mt-8 inline-block bg-godiva-charcoal px-12 py-4 text-[11px] font-bold uppercase tracking-widest text-white transition hover:bg-black"
                    >
                        Continue Shopping
                    </Link>
                </div>

                <div v-else class="mt-12 grid gap-16 lg:grid-cols-[1fr_350px]">
                    <!-- Items List -->
                    <section class="space-y-10">
                        <article
                            v-for="item in props.items"
                            :key="item.id"
                            class="flex flex-col sm:flex-row gap-8 pb-10 border-b border-gray-50 last:border-0"
                        >
                            <div class="h-40 w-full sm:w-40 flex-shrink-0 bg-white border border-gray-100 p-4">
                                <img
                                    :src="item.image || '/images/godiva/product_default.png'"
                                    :alt="item.name"
                                    class="h-full w-full object-contain"
                                />
                            </div>

                            <div class="flex flex-1 flex-col justify-between">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="font-serif text-xl tracking-tight uppercase">{{ item.name }}</h2>
                                        <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">Artisan Collection</p>
                                    </div>
                                    <button
                                        type="button"
                                        class="group text-gray-400 hover:text-godiva-gold transition"
                                        @click="removeItem(item.id)"
                                    >
                                        <XMarkIcon class="h-5 w-5" />
                                    </button>
                                </div>

                                <div class="mt-8 flex flex-wrap items-center justify-between gap-6">
                                    <!-- Quantity Selector -->
                                    <div class="flex h-10 w-32 items-center justify-between border border-gray-200 px-3">
                                        <button @click="decrement(item)" class="text-gray-400 hover:text-godiva-gold">
                                            <MinusIcon class="h-3 w-3" />
                                        </button>
                                        <span class="text-xs font-bold">{{ item.quantity }}</span>
                                        <button @click="increment(item)" class="text-gray-400 hover:text-godiva-gold">
                                            <PlusIcon class="h-3 w-3" />
                                        </button>
                                    </div>

                                    <div class="flex items-baseline gap-6 text-right">
                                        <p class="text-xs text-gray-400 tracking-widest">{{ formatMoney(item.price) }} / ea</p>
                                        <p class="font-serif text-xl">{{ formatMoney(item.line_total) }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </section>

                    <!-- Summary Sidebar -->
                    <aside class="h-fit space-y-8">
                        <div class="bg-gray-50 p-8 rounded-sm">
                            <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] mb-8">Order Summary</h2>
                            <div class="space-y-4 text-xs font-bold uppercase tracking-widest">
                                <div class="flex items-center justify-between text-gray-500">
                                    <span>Subtotal</span>
                                    <span>{{ formatMoney(props.subtotal) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-500">
                                    <span>Shipping</span>
                                    <span class="text-[9px]">Calculated at checkout</span>
                                </div>
                                <div class="pt-6 border-t border-gray-200 flex items-center justify-between text-base">
                                    <span>Total</span>
                                    <span class="font-serif text-2xl italic">{{ formatMoney(props.subtotal) }}</span>
                                </div>
                            </div>

                            <Link
                                :href="route('checkout.index')"
                                class="mt-10 flex w-full items-center justify-center gap-3 bg-godiva-charcoal py-4 text-[11px] font-bold uppercase tracking-widest text-white transition hover:bg-black"
                            >
                                Checkout Securely
                                <ArrowRightIcon class="h-4 w-4" />
                            </Link>

                            <p class="mt-6 text-[9px] text-center text-gray-400 uppercase tracking-widest">
                                Free shipping on orders over $60
                            </p>
                        </div>

                        <!-- Mini Trust Section -->
                        <div class="grid grid-cols-1 gap-4 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal/40">
                             <div class="flex items-center gap-3 bg-white border border-gray-100 p-4">
                                <div class="h-2 w-2 rounded-full bg-godiva-gold"></div>
                                Secure Encryption
                             </div>
                             <div class="flex items-center gap-3 bg-white border border-gray-100 p-4">
                                <div class="h-2 w-2 rounded-full bg-godiva-gold"></div>
                                100% Satisfaction Guarantee
                             </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </MainLayout>
</template>

