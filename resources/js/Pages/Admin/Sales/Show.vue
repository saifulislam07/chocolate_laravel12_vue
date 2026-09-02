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

const money = (value) => '৳' + Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const issuedOn = computed(() => new Date(props.sale.created_at).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
}));

const isPaid = computed(() => props.sale.payment_status === 'paid');

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

const printInvoice = () => {
    keepPageRuleLast();
    window.print();
};

// The sheet on screen is drawn at true A5 size with its own 10mm padding, so the
// page box has to contribute nothing of its own — otherwise the printed copy is
// laid out differently from the one the operator just approved on screen.
// Kept in a dedicated <style> node so it never lingers for other admin print
// views (reports, POS receipt) after an SPA navigation.
const A5_PAGE_RULE = '@page { size: A5; margin: 0; }';
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
    <Head :title="`Invoice ${sale.order_number}`" />

    <AdminLayout>
        <!-- Toolbar -->
        <div class="sale-bar d-print-none">
            <div class="sale-bar-left">
                <Link :href="route('admin.sales.index')" class="sale-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </Link>
                <span class="sale-bar-title">Invoice #{{ sale.order_number }}</span>
                <span class="sale-chip" :class="sale.order_source === 'pos' ? 'sale-chip-pos' : 'sale-chip-web'">
                    {{ sale.order_source === 'pos' ? 'POS' : 'WEB' }}
                </span>
                <span class="sale-chip" :class="isPaid ? 'sale-chip-ok' : 'sale-chip-due'">{{ sale.payment_status }}</span>
                <span v-if="sale.lead_source" class="sale-chip sale-chip-lead">
                    <i class="fas fa-bullhorn"></i> {{ sale.lead_source }}
                </span>
            </div>
            <div class="sale-bar-right">
                <Link :href="route('admin.returns.create', { order_id: sale.id })" class="sale-btn sale-btn-danger">
                    <i class="fas fa-undo"></i> Process Return
                </Link>
                <button type="button" class="sale-btn sale-btn-primary" @click="printInvoice">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
            </div>
        </div>

        <!-- Admin controls: everything that is not part of the document itself -->
        <div class="sale-panel d-print-none">
            <div class="sale-row">
                <label class="sale-field">
                    <span class="sale-label">Order Status</span>
                    <select v-model="statusForm.status" class="sale-input">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="partially_returned">Partially Returned</option>
                        <option value="returned">Returned</option>
                    </select>
                </label>
                <label class="sale-field">
                    <span class="sale-label">Payment Status</span>
                    <select v-model="statusForm.payment_status" class="sale-input">
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                    </select>
                </label>
                <button type="button" class="sale-btn sale-btn-primary" :disabled="statusForm.processing" @click="updateStatus">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>

            <div class="sale-divider"></div>

            <div v-if="sale.shipments?.length" class="sale-shipments">
                <div v-for="shipment in sale.shipments" :key="shipment.id" class="sale-shipment">
                    <span>
                        <span class="sale-chip sale-chip-web">{{ shipment.courier }}</span>
                        Tracking: <strong>{{ shipment.tracking_code || 'N/A' }}</strong>
                    </span>
                    <span class="sale-chip">{{ shipment.status }}</span>
                </div>
            </div>

            <p v-if="!courierOptions.pathao && !courierOptions.steadfast" class="sale-hint">
                No courier is configured yet. Add Pathao or Steadfast credentials in
                <Link :href="route('admin.settings.index')">Settings &rarr; Courier</Link>.
            </p>
            <div v-else class="sale-row">
                <label class="sale-field">
                    <span class="sale-label">Courier</span>
                    <select v-model="shipForm.courier" class="sale-input">
                        <option v-if="courierOptions.steadfast" value="steadfast">Steadfast</option>
                        <option v-if="courierOptions.pathao" value="pathao">Pathao</option>
                    </select>
                </label>

                <template v-if="shipForm.courier === 'pathao'">
                    <label class="sale-field">
                        <span class="sale-label">City</span>
                        <select v-model="shipForm.city_id" class="sale-input">
                            <option value="">Select City</option>
                            <option v-for="city in pathaoCities" :key="city.city_id" :value="city.city_id">{{ city.city_name }}</option>
                        </select>
                    </label>
                    <label class="sale-field">
                        <span class="sale-label">Zone</span>
                        <select v-model="shipForm.zone_id" class="sale-input" :disabled="!shipForm.city_id">
                            <option value="">Select Zone</option>
                            <option v-for="zone in pathaoZones" :key="zone.zone_id" :value="zone.zone_id">{{ zone.zone_name }}</option>
                        </select>
                    </label>
                    <label class="sale-field">
                        <span class="sale-label">Area</span>
                        <select v-model="shipForm.area_id" class="sale-input" :disabled="!shipForm.zone_id">
                            <option value="">Select Area</option>
                            <option v-for="area in pathaoAreas" :key="area.area_id" :value="area.area_id">{{ area.area_name }}</option>
                        </select>
                    </label>
                </template>

                <button type="button" class="sale-btn sale-btn-success" :disabled="shipForm.processing" @click="submitShip">
                    <i class="fas fa-truck"></i> Book Shipment
                </button>
            </div>
            <p v-if="shipForm.errors.courier" class="sale-error">{{ shipForm.errors.courier }}</p>
        </div>

        <!--
            The wrapper carries the print id: AdminLayout forces width/padding to
            zero on it, so the sheet inside keeps its own A5 geometry and what is
            printed is exactly what is on screen.
        -->
        <div id="invoice-print-area">
            <article class="invoice-sheet">
                <header class="inv-head">
                    <div class="inv-issuer">
                        <img v-if="shop.logo" :src="shop.logo" alt="" class="inv-logo">
                        <div class="inv-shop">{{ shop.site_name || 'Invoice' }}</div>
                        <div class="inv-muted" v-if="shop.address">{{ shop.address }}</div>
                        <div class="inv-muted">
                            <span v-if="shop.phone">{{ shop.phone }}</span>
                            <span v-if="shop.phone && shop.email"> · </span>
                            <span v-if="shop.email">{{ shop.email }}</span>
                        </div>
                    </div>
                    <div class="inv-meta">
                        <div class="inv-title">Invoice</div>
                        <div class="inv-number">#{{ sale.order_number }}</div>
                        <div class="inv-muted">{{ issuedOn }}</div>
                        <div class="inv-stamp" :class="isPaid ? 'inv-stamp-ok' : 'inv-stamp-due'">{{ sale.payment_status }}</div>
                    </div>
                </header>

                <section class="inv-parties">
                    <div class="inv-party">
                        <div class="inv-label">Billed To</div>
                        <template v-if="sale.order_source === 'pos'">
                            <div class="inv-strong">{{ sale.customer?.name || 'Walk-in Customer' }}</div>
                            <div v-if="sale.customer?.address">{{ sale.customer.address }}</div>
                            <div v-if="sale.customer?.phone">{{ sale.customer.phone }}</div>
                            <div v-if="sale.customer?.email">{{ sale.customer.email }}</div>
                        </template>
                        <template v-else>
                            <div class="inv-strong">{{ sale.customer_name || sale.user?.name || 'Guest User' }}</div>
                            <div v-if="sale.shipping_address" class="inv-address">{{ sale.shipping_address }}</div>
                            <div v-if="sale.customer_phone || sale.customer?.email || sale.user?.email">
                                <span v-if="sale.customer_phone">{{ sale.customer_phone }}</span>
                                <span v-if="sale.customer_phone && (sale.customer?.email || sale.user?.email)"> · </span>
                                <span v-if="sale.customer?.email || sale.user?.email">{{ sale.customer?.email || sale.user?.email }}</span>
                            </div>
                        </template>
                    </div>
                    <div class="inv-party inv-party-right">
                        <div class="inv-label">Payment</div>
                        <div class="inv-strong inv-upper">{{ sale.payment_method }}</div>
                        <div class="inv-muted inv-upper">{{ sale.order_source === 'pos' ? 'Counter Sale' : 'Online Order' }}</div>
                        <div class="inv-muted inv-upper">{{ sale.status }}</div>
                    </div>
                </section>

                <table class="inv-table">
                    <thead>
                        <tr>
                            <th class="inv-idx">#</th>
                            <th>Item</th>
                            <th class="num">Qty</th>
                            <th class="num">Price</th>
                            <th class="num">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in sale.items" :key="item.id">
                            <td class="inv-idx">{{ index + 1 }}</td>
                            <td>{{ item.product_name }}</td>
                            <td class="num">{{ item.quantity }}</td>
                            <td class="num">{{ money(item.price) }}</td>
                            <td class="num">{{ money(item.price * item.quantity) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="inv-totals">
                    <div class="inv-row"><span>Subtotal</span><span>{{ money(sale.subtotal) }}</span></div>
                    <div class="inv-row" v-if="parseFloat(sale.discount) > 0"><span>Discount</span><span class="inv-due">-{{ money(sale.discount) }}</span></div>
                    <div class="inv-row" v-if="parseFloat(sale.tax) > 0"><span>Tax</span><span>{{ money(sale.tax) }}</span></div>
                    <div class="inv-row" v-if="parseFloat(sale.shipping_cost) > 0"><span>Shipping</span><span>{{ money(sale.shipping_cost) }}</span></div>
                    <div class="inv-row inv-grand"><span>Total</span><span>{{ money(sale.total) }}</span></div>
                    <div class="inv-row" v-if="parseFloat(sale.paid_amount) > 0"><span>Paid</span><span class="inv-ok">{{ money(sale.paid_amount) }}</span></div>
                    <div class="inv-row" v-if="parseFloat(sale.due_amount) > 0"><span>Due</span><span class="inv-due">{{ money(sale.due_amount) }}</span></div>
                </div>

                <p class="inv-note" v-if="sale.notes"><strong>Note:</strong> {{ sale.notes }}</p>

                <footer class="inv-foot">
                    <div class="inv-signs">
                        <div class="inv-sign">Customer Signature</div>
                        <div class="inv-sign">Authorised Signature</div>
                    </div>
                    <p class="inv-thanks">Thank you for your order</p>
                </footer>
            </article>
        </div>
    </AdminLayout>
</template>

<!--
    Print rules that hide the admin chrome live unscoped in AdminLayout: a scoped
    `body *` cannot reach the sidebar. Everything below is the document's own look,
    and it is deliberately written once for both screen and paper — the sheet is
    already drawn at A5, so printing changes nothing but the paper affordances.
-->
<style scoped>
/* ---------------------------------------------------------------- *
 * Toolbar and admin controls (screen only)
 * ---------------------------------------------------------------- */

.sale-bar,
.sale-panel {
    max-width: 148mm;
    margin: 0 auto 12px;
}

.sale-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.sale-bar-left,
.sale-bar-right {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}

.sale-bar-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin-right: 2px;
}

.sale-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: #f1f5f9;
    color: #475569;
}

