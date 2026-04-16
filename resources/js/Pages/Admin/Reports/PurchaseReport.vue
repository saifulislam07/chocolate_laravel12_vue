<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    purchases: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'purchase_date', label: 'Date', sortable: true },
    { key: 'reference_no', label: 'Reference', sortable: true },
    { key: 'supplier', label: 'Supplier', sortable: true },
    { key: 'total_amount', label: 'Total', sortable: true, cellClass: 'text-right font-bold' },
    { key: 'paid_amount', label: 'Paid', sortable: true, cellClass: 'text-right text-success' },
    { key: 'due_amount', label: 'Due', sortable: true, cellClass: 'text-right text-danger' },
    { key: 'payment_status', label: 'Payment', sortable: true, cellClass: 'text-center d-print-none' }
];

const startDate = ref('');
const endDate = ref('');

const filterReport = () => {
    router.get(route('admin.reports.purchases'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Purchase Transaction Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Purchase Statement</h1>
                        <p class="text-muted text-sm mb-0">Analysis of inventory procurement and liabilities</p>
                    </div>
                    <div class="d-flex">
                        <div class="d-flex align-items-center mr-3 bg-white rounded-pill px-3 shadow-sm border" style="height: 42px;">
                            <input type="date" v-model="startDate" class="border-0 bg-transparent text-xs font-bold mr-2" style="outline: none;">
                            <span class="text-muted mx-2">-</span>
                            <input type="date" v-model="endDate" class="border-0 bg-transparent text-xs font-bold ml-2" style="outline: none;">
                            <button @click="filterReport" class="btn btn-link py-0 px-2 text-warning">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                        <button onclick="window.print()" class="btn btn-outline-primary shadow-sm rounded-pill font-weight-bold">
                            <i class="fas fa-print mr-2"></i> Print Statement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="premium-card bg-white rounded-lg shadow-sm">
                    <div class="text-center d-none d-print-block mb-4 pt-4">
                        <h2 class="font-weight-bold mb-0">SWEET CHOCOLATE</h2>
                        <p class="text-muted">Purchase Statement Report</p>
                    </div>

                    <PremiumTable 
                        :items="purchases" 
                        :headers="columns"
                        search-placeholder="Search records..."
                    >
                        <!-- Index Cell -->
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>

                        <!-- Reference Cell -->
                        <template #cell-reference_no="{ item }">
                            <code>{{ item.reference_no }}</code>
                        </template>

                        <!-- Supplier Cell -->
                        <template #cell-supplier="{ item }">
                            <span class="font-weight-bold">{{ item.supplier?.name }}</span>
                        </template>

                        <!-- Amount Cells -->
                        <template #cell-total_amount="{ item }">৳{{ parseFloat(item.total_amount).toFixed(2) }}</template>
                        <template #cell-paid_amount="{ item }">৳{{ parseFloat(item.paid_amount).toFixed(2) }}</template>
                        <template #cell-due_amount="{ item }">৳{{ parseFloat(item.due_amount).toFixed(2) }}</template>

                        <!-- Status Cell -->
                        <template #cell-payment_status="{ item }">
                            <span class="badge" :class="item.payment_status === 'Paid' ? 'badge-success' : 'badge-warning'">{{ item.payment_status }}</span>
                        </template>

                        <!-- Footer Slot -->
                        <template #footer="{ filteredItems }">
                            <tr class="bg-light d-print-table-row">
                                <td colspan="4" class="text-right font-weight-bold py-3">Period Totals:</td>
                                <td class="text-right font-weight-bold py-3 text-dark">
                                    ৳{{ filteredItems.reduce((sum, p) => sum + parseFloat(p.total_amount), 0).toFixed(2) }}
                                </td>
                                <td class="text-right font-weight-bold py-3 text-success">
                                    ৳{{ filteredItems.reduce((sum, p) => sum + parseFloat(p.paid_amount), 0).toFixed(2) }}
                                </td>
                                <td class="text-right font-weight-bold py-3 text-danger">
                                    ৳{{ filteredItems.reduce((sum, p) => sum + parseFloat(p.due_amount), 0).toFixed(2) }}
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
