<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';

// Shop details for the invoice header come from Settings, not hardcoded text.
const shop = computed(() => usePage().props.webSettings || {});

const props = defineProps({
    sale: Object,
    courierOptions: { type: Object, default: () => ({ pathao: false, steadfast: false }) },
});

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
    window.print();
};

// Force the print sheet to A5 only while this page is on screen. Kept in a
// dedicated <style> node so it never lingers for other admin print views
// (reports, POS receipt) after an SPA navigation.
const A5_PAGE_RULE = '@page { size: A5; margin: 10mm; }';
let a5StyleEl = null;

onMounted(() => {
    a5StyleEl = document.createElement('style');
    a5StyleEl.setAttribute('data-invoice-a5', '');
    a5StyleEl.textContent = A5_PAGE_RULE;
    document.head.appendChild(a5StyleEl);
});

onBeforeUnmount(() => {
    a5StyleEl?.remove();
    a5StyleEl = null;
});
</script>

<template>
    <Head title="Sale Details/Invoice" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Invoice #{{ sale.order_number }}</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <Link :href="route('admin.sales.index')" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </Link>
                        <Link :href="route('admin.returns.create', { order_id: sale.id })" class="btn btn-danger mr-2">
                            <i class="fas fa-undo"></i> Process Return
                        </Link>
                        <button class="btn btn-primary" @click="printInvoice">
                            <i class="fas fa-print"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row d-print-none">
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 d-flex flex-wrap align-items-end gap-3">
                                <div>
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">Order Status</label>
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
                                <div>
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">Payment Status</label>
                                    <select v-model="statusForm.payment_status" class="form-control form-control-sm">
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" :disabled="statusForm.processing" @click="updateStatus">
                                    <i class="fas fa-save mr-1"></i> Update Status
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold text-uppercase text-xs text-muted mb-3">Courier Shipment</h6>

                                <div v-if="sale.shipments?.length" class="mb-3">
                                    <div v-for="shipment in sale.shipments" :key="shipment.id" class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                        <div>
                                            <span class="badge badge-info-soft text-capitalize mr-2">{{ shipment.courier }}</span>
                                            <span class="text-sm">Tracking: <strong>{{ shipment.tracking_code || 'N/A' }}</strong></span>
                                        </div>
                                        <span class="badge badge-secondary text-capitalize">{{ shipment.status }}</span>
                                    </div>
                                </div>

                                <div v-if="!courierOptions.pathao && !courierOptions.steadfast" class="text-sm text-muted">
                                    No courier is configured yet. Add Pathao or Steadfast credentials in <Link :href="route('admin.settings.index')">Settings &rarr; Courier</Link>.
                                </div>
                                <div v-else class="d-flex flex-wrap align-items-end gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">Courier</label>
                                        <select v-model="shipForm.courier" class="form-control form-control-sm">
                                            <option v-if="courierOptions.steadfast" value="steadfast">Steadfast</option>
                                            <option v-if="courierOptions.pathao" value="pathao">Pathao</option>
                                        </select>
                                    </div>

                                    <template v-if="shipForm.courier === 'pathao'">
                                        <div>
                                            <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">City</label>
                                            <select v-model="shipForm.city_id" class="form-control form-control-sm">
                                                <option value="">Select City</option>
                                                <option v-for="city in pathaoCities" :key="city.city_id" :value="city.city_id">{{ city.city_name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">Zone</label>
                                            <select v-model="shipForm.zone_id" class="form-control form-control-sm" :disabled="!shipForm.city_id">
                                                <option value="">Select Zone</option>
                                                <option v-for="zone in pathaoZones" :key="zone.zone_id" :value="zone.zone_id">{{ zone.zone_name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-1 d-block">Area</label>
                                            <select v-model="shipForm.area_id" class="form-control form-control-sm" :disabled="!shipForm.zone_id">
                                                <option value="">Select Area</option>
                                                <option v-for="area in pathaoAreas" :key="area.area_id" :value="area.area_id">{{ area.area_name }}</option>
                                            </select>
                                        </div>
                                    </template>

                                    <button type="button" class="btn btn-success btn-sm" :disabled="shipForm.processing" @click="submitShip">
                                        <i class="fas fa-truck mr-1"></i> Book Shipment
                                    </button>
                                </div>
                                <p v-if="shipForm.errors.courier" class="text-danger text-sm mt-2 mb-0">{{ shipForm.errors.courier }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <!-- Minimal A5 invoice -->
                        <div class="invoice-sheet" id="invoice-print-area">
                            <header class="inv-head">
                                <div>
                                    <img v-if="shop.logo" :src="shop.logo" alt="" class="inv-logo">
                                    <div class="inv-shop">{{ shop.site_name || 'Invoice' }}</div>
                                    <div class="inv-muted" v-if="shop.address">{{ shop.address }}</div>
                                    <div class="inv-muted">
                                        <span v-if="shop.phone">{{ shop.phone }}</span>
                                        <span v-if="shop.phone && shop.email"> · </span>
                                        <span v-if="shop.email">{{ shop.email }}</span>
                                    </div>
                                </div>
                                <div class="inv-head-right">
                                    <div class="inv-title">Invoice</div>
                                    <div class="inv-number">#{{ sale.order_number }}</div>
                                    <div class="inv-muted">{{ new Date(sale.created_at).toLocaleDateString() }}</div>
                                </div>
                            </header>

                            <section class="inv-parties">
                                <div>
                                    <div class="inv-label">Billed To</div>
                                    <template v-if="sale.order_source === 'pos'">
                                        <div class="inv-strong">{{ sale.customer?.name || 'Walk-in Customer' }}</div>
                                        <div v-if="sale.customer?.address">{{ sale.customer.address }}</div>
                                        <div v-if="sale.customer?.phone">{{ sale.customer.phone }}</div>
                                        <div v-if="sale.customer?.email">{{ sale.customer.email }}</div>
                                    </template>
                                    <template v-else>
                                        <div class="inv-strong">{{ sale.customer_name || sale.user?.name || 'Guest User' }}</div>
                                        <div v-if="sale.shipping_address" style="white-space: pre-line">{{ sale.shipping_address }}</div>
                                        <div v-if="sale.customer_phone || sale.customer?.email || sale.user?.email">
                                            <span v-if="sale.customer_phone">{{ sale.customer_phone }}</span>
                                            <span v-if="sale.customer_phone && (sale.customer?.email || sale.user?.email)"> · </span>
                                            <span v-if="sale.customer?.email || sale.user?.email">{{ sale.customer?.email || sale.user?.email }}</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="inv-head-right">
                                    <div class="inv-label">Payment</div>
                                    <div class="inv-upper">{{ sale.payment_method }}</div>
                                    <div class="inv-upper" :class="sale.payment_status === 'paid' ? 'inv-ok' : 'inv-due'">{{ sale.payment_status }}</div>
                                </div>
                            </section>

                            <table class="inv-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="num">Qty</th>
                                        <th class="num">Price</th>
                                        <th class="num">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in sale.items" :key="item.id">
                                        <td>{{ item.product_name }}</td>
                                        <td class="num">{{ item.quantity }}</td>
                                        <td class="num">৳{{ item.price }}</td>
                                        <td class="num">৳{{ (item.price * item.quantity).toFixed(2) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="inv-totals">
                                <div class="inv-row"><span>Subtotal</span><span>৳{{ sale.subtotal }}</span></div>
                                <div class="inv-row" v-if="parseFloat(sale.tax) > 0"><span>Tax</span><span>৳{{ sale.tax }}</span></div>
                                <div class="inv-row" v-if="parseFloat(sale.shipping_cost) > 0"><span>Shipping</span><span>৳{{ sale.shipping_cost }}</span></div>
                                <div class="inv-row inv-due" v-if="parseFloat(sale.discount) > 0"><span>Discount</span><span>-৳{{ sale.discount }}</span></div>
                                <div class="inv-row inv-grand"><span>Total</span><span>৳{{ sale.total }}</span></div>
                                <div class="inv-row" v-if="parseFloat(sale.paid_amount) > 0"><span>Paid</span><span>৳{{ sale.paid_amount }}</span></div>
                                <div class="inv-row inv-due" v-if="parseFloat(sale.due_amount) > 0"><span>Due</span><span>৳{{ sale.due_amount }}</span></div>
                            </div>

                            <p class="inv-note" v-if="sale.notes"><strong>Note:</strong> {{ sale.notes }}</p>
                            <p class="inv-thanks">Thank you for your order</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<!-- Print rules that hide the admin chrome live unscoped in AdminLayout: a scoped
     `body *` cannot reach the sidebar. These are just the invoice's own look. -->
<style scoped>
.invoice-sheet {
    width: 148mm;
    max-width: 100%;
    min-height: 210mm;
    margin: 0 auto 1.5rem;
    padding: 10mm;
    background: #fff;
    border: 1px solid #ececec;
    color: #1c1c1c;
    font-size: 12px;
    line-height: 1.4;
    display: flex;
    flex-direction: column;
}

.inv-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding-bottom: 7px;
    border-bottom: 1px solid #e6e6e6;
}

.inv-logo {
    display: block;
    max-height: 30px;
    max-width: 130px;
    object-fit: contain;
    margin-bottom: 3px;
}

.inv-shop {
    font-size: 13px;
    font-weight: 700;
}

.inv-muted {
    color: #8a8a8a;
    font-size: 11px;
}

.inv-head-right {
    text-align: right;
    flex-shrink: 0;
}

.inv-title {
    text-transform: uppercase;
    letter-spacing: 0.18em;
    font-size: 11px;
    color: #8a8a8a;
}

.inv-number {
    font-weight: 700;
    font-size: 13px;
}

.inv-parties {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin: 9px 0;
}

.inv-label {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 10px;
    color: #9a9a9a;
    margin-bottom: 2px;
}

.inv-strong {
    font-weight: 700;
}

.inv-upper {
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.04em;
}

.inv-ok {
    color: #1a7f4b;
}

.inv-due {
    color: #c0392b;
}

.inv-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.inv-table th {
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9a9a9a;
    font-weight: 600;
    border-bottom: 1px solid #333;
    padding: 6px 4px;
}

.inv-table td {
    padding: 6px 4px;
    border-bottom: 1px solid #ededed;
}

.inv-table .num {
    text-align: right;
    white-space: nowrap;
}

.inv-totals {
    width: 55%;
    margin-left: auto;
    margin-top: 12px;
}

.inv-row {
    display: flex;
    justify-content: space-between;
    padding: 3px 4px;
}

.inv-grand {
    border-top: 1px solid #333;
    margin-top: 4px;
    padding-top: 6px;
    font-weight: 700;
    font-size: 13px;
}

.inv-note {
    margin-top: 12px;
    font-size: 11px;
    color: #555;
}

/* Footer: pinned to the bottom of the A5 sheet, not floating after the content. */
.inv-thanks {
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px solid #e6e6e6;
    text-align: center;
    font-size: 11px;
    color: #9a9a9a;
}

@media print {
    .invoice-sheet {
        width: auto;
        min-height: 0;
        border: 0;
        padding: 0;
        margin: 0;
        font-size: 11px;
        display: block;
    }

    .inv-thanks {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        background: #fff;
    }
}
</style>
