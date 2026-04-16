<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'details', label: 'Product Details', sortable: true },
    { key: 'total_purchased', label: 'Total Purchased', sortable: true, cellClass: 'text-center' },
    { key: 'stock', label: 'Stock Remaining', sortable: true, cellClass: 'text-center' },
    { key: 'cost_price', label: 'Cost Price', sortable: true, cellClass: 'text-right font-bold' },
    { key: 'price', label: 'Sale Price', sortable: true, cellClass: 'text-right font-bold text-indigo' }
];

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Product Movement Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Product Performance</h1>
                        <p class="text-muted text-sm mb-0">Monitor product stock movements and pricing analysis</p>
                    </div>
                    <button @click="printReport" class="btn btn-primary shadow-sm rounded-pill px-4">
                        <i class="fas fa-print mr-2"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="premium-card bg-white rounded-lg shadow-sm">
                    <div class="text-center d-none d-print-block mb-4 pt-4">
                        <h2 class="font-weight-bold mb-0">SWEET CHOCOLATE</h2>
                        <p class="text-muted">Product Movement & Performance Report</p>
                    </div>

                    <PremiumTable 
                        :items="products" 
                        :headers="columns"
                        search-placeholder="Search by product name or SKU..."
                    >
                        <!-- Index Cell -->
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>

                        <!-- Details Cell -->
                        <template #cell-details="{ item }">
                            <div class="font-weight-bold text-dark">{{ item.name }}</div>
                            <div class="text-xs text-muted">SKU: {{ item.sku || 'N/A' }} | Category: {{ item.category?.name || 'N/A' }}</div>
                        </template>

                        <!-- Total Purchased Cell -->
                        <template #cell-total_purchased="{ item }">
                            <span class="badge badge-info-soft text-info border px-2">{{ item.total_purchased || 0 }} {{ item.unit?.short_name || 'pcs' }}</span>
                        </template>

                        <!-- Stock Cell -->
                        <template #cell-stock="{ item }">
                            <span class="font-weight-bold" :class="item.stock <= 5 ? 'text-danger' : 'text-success'">
                                {{ item.stock }} {{ item.unit?.short_name || 'pcs' }}
                            </span>
                        </template>

                        <!-- Prices -->
                        <template #cell-cost_price="{ item }">৳{{ parseFloat(item.cost_price).toFixed(2) }}</template>
                        <template #cell-price="{ item }">৳{{ parseFloat(item.price).toFixed(2) }}</template>
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
