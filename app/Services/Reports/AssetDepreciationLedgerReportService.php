<?php

namespace App\Services\Reports;

use App\Models\JournalEntry;

class AssetDepreciationLedgerReportService
{
    public function generate(int $companyId, array $filters = []): array
    {
        $entries = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('source_type', 'asset_depreciation')
            ->where('status', 'posted')
            ->whereNotNull('asset_id')
            ->with([
                'asset.assetCategory',
                'lines.account',
            ])
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('entry_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('entry_date', '<=', $v))
            ->when($filters['asset_category_id'] ?? null, function ($q, $categoryId) {
                $q->whereHas('asset', fn ($assetQuery) => $assetQuery->where('asset_category_id', $categoryId));
            })
            ->orderBy('asset_id')
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $runningAccumulated = [];

        $rows = $entries->map(function ($entry) use (&$runningAccumulated) {
            $asset = $entry->asset;

            $purchaseAmount = (float) ($asset?->net_purchase_amount ?? 0);
            $openingAccumulated = (float) ($asset?->opening_accumulated_depreciation ?? 0);

            $depreciationAmount = (float) $entry->lines->sum('debit');

            if (! isset($runningAccumulated[$asset->id])) {
                $runningAccumulated[$asset->id] = $openingAccumulated;
            }

            $runningAccumulated[$asset->id] += $depreciationAmount;

            $accumulatedDepreciation = $runningAccumulated[$asset->id];
            $valueAfterDepreciation = max($purchaseAmount - $accumulatedDepreciation, 0);

            return [
                'asset' => $asset?->series ?? $asset?->id,
                'asset_name_ar' => $asset?->asset_name_ar,
                'asset_name_en' => $asset?->asset_name_en,

                'depreciation_date' => $entry->entry_date?->format('Y-m-d'),
                'purchase_amount' => round($purchaseAmount, 2),
                'opening_accumulated_depreciation' => round($openingAccumulated, 2),
                'depreciation_amount' => round($depreciationAmount, 2),
                'accumulated_depreciation' => round($accumulatedDepreciation, 2),
                'value_after_depreciation' => round($valueAfterDepreciation, 2),

                'depreciation_entry' => $entry->entry_number,

                'asset_category_ar' => $asset?->assetCategory?->name_ar,
                'asset_category_en' => $asset?->assetCategory?->name_en,
                'purchase_date' => $asset?->purchase_date?->format('Y-m-d'),
            ];
        });

        return [
            'rows' => $rows,
            'totals' => [
                'purchase_amount' => round($rows->sum('purchase_amount'), 2),
                'opening_accumulated_depreciation' => round($rows->sum('opening_accumulated_depreciation'), 2),
                'depreciation_amount' => round($rows->sum('depreciation_amount'), 2),
                'accumulated_depreciation' => round($rows->sum('accumulated_depreciation'), 2),
                'value_after_depreciation' => round($rows->sum('value_after_depreciation'), 2),
            ],
        ];
    }
}