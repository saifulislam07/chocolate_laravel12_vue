<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

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

const getSourceContext = (source) => {
    return source === 'pos' ? 'POS System' : 'Web Store';
};
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
                        <!-- Main content -->
                        <div class="invoice p-3 mb-3 border bg-white" id="invoice-print-area">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-12 text-center border-bottom pb-4 mb-4">
                                    <h2 class="font-weight-bold mb-1">
                                        <i class="fas fa-cookie-bite mr-2 text-primary"></i>SWEET CHOCOLATE
                                    </h2>
                                    <p class="mb-0 text-muted">Complete Software Solutions Demo</p>
                                    <p class="text-muted">123 Super Market, Dhaka | Phone: 01700-000000</p>
                                    <h3 class="mt-3 text-uppercase font-weight-bold text-gray-dark">Invoice</h3>
                                </div>
                            </div>
                            
                            <!-- info row -->
                            <div class="row invoice-info mb-4">
                                <div class="col-sm-4 invoice-col">
                                    <span class="text-muted text-sm text-uppercase">Billed To</span>
                                    <address class="mt-1">
                                        <template v-if="sale.order_source === 'pos'">
                                            <strong class="h5">{{ sale.customer?.name || 'Walk-in Customer' }}</strong><br>
                                            <span v-if="sale.customer?.address">{{ sale.customer.address }}<br></span>
                                            <span v-if="sale.customer?.phone">Phone: {{ sale.customer.phone }}<br></span>
                                            <span v-if="sale.customer?.email">Email: {{ sale.customer.email }}</span>
                                        </template>
                                        <template v-else>
                                            <strong class="h5">{{ sale.customer_name || sale.user?.name || 'Guest User' }}</strong><br>
                                            Address: {{ sale.shipping_address || 'N/A' }}<br>
                                            <span v-if="sale.customer_phone">Phone: {{ sale.customer_phone }}<br></span>
                                            Email: {{ sale.user?.email || 'N/A' }}
                                        </template>
                                    </address>
                                </div>
                                
                                <div class="col-sm-4 invoice-col">
                                    <span class="text-muted text-sm text-uppercase">Order Attributes</span>
                                    <address class="mt-1">
                                        <strong>Source:</strong> {{ getSourceContext(sale.order_source) }}<br>
                                        <strong>Status:</strong> <span class="text-uppercase">{{ sale.status }}</span><br>
                                        <strong>Sale Type:</strong> Retail
                                    </address>
                                </div>
                                
                                <div class="col-sm-4 invoice-col text-sm-right">
                                    <b class="h5 text-info">Invoice #{{ sale.order_number }}</b><br><br>
                                    <b>Date:</b> {{ new Date(sale.created_at).toLocaleString() }}<br>
                                    <b>Payment Method:</b> <span class="text-uppercase">{{ sale.payment_method }}</span><br>
                                    <b>Payment Status:</b> <span class="text-uppercase text-bold" :class="sale.payment_status === 'paid' ? 'text-success' : 'text-danger'">{{ sale.payment_status }}</span>
                                </div>
                            </div>
                            
                            <!-- Table row -->
                            <div class="row">
                                <div class="col-12 table-responsive">
                                    <table class="table table-striped table-bordered text-center">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th class="text-left">Product Title</th>
                                                <th>Unit Price</th>
                                                <th>Quantity</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in sale.items" :key="item.id">
                                                <td>{{ index + 1 }}</td>
                                                <td class="text-left font-weight-bold">{{ item.product_name }}</td>
                                                <td>৳{{ item.price }}</td>
                                                <td>{{ item.quantity }}</td>
                                                <td class="text-right font-weight-bold">৳{{ (item.price * item.quantity).toFixed(2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-6">
                                    <p class="lead">Payment Summary:</p>
                                    <div class="bg-light p-3 rounded border">
                                        <p class="text-muted well well-sm shadow-none mt-2 mb-0">
                                            This invoice acts as a proof of purchase for the items listed. For any queries, please communicate with the support desk.
                                            <br><br>
                                            <span v-if="sale.notes"><strong>Note:</strong> {{ sale.notes }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="table-responsive">
                                        <table class="table table-sm border">
                                            <tr>
                                                <th style="width:50%" class="bg-light pl-3">Subtotal:</th>
                                                <td class="text-right pr-3 font-weight-bold">৳{{ sale.subtotal }}</td>
                                            </tr>
                                            <tr v-if="parseFloat(sale.tax) > 0">
                                                <th class="bg-light pl-3">Tax:</th>
                                                <td class="text-right pr-3 font-weight-bold">৳{{ sale.tax }}</td>
                                            </tr>
                                            <tr v-if="parseFloat(sale.shipping_cost) > 0">
                                                <th class="bg-light pl-3">Shipping Charge:</th>
                                                <td class="text-right pr-3 font-weight-bold">৳{{ sale.shipping_cost }}</td>
                                            </tr>
                                            <tr v-if="parseFloat(sale.discount) > 0">
                                                <th class="bg-light pl-3">Discount:</th>
                                                <td class="text-right pr-3 font-weight-bold text-danger">-৳{{ sale.discount }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-gray-light pl-3 h5 border-top">Grand Total:</th>
                                                <td class="text-right pr-3 h5 text-success font-weight-bold border-top">৳{{ sale.total }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light pl-3 border-top">Total Paid:</th>
                                                <td class="text-right pr-3 text-success font-weight-bold border-top">৳{{ sale.paid_amount || '0.00' }}</td>
                                            </tr>
                                            <tr v-if="parseFloat(sale.due_amount) > 0">
                                                <th class="bg-light pl-3 text-danger">Total Due:</th>
                                                <td class="text-right pr-3 text-danger font-weight-bold h6">৳{{ sale.due_amount }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-5">
                                <div class="col-12 text-center">
                                    <hr>
                                    <p class="font-italic text-sm text-muted">Thank you for your business!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-print-area, #invoice-print-area * {
        visibility: visible;
    }
    #invoice-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
        border: none !important;
    }
}
</style>
