<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
});

const searchQuery = ref('');
const cart = ref([]);
const selectedCustomerId = ref('');
const showInvoiceModal = ref(false);
const printInvoiceData = ref(null);

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;
    return props.products.filter(p => 
        p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        (p.sku && p.sku.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const addToCart = (product) => {
    const existing = cart.value.find(item => item.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1
        });
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const discount = ref(0);
const taxRate = ref(0);
const shippingCost = ref(0);
const paidAmount = ref(0);
const paymentMethod = ref('cash');

const taxAmount = computed(() => {
    return (subtotal.value * taxRate.value) / 100;
});

const grandTotal = computed(() => {
    return subtotal.value - parseFloat(discount.value || 0) + taxAmount.value + parseFloat(shippingCost.value || 0);
});

const dueAmount = computed(() => {
    let due = grandTotal.value - parseFloat(paidAmount.value || 0);
    return due > 0 ? due : 0;
});

const changeAmount = computed(() => {
    let change = parseFloat(paidAmount.value || 0) - grandTotal.value;
    return change > 0 ? change : 0;
});

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
    payment_method: 'cash'
});

const processSale = () => {
    if (cart.value.length === 0) return alert('Cart is empty!');
    
    checkoutForm.customer_id = selectedCustomerId.value;
    checkoutForm.items = cart.value;
    checkoutForm.subtotal = subtotal.value;
    checkoutForm.discount = parseFloat(discount.value || 0);
    checkoutForm.tax = taxAmount.value;
    checkoutForm.shipping_cost = parseFloat(shippingCost.value || 0);
    checkoutForm.total = grandTotal.value;
    checkoutForm.paid_amount = parseFloat(paidAmount.value || 0);
    checkoutForm.due_amount = dueAmount.value;
    checkoutForm.payment_method = paymentMethod.value;

    checkoutForm.post(route('admin.pos.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            printInvoiceData.value = page.props.flash?.invoice || null;
            if(printInvoiceData.value){
                showInvoiceModal.value = true;
            }
            // Reset fields
            cart.value = [];
            discount.value = 0;
            taxRate.value = 0;
            shippingCost.value = 0;
            paidAmount.value = 0;
        }
    });
};

const showCustomerModal = ref(false);
const quickCustomerForm = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
});

