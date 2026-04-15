<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    order: { type: Object, required: true },
});

const moneyFormatter = new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" });

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}
</script>

<template>
    <Head title="Order Confirmed" />

    <div class="min-h-screen bg-[#f6f1eb] text-[#2f1d15]">
        <main class="mx-auto max-w-3xl px-6 py-14">
            <div class="rounded-2xl border border-[#dcc8b0] bg-white p-8 text-center">
                <p class="text-xs uppercase tracking-[0.28em] text-[#8d6242]">Thank You</p>
                <h1 class="mt-3 text-3xl font-semibold">Order Confirmed</h1>
                <p class="mt-4 text-sm text-[#6f4d34]">
                    Your order <strong>{{ order.order_number }}</strong> has been placed successfully.
                </p>
                <p class="mt-2 text-sm text-[#6f4d34]">
                    Payment method: <strong class="uppercase">{{ order.payment_method }}</strong>
                </p>
                <p class="mt-2 text-lg font-semibold">Total: {{ formatMoney(order.total) }}</p>
                <div class="mt-8 flex justify-center gap-3">
                    <Link :href="route('home')" class="rounded bg-[#2a1912] px-5 py-3 text-xs uppercase tracking-widest text-white">
                        Continue Shopping
                    </Link>
                    <Link :href="route('cart.index')" class="rounded border border-[#d8c2a8] px-5 py-3 text-xs uppercase tracking-widest">
                        View Bag
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
