<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { MinusIcon, PlusIcon, XMarkIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    items: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    paymentMethods: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
    shippingConfig: {
        type: Object,
        default: () => ({ default_charge: 0, free_threshold: null, charges: {} }),
    },
});

const form = useForm({
    full_name: "",
    email: "",
    phone: "",
    address: "",
    division_id: "",
    district_id: "",
    postal_code: "",
    payment_method: props.paymentMethods[0]?.value || "cod",
    notes: "",
});

// Whatever the chosen gateway wants the shopper to know before they commit;
// cash has nothing to add, so the note simply disappears.
const selectedPaymentNote = computed(
    () => props.paymentMethods.find((method) => method.value === form.payment_method)?.note || "",
);

const districtOptions = computed(() => {
    const division = props.divisions.find((d) => String(d.id) === String(form.division_id));
    return division?.districts || [];
});

function onDivisionChange() {
    form.district_id = "";
}

// Mirrors App\Services\ShippingCalculator so the total shown matches what the server charges.
const freeShipping = computed(() => {
    const threshold = props.shippingConfig.free_threshold;
    return threshold !== null && Number(props.summary.subtotal) >= Number(threshold);
});

const shipping = computed(() => {
    if (freeShipping.value) return 0;

    const areaCharge = props.shippingConfig.charges?.[form.district_id];
    return Number(areaCharge ?? props.shippingConfig.default_charge ?? 0);
});

const total = computed(() => Number(props.summary.subtotal) + shipping.value);

const selectedDistrictName = computed(
    () => districtOptions.value.find((d) => String(d.id) === String(form.district_id))?.name || "",
);

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

// Id of the line currently in flight, so its own buttons freeze while the
// server catches up instead of the whole summary going dead.
const pendingItemId = ref(null);
const bagError = ref("");

// The bag endpoints answer with back(), which re-renders this page with fresh
// items and totals. preserveState keeps everything already typed into the form.
function editBag(itemId, visit) {
    if (pendingItemId.value !== null) return;

    pendingItemId.value = itemId;
    bagError.value = "";

    visit({
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            bagError.value = errors.quantity || Object.values(errors)[0] || "Could not update your bag.";
        },
        onFinish: () => {
            pendingItemId.value = null;
        },
    });
}

function setQuantity(item, quantity) {
    if (quantity < 1 || quantity > item.max_quantity || quantity === item.quantity) {
        return;
    }

    editBag(item.id, (options) => router.patch(route("cart.update", item.id), { quantity }, options));
}

function increment(item) {
    setQuantity(item, item.quantity + 1);
}

function decrement(item) {
    setQuantity(item, item.quantity - 1);
}

// Emptying the bag entirely sends the shopper back to it — there is nothing
// left here to check out — which CheckoutController@index already handles.
function removeItem(item) {
    editBag(item.id, (options) => router.delete(route("cart.destroy", item.id), options));
}

function placeOrder() {
    form.post(route("checkout.store"));
}
</script>

