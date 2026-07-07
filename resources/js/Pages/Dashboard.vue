<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
    ArcElement,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler, ArcElement);

const props = defineProps({
    stats: Object,
    recent_orders: Array,
    top_products: Array,
    recent_expenses: Array,
    salesTrend: { type: Array, default: () => [] },
    ordersByStatus: { type: Object, default: () => ({}) },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT' }).format(amount || 0);
};

const salesChartData = computed(() => ({
    labels: props.salesTrend.map((day) => day.label),
    datasets: [
        {
            label: 'Sales',
            data: props.salesTrend.map((day) => day.total),
            borderColor: '#C97830',
            backgroundColor: 'rgba(232, 154, 80, 0.15)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#C97830',
        },
    ],
}));

const salesChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { font: { size: 10 } } },
        x: { ticks: { font: { size: 10 } } },
    },
};

const statusColors = {
    pending: '#f59e0b',
    processing: '#3b82f6',
    shipped: '#8b5cf6',
    delivered: '#10b981',
    cancelled: '#ef4444',
};

const orderStatusChartData = computed(() => {
    const labels = Object.keys(props.ordersByStatus);
    return {
        labels: labels.map((s) => s.charAt(0).toUpperCase() + s.slice(1)),
        datasets: [
            {
                data: Object.values(props.ordersByStatus),
                backgroundColor: labels.map((s) => statusColors[s] || '#94a3b8'),
                borderWidth: 0,
            },
        ],
    };
});

const orderStatusChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
};
</script>

<template>
    <Head title="Performance Dashboard" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-8 d-flex justify-content-between align-items-end">
            <div>
                <h1 class="text-3xl font-bold text-dark mb-1">Operational Overview</h1>
                <p class="text-slate-500 text-sm font-medium">Real-time metrics and business performance analytics.</p>
            </div>
            <div class="d-none d-md-block">
                <button class="btn btn-white border shadow-sm mr-2 bg-white"><i class="fas fa-file-export mr-2 text-primary"></i> Export Report</button>
                <Link :href="route('admin.pos.index')" class="btn btn-primary"><i class="fas fa-bolt mr-2"></i> Quick POS</Link>
            </div>
        </div>

        <section class="content">
            <!-- Today Snapshot -->
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #4B2E1E 0%, #3A2517 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-white/60 mb-1">Today's Sales</p>
                                <i class="fas fa-sun text-godiva-gold"></i>
                            </div>
                            <h3 class="text-2xl font-black tracking-tight mb-0" style="color: #E89A50;">{{ formatCurrency(stats.today_sales) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">Today's Orders</p>
                                <i class="fas fa-bolt text-blue-500"></i>
                            </div>
                            <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ stats.today_orders_count }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">New Customers Today</p>
                                <i class="fas fa-user-plus text-emerald-500"></i>
                            </div>
                            <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ stats.today_customers_count }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Stats with Vibrant Styling -->
            <div class="row">
                <!-- Revenue -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm group">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">Net Revenue</p>
                                    <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ formatCurrency(stats.total_sales) }}</h3>
                                </div>
                                <div class="p-3 rounded-2xl bg-indigo-500 shadow-lg shadow-indigo-100 text-white flex-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center text-xs">
                                <span class="text-slate-400 font-medium tracking-tight">All-time total</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm group">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">Total Orders</p>
                                    <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ stats.orders_count }}</h3>
                                </div>
                                <div class="p-3 rounded-2xl bg-blue-500 shadow-lg shadow-blue-100 text-white flex-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                            </div>
                            <div class="text-xs text-slate-400 font-medium">
                                Average: <span class="text-dark font-bold">{{ formatCurrency(stats.total_sales / (stats.orders_count || 1)) }}</span> / order
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customers -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm group">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">Total Customers</p>
                                    <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ stats.customers_count }}</h3>
                                </div>
                                <div class="p-3 rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-100 text-white flex-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-users-viewfinder"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center text-xs text-emerald-600 font-bold">
                                <i class="fas fa-chart-line mr-2"></i> Growing steadily
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Alerts -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm group">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1">Stock Alerts</p>
                                    <h3 class="text-2xl font-black text-dark tracking-tight mb-0">{{ stats.low_stock_count }}</h3>
                                </div>
                                <div class="p-3 rounded-2xl bg-rose-500 shadow-lg shadow-rose-100 text-white flex-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <Link :href="route('admin.products.index')" class="text-[10px] uppercase tracking-widest font-black text-rose-600 hover:text-rose-800 transition-colors">Review Inventory &rarr;</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-light pt-4 px-4 pb-3">
                            <h3 class="text-base font-bold text-slate-800 mb-0">Sales — Last 7 Days</h3>
                        </div>
                        <div class="card-body p-4" style="height: 260px;">
                            <Line :data="salesChartData" :options="salesChartOptions" />
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom-light pt-4 px-4 pb-3">
                            <h3 class="text-base font-bold text-slate-800 mb-0">Orders by Status</h3>
                        </div>
                        <div class="card-body p-4" style="height: 260px;">
                            <Doughnut v-if="Object.keys(ordersByStatus).length" :data="orderStatusChartData" :options="orderStatusChartOptions" />
                            <p v-else class="text-sm text-slate-400 text-center mt-5">No orders yet.</p>
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
                                            <td class="px-4 py-3 border-0 align-middle"><span class="badge bg-slate-100 text-slate-700">{{ order.order_number }}</span></td>
                                            <td class="py-3 border-0 align-middle font-medium text-slate-700">{{ order.user?.name || 'Walk-in' }}</td>
                                            <td class="py-3 border-0 align-middle font-bold text-indigo-600">{{ formatCurrency(order.total) }}</td>
                                            <td class="py-3 border-0 align-middle">
                                                <span class="badge rounded-pill bg-emerald-100 text-emerald-700 px-2 font-bold text-[10px] text-capitalize">{{ order.status }}</span>
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
