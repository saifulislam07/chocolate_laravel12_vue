<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";

const props = defineProps({
    items: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    paymentGateways: { type: Object, default: () => ({}) },
});

const form = useForm({
    full_name: "",
    email: "",
    phone: "",
    address: "",
    city: "",
    postal_code: "",
    payment_method: "cod",
    notes: "",
});

const moneyFormatter = new Intl.NumberFormat("en-BD", { 
    style: "currency", 
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function placeOrder() {
    form.post(route("checkout.store"));
}
</script>

<template>
    <Head title="Checkout" />
    <div class="min-h-screen bg-[#f6f1eb] text-[#2f1d15]">
        <header class="border-b border-[#dcc8b0] bg-[#1f120d] text-[#f7ece0]">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <Link :href="route('home')" class="text-xl font-semibold tracking-[0.32em]">CHOCOLATE</Link>
                <Link :href="route('cart.index')" class="text-xs uppercase tracking-[0.24em] text-[#f3dcc3] hover:underline">Back to Bag</Link>
            </div>
        </header>

        <main class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-xl border border-[#dcc8b0] bg-white p-6">
                <h1 class="text-2xl font-semibold">Shipping & Payment</h1>
                <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="placeOrder">
                    <input v-model="form.full_name" type="text" placeholder="Full Name" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none" />
                    <input v-model="form.email" type="email" placeholder="Email" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none" />
                    <input v-model="form.phone" type="text" placeholder="Phone (optional)" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none" />
                    <input v-model="form.city" type="text" placeholder="City" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none" />
                    <input v-model="form.postal_code" type="text" placeholder="Postal Code" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none" />
                    <select v-model="form.payment_method" class="rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none">
                        <option value="cod">Cash on Delivery</option>
                        <option value="card">Card (Demo)</option>
                        <option v-if="paymentGateways.bkash?.enabled" value="bkash">bKash Merchant</option>
                        <option v-if="paymentGateways.nagad?.enabled" value="nagad">Nagad Merchant</option>
                    </select>
                    <div v-if="form.payment_method === 'bkash'" class="sm:col-span-2 rounded-lg border border-pink-100 bg-pink-50 px-4 py-3 text-sm text-pink-900">
                        After placing the order, you will be redirected to bKash merchant checkout to complete payment.
                    </div>
                    <div v-if="form.payment_method === 'nagad'" class="sm:col-span-2 rounded-lg border border-orange-100 bg-orange-50 px-4 py-3 text-sm text-orange-900">
                        Nagad merchant details are configured. Complete redirect needs the final signed Nagad production API details from your merchant account.
                    </div>
                    <textarea v-model="form.address" rows="3" placeholder="Street Address" class="sm:col-span-2 rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none"></textarea>
                    <textarea v-model="form.notes" rows="3" placeholder="Order notes (optional)" class="sm:col-span-2 rounded border border-[#d8c2a8] px-4 py-3 text-sm focus:border-[#a6784e] focus:outline-none"></textarea>
                    <button type="submit" :disabled="form.processing" class="sm:col-span-2 rounded bg-[#2a1912] py-3 text-sm uppercase tracking-widest text-white transition hover:bg-[#3b2419] disabled:opacity-60">
                        {{ form.processing ? "Placing Order..." : "Place Order" }}
                    </button>
                </form>
            </section>

            <aside class="rounded-xl border border-[#dcc8b0] bg-white p-6">
                <h2 class="text-xl font-semibold">Order Summary</h2>
                <div class="mt-4 space-y-3">
                    <article v-for="item in items" :key="item.id" class="flex items-center justify-between text-sm">
                        <p class="text-[#6f4d34]">{{ item.name }} x {{ item.quantity }}</p>
                        <p class="font-semibold">{{ formatMoney(item.line_total) }}</p>
                    </article>
                </div>
                <div class="mt-5 space-y-2 border-t border-[#eadbca] pt-4 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ formatMoney(summary.subtotal) }}</span></div>
                    <div class="flex justify-between"><span>Shipping</span><span>{{ formatMoney(summary.shipping) }}</span></div>
                    <div class="flex justify-between"><span>Tax</span><span>{{ formatMoney(summary.tax) }}</span></div>
                    <div class="flex justify-between font-semibold text-base"><span>Total</span><span>{{ formatMoney(summary.total) }}</span></div>
                </div>
            </aside>
        </main>
    </div>
</template>
