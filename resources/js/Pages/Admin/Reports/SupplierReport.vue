<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Array
});

const searchTerm = ref('');

const filteredSuppliers = computed(() => {
    return props.suppliers.filter(s => 
        s.name.toLowerCase().includes(searchTerm.value.toLowerCase()) || 
        (s.company_name && s.company_name.toLowerCase().includes(searchTerm.value.toLowerCase()))
    );
});
</script>

<template>
    <Head title="Supplier Business Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-user-tie mr-2 text-primary"></i>Supplier Outstanding</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary shadow-sm" style="border-radius: 12px;">
                    <div class="card-header border-0 d-print-none">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" v-model="searchTerm" class="form-control" placeholder="Search supplier or company...">
                            </div>
                            <div class="col-md-8 text-right">
                                <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-print mr-1"></i> Print Report</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier Name</th>
                                    <th>Company</th>
                                    <th class="text-center">Total Orders</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-right">Total Paid</th>
                                    <th class="text-right">Balance Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(supplier, index) in filteredSuppliers" :key="supplier.id">
                                    <td>{{ index + 1 }}</td>
                                    <td class="font-weight-bold">{{ supplier.name }}<br><small class="text-muted">{{ supplier.phone }}</small></td>
                                    <td>{{ supplier.company_name || 'N/A' }}</td>
                                    <td class="text-center"><span class="badge badge-info">{{ supplier.purchases_count }} Purchases</span></td>
                                    <td class="text-right">৳{{ parseFloat(supplier.purchases_sum_total_amount || 0).toFixed(2) }}</td>
                                    <td class="text-right text-success">৳{{ parseFloat(supplier.purchases_sum_paid_amount || 0).toFixed(2) }}</td>
                                    <td class="text-right font-weight-bold text-danger">৳{{ parseFloat(supplier.purchases_sum_due_amount || 0).toFixed(2) }}</td>
                                </tr>
                                <tr v-if="filteredSuppliers.length === 0">
                                    <td colspan="7" class="text-center p-5 text-muted h5">No supplier records found.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="filteredSuppliers.length > 0" class="bg-light font-weight-bold">
                                <tr>
                                    <th colspan="4" class="text-right">Grand Total Summary:</th>
                                    <th class="text-right">৳{{ filteredSuppliers.reduce((sum, s) => sum + parseFloat(s.purchases_sum_total_amount || 0), 0).toFixed(2) }}</th>
                                    <th class="text-right text-success">৳{{ filteredSuppliers.reduce((sum, s) => sum + parseFloat(s.purchases_sum_paid_amount || 0), 0).toFixed(2) }}</th>
                                    <th class="text-right text-danger">৳{{ filteredSuppliers.reduce((sum, s) => sum + parseFloat(s.purchases_sum_due_amount || 0), 0).toFixed(2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
