<?php

namespace App\Services;

use App\Models\WebSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MetaAdsReportService
{
    public function report(?WebSetting $settings, string $datePreset = 'last_7d'): array
    {
        if (! $settings?->meta_ads_enabled || ! $settings->meta_ads_account_id || ! $settings->meta_ads_access_token) {
            return [
                'configured' => false,
                'error' => null,
                'campaigns' => [],
                'summary' => $this->emptySummary(),
            ];
        }

        $accountId = $this->normalizeAccountId($settings->meta_ads_account_id);
        $version = $settings->meta_ads_api_version ?: 'v24.0';

        $response = Http::timeout(20)
            ->acceptJson()
            ->get("https://graph.facebook.com/{$version}/{$accountId}/insights", [
                'access_token' => $settings->meta_ads_access_token,
                'level' => 'campaign',
                'date_preset' => $datePreset,
                'fields' => implode(',', [
                    'campaign_id',
                    'campaign_name',
                    'objective',
                    'impressions',
                    'reach',
                    'clicks',
                    'spend',
                    'ctr',
                    'cpc',
                    'cpm',
                    'actions',
                    'purchase_roas',
                    'date_start',
                    'date_stop',
                ]),
                'limit' => 100,
            ]);

        if ($response->failed()) {
            return [
                'configured' => true,
                'error' => $response->json('error.message') ?: 'Unable to load Meta campaign report.',
                'campaigns' => [],
                'summary' => $this->emptySummary(),
            ];
        }

        $campaigns = collect($response->json('data', []))
            ->map(fn (array $row): array => $this->mapCampaign($row))
            ->values()
            ->all();

        return [
            'configured' => true,
            'error' => null,
            'campaigns' => $campaigns,
            'summary' => $this->summary($campaigns),
        ];
    }

    private function normalizeAccountId(string $accountId): string
    {
        return Str::startsWith($accountId, 'act_') ? $accountId : 'act_' . $accountId;
    }

    private function mapCampaign(array $row): array
    {
        $actions = collect($row['actions'] ?? [])->pluck('value', 'action_type');

        return [
            'campaign_id' => $row['campaign_id'] ?? null,
            'campaign_name' => $row['campaign_name'] ?? 'Untitled campaign',
            'objective' => $row['objective'] ?? null,
            'impressions' => (int) ($row['impressions'] ?? 0),
            'reach' => (int) ($row['reach'] ?? 0),
            'clicks' => (int) ($row['clicks'] ?? 0),
            'spend' => (float) ($row['spend'] ?? 0),
            'ctr' => (float) ($row['ctr'] ?? 0),
            'cpc' => (float) ($row['cpc'] ?? 0),
            'cpm' => (float) ($row['cpm'] ?? 0),
            'purchases' => (int) ($actions['purchase'] ?? $actions['omni_purchase'] ?? 0),
            'add_to_cart' => (int) ($actions['add_to_cart'] ?? $actions['omni_add_to_cart'] ?? 0),
            'leads' => (int) ($actions['lead'] ?? $actions['onsite_conversion.lead_grouped'] ?? 0),
            'purchase_roas' => (float) data_get($row, 'purchase_roas.0.value', 0),
            'date_start' => $row['date_start'] ?? null,
            'date_stop' => $row['date_stop'] ?? null,
        ];
    }

    private function summary(array $campaigns): array
    {
        return [
            'campaigns_count' => count($campaigns),
            'spend' => array_sum(array_column($campaigns, 'spend')),
            'impressions' => array_sum(array_column($campaigns, 'impressions')),
            'reach' => array_sum(array_column($campaigns, 'reach')),
            'clicks' => array_sum(array_column($campaigns, 'clicks')),
            'purchases' => array_sum(array_column($campaigns, 'purchases')),
            'add_to_cart' => array_sum(array_column($campaigns, 'add_to_cart')),
            'leads' => array_sum(array_column($campaigns, 'leads')),
        ];
    }

    private function emptySummary(): array
    {
        return [
            'campaigns_count' => 0,
            'spend' => 0,
            'impressions' => 0,
            'reach' => 0,
            'clicks' => 0,
            'purchases' => 0,
            'add_to_cart' => 0,
            'leads' => 0,
        ];
    }
}
