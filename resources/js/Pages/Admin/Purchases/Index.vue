<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    purchases: Array
});

const columns = [
    { key: 'reference_no', label: 'Ref No', sortable: true },
    { key: 'supplier', label: 'Supplier', sortable: true },
    { key: 'purchase_date', label: 'Date', sortable: true },
    { key: 'total_amount', label: 'Total', sortable: true, cellClass: 'text-right' },
    { key: 'paid_amount', label: 'Paid', sortable: true, cellClass: 'text-right' },
    { key: 'due_amount', label: 'Due', sortable: true, cellClass: 'text-right' },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'payment_status', label: 'Payment', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' }
];

const totalPurchasesValue = computed(() => {
    return props.purchases.reduce((sum, item) => sum + parseFloat(item.total_amount), 0).toFixed(2);
});

const totalDueValue = computed(() => {
    return props.purchases.reduce((sum, item) => sum + parseFloat(item.due_amount), 0).toFixed(2);
});

function getStatusBadge(status) {
    const s = status?.toLowerCase();
    if (s === 'received') return 'badge-success';
    if (s === 'ordered') return 'badge-info';
    return 'badge-warning';
}

function getPaymentBadge(status) {
    const s = status?.toLowerCase();
    if (s === 'paid') return 'badge-success';
    if (s === 'partial') return 'badge-warning';
    return 'badge-danger';
}

function deletePurchase(id) {
    if (confirm('Are you sure you want to delete this purchase record? This will also reverse the stock!')) {
        router.delete(route('admin.purchases.destroy', id));
    }
}
</script>

<template>
    <Head title="Manage Purchases" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Purchases</h1>
                        <p class="text-muted text-sm mb-0">Track inventory acquisitions and supplier payments</p>
                    </div>
                    <Link :href="route('admin.purchases.create')" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> New Purchase
                    </Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-info-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-info-soft mr-3">
                                    <i class="fas fa-file-invoice-dollar text-info"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Purchase</div>
                                    <div class="h4 font-bold mb-0 text-info">৳{{ totalPurchasesValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-danger-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-danger-soft mr-3">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Due</div>
                                    <div class="h4 font-bold mb-0 text-danger">৳{{ totalDueValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="purchases" 
                    :headers="columns"
                    search-placeholder="Search by ref no or supplier..."
                >
                    <!-- Ref No Cell -->
                    <template #cell-reference_no="{ item }">
                        <code class="text-xs px-2 py-1 bg-light rounded text-dark">{{ item.reference_no }}</code>
                    </template>

                    <!-- Supplier Cell -->
                    <template #cell-supplier="{ item }">
                        <div class="font-weight-bold text-dark">{{ item.supplier?.name }}</div>
                        <div class="text-xs text-muted" v-if="item.supplier?.company_name">{{ item.supplier.company_name }}</div>
                    </template>

                    <!-- Date Cell -->
                    <template #cell-purchase_date="{ item }">
                        <span class="text-sm">{{ item.purchase_date }}</span>
                    </template>

                    <!-- Total Amount Cell -->
                    <template #cell-total_amount="{ item }">
                        <div class="font-weight-bold text-dark text-right">৳{{ item.total_amount }}</div>
                    </template>

                    <!-- Paid Amount Cell -->
                    <template #cell-paid_amount="{ item }">
                        <div class="text-success text-right font-medium">৳{{ item.paid_amount }}</div>
                    </template>

                    <!-- Due Amount Cell -->
                    <template #cell-due_amount="{ item }">
                        <div class="text-danger text-right font-medium">৳{{ item.due_amount }}</div>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-status="{ item }">
                        <span class="badge" :class="getStatusBadge(item.status)">{{ item.status }}</span>
                    </template>

                    <!-- Payment Status Cell -->
                    <template #cell-payment_status="{ item }">
                        <span class="badge" :class="getPaymentBadge(item.payment_status)">{{ item.payment_status }}</span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <Link :href="route('admin.purchases.show', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none" title="View">
                                <i class="fas fa-eye text-info text-xs"></i>
                            </Link>
                            <Link :href="route('admin.purchases.edit', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </Link>
                            <button @click="deletePurchase(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>
