<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
    divisions: { type: Array, default: () => [] },
    leadSources: { type: Array, default: () => [] },
});

// Shop details for the receipt header come from Settings, not hardcoded text.
const shop = computed(() => usePage().props.webSettings || {});

const today = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

const money = (value) => '৳' + Number(value || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/* ------------------------------------------------------------------ *
 * Catalogue: search, category filter, category-first ordering
 * ------------------------------------------------------------------ */

const searchQuery = ref('');
const selectedCategoryId = ref('all');

// Only the categories that actually have a product on this screen, so the
// filter bar never offers a tab that turns up empty.
const categories = computed(() => {
    const found = new Map();

    for (const product of props.products) {
        if (!product.category) continue;

        const entry = found.get(product.category.id);

        if (entry) {
            entry.count++;
        } else {
            found.set(product.category.id, { id: product.category.id, name: product.category.name, count: 1 });
        }
    }

    return [...found.values()].sort((a, b) => a.name.localeCompare(b.name));
});

const uncategorisedCount = computed(() => props.products.filter((p) => !p.category).length);

const categoryOf = (product) => product.category?.id ?? 'uncategorised';

const filteredProducts = computed(() => {
    const term = searchQuery.value.trim().toLowerCase();

    return props.products
        .filter((product) => {
            if (selectedCategoryId.value !== 'all' && categoryOf(product) !== selectedCategoryId.value) {
                return false;
            }

            if (!term) return true;

            return product.name.toLowerCase().includes(term)
                || (product.sku && product.sku.toLowerCase().includes(term));
        })
        // Category first so the grid reads as one shelf per category, then name
        // within it. Anything without a category is parked at the end.
        .sort((a, b) => {
            const rankA = a.category ? 0 : 1;
            const rankB = b.category ? 0 : 1;

            if (rankA !== rankB) return rankA - rankB;

            return (a.category?.name || '').localeCompare(b.category?.name || '')
                || a.name.localeCompare(b.name);
        });
});

/* ------------------------------------------------------------------ *
 * Cart
 * ------------------------------------------------------------------ */

const cart = ref([]);
const selectedCustomerId = ref('');

// A short-lived line above the cart. Replaces the alert() dialogs, which stop
// the operator dead in the middle of a queue.
const notice = ref('');
let noticeTimer = null;

function flashNotice(message) {
    notice.value = message;
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => { notice.value = ''; }, 2500);
}

onBeforeUnmount(() => clearTimeout(noticeTimer));

const addToCart = (product) => {
    if (product.stock <= 0) {
        flashNotice(`"${product.name}" is out of stock.`);
        return;
    }

    const existing = cart.value.find((item) => item.id === product.id);

    if (!existing) {
        // The shelf count rides along on the line so the stepper can stop at it
        // without going back to the product list on every click.
        cart.value.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            stock: product.stock,
        });

        return;
    }

    if (existing.quantity >= existing.stock) {
        flashNotice(`Only ${existing.stock} of "${product.name}" in stock.`);
        return;
    }

    existing.quantity++;
};

const increment = (item) => {
    if (item.quantity >= item.stock) {
        flashNotice(`Only ${item.stock} of "${item.name}" in stock.`);
        return;
    }

    item.quantity++;
};

const decrement = (item) => {
    if (item.quantity > 1) item.quantity--;
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));

/* ------------------------------------------------------------------ *
 * Money
 * ------------------------------------------------------------------ */

const discount = ref(0);
const taxRate = ref(0);
const shippingCost = ref(0);
const paidAmount = ref(0);
const paymentMethod = ref('cash');
// Which channel brought this sale in, and anything the operator wants on record.
const leadSource = ref('');
const saleNote = ref('');

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));

const taxAmount = computed(() => (subtotal.value * taxRate.value) / 100);

const grandTotal = computed(
    () => subtotal.value - parseFloat(discount.value || 0) + taxAmount.value + parseFloat(shippingCost.value || 0),
);

const dueAmount = computed(() => Math.max(grandTotal.value - parseFloat(paidAmount.value || 0), 0));

