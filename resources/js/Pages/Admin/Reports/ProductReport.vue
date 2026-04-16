<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array
});

const searchTerm = ref('');

const filteredProducts = computed(() => {
    return props.products.filter(p => 
        p.name.toLowerCase().includes(searchTerm.value.toLowerCase()) || 
        (p.sku && p.sku.toLowerCase().includes(searchTerm.value.toLowerCase()))
    );
});
</script>

<template>
    <Head title="Product Movement Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-barcode mr-2 text-info"></i>Product Performance</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-info shadow-sm" style="border-radius: 12px;">
                    <div class="card-header border-0 d-print-none">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" v-model="searchTerm" class="form-control" placeholder="Search by name or SKU...">
                            </div>
                            <div class="col-md-8 text-right">
                                <button onclick="window.print()" class="btn btn-info px-4 shadow-sm text-white font-weight-bold">
                                    <i class="fas fa-print mr-1"></i> Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product Details</th>
                                    <th class="text-center">Total Purchased</th>
                                    <th class="text-center">Stock Remaining</th>
                                    <th class="text-right">Cost Price</th>
                                    <th class="text-right">Sale Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(p, index) in filteredProducts" :key="p.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ p.name }}</span><br>
                                        <small class="text-muted">SKU: {{ p.sku || 'N/A' }} | Category: {{ p.category?.name || 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info shadow-sm px-2">{{ p.total_purchased || 0 }} {{ p.unit?.short_name || 'pcs' }}</span>
                                    </td>
                                    <td class="text-center font-weight-bold" :class="p.stock <= 5 ? 'text-danger' : 'text-success'">
                                        {{ p.stock }} {{ p.unit?.short_name || 'pcs' }}
                                    </td>
                                    <td class="text-right font-weight-bold">৳{{ parseFloat(p.cost_price).toFixed(2) }}</td>
                                    <td class="text-right font-weight-bold text-primary">৳{{ parseFloat(p.price).toFixed(2) }}</td>
                                </tr>
                                <tr v-if="filteredProducts.length === 0">
                                    <td colspan="6" class="text-center p-5 text-muted h5">No product records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
