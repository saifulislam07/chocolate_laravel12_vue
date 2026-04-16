<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    purchases: Array
});

const startDate = ref('');
const endDate = ref('');

const filterReport = () => {
    router.get(route('admin.reports.purchases'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Purchase Transaction Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-truck mr-2 text-warning"></i>Purchase Statement</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card shadow-sm border-0 mb-4 d-print-none" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="text-xs font-weight-bold">START DATE</label>
                                <input type="date" v-model="startDate" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="text-xs font-weight-bold">END DATE</label>
                                <input type="date" v-model="endDate" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <button @click="filterReport" class="btn btn-warning px-4 text-white font-weight-bold shadow-sm">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-3 text-right">
                                <button onclick="window.print()" class="btn btn-outline-primary px-4 shadow-sm font-weight-bold">
                                    <i class="fas fa-print mr-1"></i> Print Statement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body p-0">
                        <div class="text-center d-none d-print-block mb-4 pt-3">
                            <h2 class="font-weight-bold mb-0">SWEET CHOCOLATE</h2>
                            <p class="text-muted">Purchase Statement Report</p>
                        </div>

                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Supplier</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-right">Paid</th>
                                    <th class="text-right">Due</th>
                                    <th class="text-center d-print-none">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(p, index) in purchases" :key="p.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ p.purchase_date }}</td>
                                    <td><code>{{ p.reference_no }}</code></td>
                                    <td class="font-weight-bold">{{ p.supplier?.name }}</td>
                                    <td class="text-right font-weight-bold">৳{{ parseFloat(p.total_amount).toFixed(2) }}</td>
                                    <td class="text-right text-success">৳{{ parseFloat(p.paid_amount).toFixed(2) }}</td>
                                    <td class="text-right text-danger">৳{{ parseFloat(p.due_amount).toFixed(2) }}</td>
                                    <td class="text-center d-print-none">
                                        <span class="badge" :class="p.payment_status === 'Paid' ? 'badge-success' : 'badge-warning'">{{ p.payment_status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="purchases.length === 0">
                                    <td colspan="8" class="text-center p-5 text-muted">No purchase records found for the selected period.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="purchases.length > 0">
                                <tr class="bg-light">
                                    <th colspan="4" class="text-right">Period Totals:</th>
                                    <th class="text-right h5 font-weight-bold">৳{{ purchases.reduce((sum, p) => sum + parseFloat(p.total_amount), 0).toFixed(2) }}</th>
                                    <th class="text-right h5 font-weight-bold text-success">৳{{ purchases.reduce((sum, p) => sum + parseFloat(p.paid_amount), 0).toFixed(2) }}</th>
                                    <th class="text-right h5 font-weight-bold text-danger">৳{{ purchases.reduce((sum, p) => sum + parseFloat(p.due_amount), 0).toFixed(2) }}</th>
                                    <th class="d-print-none"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
