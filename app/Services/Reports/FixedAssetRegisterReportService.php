<?php

namespace App\Services\Reports;

use App\Models\Asset;
use App\Models\JournalEntry;
use Illuminate\Support\Collection;

class FixedAssetRegisterReportService
{
    public function generate(int $companyId, array $filters = []): array
    {
        $assets = Asset::query()
            ->where('company_id', $companyId)
            ->with(['assetCategory', 'location'])
            ->when($filters['asset_category_id'] ?? null, function ($query, $categoryId) {
                $query->where('asset_category_id', $categoryId);
            })
            ->orderBy('id')
            ->get();

        $rows = $assets->map(function (Asset $asset) {
            $netPurchaseAmount = (float) ($asset->net_purchase_amount ?? 0);
            $accumulatedDepreciation = (float) ($asset->opening_accumulated_depreciation ?? 0);

            $assetValue = max($netPurchaseAmount - $accumulatedDepreciation, 0);

            $depreciationAmount = $this->latestDepreciationAmount(
                (int) $asset->company_id,
                (int) $asset->id
            );

            return [
                'asset_id' => $asset->series ?? $asset->id,
                'asset_name' => $asset->asset_name_en ?? $asset->asset_name_ar,
                'asset_category' => $asset->assetCategory?->name_en ?? $asset->assetCategory?->name_ar,
                'purchase_date' => $asset->purchase_date?->format('Y-m-d'),
                'available_for_use_date' => $asset->available_for_use_date?->format('Y-m-d'),
                'net_purchase_amount' => round($netPurchaseAmount, 2),
                'asset_value' => round($assetValue, 2),
                'opening_accumulated_depreciation' => round($accumulatedDepreciation, 2),
                'depreciation_amount' => round($depreciationAmount, 2),
                'location' => $asset->location?->name_en ?? $asset->location?->name_ar,
            ];
        });

        return [
            'rows' => $rows,
            'totals' => $this->calculateTotals($rows),
            'filters' => $filters,
        ];
    }

    private function latestDepreciationAmount(int $companyId, int $assetId): float
    {
        $entry = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('asset_id', $assetId)
            ->where('source_type', 'asset_depreciation')
            ->where('status', 'posted')
            ->with('lines')
            ->latest('entry_date')
            ->latest('id')
            ->first();

        if (! $entry) {
            return 0;
        }

        return (float) $entry->lines->sum('debit');
    }

    private function calculateTotals(Collection $rows): array
    {
        return [
            'net_purchase_amount' => round($rows->sum('net_purchase_amount'), 2),
            'asset_value' => round($rows->sum('asset_value'), 2),
            'opening_accumulated_depreciation' => round($rows->sum('opening_accumulated_depreciation'), 2),
            'depreciation_amount' => round($rows->sum('depreciation_amount'), 2),
        ];
    }
}