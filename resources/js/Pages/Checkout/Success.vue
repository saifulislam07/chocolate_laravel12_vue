<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    order: { type: Object, required: true },
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
    <Head title="Order Confirmed" />

    <div class="min-h-screen bg-white font-body text-cocov-text antialiased">
        <main class="mx-auto max-w-3xl px-6 py-14">
            <div class="rounded-[3px] border border-cocov-line bg-white p-8 text-center">
                <p class="text-xs uppercase tracking-[0.28em] text-cocov-gold">Thank You</p>
                <h1 class="mt-3 font-heading text-3xl uppercase text-cocov-text">Order Confirmed</h1>
                <p class="mt-4 text-sm text-cocov-text/70">
                    Your order <strong>{{ order.order_number }}</strong> has been placed successfully.
                </p>
                <p class="mt-2 text-sm text-cocov-text/70">
                    Payment method: <strong class="uppercase">{{ order.payment_method }}</strong>
                </p>
                <p class="mt-2 text-sm text-cocov-text/70">
                    Payment status: <strong class="uppercase">{{ order.payment_status }}</strong>
                </p>
                <p class="mt-2 font-heading text-lg text-cocov-text">Total: {{ formatMoney(order.total) }}</p>
                <div class="mt-8 flex justify-center gap-3">
                    <Link :href="route('home')" class="rounded-[3px] bg-cocov-gold px-5 py-3 text-xs font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a]">
                        Continue Shopping
                    </Link>
                    <Link :href="route('cart.index')" class="rounded-[3px] border border-cocov-gold px-5 py-3 text-xs font-bold uppercase tracking-widest text-cocov-gold transition hover:bg-cocov-gold hover:text-white">
                        View Bag
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