.sale-chip-pos { background: #ecfdf5; color: #047857; }
.sale-chip-web { background: #eef2ff; color: #4338ca; }
.sale-chip-ok { background: #ecfdf5; color: #047857; }
.sale-chip-due { background: #fef2f2; color: #b91c1c; }
.sale-chip-lead { background: #fff7ed; color: #c2410c; }

.sale-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.sale-btn:hover { background: #f8fafc; color: #0f172a; text-decoration: none; }
.sale-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.sale-btn-primary { background: #4f46e5; border-color: #4f46e5; color: #fff; }
.sale-btn-primary:hover { background: #4338ca; border-color: #4338ca; color: #fff; }

.sale-btn-success { background: #059669; border-color: #059669; color: #fff; }
.sale-btn-success:hover { background: #047857; border-color: #047857; color: #fff; }

.sale-btn-danger { color: #b91c1c; border-color: #fecaca; }
.sale-btn-danger:hover { background: #fef2f2; color: #991b1b; }

.sale-panel {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    padding: 12px;
}

.sale-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 10px;
}

.sale-field { display: block; margin: 0; }

.sale-label {
    display: block;
    margin-bottom: 3px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
}

.sale-input {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    padding: 6px 8px;
    font-size: 12px;
    color: #334155;
    min-width: 140px;
}

.sale-input:disabled { background: #f8fafc; color: #94a3b8; }

.sale-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 12px 0;
}

.sale-shipments { margin-bottom: 10px; }

.sale-shipment {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 10px;
    margin-bottom: 6px;
    font-size: 12px;
    color: #475569;
}

.sale-hint,
.sale-error {
    margin: 0;
    font-size: 12px;
}

.sale-hint { color: #64748b; }
.sale-error { margin-top: 8px; color: #b91c1c; }

/* ---------------------------------------------------------------- *
 * The document — identical on screen and on paper
 * ---------------------------------------------------------------- */

.invoice-sheet {
    box-sizing: border-box;
    width: 148mm;
    max-width: 100%;
    min-height: 210mm;
    margin: 0 auto 1.5rem;
    padding: 10mm;
    background: #fff;
    border: 1px solid #e6e6e6;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    color: #1c1c1c;
    font-size: 12px;
    line-height: 1.45;
    display: flex;
    flex-direction: column;
    /* Keep the rules and tints the operator approved on screen; without this the
       browser drops backgrounds and the printed sheet is a different document. */
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.inv-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding-bottom: 9px;
    border-bottom: 2px solid #1c1c1c;
}

.inv-logo {
    display: block;
    max-height: 30px;
    max-width: 130px;
    object-fit: contain;
    margin-bottom: 4px;
}

.inv-shop {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.inv-muted {
    color: #8a8a8a;
    font-size: 11px;
}

.inv-meta {
    text-align: right;
    flex-shrink: 0;
}

.inv-title {
    text-transform: uppercase;
    letter-spacing: 0.22em;
    font-size: 11px;
    color: #8a8a8a;
}

.inv-number {
    font-weight: 700;
    font-size: 13px;
}

.inv-stamp {
    display: inline-block;
    margin-top: 4px;
    border: 1px solid currentColor;
    border-radius: 3px;
    padding: 1px 7px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.inv-stamp-ok { color: #1a7f4b; }
.inv-stamp-due { color: #c0392b; }

.inv-parties {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin: 10px 0 2px;
}

.inv-party { max-width: 60%; }

.inv-party-right {
    text-align: right;
    flex-shrink: 0;
}

.inv-address { white-space: pre-line; }

.inv-label {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 10px;
    color: #9a9a9a;
    margin-bottom: 2px;
}

.inv-strong { font-weight: 700; }

.inv-upper {
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.04em;
}

.inv-ok { color: #1a7f4b; }
.inv-due { color: #c0392b; }

.inv-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.inv-table th {
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b6b6b;
    font-weight: 700;
    background: #f6f6f6;
    border-bottom: 1px solid #1c1c1c;
    padding: 6px 5px;
}

.inv-table td {
    padding: 6px 5px;
    border-bottom: 1px solid #ededed;
    vertical-align: top;
}

.inv-table .num {
    text-align: right;
    white-space: nowrap;
}

.inv-table .inv-idx {
    width: 22px;
    text-align: center;
    color: #9a9a9a;
}

.inv-totals {
    width: 58%;
    margin-left: auto;
    margin-top: 12px;
}

.inv-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 3px 5px;
}

.inv-grand {
    border-top: 1px solid #1c1c1c;
    border-bottom: 2px solid #1c1c1c;
    margin-top: 4px;
    padding-top: 6px;
    padding-bottom: 6px;
    font-weight: 700;
    font-size: 14px;
}

.inv-note {
    margin-top: 12px;
    font-size: 11px;
    color: #555;
}

/* Pinned to the bottom of the sheet, not floating right after the content. */
.inv-foot { margin-top: auto; }

.inv-signs {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    padding-top: 26px;
}

.inv-sign {
    border-top: 1px solid #b9b9b9;
    padding-top: 4px;
    min-width: 44mm;
    text-align: center;
    font-size: 10px;
    color: #8a8a8a;
}

.inv-thanks {
    margin: 10px 0 0;
    text-align: center;
    font-size: 11px;
    color: #9a9a9a;
}

@media print {
    /*
     * Only the paper affordances go — geometry, type and spacing are untouched,
     * so the print is the same document the operator saw. 208mm rather than the
     * full 210mm because a box exactly as tall as the page can round into a
     * blank second sheet.
     */
    .invoice-sheet {
        min-height: 208mm;
        margin: 0;
        border: 0;
        box-shadow: none;
        max-width: none;
    }
}
</style>
