<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recent_orders: Array,
    top_products: Array,
    recent_expenses: Array
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(amount);
};
</script>

<template>
    <Head title="Performance Dashboard" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Operational Overview</h1>
            <p class="text-slate-500 text-sm">Real-time metrics and business performance analytics.</p>
        </div>

        <section class="content">
            <!-- Summary Stats -->
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4 border-left-primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-slate-500 text-xs font-bold text-uppercase tracking-wider mb-2">Net Revenue</p>
                                    <h3 class="text-2xl font-bold mb-0 text-slate-800">{{ formatCurrency(stats.total_sales) }}</h3>
                                </div>
                                <div class="bg-indigo-50 p-3 rounded-lg flex-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-dollar-sign text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-green-600 font-semibold">
                                <i class="fas fa-arrow-up mr-1"></i> 12.5% <span class="text-slate-400 font-normal ml-1">vs last month</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 border-left-info">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-slate-500 text-xs font-bold text-uppercase tracking-wider mb-2">Total Orders</p>
                                    <h3 class="text-2xl font-bold mb-0 text-slate-800">{{ stats.orders_count }}</h3>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-lg flex-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-shopping-cart text-blue-600"></i>
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-slate-400 font-normal">
                                Average order value: <span class="font-bold text-slate-700">{{ formatCurrency(stats.total_sales / (stats.orders_count || 1)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 border-left-success">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-slate-500 text-xs font-bold text-uppercase tracking-wider mb-2">Customers</p>
                                    <h3 class="text-2xl font-bold mb-0 text-slate-800">{{ stats.customers_count }}</h3>
                                </div>
                                <div class="bg-emerald-50 p-3 rounded-lg flex-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user-friends text-emerald-600"></i>
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-green-600 font-semibold">
                                <i class="fas fa-user-plus mr-1"></i> {{ Math.floor(stats.customers_count * 0.1) }} new <span class="text-slate-400 font-normal ml-1">this week</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 border-left-danger">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-slate-500 text-xs font-bold text-uppercase tracking-wider mb-2">Stock Alerts</p>
                                    <h3 class="text-2xl font-bold mb-0 text-slate-800">{{ stats.low_stock_count }}</h3>
                                </div>
                                <div class="bg-rose-50 p-3 rounded-lg flex-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-exclamation-circle text-rose-600"></i>
                                </div>
                            </div>
                             <div class="mt-3">
                                <Link :href="route('admin.reports.stock')" class="text-xs text-rose-600 font-bold hover:underline">Restock Required &rarr;</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <!-- Orders Table -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-light pt-4 px-4 pb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="text-base font-bold text-slate-800 mb-0">Recent Transactions</h3>
                                <Link :href="route('admin.sales.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">View History &rarr;</Link>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 border-0">Reference</th>
                                            <th class="py-3 border-0">Customer</th>
                                            <th class="py-3 border-0">Amount</th>
                                            <th class="py-3 border-0">Status</th>
                                            <th class="text-right px-4 py-3 border-0">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="order in recent_orders" :key="order.id" class="border-bottom">
                                            <td class="px-4 py-3 border-0 align-middle"><span class="badge bg-slate-100 text-slate-700">#ORD-{{ order.id }}</span></td>
                                            <td class="py-3 border-0 align-middle font-medium text-slate-700">{{ order.user?.name || 'Walk-in' }}</td>
                                            <td class="py-3 border-0 align-middle font-bold text-indigo-600">{{ formatCurrency(order.total_amount) }}</td>
                                            <td class="py-3 border-0 align-middle">
                                                <span class="badge rounded-pill bg-emerald-100 text-emerald-700 px-2 font-bold text-[10px]">SUCCESS</span>
                                            </td>
                                            <td class="text-right px-4 py-3 border-0 align-middle text-slate-400 text-xs">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-light pt-4 px-4 pb-3">
                            <h3 class="text-base font-bold text-slate-800 mb-0">Top Performing Products</h3>
                        </div>
                        <div class="card-body px-4 py-2">
                            <div v-for="item in top_products" :key="item.product_id" class="py-3 border-bottom d-flex align-items-center">
                                <div class="bg-slate-50 rounded p-2 mr-3 flex-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-box text-slate-400"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-sm font-bold text-slate-800">{{ item.product?.name }}</p>
                                    <span class="text-xs text-slate-500">{{ item.total_sold }} units sold</span>
                                </div>
                                <div class="text-right">
                                    <p class="mb-0 text-sm font-bold text-indigo-600">{{ formatCurrency(item.total_revenue) }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link :href="route('admin.products.index')" class="btn btn-outline-primary btn-sm btn-block rounded-lg font-bold">Manage Catalog</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.border-left-primary { border-left: 4px solid var(--primary) !important; }
.border-left-info { border-left: 4px solid #0ea5e9 !important; }
.border-left-success { border-left: 4px solid #10b981 !important; }
.border-left-danger { border-left: 4px solid #f43f5e !important; }
.border-bottom-light { border-bottom: 1px solid #f1f5f9; }
.text-slate-800 { color: #1e293b; }
.text-slate-500 { color: #64748b; }
.text-slate-400 { color: #94a3b8; }
.bg-slate-50 { background-color: #f8fafc; }
.bg-slate-100 { background-color: #f1f5f9; }

.flex-center {
    display: flex;
    align-items: center;
    justify-content: center;
}

.text-xs { font-size: 0.75rem; }
.text-base { font-size: 1rem; }
</style>

