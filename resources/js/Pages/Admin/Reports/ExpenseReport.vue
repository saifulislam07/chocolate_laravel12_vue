<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    expenses: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'expense_date', label: 'Date', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'reference_no', label: 'Reference', sortable: true },
    { key: 'description', label: 'Description', sortable: false },
    { key: 'amount', label: 'Amount', sortable: true, cellClass: 'text-right font-bold text-danger' }
];

const startDate = ref('');
const endDate = ref('');

const filterReport = () => {
    router.get(route('admin.reports.expenses'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Expense Statement Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Expense Statement</h1>
                        <p class="text-muted text-sm mb-0">Review business expenditures and overheads</p>
                    </div>
                    <div class="d-flex">
                        <div class="d-flex align-items-center mr-3 bg-white rounded-pill px-3 shadow-sm border" style="height: 42px;">
                            <input type="date" v-model="startDate" class="border-0 bg-transparent text-xs font-bold mr-2" style="outline: none;">
                            <span class="text-muted mx-2">-</span>
                            <input type="date" v-model="endDate" class="border-0 bg-transparent text-xs font-bold ml-2" style="outline: none;">
                            <button @click="filterReport" class="btn btn-link py-0 px-2 text-danger">
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
                        <h2 class="font-weight-bold mb-0 text-dark text-uppercase">SWEET CHOCOLATE</h2>
                        <p class="text-muted">Business Expense Statement Report</p>
                    </div>

                    <PremiumTable 
                        :items="expenses" 
                        :headers="columns"
                        search-placeholder="Search records..."
                    >
                        <!-- Index Cell -->
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>

                        <!-- Category Cell -->
                        <template #cell-category="{ item }">
                            <span class="badge badge-secondary">{{ item.category?.name }}</span>
                        </template>

                        <!-- Reference Cell -->
                        <template #cell-reference_no="{ item }">
                            <code>{{ item.reference_no || 'N/A' }}</code>
                        </template>

                        <!-- Amount Cell -->
                        <template #cell-amount="{ item }">
                            ৳{{ parseFloat(item.amount).toFixed(2) }}
                        </template>

                        <!-- Footer Slot -->
                        <template #footer="{ filteredItems }">
                            <tr class="bg-light d-print-table-row">
                                <td colspan="5" class="text-right font-weight-bold py-3">Total Expenditures:</td>
                                <td class="text-right font-weight-bold text-danger h5 mb-0 py-3">
                                    ৳{{ filteredItems.reduce((sum, e) => sum + parseFloat(e.amount), 0).toFixed(2) }}
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
