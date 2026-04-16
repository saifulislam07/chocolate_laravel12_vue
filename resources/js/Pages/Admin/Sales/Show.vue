<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sale: Object
});

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
                        <button class="btn btn-primary" @click="printInvoice">
                            <i class="fas fa-print"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
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
                                            <strong class="h5">{{ sale.user?.name || 'Guest User' }}</strong><br>
                                            Address: {{ sale.shipping_address || 'N/A' }}<br>
                                            <span v-if="sale.user?.phone">Phone: {{ sale.user.phone }}<br></span>
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
