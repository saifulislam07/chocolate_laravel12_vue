<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'product', label: 'Product', sortable: true },
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'stock', label: 'Current Stock', sortable: true, cellClass: 'text-center' },
    { key: 'cost_price', label: 'Unit Cost', sortable: true, cellClass: 'text-right' },
    { key: 'stock_value', label: 'Stock Value', sortable: true, cellClass: 'text-right font-bold' },
    { key: 'status', label: 'Status', sortable: true, cellClass: 'text-center d-print-none' }
];

const filterStatus = ref('all');

const processedProducts = computed(() => {
    let items = [...props.products];
    if (filterStatus.value === 'low') items = items.filter(p => p.stock <= 5 && p.stock > 0);
    if (filterStatus.value === 'out') items = items.filter(p => p.stock <= 0);
    return items;
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

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Inventory Summary</h1>
                        <p class="text-muted text-sm mb-0">Detailed stock levels and asset valuation</p>
                    </div>
                    <button @click="printReport" class="btn btn-primary shadow-sm rounded-pill px-4">
                        <i class="fas fa-print mr-2"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Summary Stats -->
                <div class="row mb-4 d-print-none">
                    <div class="col-md-4">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-info-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-info-soft mr-3">
                                    <i class="fas fa-warehouse text-info"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Stock Capital Value</div>
                                    <div class="h4 font-bold mb-0 text-info">৳{{ totalStockValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-warning-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-warning-soft mr-3">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Low Stock Alert</div>
                                    <div class="h4 font-bold mb-0 text-warning">{{ products.filter(p => p.stock <= 5 && p.stock > 0).length }} Items</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-danger-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-danger-soft mr-3">
                                    <i class="fas fa-times-circle text-danger"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Out of Stock</div>
                                    <div class="h4 font-bold mb-0 text-danger">{{ products.filter(p => p.stock <= 0).length }} Items</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="premium-card bg-white rounded-lg shadow-sm">
                    <div class="text-center d-none d-print-block mb-4 pt-4">
                        <h2 class="font-weight-bold mb-0 text-dark">SWEET CHOCOLATE</h2>
                        <p class="text-muted">Inventory Stock Report - {{ new Date().toLocaleDateString() }}</p>
                    </div>

                    <PremiumTable 
                        :items="processedProducts" 
                        :headers="columns"
                        search-placeholder="Search by product name or SKU..."
                    >
                        <!-- Custom Filters Slot -->
                        <template #filters>
                            <select v-model="filterStatus" class="form-control-sm border-0 bg-light rounded-pill px-3 text-xs font-bold" style="height: 38px; min-width: 150px;">
                                <option value="all">All Inventory</option>
                                <option value="low">Low Stock (≤ 5)</option>
                                <option value="out">Out of Stock</option>
                            </select>
                        </template>

                        <!-- Index Cell -->
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>

                        <!-- Product Cell -->
                        <template #cell-product="{ item }">
                            <div class="d-flex align-items-center">
                                <img :src="item.image || '/uploads/products/default.png'" 
                                     class="rounded-circle border mr-3 d-print-none" 
                                     style="width: 32px; height: 32px; object-fit: cover;">
                                <div class="font-weight-bold text-dark text-sm">{{ item.name }}</div>
                            </div>
                        </template>

                        <!-- SKU Cell -->
                        <template #cell-sku="{ item }">
                            <code class="text-xs px-2 py-1 bg-light rounded">{{ item.sku || 'N/A' }}</code>
                        </template>

                        <!-- Category Cell -->
                        <template #cell-category="{ item }">
                            <span class="text-muted text-xs">{{ item.category?.name || 'N/A' }}</span>
                        </template>

                        <!-- Stock Cell -->
                        <template #cell-stock="{ item }">
                            <span class="font-weight-bold" :class="item.stock <= 5 ? 'text-danger' : 'text-success'">
                                {{ item.stock }} <small class="text-muted">{{ item.unit?.short_name || 'pcs' }}</small>
                            </span>
                        </template>

                        <!-- Unit Cost Cell -->
                        <template #cell-cost_price="{ item }">
                            <span class="text-muted">৳{{ item.cost_price }}</span>
                        </template>

                        <!-- Stock Value Cell -->
                        <template #cell-stock_value="{ item }">
                            <span class="text-dark font-weight-bold">৳{{ (item.stock * item.cost_price).toFixed(2) }}</span>
                        </template>

                        <!-- Status Cell -->
                        <template #cell-status="{ item }">
                            <span v-if="item.stock <= 0" class="badge badge-danger">Out of Stock</span>
                            <span v-else-if="item.stock <= 5" class="badge badge-warning text-white">Low Stock</span>
                            <span v-else class="badge badge-success">Available</span>
                        </template>

                        <!-- Footer Slot -->
                        <template #footer="{ filteredItems }">
                            <tr class="bg-light d-print-table-row">
                                <td colspan="4" class="text-right font-weight-bold py-3">Total Inventory Valuation:</td>
                                <td class="text-center font-weight-bold">
                                    {{ filteredItems.reduce((sum, p) => sum + p.stock, 0) }} Items
                                </td>
                                <td></td>
                                <td class="text-right font-weight-bold text-indigo h6 mb-0 py-3">
                                    ৳{{ filteredItems.reduce((sum, p) => sum + (p.stock * p.cost_price), 0).toFixed(2) }}
                                </td>
                                <td class="d-print-none"></td>
                            </tr>
                        </template>
                    </PremiumTable>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
@media print {
    .main-sidebar, .main-header, .content-header, .table-actions-bar, .table-footer, .main-footer {
        display: none !important;
    }
    .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: white !important; }
    .premium-card { border: 0 !important; box-shadow: none !important; }
    .container-fluid { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
    .table thead th { background-color: #f1f5f9 !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    .premium-table-container { padding: 0 !important; }
}
</style>
template>

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
