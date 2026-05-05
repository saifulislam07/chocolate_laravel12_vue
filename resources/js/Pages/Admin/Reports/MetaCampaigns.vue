<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    datePreset: String,
    report: Object,
});

const presets = [
    { value: 'today', label: 'Today' },
    { value: 'yesterday', label: 'Yesterday' },
    { value: 'last_7d', label: 'Last 7 Days' },
    { value: 'last_30d', label: 'Last 30 Days' },
    { value: 'this_month', label: 'This Month' },
];

function changePreset(event) {
    router.get(route('admin.reports.meta-campaigns'), { date_preset: event.target.value }, {
        preserveScroll: true,
        preserveState: true,
    });
}

const money = (amount) => Number(amount || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const number = (value) => Number(value || 0).toLocaleString();
</script>

<template>
    <Head title="Meta Campaign Reports" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fab fa-facebook mr-2 text-primary"></i>Meta Campaigns</h1>
                        <p class="text-muted text-sm mb-0">Check Facebook campaign and boosted post performance from Meta Ads.</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <select class="form-control mr-2" style="width: 170px;" :value="datePreset" @change="changePreset">
                            <option v-for="preset in presets" :key="preset.value" :value="preset.value">{{ preset.label }}</option>
                        </select>
                        <button class="btn btn-primary" type="button" @click="router.reload({ only: ['report'] })">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div v-if="!report.configured" class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <i class="fab fa-facebook fa-3x text-primary mb-3"></i>
                        <h5 class="font-weight-bold">Meta Ads is not connected yet</h5>
                        <p class="text-muted mb-4">Add your Ad Account ID and access token from Settings to load campaign and boosting reports.</p>
                        <Link :href="route('admin.settings.index')" class="btn btn-primary">
                            <i class="fas fa-cog mr-1"></i> Open Meta Ads Settings
                        </Link>
                    </div>
                </div>

                <div v-else-if="report.error" class="alert alert-danger">
                    <strong>Meta API error:</strong> {{ report.error }}
                </div>

                <template v-else>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <span>Spend</span>
                                <strong>{{ money(report.summary.spend) }}</strong>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <span>Reach</span>
                                <strong>{{ number(report.summary.reach) }}</strong>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <span>Clicks</span>
                                <strong>{{ number(report.summary.clicks) }}</strong>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="metric-card">
                                <span>Purchases</span>
                                <strong>{{ number(report.summary.purchases) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold mb-0">Campaign Performance</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Campaign</th>
                                            <th class="text-right">Spend</th>
                                            <th class="text-right">Reach</th>
                                            <th class="text-right">Impressions</th>
                                            <th class="text-right">Clicks</th>
                                            <th class="text-right">CTR</th>
                                            <th class="text-right">CPC</th>
                                            <th class="text-right">ATC</th>
                                            <th class="text-right">Purchases</th>
                                            <th class="text-right">ROAS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="campaign in report.campaigns" :key="campaign.campaign_id || campaign.campaign_name">
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ campaign.campaign_name }}</div>
                                                <div class="text-xs text-muted">{{ campaign.objective || 'Campaign' }}</div>
                                            </td>
                                            <td class="text-right">{{ money(campaign.spend) }}</td>
                                            <td class="text-right">{{ number(campaign.reach) }}</td>
                                            <td class="text-right">{{ number(campaign.impressions) }}</td>
                                            <td class="text-right">{{ number(campaign.clicks) }}</td>
                                            <td class="text-right">{{ money(campaign.ctr) }}%</td>
                                            <td class="text-right">{{ money(campaign.cpc) }}</td>
                                            <td class="text-right">{{ number(campaign.add_to_cart) }}</td>
                                            <td class="text-right">{{ number(campaign.purchases) }}</td>
                                            <td class="text-right">{{ money(campaign.purchase_roas) }}</td>
                                        </tr>
                                        <tr v-if="report.campaigns.length === 0">
                                            <td colspan="10" class="text-center text-muted p-5">No Meta campaign data found for this date range.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.metric-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    padding: 1.25rem;
}

.metric-card span {
    color: #64748b;
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.metric-card strong {
    color: #0f172a;
    display: block;
    font-size: 1.55rem;
    margin-top: 0.5rem;
}
</style>
