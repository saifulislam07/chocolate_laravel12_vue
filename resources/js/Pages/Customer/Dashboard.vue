<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { HeartIcon, ShoppingBagIcon, SparklesIcon, UserCircleIcon } from "@heroicons/vue/24/outline";

defineProps({
    stats: { type: Object, required: true },
    recentOrders: { type: Array, default: () => [] },
    wishlistItems: { type: Array, default: () => [] },
});

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}
</script>

<template>
    <MainLayout>
        <Head title="Customer Dashboard | Coco Craft" />

        <div class="bg-white">
            <section class="border-b border-[#eee4d8] bg-[#fcf8f3] py-14">
                <div class="mx-auto max-w-screen-2xl px-6">
                    <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-cocov-gold">My Account</p>
                    <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h1 class="font-heading text-5xl uppercase tracking-tight text-cocov-text">Customer Dashboard</h1>
                            <p class="mt-4 text-sm leading-7 text-cocov-text/60">Track orders, revisit favorites, and keep your chocolate plans close.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('products.index')" class="inline-flex rounded-[3px] bg-cocov-gold px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-white transition hover:bg-[#e0851a]">
                                Continue Shopping
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="inline-flex rounded-[3px] border border-cocov-text/20 px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-cocov-text transition hover:border-red-300 hover:text-red-500">
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <main class="mx-auto max-w-screen-2xl px-6 py-12">
                <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="border border-gray-100 p-6">
                        <ShoppingBagIcon class="h-7 w-7 text-cocov-gold" />
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Orders</p>
                        <p class="mt-2 font-heading text-3xl">{{ stats.orders_count }}</p>
                    </div>
                    <div class="border border-gray-100 p-6">
                        <HeartIcon class="h-7 w-7 text-red-500" />
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Wishlist</p>
                        <p class="mt-2 font-heading text-3xl">{{ stats.wishlist_count }}</p>
                    </div>
                    <div class="border border-gray-100 p-6">
                        <SparklesIcon class="h-7 w-7 text-cocov-gold" />
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Bag Items</p>
                        <p class="mt-2 font-heading text-3xl">{{ stats.cart_count }}</p>
                    </div>
                    <div class="border border-gray-100 p-6">
                        <UserCircleIcon class="h-7 w-7 text-cocov-text" />
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Total Spent</p>
                        <p class="mt-2 font-heading text-3xl">{{ formatMoney(stats.total_spent) }}</p>
                    </div>
                </section>

                <section class="mt-12 grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="font-heading text-3xl uppercase">Recent Orders</h2>
                            <Link :href="route('profile.edit')" class="text-[10px] font-bold uppercase tracking-widest text-cocov-gold">Profile</Link>
                        </div>

                        <div v-if="recentOrders.length" class="divide-y divide-gray-100 border border-gray-100">
                            <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between gap-4 p-5">
                                <div>
                                    <p class="font-heading text-lg">#{{ order.order_number || `ORD-${order.id}` }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-widest text-gray-400">{{ new Date(order.created_at).toLocaleDateString() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-heading text-xl">{{ formatMoney(order.total) }}</p>
                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-cocov-gold">{{ order.status }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="border border-dashed border-gray-200 p-10 text-center">
                            <p class="font-heading text-2xl uppercase text-gray-400">No orders yet.</p>
                            <Link :href="route('products.index')" class="mt-6 inline-flex text-[10px] font-bold uppercase tracking-widest text-cocov-gold">Start shopping</Link>
                        </div>
                    </div>

                    <div>
                        <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4">
                            <h2 class="font-heading text-3xl uppercase">Saved Favorites</h2>
                            <Link :href="route('wishlist.index')" class="text-[10px] font-bold uppercase tracking-widest text-cocov-gold">View all</Link>
                        </div>

                        <div v-if="wishlistItems.length" class="grid gap-4 sm:grid-cols-2">
                            <Link v-for="item in wishlistItems" :key="item.id" :href="route('products.show', item.product.slug)" class="group border border-gray-100 p-4 text-center transition hover:border-cocov-gold">
                                <img :src="item.product.images?.[0]?.image_path || '/images/godiva/product_default.png'" :alt="item.product.name" class="mx-auto h-32 w-full object-contain transition group-hover:scale-105" />
                                <p class="mt-4 font-heading text-lg leading-tight">{{ item.product.name }}</p>
                            </Link>
                        </div>

                        <div v-else class="border border-dashed border-gray-200 p-10 text-center">
                            <p class="font-heading text-2xl uppercase text-gray-400">No favorites saved.</p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </MainLayout>
</template>
