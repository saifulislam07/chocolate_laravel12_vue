<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    items: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    paymentGateways: { type: Object, default: () => ({}) },
    divisions: { type: Array, default: () => [] },
});

const form = useForm({
    full_name: "",
    email: "",
    phone: "",
    address: "",
    division_id: "",
    district_id: "",
    postal_code: "",
    payment_method: "cod",
    notes: "",
});

const districtOptions = computed(() => {
    const division = props.divisions.find((d) => String(d.id) === String(form.division_id));
    return division?.districts || [];
});

function onDivisionChange() {
    form.district_id = "";
}

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
    <div class="min-h-screen bg-white font-body text-cocov-text antialiased">
        <header class="border-b border-cocov-line bg-cocov-brown-dark text-white">
            <div class="mx-auto flex max-w-screen-2xl items-center justify-between px-6 py-5">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <img src="/images/cococraft-v2/logo.png" alt="CocoCraft" class="h-10 w-10 rounded-full bg-white object-contain p-1" />
                    <span class="font-heading text-lg uppercase tracking-[0.28em]">CocoCraft</span>
                </Link>
                <Link :href="route('cart.index')" class="text-xs uppercase tracking-[0.24em] text-white/80 transition hover:text-cocov-gold">Back to Bag</Link>
            </div>
        </header>

        <main class="mx-auto grid max-w-screen-2xl gap-8 px-6 py-10 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-[3px] border border-cocov-line bg-white p-6">
                <h1 class="font-heading text-2xl uppercase text-cocov-text">Shipping &amp; Payment</h1>
                <p class="mt-1 text-xs uppercase tracking-widest text-cocov-gold">Just your phone &amp; address to place an order &mdash; everything else is optional.</p>
                <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="placeOrder">
                    <div class="sm:col-span-2">
                        <input v-model="form.phone" type="tel" required placeholder="Phone Number *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <textarea v-model="form.address" rows="3" required placeholder="Delivery Address *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none"></textarea>
                        <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
                    </div>
                    <div>
                        <select v-model="form.division_id" required @change="onDivisionChange" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none">
                            <option value="" disabled>Division *</option>
                            <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
                        </select>
                        <p v-if="form.errors.division_id" class="mt-1 text-xs text-red-600">{{ form.errors.division_id }}</p>
                    </div>
                    <div>
                        <select v-model="form.district_id" required :disabled="!form.division_id" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none disabled:opacity-60">
                            <option value="" disabled>District *</option>
                            <option v-for="district in districtOptions" :key="district.id" :value="district.id">{{ district.name }}</option>
                        </select>
                        <p v-if="form.errors.district_id" class="mt-1 text-xs text-red-600">{{ form.errors.district_id }}</p>
                    </div>

                    <p class="sm:col-span-2 mt-2 text-xs font-semibold uppercase tracking-widest text-cocov-gold">Optional details</p>
                    <input v-model="form.full_name" type="text" placeholder="Full Name (optional)" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                    <input v-model="form.email" type="email" placeholder="Email (optional)" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                    <input v-model="form.postal_code" type="text" placeholder="Postal Code (optional)" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />

                    <select v-model="form.payment_method" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none">
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
                    <textarea v-model="form.notes" rows="3" placeholder="Order notes (optional)" class="sm:col-span-2 rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none"></textarea>
                    <button type="submit" :disabled="form.processing" class="sm:col-span-2 rounded-[3px] bg-cocov-gold py-3 text-sm font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a] disabled:opacity-60">
                        {{ form.processing ? "Placing Order..." : "Place Order" }}
                    </button>
                </form>
            </section>

            <aside class="rounded-[3px] border border-cocov-line bg-white p-6">
                <h2 class="font-heading text-xl uppercase text-cocov-text">Order Summary</h2>
                <div class="mt-4 space-y-3">
                    <article v-for="item in items" :key="item.id" class="flex items-center justify-between text-sm">
                        <p class="text-cocov-text/70">{{ item.name }} x {{ item.quantity }}</p>
                        <p class="font-semibold">{{ formatMoney(item.line_total) }}</p>
                    </article>
                </div>
                <div class="mt-5 space-y-2 border-t border-cocov-line pt-4 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ formatMoney(summary.subtotal) }}</span></div>
                    <div class="flex justify-between"><span>Shipping</span><span>{{ formatMoney(summary.shipping) }}</span></div>
                    <div class="flex justify-between"><span>Tax</span><span>{{ formatMoney(summary.tax) }}</span></div>
                    <div class="flex justify-between font-heading text-base text-cocov-text"><span>Total</span><span>{{ formatMoney(summary.total) }}</span></div>
                </div>
            </aside>
        </main>
    </div>
</template>
