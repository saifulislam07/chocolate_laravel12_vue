<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, onUpdated, ref, watch } from 'vue';
import axios from 'axios';

// Shop details for the invoice header come from Settings, not hardcoded text.
const shop = computed(() => usePage().props.webSettings || {});

const props = defineProps({
    sale: Object,
    courierOptions: { type: Object, default: () => ({ pathao: false, steadfast: false }) },
});

const money = (value) => Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const orderDate = computed(() => new Date(props.sale.created_at).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
}));

// Orders store their own subtotal, but rows written before that column existed
// can arrive null — fall back to the line items rather than show a blank.
const subtotal = computed(() => (
    props.sale.subtotal ?? (props.sale.items || []).reduce(
        (sum, item) => sum + (Number(item.price) * Number(item.quantity)),
        0,
    )
));

const isPaid = computed(() => props.sale.payment_status === 'paid');
const amount = (value) => Number(value || 0);

function getPaymentBadge(status) {
    const s = status?.toLowerCase();
    if (s === 'paid') return 'badge-success';
    if (s === 'partial') return 'badge-warning';
    return 'badge-danger';
}

const statusForm = useForm({
    status: props.sale.status,
    payment_status: props.sale.payment_status,
});

function updateStatus() {
    statusForm.patch(route('admin.sales.update-status', props.sale.id), {
        preserveScroll: true,
    });
}

const shipForm = useForm({
    courier: props.courierOptions.steadfast ? 'steadfast' : 'pathao',
    city_id: '',
    zone_id: '',
    area_id: '',
});

const pathaoCities = ref([]);
const pathaoZones = ref([]);
const pathaoAreas = ref([]);

watch(() => shipForm.courier, (courier) => {
    if (courier === 'pathao' && pathaoCities.value.length === 0) {
        axios.get(route('admin.courier.pathao.cities')).then((res) => {
            pathaoCities.value = res.data || [];
        });
    }
});

watch(() => shipForm.city_id, (cityId) => {
    shipForm.zone_id = '';
    shipForm.area_id = '';
    pathaoZones.value = [];
    pathaoAreas.value = [];
    if (cityId) {
        axios.get(route('admin.courier.pathao.zones', cityId)).then((res) => {
            pathaoZones.value = res.data || [];
        });
    }
});

watch(() => shipForm.zone_id, (zoneId) => {
    shipForm.area_id = '';
    pathaoAreas.value = [];
    if (zoneId) {
        axios.get(route('admin.courier.pathao.areas', zoneId)).then((res) => {
            pathaoAreas.value = res.data || [];
        });
    }
});

function submitShip() {
    shipForm.post(route('admin.sales.ship', props.sale.id), {
        preserveScroll: true,
    });
}

// The printed sheet is drawn at true A5 with its own 10mm padding, so the page
// box has to contribute nothing of its own. Kept in a dedicated <style> node so
// it never lingers for other admin print views (reports, POS receipt) after an
// SPA navigation.
const A5_PAGE_RULE = '@page { size: A5 portrait; margin: 0; }';
let a5StyleEl = null;

// @page carries no specificity, so the last declaration wins — against both
// AdminLTE's `@page{size:a3}` and AdminLayout's A4 default. Staying last in
// <head> is what keeps this sheet A5: the layout is the parent, so it mounts
// after this component, and Inertia re-inserts its head links on every
// re-render.
function keepPageRuleLast() {
    if (a5StyleEl && document.head.lastElementChild !== a5StyleEl) {
        document.head.appendChild(a5StyleEl);
    }
}

function printInvoice() {
    keepPageRuleLast();
    window.print();
}

onMounted(() => {
    a5StyleEl = document.createElement('style');
    a5StyleEl.setAttribute('data-invoice-a5', '');
    a5StyleEl.textContent = A5_PAGE_RULE;
    document.head.appendChild(a5StyleEl);

    // Runs once the whole tree is mounted, so it is ordered after the layout.
    nextTick(keepPageRuleLast);
});

onUpdated(keepPageRuleLast);

// A browser-driven print (Ctrl+P) never passes through the button, so re-assert
// the rule right before the print dialog reads the stylesheets.
if (typeof window !== 'undefined') {
    window.addEventListener('beforeprint', keepPageRuleLast);
}

