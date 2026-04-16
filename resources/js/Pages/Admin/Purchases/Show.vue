<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    purchase: Object
});

function getStatusBadge(status) {
    const s = status?.toLowerCase();
    if (s === 'received') return 'badge-success';
    if (s === 'ordered') return 'badge-info';
    return 'badge-warning';
}

function getPaymentBadge(status) {
    const s = status?.toLowerCase();
    if (s === 'paid') return 'badge-success';
    if (s === 'partial') return 'badge-warning';
    return 'badge-danger';
}
</script>

<template>
    <Head title="Purchase Details" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Purchase Details</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <Link :href="route('admin.purchases.index')" class="btn btn-default btn-sm mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to List
                        </Link>
                        <button class="btn btn-primary btn-sm mr-2" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                        <Link :href="route('admin.purchases.edit', purchase.id)" class="btn btn-info btn-sm">
                            <i class="fas fa-edit mr-1"></i> Edit Purchase
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="invoice p-3 mb-3 shadow-sm border-0" style="border-radius: 15px;">
                    <!-- title row -->
                    <div class="row">
                        <div class="col-12">
                            <h4>
                                <i class="fas fa-file-invoice mr-2 text-primary"></i> SWEET CHOCOLATE
                                <small class="float-right text-muted font-weight-normal">Date: {{ purchase.purchase_date }}</small>
                            </h4>
                        </div>
                    </div>
                    
                    <!-- info row -->
                    <div class="row invoice-info mt-4">
                        <div class="col-sm-4 invoice-col border-right">
                            <p class="text-muted text-uppercase font-weight-bold mb-2 small">Supplier Details</p>
                            <address>
                                <strong class="h5 text-primary">{{ purchase.supplier?.name }}</strong><br>
                                {{ purchase.supplier?.company_name }}<br>
                                {{ purchase.supplier?.address }}<br>
                                Phone: {{ purchase.supplier?.phone }}<br>
                                Email: {{ purchase.supplier?.email || 'N/A' }}
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col border-right pl-4">
                            <p class="text-muted text-uppercase font-weight-bold mb-2 small">Bill Information</p>
                            <b>Reference:</b> <code class="h6">{{ purchase.reference_no }}</code><br>
                            <b>Status:</b> <span class="badge" :class="getStatusBadge(purchase.status)">{{ purchase.status }}</span><br>
                            <b>Payment:</b> <span class="badge ml-1" :class="getPaymentBadge(purchase.payment_status)">{{ purchase.payment_status }}</span><br>
                            <b>Created At:</b> {{ new Date(purchase.created_at).toLocaleString() }}
                        </div>
                        <div class="col-sm-4 invoice-col pl-4">
                             <p class="text-muted text-uppercase font-weight-bold mb-2 small">Financial Summary</p>
                             <div class="bg-light p-2 rounded">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Payable:</span>
                                    <strong class="text-dark">৳{{ purchase.total_amount }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Paid:</span>
                                    <strong class="text-success">৳{{ purchase.paid_amount }}</strong>
                                </div>
                                <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                    <span class="font-weight-bold">Due:</span>
                                    <strong class="text-danger">৳{{ purchase.due_amount }}</strong>
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
                                        <th class="text-right">Unit Cost</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in purchase.items" :key="item.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ item.product?.name }}</span><br>
                                            <small class="text-muted">SKU: {{ item.product?.sku }}</small>
                                        </td>
                                        <td class="text-center align-middle h6">{{ item.quantity }} {{ item.product?.unit?.short_name || 'pcs' }}</td>
                                        <td class="text-right align-middle">৳{{ item.unit_cost }}</td>
                                        <td class="text-right align-middle text-danger">৳{{ item.discount_amount }}</td>
                                        <td class="text-right align-middle font-weight-bold">৳{{ item.subtotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- accepted payments column -->
                        <div class="col-6">
                            <p class="lead font-weight-bold text-muted small text-uppercase">Terms & Notes:</p>
                            <div class="text-muted bg-light p-3 rounded" style="min-height: 100px;">
                                {{ purchase.notes || 'No notes available for this purchase.' }}
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-6">
                            <p class="lead font-weight-bold text-muted small text-uppercase text-right">Payment Calculation:</p>

                            <div class="table-responsive">
                                <table class="table border">
                                    <tr>
                                        <th style="width:50%">Items Subtotal:</th>
                                        <td class="text-right font-weight-bold">৳{{ parseFloat(purchase.total_amount - (purchase.tax_amount || 0) - (purchase.shipping_cost || 0) + (purchase.total_discount || 0)).toFixed(2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tax (%)</th>
                                        <td class="text-right">৳{{ purchase.tax_amount }}</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping:</th>
                                        <td class="text-right">৳{{ purchase.shipping_cost }}</td>
                                    </tr>
                                    <tr v-if="purchase.total_discount > 0">
                                        <th class="text-danger">Bill Discount:</th>
                                        <td class="text-right text-danger">-৳{{ purchase.total_discount }}</td>
                                    </tr>
                                    <tr class="bg-primary text-white">
                                        <th class="h5">Grand Total:</th>
                                        <td class="text-right h5 font-weight-bold">৳{{ purchase.total_amount }}</td>
                                    </tr>
                                </table>
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
    .content-header, .btn, .main-footer {
        display: none !important;
    }
    .invoice {
        border: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
