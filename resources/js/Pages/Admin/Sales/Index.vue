<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sales: Array
});

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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Sales List</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-globe"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Web Sales</span>
                                <span class="info-box-number">৳{{ totalWebSalesValue }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-cash-register"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">POS Sales</span>
                                <span class="info-box-number">৳{{ totalPosSalesValue }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">All Sales Records</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Order/Inv No</th>
                                    <th>Source</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total (৳)</th>
                                    <th>Paid (৳)</th>
                                    <th>Due (৳)</th>
                                    <th>Payment</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(sale, index) in sales" :key="sale.id">
                                    <td>{{ index + 1 }}</td>
                                    <td><code>{{ sale.order_number }}</code></td>
                                    <td>
                                        <span class="badge" :class="getSourceBadge(sale.order_source)">
                                            <i class="fas" :class="sale.order_source === 'pos' ? 'fa-cash-register' : 'fa-globe'"></i>
                                            {{ sale.order_source === 'pos' ? 'POS' : 'WEB' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="sale.order_source === 'pos'">
                                            {{ sale.customer?.name || 'Walk-in Customer' }}
                                        </span>
                                        <span v-else>
                                            {{ sale.user?.name || 'Guest' }}
                                        </span>
                                    </td>
                                    <td>{{ new Date(sale.created_at).toLocaleDateString() }}</td>
                                    <td class="font-weight-bold">৳{{ sale.total }}</td>
                                    <td class="text-success">৳{{ sale.paid_amount || '0.00' }}</td>
                                    <td class="text-danger">৳{{ sale.due_amount || '0.00' }}</td>
                                    <td>
                                        <span class="badge" :class="getPaymentBadge(sale.payment_status)">{{ sale.payment_status }}</span>
                                    </td>
                                    <td>
                                        <Link :href="route('admin.sales.show', sale.id)" class="btn btn-info btn-xs mr-1">
                                            <i class="fas fa-eye"></i>
                                        </Link>
                                        <button class="btn btn-danger btn-xs" @click="deleteSale(sale.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="sales.length === 0">
                                    <td colspan="10" class="text-center p-4 text-muted">No sales records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