<template>
    <Head title="Checkout" />
    <div class="min-h-screen bg-white font-body text-cocov-text antialiased">
        <header class="border-b border-cocov-line bg-cocov-brown-dark text-white">
            <div class="mx-auto flex max-w-full items-center justify-between px-6 py-5">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <img src="/images/cococraft-v2/logo.png" alt="CocoCraft" class="h-10 w-10 rounded-full bg-white object-contain p-1" />
                    <span class="font-heading text-lg uppercase tracking-[0.28em]">CocoCraft</span>
                </Link>
                <Link :href="route('cart.index')" class="text-xs uppercase tracking-[0.24em] text-white/80 transition hover:text-cocov-gold">Back to Bag</Link>
            </div>
        </header>

        <main class="mx-auto grid max-w-full gap-8 px-6 py-10 lg:grid-cols-3">
            <section class="lg:col-span-2 rounded-[3px] border border-cocov-line bg-white p-6">
                <h1 class="font-heading text-2xl uppercase text-cocov-text">Shipping &amp; Payment</h1>
                <p class="mt-1 text-xs uppercase tracking-widest text-cocov-gold">Just your name, phone &amp; address to place an order &mdash; everything else is optional.</p>
                <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="placeOrder">
                    <div class="sm:col-span-2">
                        <input v-model="form.full_name" type="text" required maxlength="120" placeholder="Full Name *" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                        <p v-if="form.errors.full_name" class="mt-1 text-xs text-red-600">{{ form.errors.full_name }}</p>
                    </div>
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
                    <input v-model="form.email" type="email" placeholder="Email (optional)" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />
                    <input v-model="form.postal_code" type="text" placeholder="Postal Code (optional)" class="rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none" />

                    <div>
                        <select v-model="form.payment_method" class="w-full rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none">
                            <option v-for="method in paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
                        </select>
                        <p v-if="form.errors.payment_method" class="mt-1 text-xs text-red-600">{{ form.errors.payment_method }}</p>
                    </div>
                    <div v-if="selectedPaymentNote" class="sm:col-span-2 rounded-lg border border-cocov-line bg-[#fcf8f3] px-4 py-3 text-sm text-cocov-text/80">
                        {{ selectedPaymentNote }}
                    </div>
                    <textarea v-model="form.notes" rows="3" placeholder="Order notes (optional)" class="sm:col-span-2 rounded border border-cocov-line px-4 py-3 text-sm focus:border-cocov-gold focus:outline-none"></textarea>
                    <button type="submit" :disabled="form.processing" class="sm:col-span-2 rounded-[3px] bg-cocov-gold py-3 text-sm font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a] disabled:opacity-60">
                        {{ form.processing ? "Placing Order..." : "Place Order" }}
                    </button>
                </form>
            </section>

            <aside class="rounded-[3px] border border-cocov-line bg-white p-6">
                <h2 class="font-heading text-xl uppercase text-cocov-text">Order Summary</h2>
                <p v-if="bagError" class="mt-3 rounded border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700">{{ bagError }}</p>
                <div class="mt-4 divide-y divide-cocov-line">
                    <article
                        v-for="item in items"
                        :key="item.id"
                        class="flex gap-3 py-3 text-sm transition"
                        :class="pendingItemId === item.id ? 'opacity-50' : ''"
                    >
                        <img
                            :src="item.image || '/images/godiva/product_default.png'"
                            :alt="item.name"
                            class="h-14 w-14 flex-shrink-0 border border-cocov-line object-contain p-1"
                        />
                        <div class="flex flex-1 flex-col justify-between gap-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-cocov-text/80">{{ item.name }}</p>
                                    <p class="mt-0.5 text-[10px] uppercase tracking-widest text-gray-400">{{ formatMoney(item.price) }} / ea</p>
                                </div>
                                <button
                                    type="button"
                                    :disabled="pendingItemId !== null"
                                    :aria-label="`Remove ${item.name} from bag`"
                                    class="text-gray-400 transition hover:text-cocov-gold disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="removeItem(item)"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex h-8 w-24 items-center justify-between border border-cocov-line px-2">
                                    <button
                                        type="button"
                                        :disabled="pendingItemId !== null || item.quantity <= 1"
                                        aria-label="Decrease quantity"
                                        class="text-gray-400 transition hover:text-cocov-gold disabled:cursor-not-allowed disabled:opacity-30"
                                        @click="decrement(item)"
                                    >
                                        <MinusIcon class="h-3 w-3" />
                                    </button>
                                    <span class="text-xs font-bold">{{ item.quantity }}</span>
                                    <button
                                        type="button"
                                        :disabled="pendingItemId !== null || item.quantity >= item.max_quantity"
                                        aria-label="Increase quantity"
                                        class="text-gray-400 transition hover:text-cocov-gold disabled:cursor-not-allowed disabled:opacity-30"
                                        @click="increment(item)"
                                    >
                                        <PlusIcon class="h-3 w-3" />
                                    </button>
                                </div>
                                <p class="font-semibold">{{ formatMoney(item.line_total) }}</p>
                            </div>
                            <p v-if="item.quantity >= item.max_quantity" class="text-[10px] uppercase tracking-widest text-cocov-gold">
                                {{
                                    item.max_quantity >= item.stock
                                        ? `Only ${item.stock} left in stock`
                                        : `Limit ${item.max_quantity} per item`
                                }}
                            </p>
                        </div>
                    </article>
                </div>
                <div class="mt-5 space-y-2 border-t border-cocov-line pt-4 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ formatMoney(summary.subtotal) }}</span></div>
                    <div class="flex justify-between">
                        <span>
                            Shipping
                            <span v-if="selectedDistrictName" class="block text-[10px] uppercase tracking-widest text-cocov-gold">{{ selectedDistrictName }}</span>
                            <span v-else class="block text-[10px] uppercase tracking-widest text-gray-400">Select your area</span>
                        </span>
                        <span v-if="freeShipping" class="font-semibold uppercase tracking-widest text-green-600">Free</span>
                        <span v-else>{{ formatMoney(shipping) }}</span>
                    </div>
                    <div class="flex justify-between font-heading text-base text-cocov-text"><span>Total</span><span>{{ formatMoney(total) }}</span></div>
                    <p v-if="!freeShipping && shippingConfig.free_threshold" class="pt-1 text-[10px] uppercase tracking-widest text-cocov-gold">
                        Add {{ formatMoney(shippingConfig.free_threshold - summary.subtotal) }} more for free delivery
                    </p>
                </div>
            </aside>
        </main>
    </div>
</template>
