<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: Object
});

const startDate = ref(props.stats.start_date);
const endDate = ref(props.stats.end_date);

const updateReport = () => {
    router.get(route('admin.reports.profit-loss'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Profit & Loss Statement" />

    <AdminLayout border="0">
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i>Income Statement</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Date Filter -->
                <div class="card shadow-sm border-0 mb-4 d-print-none" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="text-xs font-weight-bold text-muted">START DATE</label>
                                <input type="date" v-model="startDate" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="text-xs font-weight-bold text-muted">END DATE</label>
                                <input type="date" v-model="endDate" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <button @click="updateReport" class="btn btn-success px-4 mr-2 shadow-sm font-weight-bold">
                                    <i class="fas fa-sync-alt mr-1"></i> Update Data
                                </button>
                                <button onclick="window.print()" class="btn btn-outline-primary px-4 shadow-sm font-weight-bold">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                            <div class="card-header bg-success text-white text-center py-4 border-0">
                                <h3 class="font-weight-bold mb-0">PROFIT & LOSS STATEMENT</h3>
                                <p class="mb-0 opacity-75">Sweet Chocolate ERP System</p>
                                <small class="text-uppercase tracking-wider">Period: {{ stats.start_date }} to {{ stats.end_date }}</small>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-borderless mb-0 h5">
                                    <tbody>
                                        <!-- Revenue -->
                                        <tr class="bg-light border-bottom">
                                            <th class="p-4"><i class="fas fa-plus-circle text-success mr-2"></i>REVENUE / SALES</th>
                                            <th class="p-4 text-right text-success">৳{{ stats.sales.toFixed(2) }}</th>
                                        </tr>
                                        
                                        <!-- Costs -->
                                        <tr>
                                            <td class="p-4 pl-5 text-muted">Total Purchases (COGS)</td>
                                            <td class="p-4 text-right text-danger">- ৳{{ stats.purchases.toFixed(2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="p-4 pl-5 text-muted">Total Operating Expenses</td>
                                            <td class="p-4 text-right text-danger">- ৳{{ stats.expenses.toFixed(2) }}</td>
                                        </tr>

                                        <!-- Summary Row -->
                                        <tr class="bg-light border-top" style="border-top: 2px solid #28a745 !important;">
                                            <th class="p-4 h3 font-weight-bold text-dark text-uppercase">Net Profit/Loss</th>
                                            <th class="p-4 text-right h3 font-weight-bold" :class="stats.net_profit >= 0 ? 'text-success' : 'text-danger'">
                                                ৳{{ stats.net_profit.toFixed(2) }}
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Additional Details Area -->
                                <div class="p-5 text-center bg-white">
                                    <div class="row">
                                        <div class="col-4 border-right">
                                            <p class="text-muted small mb-1 text-uppercase">Sales Growth</p>
                                            <h4 class="font-weight-bold text-primary">-- %</h4>
                                        </div>
                                        <div class="col-4 border-right">
                                            <p class="text-muted small mb-1 text-uppercase">Profit Margin</p>
                                            <h4 class="font-weight-bold text-info">{{ stats.sales > 0 ? ((stats.net_profit / stats.sales) * 100).toFixed(1) : 0 }}%</h4>
                                        </div>
                                        <div class="col-4">
                                            <p class="text-muted small mb-1 text-uppercase">Status</p>
                                            <h4 class="font-weight-bold" :class="stats.net_profit >= 0 ? 'text-success' : 'text-danger'">
                                                {{ stats.net_profit >= 0 ? 'PROFITABLE' : 'LOSS' }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-center py-4 border-top">
                                <p class="text-muted italic small mb-0 font-italic">Generated on {{ new Date().toLocaleString() }} | ERP Authorized Data</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.card { border-radius: 20px; }
.tracking-wider { letter-spacing: 2px; }
@media print {
    .d-print-none { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .bg-success { background-color: #28a745 !important; color: white !important; -webkit-print-color-adjust: exact; }
    .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
}
</style>
