<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Array,
    products: Array,
    reference_no: String,
});

const searchTerm = ref('');
const selectedProduct = ref('');

const filteredProducts = computed(() => {
    if (!searchTerm.value) return [];
    return props.products.filter(p => 
        p.name.toLowerCase().includes(searchTerm.value.toLowerCase()) || 
        (p.sku && p.sku.toLowerCase().includes(searchTerm.value.toLowerCase()))
    ).slice(0, 10);
});

const form = useForm({
    supplier_id: '',
    reference_no: props.reference_no,
    purchase_date: new Date().toISOString().slice(0, 10),
    items: [],
    tax_amount: 0,
    shipping_cost: 0,
    total_discount: 0,
    paid_amount: 0,
    notes: '',
});

const addProduct = (product) => {
    const existing = form.items.find(item => item.product_id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        form.items.push({
            product_id: product.id,
            name: product.name,
            sku: product.sku,
            unit: product.unit ? product.unit.short_name : 'pcs',
            quantity: 1,
            unit_cost: product.cost_price || 0,
            discount_amount: 0,
        });
    }
    searchTerm.value = '';
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + ((item.quantity * item.unit_cost) - (parseFloat(item.discount_amount) || 0)), 0);
});

const totalAmount = computed(() => {
    return (subtotal.value + (parseFloat(form.tax_amount) || 0) + (parseFloat(form.shipping_cost) || 0)) - (parseFloat(form.total_discount) || 0);
});

const dueAmount = computed(() => {
    return totalAmount.value - (parseFloat(form.paid_amount) || 0);
});

const submit = () => {
    form.post(route('admin.purchases.store'));
};
</script>

<template>
    <Head title="Create Purchase" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Add New Purchase</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Left Panel: Purchase Details & Items -->
                        <div class="col-md-9">
                            <div class="card card-primary card-outline shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Supplier <span class="text-danger">*</span></label>
                                            <select v-model="form.supplier_id" class="form-control" :class="{'is-invalid': form.errors.supplier_id}" required>
                                                <option value="">Select Supplier</option>
                                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.phone }})</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Reference No</label>
                                            <input type="text" v-model="form.reference_no" class="form-control" readonly>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Purchase Date <span class="text-danger">*</span></label>
                                            <input type="date" v-model="form.purchase_date" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Product Search -->
                                    <div class="form-group position-relative">
                                        <label><i class="fas fa-search mr-1"></i> Scan or Search Product</label>
                                        <input type="text" v-model="searchTerm" class="form-control form-control-lg" placeholder="Type product name or SKU...">
                                        
                                        <div v-if="filteredProducts.length > 0" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1000; top: 100%;">
                                            <button type="button" v-for="p in filteredProducts" :key="p.id" 
                                                @click="addProduct(p)"
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <span><strong>{{ p.name }}</strong> ({{ p.sku || 'No SKU' }})</span>
                                                <span class="badge badge-primary">৳{{ p.cost_price }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Purchase Items Table -->
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered table-striped">
                                            <thead class="bg-light text-sm">
                                                <tr>
                                                    <th>Product</th>
                                                    <th style="width: 150px">Unit Cost</th>
                                                    <th style="width: 120px">Quantity</th>
                                                    <th style="width: 120px">Discount</th>
                                                    <th style="width: 150px" class="text-right">Subtotal</th>
                                                    <th style="width: 50px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>
                                                        <span class="font-weight-bold">{{ item.name }}</span><br>
                                                        <small class="text-muted">SKU: {{ item.sku }}</small>
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model="item.unit_cost" class="form-control form-control-sm text-right">
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" v-model="item.quantity" class="form-control text-center">
                                                            <div class="input-group-append"><span class="input-group-text text-xs">{{ item.unit }}</span></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model="item.discount_amount" class="form-control form-control-sm text-right text-danger" placeholder="0.00">
                                                    </td>
                                                    <td class="text-right align-middle">
                                                        <strong>৳{{ ((item.quantity * item.unit_cost) - (parseFloat(item.discount_amount) || 0)).toFixed(2) }}</strong>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" @click="removeItem(index)" class="btn btn-outline-danger btn-xs">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr v-if="form.items.length === 0">
                                                    <td colspan="6" class="text-center p-5 text-muted">
                                                        <i class="fas fa-shopping-basket fa-3x mb-3 d-block opacity-25"></i>
                                                        No products added yet. Start searching above.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Summary & Payment -->
                        <div class="col-md-3">
                            <div class="card card-success card-outline shadow-sm">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold">Summary</h3></div>
                                <div class="card-body pt-0">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Items Subtotal:</span>
                                        <strong>৳{{ subtotal.toFixed(2) }}</strong>
                                    </div>
                                    
                                    <div class="form-group mb-2">
                                        <label class="text-xs mb-1 text-muted">Tax / VAT (৳)</label>
                                        <input type="number" v-model="form.tax_amount" class="form-control form-control-sm text-right">
                                    </div>
                                    
                                    <div class="form-group mb-2">
                                        <label class="text-xs mb-1 text-muted">Shipping (৳)</label>
                                        <input type="number" v-model="form.shipping_cost" class="form-control form-control-sm text-right">
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="text-xs mb-1 text-danger font-weight-bold">Extra Discount (৳)</label>
                                        <input type="number" v-model="form.total_discount" class="form-control form-control-sm text-right text-danger font-weight-bold border-danger">
                                    </div>

                                    <div class="dropdown-divider my-3"></div>

                                    <div class="d-flex justify-content-between mb-3 bg-light p-2 rounded">
                                        <span class="h6 font-weight-bold mb-0">TOTAL:</span>
                                        <span class="h6 font-weight-bold text-success mb-0">৳{{ totalAmount.toFixed(2) }}</span>
                                    </div>

                                    <div class="form-group">
                                        <label>Paid Amount</label>
                                        <div class="input-group shadow-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-success text-white border-success">৳</span></div>
                                            <input type="number" v-model="form.paid_amount" class="form-control form-control-lg text-success font-weight-bold border-success">
                                        </div>
                                    </div>
                                    
                                    <div class="info-box bg-light border mb-3 elevation-0">
                                        <div class="info-box-content text-center py-2">
                                            <span class="info-box-text text-xs text-uppercase tracking-wider">Due to Supplier</span>
                                            <span class="info-box-number text-danger h5 mb-0 font-weight-bold">৳{{ dueAmount.toFixed(2) }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="text-xs">Purchases Note</label>
                                        <textarea v-model="form.notes" class="form-control form-control-sm" rows="3" placeholder="Write any remarks..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm mb-2" :disabled="form.processing || form.items.length === 0">
                                        <i class="fas fa-check-circle mr-1"></i> Confirm Purchase
                                    </button>
                                    <Link :href="route('admin.purchases.index')" class="btn btn-outline-secondary btn-block btn-sm">Cancel</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.list-group-item {
    cursor: pointer;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
