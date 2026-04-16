<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    summary: Object
});
</script>

<template>
    <Head title="Reports Dashboard" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-chart-bar mr-2 text-primary"></i>Analytics & Reports</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info shadow-sm">
                            <div class="inner">
                                <h3>{{ summary.total_stock }}</h3>
                                <p>Items in Stock</p>
                            </div>
                            <div class="icon"><i class="fas fa-cubes"></i></div>
                            <Link :href="route('admin.reports.stock')" class="small-box-footer">View Stock Report <i class="fas fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success shadow-sm">
                            <div class="inner">
                                <h3>৳{{ summary.total_sales }}</h3>
                                <p>Total Revenue</p>
                            </div>
                            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                            <Link :href="route('admin.reports.profit-loss')" class="small-box-footer">View Profit/Loss <i class="fas fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning shadow-sm">
                            <div class="inner">
                                <h3 class="text-white">৳{{ summary.total_purchases }}</h3>
                                <p class="text-white">Purchases Cost</p>
                            </div>
                            <div class="icon"><i class="fas fa-truck-loading"></i></div>
                            <Link :href="route('admin.reports.purchases')" class="small-box-footer">View Purchase Report <i class="fas fa-arrow-circle-right text-white"></i></Link>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger shadow-sm">
                            <div class="inner">
                                <h3>৳{{ summary.total_expenses }}</h3>
                                <p>Total Expenses</p>
                            </div>
                            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                            <Link :href="route('admin.reports.expenses')" class="small-box-footer">View Expense Report <i class="fas fa-arrow-circle-right"></i></Link>
                        </div>
                    </div>
                </div>

                <!-- Report Menu Grid -->
                <div class="row mt-4">
                    <div class="col-md-4">
                         <div class="card card-outline card-primary shadow-sm h-100">
                             <div class="card-header border-0">
                                 <h3 class="card-title font-weight-bold">Inventory Reports</h3>
                             </div>
                             <div class="card-body px-0 py-2">
                                 <div class="list-group list-group-flush">
                                     <Link :href="route('admin.reports.stock')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-box-open mr-3 text-primary"></i> Current Stock Summary
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                     <Link :href="route('admin.reports.products')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-barcode mr-3 text-info"></i> Product Movement Report
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <div class="col-md-4">
                         <div class="card card-outline card-success shadow-sm h-100">
                             <div class="card-header border-0">
                                 <h3 class="card-title font-weight-bold">Financial Reports</h3>
                             </div>
                             <div class="card-body px-0 py-2">
                                 <div class="list-group list-group-flush">
                                     <Link :href="route('admin.reports.profit-loss')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-file-invoice-dollar mr-3 text-success"></i> Income Statement (Profit/Loss)
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                     <Link :href="route('admin.reports.expenses')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-wallet mr-3 text-danger"></i> Expense Statement
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <div class="col-md-4">
                         <div class="card card-outline card-warning shadow-sm h-100">
                             <div class="card-header border-0">
                                 <h3 class="card-title font-weight-bold">Procurement Reports</h3>
                             </div>
                             <div class="card-body px-0 py-2">
                                 <div class="list-group list-group-flush">
                                     <Link :href="route('admin.reports.purchases')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-truck mr-3 text-warning"></i> Purchase Summary
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                     <Link :href="route('admin.reports.suppliers')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-user-tie mr-3 text-secondary"></i> Supplier Outstanding Report
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                 </div>
                             </div>
                         </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-none border-0 bg-light">
                            <div class="card-body text-center p-5">
                                <i class="fas fa-shield-alt fa-3x text-muted opacity-25 mb-3"></i>
                                <h5 class="text-muted font-weight-bold">Secure Business Data</h5>
                                <p class="text-muted">All reports are generated in real-time based on your transaction history.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.small-box { border-radius: 15px; overflow: hidden; }
.card { border-radius: 15px; }
.list-group-item:hover { background-color: #f8f9fa; }
</style>
