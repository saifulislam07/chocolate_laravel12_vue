<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    refunds: { type: Array, default: () => [] },
});

const columns = [
    { key: 'return', label: 'Return #', sortable: false },
    { key: 'customer', label: 'Customer', sortable: false },
    { key: 'amount', label: 'Amount', sortable: true, cellClass: 'text-right' },
    { key: 'method', label: 'Method', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true },
];

const methodBadges = {
    wallet: 'badge-success',
    cash: 'badge-secondary',
    bkash: 'badge-danger',
    nagad: 'badge-warning',
    bank: 'badge-info',
    card: 'badge-primary',
};

function formatMoney(value) {
    return '৳' + Number(value || 0).toFixed(2);
}
</script>

<template>
    <Head title="Return Refunds" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-4">
                    <h1 class="m-0 text-dark font-bold h3">Return Refunds</h1>
                    <p class="text-muted text-sm mb-0">Every refund issued against a sales return — wallet credits and external refunds alike</p>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable :items="refunds" :headers="columns" search-placeholder="Search refunds...">
                    <template #cell-return="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.sales_return?.return_number || 'N/A' }}</span>
                    </template>
                    <template #cell-customer="{ item }">{{ item.customer?.name || 'N/A' }}</template>
                    <template #cell-amount="{ item }">
                        <span class="font-weight-bold text-danger">{{ formatMoney(item.amount) }}</span>
                    </template>
                    <template #cell-method="{ item }">
                        <span class="badge text-uppercase" :class="methodBadges[item.method] || 'badge-secondary'">{{ item.method }}</span>
                    </template>
                    <template #cell-status="{ item }">
                        <span class="badge badge-success text-capitalize">{{ item.status }}</span>
                    </template>
                    <template #cell-created_at="{ item }">
                        <span class="text-xs text-muted">{{ new Date(item.created_at).toLocaleDateString() }}</span>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>
