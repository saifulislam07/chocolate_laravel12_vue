<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sales: Array
});

const columns = [
    { key: 'order_number', label: 'Order/Inv', sortable: true },
    { key: 'order_source', label: 'Source', sortable: true },
    { key: 'customer', label: 'Customer', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true },
    { key: 'total', label: 'Total', sortable: true },
    { key: 'payment_status', label: 'Payment', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

const getSourceBadge = (source) => {
    if (source === 'pos') return 'badge-success';
    return 'badge-info';
};

const getPaymentBadge = (status) => {
    if (status === 'paid') return 'badge-success';
    if (status === 'partial') return 'badge-warning';
    return 'badge-danger';
};

const totalWebSalesValue = computed(() => {
    return props.sales.filter(s => s.order_source === 'web').reduce((acc, curr) => acc + parseFloat(curr.total), 0).toFixed(2);
});

const totalPosSalesValue = computed(() => {
    return props.sales.filter(s => s.order_source === 'pos').reduce((acc, curr) => acc + parseFloat(curr.total), 0).toFixed(2);
});

function deleteSale(id) {
    if (confirm('Are you sure you want to delete this sale record?')) {
        router.delete(route('admin.sales.destroy', id));
    }
}
</script>

<template>
    <Head title="Sale List (Web & POS)" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Sales Records</h1>
                        <p class="text-muted text-sm mb-0">Overview of all transactions from Web and POS</p>
                    </div>
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
                                    <i class="fas fa-globe text-info"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Web Sales</div>
                                    <div class="h4 font-bold mb-0 text-info">৳{{ totalWebSalesValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-success-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-success-soft mr-3">
                                    <i class="fas fa-cash-register text-success"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">POS Sales</div>
                                    <div class="h4 font-bold mb-0 text-success">৳{{ totalPosSalesValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="sales" 
                    :headers="columns"
                    search-placeholder="Search by order number or customer..."
                >
                    <!-- Order Number Cell -->
                    <template #cell-order_number="{ item }">
                        <code class="text-xs font-bold text-indigo">{{ item.order_number }}</code>
                    </template>

                    <!-- Source Cell -->
                    <template #cell-order_source="{ item }">
                        <span class="badge" :class="getSourceBadge(item.order_source)">
                            <i class="fas" :class="item.order_source === 'pos' ? 'fa-cash-register' : 'fa-globe'"></i>
                            {{ item.order_source === 'pos' ? 'POS' : 'WEB' }}
                        </span>
                    </template>

                    <!-- Customer Cell -->
                    <template #cell-customer="{ item }">
                        <div class="font-weight-medium">
                            {{ item.order_source === 'pos' ? (item.customer?.name || 'Walk-in Customer') : (item.user?.name || 'Guest') }}
                        </div>
                    </template>

                    <!-- Date Cell -->
                    <template #cell-created_at="{ item }">
                        <div class="text-sm text-muted">
                            {{ new Date(item.created_at).toLocaleDateString() }}
                        </div>
                    </template>

                    <!-- Total Cell -->
                    <template #cell-total="{ item }">
                        <div class="font-weight-bold text-dark">৳{{ item.total }}</div>
                        <div class="text-xs text-success" v-if="item.paid_amount > 0">Paid: ৳{{ item.paid_amount }}</div>
                    </template>

                    <!-- Payment Cell -->
                    <template #cell-payment_status="{ item }">
                        <span class="badge" :class="getPaymentBadge(item.payment_status)">{{ item.payment_status }}</span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <Link :href="route('admin.sales.show', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none">
                                <i class="fas fa-eye text-primary text-xs"></i>
                            </Link>
                            <button @click="deleteSale(item.id)" class="btn btn-light btn-sm border shadow-none">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.bg-info-soft { background-color: rgba(37, 99, 235, 0.05) !important; }
.bg-success-soft { background-color: rgba(5, 150, 105, 0.05) !important; }
.border-info-soft { border-color: rgba(37, 99, 235, 0.1) !important; }
.border-success-soft { border-color: rgba(5, 150, 105, 0.1) !important; }
.icon-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>
