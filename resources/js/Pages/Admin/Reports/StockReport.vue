<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array
});

const searchTerm = ref('');
const filterStatus = ref('all');

const filteredProducts = computed(() => {
    return props.products.filter(p => {
        const matchesSearch = p.name.toLowerCase().includes(searchTerm.value.toLowerCase()) || 
                             (p.sku && p.sku.toLowerCase().includes(searchTerm.value.toLowerCase()));
        
        if (filterStatus.value === 'low') return matchesSearch && p.stock <= 5; // Alert level
        if (filterStatus.value === 'out') return matchesSearch && p.stock <= 0;
        return matchesSearch;
    });
});

const totalStockValue = computed(() => {
    return props.products.reduce((sum, p) => sum + (p.stock * p.cost_price), 0).toFixed(2);
});

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Inventory Stock Report" />

    <AdminLayout border="0">
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-boxes mr-2 text-info"></i>Inventory Summary</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Summary Stats -->
                <div class="row d-print-none">
                    <div class="col-md-4">
                        <div class="info-box shadow-sm border-0" style="border-radius: 12px">
                            <span class="info-box-icon bg-info elevation-0"><i class="fas fa-warehouse"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Stock Capital Value</span>
                                <span class="info-box-number h4 mb-0">৳{{ totalStockValue }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box shadow-sm border-0" style="border-radius: 12px">
                            <span class="info-box-icon bg-warning elevation-0 text-white"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Low Stock Alert</span>
                                <span class="info-box-number h4 mb-0">{{ products.filter(p => p.stock <= 5 && p.stock > 0).length }} Items</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box shadow-sm border-0" style="border-radius: 12px">
                            <span class="info-box-icon bg-danger elevation-0"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Out of Stock</span>
                                <span class="info-box-number h4 mb-0">{{ products.filter(p => p.stock <= 0).length }} Items</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-info shadow-none border-0" style="border-radius: 15px;">
                    <div class="card-header border-0 d-print-none">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" v-model="searchTerm" class="form-control" placeholder="Search by name or SKU...">
                                    <div class="input-group-append"><span class="input-group-text bg-white"><i class="fas fa-search"></i></span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select v-model="filterStatus" class="form-control">
                                    <option value="all">All Inventory</option>
                                    <option value="low">Low Stock (≤ 5)</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-4 text-right">
                                <button @click="printReport" class="btn btn-primary px-4 shadow-sm rounded-pill font-weight-bold">
                                    <i class="fas fa-print mr-1"></i> Print Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="text-center d-none d-print-block mb-4">
                            <h2 class="font-weight-bold mb-0">SWEET CHOCOLATE</h2>
                            <p class="text-muted">Inventory Stock Report - {{ new Date().toLocaleDateString() }}</p>
                        </div>

                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light border-top">
                                <tr>
                                    <th style="width: 50px">#</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-right">Unit cost</th>
                                    <th class="text-right">Stock Value</th>
                                    <th class="text-center d-print-none">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in filteredProducts" :key="product.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-img mr-2 d-print-none">
                                                <img :src="product.image || '/uploads/products/default.png'" alt="Product" class="img-circle border" style="width: 35px; height:35px; object-fit: cover;">
                                            </div>
                                            <div>
                                                <span class="font-weight-bold">{{ product.name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code>{{ product.sku || 'N/A' }}</code></td>
                                    <td>{{ product.category?.name || 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="h6 font-weight-bold" :class="product.stock <= 5 ? 'text-danger' : 'text-success'">
                                            {{ product.stock }} {{ product.unit?.short_name || 'pcs' }}
                                        </span>
                                    </td>
                                    <td class="text-right">৳{{ product.cost_price }}</td>
                                    <td class="text-right font-weight-bold">৳{{ (product.stock * product.cost_price).toFixed(2) }}</td>
                                    <td class="text-center d-print-none">
                                        <span v-if="product.stock <= 0" class="badge badge-danger">Out of Stock</span>
                                        <span v-else-if="product.stock <= 5" class="badge badge-warning">Low Stock</span>
                                        <span v-else class="badge badge-success">Available</span>
                                    </td>
                                </tr>
                                <tr v-if="filteredProducts.length === 0">
                                    <td colspan="8" class="text-center p-5 text-muted h5">No inventory records found.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="filteredProducts.length > 0">
                                <tr class="bg-light">
                                    <th colspan="4" class="text-right font-weight-bold">Total Inventory Value:</th>
                                    <th class="text-center h5 font-weight-bold">{{ filteredProducts.reduce((sum, p) => sum + p.stock, 0) }} Total Qty</th>
                                    <th></th>
                                    <th class="text-right h5 font-weight-bold text-primary">৳{{ filteredProducts.reduce((sum, p) => sum + (p.stock * p.cost_price), 0).toFixed(2) }}</th>
                                    <th class="d-print-none"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
@media print {
    .main-sidebar, .main-header, .content-header, .card-header, .main-footer {
        display: none !important;
    }
    .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
    .card { border: 0 !important; box-shadow: none !important; }
    .table thead th { background-color: #eee !important; color: #000 !important; }
}
</style>
