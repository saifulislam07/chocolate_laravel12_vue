<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    purchases: Array
});

const totalPurchases = computed(() => {
    return props.purchases.reduce((sum, item) => sum + parseFloat(item.total_amount), 0).toFixed(2);
});

const totalDue = computed(() => {
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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Purchases</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-info shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Purchase</span>
                                <span class="info-box-number">৳{{ totalPurchases }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Due</span>
                                <span class="info-box-number">৳{{ totalDue }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">Purchase Records</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.purchases.create')" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> New Purchase
                            </Link>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Ref No</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(purchase, index) in purchases" :key="purchase.id">
                                    <td>{{ index + 1 }}</td>
                                    <td><code>{{ purchase.reference_no }}</code></td>
                                    <td class="text-bold">{{ purchase.supplier?.name }}</td>
                                    <td>{{ purchase.purchase_date }}</td>
                                    <td>৳{{ purchase.total_amount }}</td>
                                    <td class="text-success">৳{{ purchase.paid_amount }}</td>
                                    <td class="text-danger">৳{{ purchase.due_amount }}</td>
                                    <td>
                                        <span class="badge" :class="getStatusBadge(purchase.status)">{{ purchase.status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getPaymentBadge(purchase.payment_status)">{{ purchase.payment_status }}</span>
                                    </td>
                                    <td>
                                        <Link :href="route('admin.purchases.show', purchase.id)" class="btn btn-info btn-xs mr-1" title="View Detail">
                                            <i class="fas fa-eye"></i>
                                        </Link>
                                        <Link :href="route('admin.purchases.edit', purchase.id)" class="btn btn-warning btn-xs mr-1" title="Edit Record">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button class="btn btn-danger btn-xs" @click="deletePurchase(purchase.id)" title="Delete & Reverse Stock">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="purchases.length === 0">
                                    <td colspan="10" class="text-center p-4 text-muted">No purchase records found. Click "New Purchase" to add.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