const changeAmount = computed(() => Math.max(parseFloat(paidAmount.value || 0) - grandTotal.value, 0));

/* ------------------------------------------------------------------ *
 * Checkout
 * ------------------------------------------------------------------ */

const showInvoiceModal = ref(false);
const printInvoiceData = ref(null);

const checkoutForm = useForm({
    customer_id: '',
    items: [],
    subtotal: 0,
    discount: 0,
    tax: 0,
    shipping_cost: 0,
    total: 0,
    paid_amount: 0,
    due_amount: 0,
    payment_method: 'cash',
    lead_source: '',
    notes: '',
});

// Stock and validation failures come back keyed by field; the operator needs to
// see them next to the cart rather than nowhere at all.
const checkoutError = computed(() => Object.values(checkoutForm.errors)[0] || '');

const processSale = () => {
    if (!cart.value.length) return;

    checkoutForm.customer_id = selectedCustomerId.value;
    checkoutForm.items = cart.value.map(({ id, name, price, quantity }) => ({ id, name, price, quantity }));
    checkoutForm.subtotal = subtotal.value;
    checkoutForm.discount = parseFloat(discount.value || 0);
    checkoutForm.tax = taxAmount.value;
    checkoutForm.shipping_cost = parseFloat(shippingCost.value || 0);
    checkoutForm.total = grandTotal.value;
    checkoutForm.paid_amount = parseFloat(paidAmount.value || 0);
    checkoutForm.due_amount = dueAmount.value;
    checkoutForm.payment_method = paymentMethod.value;
    checkoutForm.lead_source = leadSource.value;
    checkoutForm.notes = saleNote.value;

    checkoutForm.post(route('admin.pos.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            printInvoiceData.value = page.props.flash?.invoice || null;

            if (printInvoiceData.value) {
                showInvoiceModal.value = true;
            }

            clearSale();
        },
    });
};

function clearSale() {
    cart.value = [];
    discount.value = 0;
    taxRate.value = 0;
    shippingCost.value = 0;
    paidAmount.value = 0;
    leadSource.value = '';
    saleNote.value = '';
}

const printInvoice = () => {
    window.print();
};

/* ------------------------------------------------------------------ *
 * Quick add customer
 * ------------------------------------------------------------------ */

const showCustomerModal = ref(false);

const quickCustomerForm = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    division_id: '',
    district_id: '',
});

const quickCustomerDistrictOptions = computed(() => {
    const division = props.divisions.find((d) => String(d.id) === String(quickCustomerForm.division_id));
    return division?.districts || [];
});

function onQuickCustomerDivisionChange() {
    quickCustomerForm.district_id = '';
}

const submitQuickCustomer = () => {
    const phone = quickCustomerForm.phone;

    quickCustomerForm.post(route('admin.customers.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Phone is unique, so the refreshed list is enough to find the row
            // that was just created and put it straight on this sale.
            const created = (page.props.customers || []).find((c) => c.phone === phone);

            if (created) selectedCustomerId.value = created.id;

            showCustomerModal.value = false;
            quickCustomerForm.reset();
        },
    });
};
</script>

