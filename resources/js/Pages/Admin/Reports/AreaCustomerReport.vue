<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    areas: { type: Array, default: () => [] }
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '50px' },
    { key: 'division_name', label: 'Division', sortable: true },
    { key: 'district_name', label: 'District', sortable: true },
    { key: 'customers_count', label: 'Customers', sortable: true, cellClass: 'text-center' },
];

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Area-wise Customer Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Area-wise Customers</h1>
                        <p class="text-muted text-sm mb-0">Customer distribution across divisions and districts</p>
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
                    <PremiumTable
                        :items="areas"
                        :headers="columns"
                        search-placeholder="Search division or district..."
                    >
                        <template #cell-index="{ index }">
                            <span class="text-muted">{{ index + 1 }}</span>
                        </template>
                        <template #cell-division_name="{ item }">
                            <span class="font-weight-bold text-dark">{{ item.division_name || 'Unknown' }}</span>
                        </template>
                        <template #cell-district_name="{ item }">{{ item.district_name || 'Unknown' }}</template>
                        <template #cell-customers_count="{ item }">
                            <span class="badge badge-success-soft text-success border px-2">{{ item.customers_count }} Customers</span>
                        </template>

                        <template #footer="{ filteredItems }">
                            <tr class="bg-light d-print-table-row">
                                <td colspan="3" class="text-right font-weight-bold py-3">Grand Total:</td>
                                <td class="text-center font-weight-bold py-3">
                                    {{ filteredItems.reduce((sum, a) => sum + Number(a.customers_count || 0), 0) }}
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
}
</style>