const submitQuickCustomer = () => {
    quickCustomerForm.post(route('admin.customers.store'), {
        onSuccess: (page) => {
            // The list of customers will be updated via props automatically if using resource
            // but we need to select the new one. 
            // Since we don't have the new ID immediately in standard Inertia response without custom logic,
            // we can try to find it by phone in the updated props if they are refreshed.
            showCustomerModal.value = false;
            quickCustomerForm.reset();
        },
    });
};

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <Head title="POS - Point of Sale" />
    <AdminLayout border="0">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Advanced POS System</h2>
        </template>

        <section class="content pt-3 d-print-none">
            <div class="container-fluid">
                <div class="row">
                    <!-- Left: Product List -->
                    <div class="col-lg-7 col-md-6">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header border-0">
                                <div class="input-group">
                                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search product by name or SKU...">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-2" style="height: 75vh; overflow-y: auto;">
                                <div class="row">
                                    <div v-for="product in filteredProducts" :key="product.id" class="col-lg-3 col-md-4 col-sm-4 col-6 mb-3 px-2">
                                        <div class="card h-100 product-card shadow-sm border-0 bg-white" @click="addToCart(product)" style="cursor: pointer; border-radius: 12px;">
                                            <div class="position-relative text-center p-2">
                                                <img :src="product.image || '/uploads/products/default.png'" class="img-fluid" style="height: 100px; object-fit: contain;">
                                                <span class="badge badge-success position-absolute" style="top: 10px; right: 10px; font-size: 0.85rem; border-radius: 8px;">৳{{ product.price }}</span>
                                            </div>
                                            <div class="card-body p-2 text-center border-top">
                                                <p class="card-title text-sm font-weight-bold mb-0 w-100 text-truncate text-dark" :title="product.name">{{ product.name }}</p>
                                                <small class="text-muted d-block">{{ product.sku || 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Cart & Checkout calculation -->
                    <div class="col-lg-5 col-md-6">
                        <div class="card card-outline card-success shadow-sm border-0" style="border-radius: 15px;">
                            <div class="card-header p-2 border-0 bg-white">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white border-success" style="border-radius: 8px 0 0 8px;"><i class="fas fa-user-check"></i></span>
                                    </div>
                                    <select v-model="selectedCustomerId" class="form-control" style="border-radius: 0;">
                                        <option value="">Walk-in Customer</option>
                                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                            {{ customer.name }} ({{ customer.phone }})
                                        </option>
                                    </select>
                                    <div class="input-group-append">
                                         <button type="button" @click="showCustomerModal = true" class="btn btn-primary" title="Add New Customer" style="border-radius: 0 8px 8px 0;"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0" style="height: 35vh; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Product</th>
                                            <th style="width: 80px" class="text-center">Qty</th>
                                            <th style="width: 90px" class="text-right">Total</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in cart" :key="index">
                                            <td class="text-sm font-weight-bold">{{ item.name }}<br><small class="text-muted">৳{{ item.price }}</small></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" v-model.number="item.quantity" class="form-control text-center px-1" min="1">
                                                </div>
                                            </td>
                                            <td class="text-right font-weight-bold">৳{{ (item.price * item.quantity).toFixed(2) }}</td>
                                            <td class="text-center">
                                                <button @click="removeFromCart(index)" class="btn btn-xs btn-outline-danger"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <tr v-if="cart.length === 0">
                                            <td colspan="4" class="text-center p-5 text-muted"><h4><i class="fas fa-shopping-cart mb-2"></i></h4> Cart is empty</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Calculation summary -->
                            <div class="card-footer bg-light p-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="font-weight-bold text-muted">Subtotal:</td>
                                        <td class="text-right font-weight-bold h5">৳{{ subtotal.toFixed(2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle">Discount (৳):</td>
                                        <td><input type="number" v-model.number="discount" class="form-control form-control-sm text-right" min="0"></td>
                                    </tr>
                                    <tr>
                                        <td class="align-middle">Tax (%):</td>
                                        <td><input type="number" v-model.number="taxRate" class="form-control form-control-sm text-right" min="0" max="100"></td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="align-middle">Shipping (৳):</td>
                                        <td><input type="number" v-model.number="shippingCost" class="form-control form-control-sm text-right" min="0"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold h4 text-success pt-2">Grand Total:</td>
                                        <td class="text-right font-weight-bold h3 text-success pt-2">৳{{ grandTotal.toFixed(2) }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Payment Area -->
                            <div class="card-body p-2 bg-white border-top">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="text-xs text-muted mb-0">Payment Method</label>
                                        <select v-model="paymentMethod" class="form-control form-control-sm">
                                            <option value="cash">Cash</option>
                                            <option value="card">Card / POS</option>
                                            <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                                            <option value="bank">Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-xs text-muted mb-0">Paid Amount (৳)</label>
                                        <input type="number" v-model.number="paidAmount" class="form-control form-control-sm text-right font-weight-bold text-primary" style="font-size: 1.1rem" :min="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="bg-gray-light p-1 rounded text-center">
                                            <small class="d-block text-muted">Change</small>
                                            <span class="font-weight-bold text-success">৳{{ changeAmount.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-gray-light p-1 rounded text-center">
                                            <small class="d-block text-muted">Due</small>
                                            <span class="font-weight-bold text-danger">৳{{ dueAmount.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer p-2">
                                <div class="row">
                                    <div class="col-4">
                                        <button class="btn btn-block btn-outline-danger" @click="cart = []">Cancel</button>
                                    </div>
                                    <div class="col-8">
                                        <button @click="processSale" class="btn btn-block btn-success shadow" :disabled="cart.length === 0 || checkoutForm.processing">
                                            <i class="fas fa-check-circle mr-1"></i> Complete Sale & Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Invoice Modal/Section (only visible in print or when modal active) -->
        <div v-if="showInvoiceModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-print-none">
                        <h5 class="modal-title">Sale Complete - Invoice #{{ printInvoiceData?.invoice_no }}</h5>
                        <button type="button" class="close" @click="showInvoiceModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" id="invoice-print-area">
                        <div class="text-center mb-4 border-bottom pb-3">
                            <h2 class="font-weight-bold mb-1">SWEET CHOCOLATE</h2>
                            <p class="mb-0">123 Super Market, Dhaka</p>
                            <p>Phone: 01700-000000</p>
                            <h4 class="mt-2 text-uppercase font-weight-bold">Invoice</h4>
                        </div>
                        <div class="row mb-4">
                            <div class="col-6">
                                <strong>Invoice To:</strong><br>
                                {{ printInvoiceData?.customer_name || 'Walk-in Customer' }}<br>
                                Phone: {{ printInvoiceData?.customer_phone || 'N/A' }}
                            </div>
                            <div class="col-6 text-right">
                                <strong>Invoice No:</strong> {{ printInvoiceData?.invoice_no }}<br>
                                <strong>Date:</strong> {{ new Date().toLocaleDateString() }}<br>
                                <strong>Payment Method:</strong> <span class="text-uppercase">{{ printInvoiceData?.payment_method }}</span>
                            </div>
                        </div>
                        <table class="table table-bordered table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in printInvoiceData?.items" :key="item.id">
                                    <td>{{ item.name }}</td>
                                    <td class="text-center">{{ item.quantity }}</td>
                                    <td class="text-right">৳{{ item.price }}</td>
                                    <td class="text-right">৳{{ (item.quantity * item.price).toFixed(2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Subtotal:</th>
                                    <th class="text-right">৳{{ printInvoiceData?.subtotal }}</th>
                                </tr>
                                <tr v-if="printInvoiceData?.discount > 0">
                                    <th colspan="3" class="text-right">Discount:</th>
                                    <th class="text-right text-danger">-৳{{ printInvoiceData?.discount }}</th>
                                </tr>
                                <tr v-if="printInvoiceData?.tax > 0">
                                    <th colspan="3" class="text-right">Tax:</th>
                                    <th class="text-right">৳{{ printInvoiceData?.tax }}</th>
                                </tr>
                                <tr v-if="printInvoiceData?.shipping_cost > 0">
                                    <th colspan="3" class="text-right">Shipping:</th>
                                    <th class="text-right">৳{{ printInvoiceData?.shipping_cost }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right h5">Grand Total:</th>
                                    <th class="text-right h5">৳{{ printInvoiceData?.total }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Paid Amount:</th>
                                    <th class="text-right text-success">৳{{ printInvoiceData?.paid_amount }}</th>
                                </tr>
                                <tr v-if="printInvoiceData?.due_amount > 0">
                                    <th colspan="3" class="text-right">Due Amount:</th>
                                    <th class="text-right text-danger">৳{{ printInvoiceData?.due_amount }}</th>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="text-center mt-5">
                            <p class="font-italic">Thank you for your business!</p>
                        </div>
                    </div>
                    <div class="modal-footer d-print-none">
                        <button type="button" class="btn btn-secondary" @click="showInvoiceModal = false">Close</button>
                        <button type="button" class="btn btn-primary" @click="printInvoice"><i class="fas fa-print mr-1"></i> Print Invoice</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Add Customer Modal -->
        <div v-if="showCustomerModal" class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Quick Add Customer</h5>
                        <button type="button" class="close text-white" @click="showCustomerModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitQuickCustomer">
                        <div class="modal-body bg-light">
                            <div class="form-group">
                                <label class="font-weight-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" v-model="quickCustomerForm.name" class="form-control" placeholder="Enter customer name" required>
                                <span class="text-danger text-sm" v-if="quickCustomerForm.errors.name">{{ quickCustomerForm.errors.name }}</span>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Phone <span class="text-danger">*</span></label>
                                <input type="text" v-model="quickCustomerForm.phone" class="form-control" placeholder="Enter phone number" required>
                                <span class="text-danger text-sm" v-if="quickCustomerForm.errors.phone">{{ quickCustomerForm.errors.phone }}</span>
                            </div>
                            <div class="form-group">
                                <label class="text-xs">Email (Optional)</label>
                                <input type="email" v-model="quickCustomerForm.email" class="form-control" placeholder="Email address">
                            </div>
                            <div class="form-group mb-0">
                                <label class="text-xs">Address (Optional)</label>
                                <textarea v-model="quickCustomerForm.address" class="form-control" rows="2" placeholder="Customer address..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary shadow-sm" @click="showCustomerModal = false">Cancel</button>
                            <button type="submit" class="btn btn-success px-4 shadow-sm" :disabled="quickCustomerForm.processing">
                                <i class="fas fa-save mr-1"></i> Save Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.product-card:hover {
    border-color: #28a745 !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}
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
    }
}
</style>
