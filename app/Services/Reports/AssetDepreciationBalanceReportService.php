<?php

namespace App\Services\Reports;

use App\Models\Asset;
use App\Models\AssetSale;
use App\Models\AssetScrapping;
use App\Models\AssetCapitalization;
use App\Models\AssetValueAdjustment;
use App\Models\JournalEntry;
use Illuminate\Support\Collection;

class AssetDepreciationBalanceReportService
{
    public function generate(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfYear()->toDateString();
        $dateTo = $filters['date_to'] ?? now()->endOfYear()->toDateString();

        $assets = Asset::query()
            ->where('company_id', $companyId)
            ->with('assetCategory')
            ->when($filters['asset_category_id'] ?? null, fn ($q, $v) => $q->where('asset_category_id', $v))
            ->get();

        $rows = $assets
            ->groupBy('asset_category_id')
            ->map(function (Collection $categoryAssets) use ($companyId, $dateFrom, $dateTo) {
                $firstAsset = $categoryAssets->first();
                $categoryId = $firstAsset->asset_category_id;

                $valueStart = $this->valueAsOnStart($categoryAssets, $dateFrom);
                $newPurchase = $this->valueOfNewPurchase($categoryAssets, $dateFrom, $dateTo);
                $soldAsset = $this->valueOfSoldAsset($companyId, $categoryAssets, $dateFrom, $dateTo);
                $scrappedAsset = $this->valueOfScrappedAsset($companyId, $categoryAssets, $dateFrom, $dateTo);
                $newCapitalization = $this->valueOfNewCapitalization($companyId, $categoryAssets, $dateFrom, $dateTo);
                $valueAdjustments = $this->valueOfAdjustments($companyId, $categoryAssets, $dateFrom, $dateTo);

                $valueEnd = $valueStart
                    + $newPurchase
                    + $newCapitalization
                    + $valueAdjustments
                    - $soldAsset
                    - $scrappedAsset;

                $accDepStart = $this->accumulatedDepreciationStart($categoryAssets, $dateFrom);
                $depreciationDuring = $this->depreciationDuringPeriod($companyId, $categoryAssets, $dateFrom, $dateTo);
                $depreciationEliminatedDisposal = $this->depreciationEliminatedDueToDisposal($companyId, $categoryAssets, $dateFrom, $dateTo);
                $depreciationEliminatedReversal = $this->depreciationEliminatedViaReversal($companyId, $categoryAssets, $dateFrom, $dateTo);

                $accDepEnd = $accDepStart
                    + $depreciationDuring
                    - $depreciationEliminatedDisposal
                    - $depreciationEliminatedReversal;

                return [
                    'asset_category_id' => $categoryId,
                    'asset_category_ar' => $firstAsset->assetCategory?->name_ar,
                    'asset_category_en' => $firstAsset->assetCategory?->name_en,

                    'value_start' => round($valueStart, 2),
                    'new_purchase' => round($newPurchase, 2),
                    'sold_asset' => round($soldAsset, 2),
                    'scrapped_asset' => round($scrappedAsset, 2),
                    'new_capitalization' => round($newCapitalization, 2),
                    'value_end' => round(max($valueEnd, 0), 2),

                    'accumulated_depreciation_start' => round($accDepStart, 2),
                    'depreciation_during_period' => round($depreciationDuring, 2),
                    'depreciation_eliminated_disposal' => round($depreciationEliminatedDisposal, 2),
                    'depreciation_eliminated_reversal' => round($depreciationEliminatedReversal, 2),
                    'accumulated_depreciation_end' => round(max($accDepEnd, 0), 2),

                    'net_asset_value_start' => round(max($valueStart - $accDepStart, 0), 2),
                    'net_asset_value_end' => round(max($valueEnd - $accDepEnd, 0), 2),
                ];
            })
            ->values();

        return [
            'rows' => $rows,
            'totals' => $this->totals($rows),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function assetIds(Collection $assets): array
    {
        return $assets->pluck('id')->values()->toArray();
    }

    private function valueAsOnStart(Collection $assets, string $dateFrom): float
    {
        return (float) $assets
            ->filter(fn ($asset) => $asset->purchase_date?->format('Y-m-d') < $dateFrom)
            ->sum('net_purchase_amount');
    }

    private function valueOfNewPurchase(Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) $assets
            ->filter(fn ($asset) =>
                $asset->purchase_date?->format('Y-m-d') >= $dateFrom &&
                $asset->purchase_date?->format('Y-m-d') <= $dateTo
            )
            ->sum('net_purchase_amount');
    }

    private function valueOfSoldAsset(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) AssetSale::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('posting_date', [$dateFrom, $dateTo])
            ->sum('sold_asset_cost');
    }