<template>
    <Head title="POS - Point of Sale" />

    <AdminLayout>
        <div class="d-print-none">
            <!-- Page heading -->
            <div class="mb-3.5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-[1.25rem] font-bold tracking-tight text-slate-900">Point of Sale</h1>
                    <p class="mt-0.5 text-sm text-slate-500">Ring up a sale, print the invoice &mdash; stock updates itself.</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <span class="rounded-md border-[1px] border-slate-200 bg-white px-2.5 py-1.5">
                        <i class="far fa-calendar mr-1.5 text-slate-400"></i>{{ today }}
                    </span>
                    <span class="rounded-md border-[1px] border-slate-200 bg-white px-2.5 py-1.5">
                        <i class="fas fa-box mr-1.5 text-slate-400"></i>{{ filteredProducts.length }} shown
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-4 xl:h-[calc(100vh-11rem)] xl:flex-row">
                <!-- ---------------------------------------------------- -->
                <!-- Catalogue                                            -->
                <!-- ---------------------------------------------------- -->
                <section class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border-[1px] border-slate-200 bg-white">
                    <div class="space-y-3 border-b border-slate-100 p-3.5">
                        <div class="relative">
                            <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by product name or SKU"
                                class="w-full rounded-xl border-[1px] border-slate-200 bg-slate-50 py-2.5 pl-10 pr-2.5 text-sm text-slate-700 placeholder-slate-400 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100"
                            >
                        </div>

                        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                            <button
                                type="button"
                                class="shrink-0 rounded-full border-[1px] px-2.5 py-1.5 text-xs font-semibold transition"
                                :class="selectedCategoryId === 'all'
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                @click="selectedCategoryId = 'all'"
                            >
                                All <span class="ml-1 opacity-70">{{ products.length }}</span>
                            </button>
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                type="button"
                                class="shrink-0 rounded-full border-[1px] px-2.5 py-1.5 text-xs font-semibold transition"
                                :class="selectedCategoryId === category.id
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                @click="selectedCategoryId = category.id"
                            >
                                {{ category.name }} <span class="ml-1 opacity-70">{{ category.count }}</span>
                            </button>
                            <button
                                v-if="uncategorisedCount"
                                type="button"
                                class="shrink-0 rounded-full border-[1px] px-2.5 py-1.5 text-xs font-semibold transition"
                                :class="selectedCategoryId === 'uncategorised'
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                @click="selectedCategoryId = 'uncategorised'"
                            >
                                Uncategorised <span class="ml-1 opacity-70">{{ uncategorisedCount }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-3.5">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                            <button
                                v-for="product in filteredProducts"
                                :key="product.id"
                                type="button"
                                :disabled="product.stock <= 0"
                                class="group flex flex-col overflow-hidden rounded-xl border-[1px] border-slate-200 bg-white text-left transition hover:border-indigo-300 hover:shadow-md focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-slate-200 disabled:hover:shadow-none"
                                @click="addToCart(product)"
                            >
                                <div class="relative aspect-square w-full bg-slate-50">
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="product.name"
                                        class="h-full w-full object-contain p-2"
                                    >
                                    <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-[1.25rem]"></i>
                                    </div>

                                    <span
                                        class="absolute left-1.5 top-1.5 rounded-md px-1.5 py-0.5 text-[10px] font-bold leading-none"
                                        :class="product.stock > 10
                                            ? 'bg-white/90 text-slate-500 ring-1 ring-slate-200'
                                            : product.stock > 0
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-rose-100 text-rose-700'"
                                    >
                                        {{ product.stock > 0 ? product.stock : 'Out' }}
                                    </span>
                                    <span
                                        v-if="product.compare_at_price > product.price"
                                        class="absolute right-1.5 top-1.5 rounded-md bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
                                    >
                                        -{{ Math.round((product.compare_at_price - product.price) / product.compare_at_price * 100) }}%
                                    </span>
                                </div>

                                <div class="flex flex-1 flex-col gap-1 border-t border-slate-100 p-2">
                                    <p class="line-clamp-2 text-[11px] font-semibold leading-snug text-slate-700" :title="product.name">
                                        {{ product.name }}
                                    </p>
                                    <div class="mt-auto flex items-baseline justify-between gap-1">
                                        <span class="text-xs font-bold text-slate-900">৳{{ product.price }}</span>
                                        <del v-if="product.compare_at_price > product.price" class="text-[10px] text-slate-400">
                                            ৳{{ product.compare_at_price }}
                                        </del>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <div v-if="!filteredProducts.length" class="py-16 text-center">
                            <i class="fas fa-box-open mb-2.5 block text-3xl text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">No products match this search or category.</p>
                        </div>
                    </div>
                </section>

                <!-- ---------------------------------------------------- -->
                <!-- Cart & checkout                                      -->
                <!-- ---------------------------------------------------- -->
                <aside class="flex min-h-0 w-full flex-col overflow-hidden rounded-2xl border-[1px] border-slate-200 bg-white xl:w-[26rem] xl:shrink-0">
                    <!-- Customer -->
                    <div class="flex items-center gap-2 border-b border-slate-100 p-2.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <select
                            v-model="selectedCustomerId"
                            class="min-w-0 flex-1 rounded-md border-[1px] border-slate-200 bg-white py-2 pl-2.5 pr-8 text-sm font-medium text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        >
                            <option value="">Walk-in Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }} ({{ customer.phone }})
                            </option>
                        </select>
                        <button
                            type="button"
                            title="Add a new customer"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white transition hover:bg-indigo-700"
                            @click="showCustomerModal = true"
                        >
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>

                    <!-- Cart lines -->
                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <div v-if="notice" class="border-b border-amber-100 bg-amber-50 px-3.5 py-2 text-xs font-medium text-amber-800">
                            <i class="fas fa-triangle-exclamation mr-1.5"></i>{{ notice }}
                        </div>
                        <div v-if="checkoutError" class="border-b border-rose-100 bg-rose-50 px-3.5 py-2 text-xs font-medium text-rose-700">
                            <i class="fas fa-circle-exclamation mr-1.5"></i>{{ checkoutError }}
                        </div>

                        <ul v-if="cart.length" class="divide-y divide-slate-100">
                            <li v-for="(item, index) in cart" :key="item.id" class="flex items-center gap-2 px-2.5 py-2.5">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800" :title="item.name">{{ item.name }}</p>
                                    <p class="text-[11px] text-slate-400">৳{{ item.price }} each</p>
                                </div>

                                <div class="flex shrink-0 items-center rounded-md border-[1px] border-slate-200">
                                    <button
                                        type="button"
                                        aria-label="Decrease quantity"
                                        class="flex h-7 w-7 items-center justify-center rounded-l-md text-slate-500 transition hover:bg-slate-50 disabled:opacity-30"
                                        :disabled="item.quantity <= 1"
                                        @click="decrement(item)"
                                    >
                                        <i class="fas fa-minus text-[10px]"></i>
                                    </button>
                                    <span class="w-7 text-center text-xs font-bold text-slate-800">{{ item.quantity }}</span>
                                    <button
                                        type="button"
                                        aria-label="Increase quantity"
                                        class="flex h-7 w-7 items-center justify-center rounded-r-md text-slate-500 transition hover:bg-slate-50 disabled:opacity-30"
                                        :disabled="item.quantity >= item.stock"
                                        @click="increment(item)"
                                    >
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                </div>

                                <span class="w-20 shrink-0 text-right text-sm font-bold text-slate-900">
                                    {{ money(item.price * item.quantity) }}
                                </span>

                                <button
                                    type="button"
                                    aria-label="Remove item"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-300 transition hover:bg-rose-50 hover:text-rose-500"
                                    @click="removeFromCart(index)"
                                >
                                    <i class="fas fa-xmark text-xs"></i>
                                </button>
                            </li>
                        </ul>

                        <div v-else class="flex h-full flex-col items-center justify-center px-6 py-12 text-center">
                            <div class="mb-2.5 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50">
                                <i class="fas fa-cart-shopping text-base text-slate-300"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-600">Cart is empty</p>
                            <p class="mt-1 text-xs text-slate-400">Tap a product to start the sale.</p>
                        </div>
                    </div>

                    <!-- Adjustments & totals -->
                    <div class="space-y-3 border-t border-slate-100 bg-slate-50/60 p-2.5">
                        <div class="grid grid-cols-3 gap-2">
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Discount ৳</span>
                                <input v-model.number="discount" type="number" min="0" class="w-full rounded-md border-[1px] border-slate-200 bg-white px-2 py-1.5 text-right text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tax %</span>
                                <input v-model.number="taxRate" type="number" min="0" max="100" class="w-full rounded-md border-[1px] border-slate-200 bg-white px-2 py-1.5 text-right text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Shipping ৳</span>
                                <input v-model.number="shippingCost" type="number" min="0" class="w-full rounded-md border-[1px] border-slate-200 bg-white px-2 py-1.5 text-right text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            </label>
                        </div>

                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal <span class="text-slate-400">({{ cartCount }} item{{ cartCount === 1 ? '' : 's' }})</span></span>
                                <span class="font-medium text-slate-700">{{ money(subtotal) }}</span>
                            </div>
                            <div v-if="discount > 0" class="flex justify-between text-slate-500">
                                <span>Discount</span>
                                <span class="font-medium text-rose-600">-{{ money(discount) }}</span>
                            </div>
                            <div v-if="taxAmount > 0" class="flex justify-between text-slate-500">
                                <span>Tax ({{ taxRate }}%)</span>
                                <span class="font-medium text-slate-700">{{ money(taxAmount) }}</span>
                            </div>
                            <div v-if="shippingCost > 0" class="flex justify-between text-slate-500">
                                <span>Shipping</span>
                                <span class="font-medium text-slate-700">{{ money(shippingCost) }}</span>
                            </div>
                            <div class="flex items-baseline justify-between border-t border-slate-200 pt-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Grand Total</span>
                                <span class="text-[1.25rem] font-bold text-slate-900">{{ money(grandTotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment details -->
                    <div class="space-y-2 border-t border-slate-100 p-2.5">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Payment Method</span>
                                <select v-model="paymentMethod" class="w-full rounded-md border-[1px] border-slate-200 bg-white py-1.5 pl-2 pr-7 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card / POS</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Paid Amount ৳</span>
                                <input v-model.number="paidAmount" type="number" min="0" class="w-full rounded-md border-[1px] border-slate-200 bg-white px-2 py-1.5 text-right text-sm font-bold text-indigo-600 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Lead From</span>
                                <select v-model="leadSource" class="w-full rounded-md border-[1px] border-slate-200 bg-white py-1.5 pl-2 pr-7 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option value="">Not specified</option>
                                    <option v-for="source in leadSources" :key="source" :value="source">{{ source }}</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Note</span>
                                <input v-model="saleNote" type="text" maxlength="1000" placeholder="Optional" class="w-full rounded-md border-[1px] border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="rounded-md bg-emerald-50 px-2.5 py-2 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Change</p>
                                <p class="text-sm font-bold text-emerald-700">{{ money(changeAmount) }}</p>
                            </div>
                            <div class="rounded-md px-2.5 py-2 text-center" :class="dueAmount > 0 ? 'bg-rose-50' : 'bg-slate-50'">
                                <p class="text-[10px] font-semibold uppercase tracking-wide" :class="dueAmount > 0 ? 'text-rose-600' : 'text-slate-400'">Due</p>
                                <p class="text-sm font-bold" :class="dueAmount > 0 ? 'text-rose-700' : 'text-slate-500'">{{ money(dueAmount) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 border-t border-slate-100 p-2.5">
                        <button
                            type="button"
                            class="rounded-xl border-[1px] border-slate-200 px-3.5 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 disabled:opacity-40"
                            :disabled="!cart.length"
                            @click="clearSale"
                        >
                            Clear
                        </button>
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-between gap-2 rounded-xl bg-indigo-600 px-3.5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!cart.length || checkoutForm.processing"
                            @click="processSale"
                        >
                            <span>
                                <i class="fas fa-circle-check mr-1.5"></i>
                                {{ checkoutForm.processing ? 'Processing…' : 'Complete Sale' }}
                            </span>
                            <span>{{ money(grandTotal) }}</span>
                        </button>
                    </div>
                </aside>
            </div>
        </div>

        <!-- ---------------------------------------------------------- -->
        <!-- Invoice                                                    -->
        <!-- ---------------------------------------------------------- -->
        <div v-if="showInvoiceModal" class="pos-overlay fixed inset-0 z-[1200] overflow-y-auto bg-slate-900/60 p-3.5 backdrop-blur-sm">
            <div class="pos-sheet mx-auto w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="d-print-none flex items-center justify-between border-b border-slate-100 px-6 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Sale complete</p>
                            <p class="text-xs text-slate-500">Invoice #{{ printInvoiceData?.invoice_no }}</p>
                        </div>
                    </div>
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" @click="showInvoiceModal = false">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div id="invoice-print-area" class="px-8 py-7 text-slate-700">
                    <header class="flex items-start justify-between gap-6 border-b border-slate-200 pb-3.5">
                        <div>
                            <img v-if="shop.logo" :src="shop.logo" alt="" class="invoice-logo mb-2">
                            <h2 class="text-base font-bold text-slate-900">{{ shop.site_name || 'Invoice' }}</h2>
                            <p v-if="shop.address" class="mt-0.5 text-xs text-slate-500">{{ shop.address }}</p>
                            <p v-if="shop.phone" class="text-xs text-slate-500">Phone: {{ shop.phone }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Invoice</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">#{{ printInvoiceData?.invoice_no }}</p>
                            <p class="text-xs text-slate-500">{{ today }}</p>
                        </div>
                    </header>

                    <section class="flex items-start justify-between gap-6 py-6 text-sm">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Invoice To</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ printInvoiceData?.customer_name || 'Walk-in Customer' }}</p>
                            <p class="text-xs text-slate-500">Phone: {{ printInvoiceData?.customer_phone || 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment</p>
                            <p class="mt-1 font-semibold uppercase text-slate-800">{{ printInvoiceData?.payment_method }}</p>
                            <!-- Attribution is for the shop, not the customer, so it stays off the printed copy. -->
                            <p v-if="printInvoiceData?.lead_source" class="d-print-none text-xs text-slate-400">
                                Lead from: {{ printInvoiceData.lead_source }}
                            </p>
                        </div>
                    </section>

                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-y border-slate-200 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
                                <th class="px-2.5 py-2 text-left font-bold">Item</th>
                                <th class="px-2.5 py-2 text-center font-bold">Qty</th>
                                <th class="px-2.5 py-2 text-right font-bold">Price</th>
                                <th class="px-2.5 py-2 text-right font-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in printInvoiceData?.items" :key="item.id" class="border-b border-slate-100">
                                <td class="px-2.5 py-2">{{ item.name }}</td>
                                <td class="px-2.5 py-2 text-center">{{ item.quantity }}</td>
                                <td class="px-2.5 py-2 text-right">৳{{ item.price }}</td>
                                <td class="px-2.5 py-2 text-right font-medium">{{ money(item.quantity * item.price) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-6 ml-auto w-full max-w-xs space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span><span class="text-slate-700">{{ money(printInvoiceData?.subtotal) }}</span>
                        </div>
                        <div v-if="printInvoiceData?.discount > 0" class="flex justify-between text-slate-500">
                            <span>Discount</span><span class="text-rose-600">-{{ money(printInvoiceData?.discount) }}</span>
                        </div>
                        <div v-if="printInvoiceData?.tax > 0" class="flex justify-between text-slate-500">
                            <span>Tax</span><span class="text-slate-700">{{ money(printInvoiceData?.tax) }}</span>
                        </div>
                        <div v-if="printInvoiceData?.shipping_cost > 0" class="flex justify-between text-slate-500">
                            <span>Shipping</span><span class="text-slate-700">{{ money(printInvoiceData?.shipping_cost) }}</span>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-slate-200 pt-2 text-base font-bold text-slate-900">
                            <span>Grand Total</span><span>{{ money(printInvoiceData?.total) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Paid</span><span class="font-semibold text-emerald-600">{{ money(printInvoiceData?.paid_amount) }}</span>
                        </div>
                        <div v-if="printInvoiceData?.due_amount > 0" class="flex justify-between text-slate-500">
                            <span>Due</span><span class="font-semibold text-rose-600">{{ money(printInvoiceData?.due_amount) }}</span>
                        </div>
                    </div>

                    <p v-if="printInvoiceData?.notes" class="mt-6 border-t border-slate-100 pt-2.5 text-xs text-slate-600">
                        <strong class="text-slate-700">Note:</strong> {{ printInvoiceData.notes }}
                    </p>

                    <p class="mt-8 text-center text-xs italic text-slate-400">Thank you for your order</p>
                </div>

                <div class="d-print-none flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-2.5">
                    <button type="button" class="rounded-md border-[1px] border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" @click="showInvoiceModal = false">
                        Close
                    </button>
                    <button type="button" class="rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700" @click="printInvoice">
                        <i class="fas fa-print mr-1.5"></i> Print Invoice
                    </button>
                </div>
            </div>
        </div>

        <!-- ---------------------------------------------------------- -->
        <!-- Quick add customer                                         -->
        <!-- ---------------------------------------------------------- -->
        <div v-if="showCustomerModal" class="d-print-none fixed inset-0 z-[1200] overflow-y-auto bg-slate-900/60 p-3.5 backdrop-blur-sm">
            <div class="mx-auto w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <i class="fas fa-user-plus text-xs"></i>
                        </span>
                        <p class="text-sm font-bold text-slate-800">Quick Add Customer</p>
                    </div>
                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" @click="showCustomerModal = false">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="submitQuickCustomer">
                    <div class="space-y-3 px-6 py-3.5">
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-slate-600">Name <span class="text-rose-500">*</span></span>
                            <input v-model="quickCustomerForm.name" type="text" required placeholder="Customer name" class="w-full rounded-md border-[1px] border-slate-200 px-2.5 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <span v-if="quickCustomerForm.errors.name" class="mt-1 block text-xs text-rose-600">{{ quickCustomerForm.errors.name }}</span>
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-slate-600">Phone <span class="text-rose-500">*</span></span>
                            <input v-model="quickCustomerForm.phone" type="text" required placeholder="01XXXXXXXXX" class="w-full rounded-md border-[1px] border-slate-200 px-2.5 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <span v-if="quickCustomerForm.errors.phone" class="mt-1 block text-xs text-rose-600">{{ quickCustomerForm.errors.phone }}</span>
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-slate-600">Email <span class="font-normal text-slate-400">(optional)</span></span>
                            <input v-model="quickCustomerForm.email" type="email" placeholder="name@example.com" class="w-full rounded-md border-[1px] border-slate-200 px-2.5 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-slate-600">Address <span class="font-normal text-slate-400">(optional)</span></span>
                            <textarea v-model="quickCustomerForm.address" rows="2" placeholder="Customer address" class="w-full rounded-md border-[1px] border-slate-200 px-2.5 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold text-slate-600">Division</span>
                                <select v-model="quickCustomerForm.division_id" class="w-full rounded-md border-[1px] border-slate-200 py-2 pl-2.5 pr-7 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100" @change="onQuickCustomerDivisionChange">
                                    <option value="">Select</option>
                                    <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold text-slate-600">District</span>
                                <select v-model="quickCustomerForm.district_id" :disabled="!quickCustomerForm.division_id" class="w-full rounded-md border-[1px] border-slate-200 py-2 pl-2.5 pr-7 text-sm text-slate-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 disabled:bg-slate-50 disabled:text-slate-400">
                                    <option value="">Select</option>
                                    <option v-for="district in quickCustomerDistrictOptions" :key="district.id" :value="district.id">{{ district.name }}</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-2.5">
                        <button type="button" class="rounded-md border-[1px] border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" @click="showCustomerModal = false">
                            Cancel
                        </button>
                        <button type="submit" :disabled="quickCustomerForm.processing" class="rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50">
                            <i class="fas fa-save mr-1.5"></i> Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.invoice-logo {
    max-height: 55px;
    max-width: 180px;
    object-fit: contain;
}

/*
 * AdminLayout's print sheet flattens Bootstrap's .modal wrappers; this dialog is
 * plain Tailwind, so it has to unpin itself. Scoped is fine here — these are
 * this component's own elements, unlike the sidebar rules that had to be global.
 */
@media print {
    .pos-overlay {
        position: static;
        overflow: visible;
        background: none;
        padding: 0;
        backdrop-filter: none;
    }

    .pos-sheet {
        max-width: none;
        border-radius: 0;
        box-shadow: none;
    }
}
</style>
