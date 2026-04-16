<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'name', label: 'Supplier Name', sortable: true },
    { key: 'company_name', label: 'Company', sortable: true },
    { key: 'purchases_count', label: 'Total Orders', sortable: true, cellClass: 'text-center' },
    { key: 'total_amount', label: 'Total Amount', sortable: true, cellClass: 'text-right' },
    { key: 'paid_amount', label: 'Total Paid', sortable: true, cellClass: 'text-right text-success' },
    { key: 'due_amount', label: 'Balance Due', sortable: true, cellClass: 'text-right font-bold text-danger' }
];

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Supplier Business Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Supplier Outstanding</h1>
                        <p class="text-muted text-sm mb-0">Monitor supplier liabilities and procurement history</p>
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
                        <p class="text-muted">Supplier Outstanding & Business Report</p>
                    </div>

                    <PremiumTable 
                        :items="suppliers" 
                        :headers="columns"
                        search-placeholder="Search supplier or company..."
                    >
                        <!-- Index Cell -->
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>

                        <!-- Supplier Name Cell -->
                        <template #cell-name="{ item }">
                            <div class="font-weight-bold text-dark">{{ item.name }}</div>
                            <div class="text-xs text-muted">{{ item.phone }}</div>
                        </template>

                        <!-- Company Cell -->
                        <template #cell-company_name="{ item }">
                            <span class="text-muted text-sm">{{ item.company_name || 'N/A' }}</span>
                        </template>

                        <!-- Total Orders Cell -->
                        <template #cell-purchases_count="{ item }">
                            <span class="badge badge-info-soft text-info border px-2">{{ item.purchases_count }} Purchases</span>
                        </template>

                        <!-- Amounts -->
                        <template #cell-total_amount="{ item }">৳{{ parseFloat(item.purchases_sum_total_amount || 0).toFixed(2) }}</template>
                        <template #cell-paid_amount="{ item }">৳{{ parseFloat(item.purchases_sum_paid_amount || 0).toFixed(2) }}</template>
                        <template #cell-due_amount="{ item }">৳{{ parseFloat(item.purchases_sum_due_amount || 0).toFixed(2) }}</template>

                        <!-- Footer Slot -->
                        <template #footer="{ filteredItems }">
                            <tr class="bg-light d-print-table-row">
                                <td colspan="4" class="text-right font-weight-bold py-3">Grand Total Summary:</td>
                                <td class="text-right font-weight-bold py-3 text-dark">
                                    ৳{{ filteredItems.reduce((sum, s) => sum + parseFloat(s.purchases_sum_total_amount || 0), 0).toFixed(2) }}
                                </td>
                                <td class="text-right font-weight-bold py-3 text-success">
                                    ৳{{ filteredItems.reduce((sum, s) => sum + parseFloat(s.purchases_sum_paid_amount || 0), 0).toFixed(2) }}
                                </td>
                                <td class="text-right font-weight-bold py-3 text-danger">
                                    ৳{{ filteredItems.reduce((sum, s) => sum + parseFloat(s.purchases_sum_due_amount || 0), 0).toFixed(2) }}
                                </td>
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
