<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    expenses: Array
});

const startDate = ref('');
const endDate = ref('');

const filterReport = () => {
    router.get(route('admin.reports.expenses'), {
        start_date: startDate.value,
        end_date: endDate.value
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Expense Statement Report" />

    <AdminLayout>
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-money-bill-wave mr-2 text-danger"></i>Expense Statement</h1>
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
                                <button @click="filterReport" class="btn btn-danger px-4 font-weight-bold shadow-sm">
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
                            <h2 class="font-weight-bold mb-0 text-uppercase">Sweet Chocolate</h2>
                            <p class="text-muted">Business Expense Statement Report</p>
                        </div>

                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px">#</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(e, index) in expenses" :key="e.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ e.expense_date }}</td>
                                    <td><span class="badge badge-secondary">{{ e.category?.name }}</span></td>
                                    <td><code>{{ e.reference_no || 'N/A' }}</code></td>
                                    <td class="text-sm">{{ e.description }}</td>
                                    <td class="text-right font-weight-bold text-danger">৳{{ parseFloat(e.amount).toFixed(2) }}</td>
                                </tr>
                                <tr v-if="expenses.length === 0">
                                    <td colspan="6" class="text-center p-5 text-muted">No expense records found for the selected period.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="expenses.length > 0">
                                <tr class="bg-light">
                                    <th colspan="5" class="text-right h5 font-weight-bold">Total Expenditures:</th>
                                    <th class="text-right h5 font-weight-bold text-danger">৳{{ expenses.reduce((sum, e) => sum + parseFloat(e.amount), 0).toFixed(2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