    private function valueOfScrappedAsset(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) AssetScrapping::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('scrap_date', [$dateFrom, $dateTo])
            ->sum('asset_cost');
    }

    private function valueOfNewCapitalization(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) AssetCapitalization::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('target_asset_id', $this->assetIds($assets))
            ->whereBetween('posting_date', [$dateFrom, $dateTo])
            ->sum('consumed_asset_total_value');
    }

    private function valueOfAdjustments(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) AssetValueAdjustment::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('posting_date', [$dateFrom, $dateTo])
            ->sum('difference_amount');
    }

    private function accumulatedDepreciationStart(Collection $assets, string $dateFrom): float
    {
        return (float) $assets
            ->filter(fn ($asset) => $asset->purchase_date?->format('Y-m-d') < $dateFrom)
            ->sum('opening_accumulated_depreciation');
    }

    private function depreciationDuringPeriod(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('source_type', 'asset_depreciation')
            ->where('status', 'posted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->with('lines')
            ->get()
            ->sum(fn ($entry) => (float) $entry->lines->sum('debit'));
    }

    private function depreciationEliminatedDueToDisposal(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        $sold = (float) AssetSale::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('posting_date', [$dateFrom, $dateTo])
            ->sum('sold_accumulated_depreciation');

        $scrapped = (float) AssetScrapping::query()
            ->where('company_id', $companyId)
            ->where('status', 'submitted')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('scrap_date', [$dateFrom, $dateTo])
            ->sum('accumulated_depreciation_amount');

        return $sold + $scrapped;
    }

    private function depreciationEliminatedViaReversal(int $companyId, Collection $assets, string $dateFrom, string $dateTo): float
    {
        return (float) JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('source_type', 'asset_depreciation')
            ->where('status', 'cancelled')
            ->whereIn('asset_id', $this->assetIds($assets))
            ->whereBetween('cancelled_at', [$dateFrom, $dateTo])
            ->with('lines')
            ->get()
            ->sum(fn ($entry) => (float) $entry->lines->sum('debit'));
    }

    private function totals(Collection $rows): array
    {
        return [
            'value_start' => round($rows->sum('value_start'), 2),
            'new_purchase' => round($rows->sum('new_purchase'), 2),
            'sold_asset' => round($rows->sum('sold_asset'), 2),
            'scrapped_asset' => round($rows->sum('scrapped_asset'), 2),
            'new_capitalization' => round($rows->sum('new_capitalization'), 2),
            'value_end' => round($rows->sum('value_end'), 2),

            'accumulated_depreciation_start' => round($rows->sum('accumulated_depreciation_start'), 2),
            'depreciation_during_period' => round($rows->sum('depreciation_during_period'), 2),
            'depreciation_eliminated_disposal' => round($rows->sum('depreciation_eliminated_disposal'), 2),
            'depreciation_eliminated_reversal' => round($rows->sum('depreciation_eliminated_reversal'), 2),
            'accumulated_depreciation_end' => round($rows->sum('accumulated_depreciation_end'), 2),

            'net_asset_value_start' => round($rows->sum('net_asset_value_start'), 2),
            'net_asset_value_end' => round($rows->sum('net_asset_value_end'), 2),
        ];
    }
}