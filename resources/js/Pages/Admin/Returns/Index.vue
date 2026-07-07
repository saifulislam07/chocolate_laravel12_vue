<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    returns: { type: Array, default: () => [] },
});

const columns = [
    { key: 'return_number', label: 'Return #', sortable: true },
    { key: 'order', label: 'Order', sortable: false },
    { key: 'return_source', label: 'Source', sortable: true },
    { key: 'customer', label: 'Customer', sortable: false },
    { key: 'subtotal_refund', label: 'Refund Amount', sortable: true, cellClass: 'text-right' },
    { key: 'created_at', label: 'Date', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '100px' },
];

function formatMoney(value) {
    return '৳' + Number(value || 0).toFixed(2);
}

function voidReturn(id) {
    if (confirm('Void this return? Stock and wallet effects will be reversed.')) {
        router.delete(route('admin.returns.destroy', id));
    }
}
</script>

<template>
    <Head title="Sales Returns" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-4">
                    <h1 class="m-0 text-dark font-bold h3">Sales Returns</h1>
                    <p class="text-muted text-sm mb-0">Returns processed against Online and POS sales</p>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable :items="returns" :headers="columns" search-placeholder="Search returns...">
                    <template #cell-return_number="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.return_number }}</span>
                    </template>
                    <template #cell-order="{ item }">{{ item.order?.order_number || 'N/A' }}</template>
                    <template #cell-return_source="{ item }">
                        <span class="badge" :class="item.return_source === 'pos' ? 'badge-success' : 'badge-info'">
                            <i class="fas" :class="item.return_source === 'pos' ? 'fa-cash-register' : 'fa-globe'"></i>
                            {{ item.return_source === 'pos' ? 'POS' : 'WEB' }}
                        </span>
                    </template>
                    <template #cell-customer="{ item }">{{ item.customer?.name || 'N/A' }}</template>
                    <template #cell-subtotal_refund="{ item }">
                        <span class="font-weight-bold text-danger">{{ formatMoney(item.subtotal_refund) }}</span>
                    </template>
                    <template #cell-created_at="{ item }">
                        <span class="text-xs text-muted">{{ new Date(item.created_at).toLocaleDateString() }}</span>
                    </template>
                    <template #cell-actions="{ item }">
                        <button @click="voidReturn(item.id)" class="btn btn-light btn-sm border shadow-none" title="Void Return">
                            <i class="fas fa-trash text-danger text-xs"></i>
                        </button>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>
