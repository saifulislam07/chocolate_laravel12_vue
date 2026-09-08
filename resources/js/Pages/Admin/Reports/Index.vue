<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    summary: Object
});

// The summary values arrive as raw floats, so each card printed a different
// number of decimals. One formatter keeps every figure the same shape.
const money = (value) => Number(value || 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const count = (value) => Number(value || 0).toLocaleString('en-BD');
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
                <!--
                    An auto-fitting grid rather than fixed columns: five cards in
                    a four-column row left the last one stranded on a line of its
                    own, and they stretch to a shared height so the row no longer
                    steps up and down with the length of each caption.
                -->
                <div class="summary-grid">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3>{{ count(summary.total_stock) }}</h3>
                            <p>Items in Stock</p>
                        </div>
                        <div class="icon"><i class="fas fa-cubes"></i></div>
                        <Link :href="route('admin.reports.stock')" class="small-box-footer">View Stock Report <i class="fas fa-arrow-circle-right"></i></Link>
                    </div>

                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3>৳{{ money(summary.net_sales) }}</h3>
                            <p>Net Revenue</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        <Link :href="route('admin.reports.profit-loss')" class="small-box-footer">View Profit/Loss <i class="fas fa-arrow-circle-right"></i></Link>
                    </div>

                    <div class="small-box shadow-sm" style="background-color: #e11d48;">
                        <div class="inner">
                            <h3 class="text-white">৳{{ money(summary.total_refunds) }}</h3>
                            <p class="text-white">Refunds Issued</p>
                        </div>
                        <div class="icon"><i class="fas fa-rotate-left"></i></div>
                        <Link :href="route('admin.refunds.index')" class="small-box-footer">View Refunds <i class="fas fa-arrow-circle-right text-white"></i></Link>
                    </div>

                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3 class="text-white">৳{{ money(summary.total_purchases) }}</h3>
                            <p class="text-white">Purchases Cost</p>
                        </div>
                        <div class="icon"><i class="fas fa-truck-loading"></i></div>
                        <Link :href="route('admin.reports.purchases')" class="small-box-footer">View Purchase Report <i class="fas fa-arrow-circle-right text-white"></i></Link>
                    </div>

                    <div class="small-box bg-danger shadow-sm">
                        <div class="inner">
                            <h3>৳{{ money(summary.total_expenses) }}</h3>
                            <p>Total Expenses</p>
                        </div>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        <Link :href="route('admin.reports.expenses')" class="small-box-footer">View Expense Report <i class="fas fa-arrow-circle-right"></i></Link>
                    </div>
                </div>

                <!--
                    The figures behind Net Revenue. They were crammed under that
                    card's caption, which made it taller than every other card in
                    the row.
                -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body py-3">
                        <div class="row text-center">
                            <div class="col-md-4 col-12 py-2">
                                <div class="text-muted text-uppercase text-xs font-weight-bold">Delivered Revenue</div>
                                <div class="h5 font-weight-bold text-success mb-0">৳{{ money(summary.total_sales) }}</div>
                                <div class="text-muted text-xs">Shipping already excluded</div>
                            </div>
                            <div class="col-md-4 col-12 py-2 breakdown-divider">
                                <div class="text-muted text-uppercase text-xs font-weight-bold">Returns</div>
                                <div class="h5 font-weight-bold text-danger mb-0">− ৳{{ money(summary.total_returns) }}</div>
                                <div class="text-muted text-xs">Taken off the revenue above</div>
                            </div>
                            <div class="col-md-4 col-12 py-2 breakdown-divider">
                                <div class="text-muted text-uppercase text-xs font-weight-bold">Shipping Collected</div>
                                <div class="h5 font-weight-bold text-muted mb-0">৳{{ money(summary.shipping_collected) }}</div>
                                <div class="text-muted text-xs">Passed on to the courier</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Menu Grid -->
                <div class="row mt-4">
                    <div class="col-xl-3 col-md-6 mb-4">
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

                    <div class="col-xl-3 col-md-6 mb-4">
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
                                     <Link :href="route('admin.returns.index')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-rotate-left mr-3 text-rose-500"></i> Sales Returns
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                     <Link :href="route('admin.refunds.index')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fas fa-money-bill-transfer mr-3 text-amber-500"></i> Return Refunds
                                         <span class="float-right text-muted"><i class="fas fa-chevron-right text-xs"></i></span>
                                     </Link>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
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

                    <div class="col-xl-3 col-md-6 mb-4">
                         <div class="card card-outline card-info shadow-sm h-100">
                             <div class="card-header border-0">
                                 <h3 class="card-title font-weight-bold">Marketing Reports</h3>
                             </div>
                             <div class="card-body px-0 py-2">
                                 <div class="list-group list-group-flush">
                                     <Link :href="route('admin.reports.meta-campaigns')" class="list-group-item list-group-item-action border-0 px-4 py-3">
                                         <i class="fab fa-facebook mr-3 text-primary"></i> Meta Campaigns & Boosting
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

/* Cards size themselves to the row rather than to a fixed column count, so a
   fifth card never ends up alone on a line of its own. */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.summary-grid .small-box {
    display: flex;
    flex-direction: column;
    height: 100%;
    /* AdminLTE spaces small boxes with a bottom margin; the grid gap does it
       here, and both together left an uneven gutter under the first row. */
    margin-bottom: 0;
}

.summary-grid .small-box .inner {
    flex: 1 1 auto;
}

/* Keeps every card's footer on its bottom edge once the boxes are stretched. */
.summary-grid .small-box .small-box-footer {
    margin-top: auto;
}

/* The figures are wider than AdminLTE's default heading, and a wrapped amount
   was what made neighbouring cards different heights. */
.summary-grid .small-box .inner h3 {
    font-size: 1.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (min-width: 768px) {
    .breakdown-divider {
        border-left: 1px solid #eef2f7;
    }
}
</style>