onBeforeUnmount(() => {
    window.removeEventListener('beforeprint', keepPageRuleLast);
    a5StyleEl?.remove();
    a5StyleEl = null;
});
</script>

<template>
    <Head :title="'Invoice ' + sale.order_number" />

    <AdminLayout>
        <!--
            Two renderings of one order, on purpose: the screen view below is the
            admin's working page, and the A5 sheet at the bottom is the document
            that goes on paper. Each is hidden from the other medium, so the print
            layout can be redrawn without touching the page the operator works on.
        -->
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Sale Details</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <Link :href="route('admin.sales.index')" class="btn btn-default btn-sm mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to List
                        </Link>
                        <button type="button" class="btn btn-primary btn-sm mr-2" @click="printInvoice">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                        <Link :href="route('admin.returns.create', { order_id: sale.id })" class="btn btn-danger btn-sm">
                            <i class="fas fa-undo mr-1"></i> Process Return
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <section class="content d-print-none">
            <div class="container-fluid">
                <!-- Admin controls: everything that is not part of the document itself -->
                <div class="card card-outline card-primary shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-cogs mr-1"></i> Order Management
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="form-row align-items-end">
                            <div class="col-md-3 form-group mb-2">
                                <label class="text-muted text-uppercase small mb-1">Order Status</label>
                                <select v-model="statusForm.status" class="form-control form-control-sm">
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="partially_returned">Partially Returned</option>
                                    <option value="returned">Returned</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="text-muted text-uppercase small mb-1">Payment Status</label>
                                <select v-model="statusForm.payment_status" class="form-control form-control-sm">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <button type="button" class="btn btn-primary btn-sm" :disabled="statusForm.processing" @click="updateStatus">
                                    <i class="fas fa-save mr-1"></i> Update Status
                                </button>
                            </div>
                        </div>

                        <hr>

                        <div v-if="sale.shipments?.length" class="mb-3">
                            <p class="text-muted text-uppercase font-weight-bold mb-2 small">Shipments</p>
                            <div v-for="shipment in sale.shipments" :key="shipment.id"
                                 class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                                <span>
                                    <span class="badge badge-info text-uppercase mr-1">{{ shipment.courier }}</span>
                                    Tracking: <strong>{{ shipment.tracking_code || 'N/A' }}</strong>
                                </span>
                                <span class="badge badge-secondary">{{ shipment.status }}</span>
                            </div>
                        </div>

                        <p v-if="!courierOptions.pathao && !courierOptions.steadfast" class="text-muted small mb-0">
                            No courier is configured yet. Add Pathao or Steadfast credentials in
                            <Link :href="route('admin.settings.index')">Settings &rarr; Courier</Link>.
                        </p>
                        <div v-else class="form-row align-items-end">
                            <div class="col-md-3 form-group mb-2">
                                <label class="text-muted text-uppercase small mb-1">Courier</label>
                                <select v-model="shipForm.courier" class="form-control form-control-sm">
                                    <option v-if="courierOptions.steadfast" value="steadfast">Steadfast</option>
                                    <option v-if="courierOptions.pathao" value="pathao">Pathao</option>
                                </select>
                            </div>

                            <template v-if="shipForm.courier === 'pathao'">
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-muted text-uppercase small mb-1">City</label>
                                    <select v-model="shipForm.city_id" class="form-control form-control-sm">
                                        <option value="">Select City</option>
                                        <option v-for="city in pathaoCities" :key="city.city_id" :value="city.city_id">{{ city.city_name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-muted text-uppercase small mb-1">Zone</label>
                                    <select v-model="shipForm.zone_id" class="form-control form-control-sm" :disabled="!shipForm.city_id">
                                        <option value="">Select Zone</option>
                                        <option v-for="zone in pathaoZones" :key="zone.zone_id" :value="zone.zone_id">{{ zone.zone_name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-muted text-uppercase small mb-1">Area</label>
                                    <select v-model="shipForm.area_id" class="form-control form-control-sm" :disabled="!shipForm.zone_id">
                                        <option value="">Select Area</option>
                                        <option v-for="area in pathaoAreas" :key="area.area_id" :value="area.area_id">{{ area.area_name }}</option>
                                    </select>
                                </div>
                            </template>

                            <div class="col-md-3 form-group mb-2">
                                <button type="button" class="btn btn-success btn-sm" :disabled="shipForm.processing" @click="submitShip">
                                    <i class="fas fa-truck mr-1"></i> Book Shipment
                                </button>
                            </div>
                        </div>
                        <p v-if="shipForm.errors.courier" class="text-danger small mb-0 mt-2">{{ shipForm.errors.courier }}</p>
                    </div>
                </div>

                <!-- Screen view of the sale -->
                <div class="invoice p-3 mb-3 shadow-sm border-0" style="border-radius: 15px;">
                    <!-- title row -->
                    <div class="row align-items-start">
                        <div class="col-12 col-md-8 d-flex align-items-center">
                            <img v-if="shop.logo" :src="shop.logo" :alt="shop.site_name || ''" class="screen-logo mr-3">
                            <div>
                                <h4 class="mb-1">
                                    <i v-if="!shop.logo" class="fas fa-file-invoice mr-2 text-primary"></i>{{ shop.site_name || 'SWEET CHOCOLATE' }}
                                </h4>
                                <p v-if="shop.address || shop.phone || shop.email" class="text-muted small mb-0">
                                    <span v-if="shop.address">{{ shop.address }}</span>
                                    <span v-if="shop.address && (shop.phone || shop.email)"> &middot; </span>
                                    <span v-if="shop.phone">{{ shop.phone }}</span>
                                    <span v-if="shop.phone && shop.email"> &middot; </span>
                                    <span v-if="shop.email">{{ shop.email }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-right">
                            <div class="text-muted text-uppercase small invoice-word">Invoice</div>
                            <div class="h6 font-weight-bold mb-0">#{{ sale.order_number }}</div>
                            <small class="text-muted">Date: {{ orderDate }}</small>
                        </div>
                    </div>

                    <!-- info row -->
                    <div class="row invoice-info mt-4">
                        <div class="col-sm-4 invoice-col border-right">
                            <p class="text-muted text-uppercase font-weight-bold mb-2 small">Customer Details</p>
                            <address v-if="sale.order_source === 'pos'">
                                <strong class="h5 text-primary">{{ sale.customer?.name || 'Walk-in Customer' }}</strong><br>
                                {{ sale.customer?.address || 'Counter Sale' }}<br>
                                Phone: {{ sale.customer?.phone || 'N/A' }}<br>
                                Email: {{ sale.customer?.email || 'N/A' }}
                            </address>
                            <address v-else>
                                <strong class="h5 text-primary">{{ sale.customer_name || sale.user?.name || 'Guest User' }}</strong><br>
                                <span class="address-lines">{{ sale.shipping_address || 'N/A' }}</span><br>
                                Phone: {{ sale.customer_phone || 'N/A' }}<br>
                                Email: {{ sale.customer?.email || sale.user?.email || 'N/A' }}
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col border-right pl-4">
                            <p class="text-muted text-uppercase font-weight-bold mb-2 small">Bill Information</p>
                            <b>Payment:</b> <span class="badge ml-1" :class="getPaymentBadge(sale.payment_status)">{{ sale.payment_status }}</span>
                            <span class="text-muted text-uppercase small ml-1">{{ sale.payment_method }}</span><br>
                            <template v-if="sale.lead_source">
                                <b>Lead Source:</b> {{ sale.lead_source }}<br>
                            </template>
                            <b>Order Date:</b> {{ orderDate }}
                        </div>
                        <div class="col-sm-4 invoice-col pl-4">
                             <p class="text-muted text-uppercase font-weight-bold mb-2 small">Payment Summary</p>
                             <div class="bg-light p-2 rounded">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Bill:</span>
                                    <strong class="text-dark">৳{{ money(sale.total) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Paid:</span>
                                    <strong class="text-success">৳{{ money(sale.paid_amount) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                    <span class="font-weight-bold">Due:</span>
                                    <strong class="text-danger">৳{{ money(sale.due_amount) }}</strong>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Table row -->
                    <div class="row mt-4">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped table-hover border">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Product / SKU</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in sale.items" :key="item.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ item.product_name || item.product?.name }}</span><br>
                                            <small class="text-muted">SKU: {{ item.product?.sku || 'N/A' }}</small>
                                        </td>
                                        <td class="text-center align-middle h6">{{ item.quantity }}</td>
                                        <td class="text-right align-middle">৳{{ money(item.price) }}</td>
                                        <td class="text-right align-middle font-weight-bold">৳{{ money(item.price * item.quantity) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- notes column -->
                        <div class="col-12 col-md-6">
                            <p class="lead font-weight-bold text-muted small text-uppercase">Terms &amp; Notes:</p>
                            <div v-if="sale.notes" class="text-muted bg-light p-3 rounded" style="min-height: 100px;">{{ sale.notes }}</div>
                            <div v-else class="text-muted bg-light p-3 rounded" style="min-height: 100px;">
                                No notes available for this sale.
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-12 col-md-6">
                            <p class="lead font-weight-bold text-muted small text-uppercase text-right">Payment Calculation:</p>

                            <div class="table-responsive">
                                <table class="table border">
                                    <tr>
                                        <th style="width:50%">Items Subtotal:</th>
                                        <td class="text-right font-weight-bold">৳{{ money(subtotal) }}</td>
                                    </tr>
                                    <tr v-if="amount(sale.discount) > 0">
                                        <th class="text-danger">Discount:</th>
                                        <td class="text-right text-danger">-৳{{ money(sale.discount) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tax:</th>
                                        <td class="text-right">৳{{ money(sale.tax) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping:</th>
                                        <td class="text-right">৳{{ money(sale.shipping_cost) }}</td>
                                    </tr>
                                    <tr class="bg-primary text-white">
                                        <th class="h5">Grand Total:</th>
                                        <td class="text-right h5 font-weight-bold">৳{{ money(sale.total) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-success">Paid:</th>
                                        <td class="text-right text-success">৳{{ money(sale.paid_amount) }}</td>
                                    </tr>
                                    <tr v-if="amount(sale.due_amount) > 0">
                                        <th class="text-danger">Due:</th>
                                        <td class="text-right text-danger">৳{{ money(sale.due_amount) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--
            The printed document. Hidden on screen and drawn at true A5, so the
            paper layout is free of the admin page's proportions. AdminLayout
            zeroes width/padding on #invoice-print-area, leaving the sheet its own
            geometry.
        -->
        <div id="invoice-print-area" class="print-only">
            <article class="sheet">
                <header class="head">
                    <div class="issuer">
                        <img v-if="shop.logo" :src="shop.logo" :alt="shop.site_name || ''" class="logo">
                        <div class="shop">{{ shop.site_name || 'Invoice' }}</div>
                        <div class="muted" v-if="shop.address">{{ shop.address }}</div>
                        <div class="muted">
                            <span v-if="shop.phone">{{ shop.phone }}</span>
                            <span v-if="shop.phone && shop.email"> · </span>
                            <span v-if="shop.email">{{ shop.email }}</span>
                        </div>
                    </div>
                    <div class="meta">
                        <div class="word">Invoice</div>
                        <div class="number">#{{ sale.order_number }}</div>
                        <div class="muted">Order Date: {{ orderDate }}</div>
                        <div class="stamp" :class="isPaid ? 'stamp-ok' : 'stamp-due'">{{ sale.payment_status }}</div>
                    </div>
                </header>

                <section class="parties">
                    <div class="party">
                        <div class="label">Billed To</div>
                        <template v-if="sale.order_source === 'pos'">
                            <div class="strong">{{ sale.customer?.name || 'Walk-in Customer' }}</div>
                            <div v-if="sale.customer?.address">{{ sale.customer.address }}</div>
                            <div v-if="sale.customer?.phone">{{ sale.customer.phone }}</div>
                            <div v-if="sale.customer?.email">{{ sale.customer.email }}</div>
                        </template>
                        <template v-else>
                            <div class="strong">{{ sale.customer_name || sale.user?.name || 'Guest User' }}</div>
                            <div v-if="sale.shipping_address" class="address-lines">{{ sale.shipping_address }}</div>
                            <div v-if="sale.customer_phone">{{ sale.customer_phone }}</div>
                            <div v-if="sale.customer?.email || sale.user?.email">{{ sale.customer?.email || sale.user?.email }}</div>
                        </template>
                    </div>
                    <div class="party party-right">
                        <div class="label">Payment</div>
                        <div class="strong upper">{{ sale.payment_method }}</div>
                        <div class="muted upper" v-if="sale.lead_source">{{ sale.lead_source }}</div>
                    </div>
                </section>

                <table class="items">
                    <thead>
                        <tr>
                            <th class="idx">#</th>
                            <th>Item</th>
                            <th class="num">Qty</th>
                            <th class="num">Price</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in sale.items" :key="item.id">
                            <td class="idx">{{ index + 1 }}</td>
                            <td>
                                <div>{{ item.product_name || item.product?.name }}</div>
                                <div class="sku" v-if="item.product?.sku">{{ item.product.sku }}</div>
                            </td>
                            <td class="num">{{ item.quantity }}</td>
                            <td class="num">৳{{ money(item.price) }}</td>
                            <td class="num">৳{{ money(item.price * item.quantity) }}</td>
                        </tr>
                    </tbody>
                </table>

                <section class="summary">
                    <p class="in-words"><span class="label">In Words</span>{{ sale.total_in_words }}</p>

                    <div class="totals">
                        <div class="trow"><span>Subtotal</span><span>৳{{ money(subtotal) }}</span></div>
                        <div class="trow" v-if="amount(sale.discount) > 0"><span>Discount</span><span class="due">-৳{{ money(sale.discount) }}</span></div>
                        <div class="trow" v-if="amount(sale.tax) > 0"><span>Tax</span><span>৳{{ money(sale.tax) }}</span></div>
                        <div class="trow" v-if="amount(sale.shipping_cost) > 0"><span>Shipping</span><span>৳{{ money(sale.shipping_cost) }}</span></div>
                        <div class="trow grand"><span>Total</span><span>৳{{ money(sale.total) }}</span></div>
                        <div class="trow"><span>Paid</span><span class="ok">৳{{ money(sale.paid_amount) }}</span></div>
                        <div class="trow" v-if="amount(sale.due_amount) > 0"><span>Due</span><span class="due">৳{{ money(sale.due_amount) }}</span></div>
                    </div>
                </section>

                <p class="note" v-if="sale.notes"><span class="label">Note</span>{{ sale.notes }}</p>

                <footer class="foot">
                    <div class="signs">
                        <div class="sign">Customer Signature</div>
                        <div class="sign">Authorised Signature</div>
                    </div>
                    <p class="thanks">Thank you for your order</p>
                </footer>
            </article>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* ---------------------------------------------------------------- *
 * Screen view
 * ---------------------------------------------------------------- */

.screen-logo {
    max-height: 60px;
    max-width: 170px;
    object-fit: contain;
}

.invoice-word { letter-spacing: 0.18em; }

.address-lines { white-space: pre-line; }

/* The sheet exists only on paper; keeping it out of the flow means the screen
   page is untouched by everything below. */
.print-only { display: none; }


/* ---------------------------------------------------------------- *
 * The printed document — A5, minimal, one sheet
 *
 * Only three weights of ink are used: near-black for the figures that
 * matter, grey for labels, and a single hairline for structure. No fills,
 * no boxes — so nothing depends on the print dialog's "background
 * graphics" setting, and the sheet stays readable on a cheap printer.
 * ---------------------------------------------------------------- */

@media print {
    .print-only { display: block; }

    .sheet {
        box-sizing: border-box;
        /* Centred and capped at A5 so the document keeps its proportions even
           when the browser or the printer overrides the page box with A4. */
        width: 148mm;
        max-width: 100%;
        margin: 0 auto;
        /* 206mm rather than the full 210mm because a box exactly as tall as the
           page can round into a blank second sheet. */
        min-height: 206mm;
        /* The margin belongs to the sheet, not to @page: the print dialog's own
           margin setting overrides a page box, and on "None" the document would
           print hard against the top edge. Padding answers to nobody, and a
           block box repeats its horizontal padding on every sheet it runs onto. */
        padding: 12mm 11mm;
        background: #fff;
        color: #111;
        font-size: 10.5px;
        line-height: 1.5;
        /* Block, not flex: Chrome will not fragment a flex item across sheets,
           so a flex sheet moved the whole items table to page two and left the
           first one holding nothing but the address. */
        display: block;
        /* Keep the hairlines; without this some browsers drop them and the
           sheet loses the little structure it has. */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
    }

    .logo {
        display: block;
        max-height: 30px;
        max-width: 130px;
        object-fit: contain;
        margin-bottom: 6px;
    }

    .shop {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .muted {
        color: #8c8c8c;
        font-size: 9.5px;
    }

    .meta {
        text-align: right;
        flex-shrink: 0;
    }

    .word {
        text-transform: uppercase;
        letter-spacing: 0.28em;
        font-size: 8.5px;
        color: #9a9a9a;
    }

    .number {
        font-weight: 700;
        font-size: 12px;
        margin: 1px 0 1px;
    }

    /* A word, not a badge — a bordered stamp is one box too many here. */
    .stamp {
        margin-top: 3px;
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .stamp-ok { color: #1a7f4b; }
    .stamp-due { color: #c0392b; }

    .parties {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 6mm;
    }

    .party { max-width: 62%; }

    .party-right {
        text-align: right;
        flex-shrink: 0;
    }

    .label {
        text-transform: uppercase;
        letter-spacing: 0.16em;
        font-size: 8px;
        color: #a3a3a3;
        margin-bottom: 3px;
    }

    .strong { font-weight: 700; }

    .upper {
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ok { color: #1a7f4b; }
    .due { color: #c0392b; }

    .items {
        width: 100%;
        border-collapse: collapse;
        /* Tighter than the sheet's 1.5: with one line per name and one per SKU,
           every notch here is paid twice on every row. */
        line-height: 1.3;
    }

    /* A rule instead of a filled band: the head reads as a caption, and the
       eye goes to the amounts. */
    .items th {
        text-align: left;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: #a3a3a3;
        font-weight: 600;
        border-bottom: 1px solid #111;
        /* The gap above the table lives here rather than on .items, because a
           thead repeats on every sheet and a margin does not: this is what
           keeps page two off the top edge. */
        padding: 6mm 4px 5px;
    }

    .items td {
        padding: 2.5px 4px;
        border-bottom: 1px solid #ececec;
        vertical-align: top;
    }

    .items tr { page-break-inside: avoid; }

    .items .num {
        text-align: right;
        white-space: nowrap;
    }

    .items .idx {
        width: 20px;
        text-align: left;
        color: #b0b0b0;
    }

    .sku {
        color: #a3a3a3;
        font-size: 8px;
        line-height: 1.2;
        letter-spacing: 0.02em;
    }

    /* The figures have always sat in the right half; the words go in the left
       half that was empty beside them, so spelling the total out costs the
       items table no room at all. */
    .summary {
        display: flex;
        align-items: flex-start;
        gap: 8mm;
        margin-top: 6mm;
        page-break-inside: avoid;
    }

    .in-words {
        flex: 1;
        margin: 0;
        font-size: 9.5px;
        line-height: 1.35;
        color: #444;
    }

    .in-words .label { display: block; }

    .totals {
        width: 52%;
        flex-shrink: 0;
    }

    .trow {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 2.5px 4px;
        color: #555;
    }

    /* The one figure the reader is looking for. */
    .grand {
        border-top: 1px solid #111;
        margin-top: 3px;
        padding-top: 5px;
        padding-bottom: 5px;
        font-weight: 700;
        font-size: 12px;
        color: #111;
    }

    .note {
        margin-top: 8mm;
        font-size: 9.5px;
        color: #6b6b6b;
        max-width: 88mm;
    }

    .note .label { display: block; }

    .foot {
        margin-top: 8mm;
        page-break-inside: avoid;
    }

    .signs {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        padding-top: 10mm;
    }

    .sign {
        border-top: 1px solid #cfcfcf;
        padding-top: 4px;
        min-width: 42mm;
        text-align: center;
        font-size: 8.5px;
        letter-spacing: 0.04em;
        color: #9a9a9a;
    }

    .thanks {
        margin: 6mm 0 0;
        text-align: center;
        font-size: 9px;
        letter-spacing: 0.06em;
        color: #b0b0b0;
    }
}
</style>
