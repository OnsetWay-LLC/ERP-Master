<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\AssetItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return Asset::query()
            ->where('company_id', $companyId)
            ->with(['assetItem', 'assetCategory', 'location'])
            ->when($filters['asset_item_id'] ?? null, fn ($q, $v) => $q->where('asset_item_id', $v))
            ->when($filters['asset_category_id'] ?? null, fn ($q, $v) => $q->where('asset_category_id', $v))
            ->when($filters['location_id'] ?? null, fn ($q, $v) => $q->where('location_id', $v))
            ->when($filters['asset_type'] ?? null, fn ($q, $v) => $q->where('asset_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest('id')
            ->get();
    }

   public function create(array $data, int $companyId, ?int $createdBy = null): Asset
{
   
    return DB::transaction(function () use ($data, $companyId, $createdBy) {
        $assetItem = $this->getValidAssetItem($companyId, (int) $data['asset_item_id']);

        $data['company_id'] = $companyId;
        $data['asset_category_id'] = $assetItem->asset_category_id;
        $data['asset_name_ar'] = $assetItem->name_ar;
        $data['asset_name_en'] = $assetItem->name_en;
        $data['series'] = $this->generateSeries($companyId);
        $data['created_by'] = $createdBy;
        $data['status'] = 'draft';
        $data['salvage_value'] = $data['salvage_value'] ?? 0;
        $data['opening_accumulated_depreciation'] = $data['opening_accumulated_depreciation'] ?? 0;
        $data['opening_number_of_booked_depreciations'] = $data['opening_number_of_booked_depreciations'] ?? 0;

        if ($data['asset_type'] === 'existing_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
        }

        if ($data['asset_type'] === 'composite_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
            $data['net_purchase_amount'] = 0;
        }

        if ($data['asset_type'] === 'composite_component') {
            $invoiceData = $this->resolvePurchaseInvoiceData(
                $companyId,
                (int) $data['purchase_invoice_id'],
                (int) $assetItem->item_id
            );

            $data['purchase_date'] = $invoiceData['purchase_date'];
            $data['net_purchase_amount'] = $invoiceData['net_purchase_amount'];
            $data['purchase_receipt_id'] = $invoiceData['purchase_receipt_id'];
        }
$transactionDate = $data['purchase_date']
    ?? $data['available_for_use_date']
    ?? now()->toDateString();

app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $transactionDate,
        'create'
    );
        if ($data['available_for_use_date'] < $data['purchase_date']) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        return Asset::create($data)->load([
            'assetItem',
            'assetCategory',
            'location',
        ]);
    });
}

public function update(Asset $asset, array $data): Asset
{
    
    return DB::transaction(function () use ($asset, $data) {
        if ($asset->status === 'submitted') {
            throw new InvalidArgumentException('Submitted asset cannot be updated.');
        }
$transactionDate = $data['purchase_date']
    ?? $data['available_for_use_date']
    ?? $asset->purchase_date?->format('Y-m-d')
    ?? $asset->available_for_use_date?->format('Y-m-d');

app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $asset->company_id,
        $transactionDate,
        'update'
    );
        $assetItem = isset($data['asset_item_id'])
            ? $this->getValidAssetItem($asset->company_id, (int) $data['asset_item_id'])
            : $asset->assetItem;

        if (isset($data['asset_item_id'])) {
            $data['asset_category_id'] = $assetItem->asset_category_id;
            $data['asset_name_ar'] = $assetItem->name_ar;
            $data['asset_name_en'] = $assetItem->name_en;
        }

        $assetType = $data['asset_type'] ?? $asset->asset_type;

        if ($assetType === 'existing_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
        }

        if ($assetType === 'composite_asset') {
            $data['purchase_invoice_id'] = null;
            $data['purchase_receipt_id'] = null;
            unset($data['net_purchase_amount']);
        }

        if ($assetType === 'composite_component') {
            $purchaseInvoiceId = $data['purchase_invoice_id'] ?? $asset->purchase_invoice_id;

            if (! $purchaseInvoiceId) {
                throw new InvalidArgumentException('Purchase invoice is required for composite component assets.');
            }

            $invoiceData = $this->resolvePurchaseInvoiceData(
                $asset->company_id,
                (int) $purchaseInvoiceId,
                (int) $assetItem->item_id
            );

            $data['purchase_invoice_id'] = $purchaseInvoiceId;
            $data['purchase_date'] = $invoiceData['purchase_date'];
            $data['net_purchase_amount'] = $invoiceData['net_purchase_amount'];
            $data['purchase_receipt_id'] = $invoiceData['purchase_receipt_id'];
        }

        $purchaseDate = $data['purchase_date'] ?? $asset->purchase_date?->format('Y-m-d');
        $availableDate = $data['available_for_use_date'] ?? $asset->available_for_use_date?->format('Y-m-d');

        if ($availableDate < $purchaseDate) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        $asset->update($data);

        return $asset->fresh([
            'assetItem',
            'assetCategory',
            'location',
        ]);
    });
}

public function submit(Asset $asset): Asset
{
    $transactionDate = $asset->purchase_date?->format('Y-m-d')
    ?? $asset->available_for_use_date?->format('Y-m-d')
    ?? now()->toDateString();

app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $asset->company_id,
        $transactionDate,
        'create'
    );
    return DB::transaction(function () use ($asset) {
        if ($asset->status === 'submitted') {
            throw new InvalidArgumentException('Asset is already submitted.');
        }

        if ($asset->available_for_use_date < $asset->purchase_date) {
            throw new InvalidArgumentException('Available for use date must be greater than or equal to purchase date.');
        }

        if ($asset->asset_type === 'composite_asset' && (float) $asset->net_purchase_amount <= 0) {
            throw new InvalidArgumentException('Composite asset must be capitalized before submit.');
        }

        if ($asset->asset_type === 'composite_component' && ! $asset->purchase_invoice_id) {
            throw new InvalidArgumentException('Purchase invoice is required for composite component assets.');
        }
        

        $asset->update([
            'status' => 'submitted',
        ]);

        return $asset->fresh([
            'assetItem',
            'assetCategory',
            'location',
        ]);
    });
}
private function resolvePurchaseInvoiceData(int $companyId, int $purchaseInvoiceId, int $itemId): array
{
    $invoice = DB::table('purchase_invoices')
        ->where('company_id', $companyId)
        ->where('id', $purchaseInvoiceId)
        ->where('status', 'submitted')
        ->first();

    if (! $invoice) {
        throw new InvalidArgumentException('Invalid submitted purchase invoice selected.');
    }

    $itemLine = DB::table('purchase_invoice_items')
        ->where('purchase_invoice_id', $purchaseInvoiceId)
        ->where('item_id', $itemId)
        ->first();

    if (! $itemLine) {
        throw new InvalidArgumentException('Selected purchase invoice does not contain the selected item code.');
    }

    return [
        'purchase_date' => $invoice->posting_date,
        'net_purchase_amount' => (float) $itemLine->amount,
        'purchase_receipt_id' => $invoice->purchase_receipt_id ?? null,
    ];
}
    public function delete(Asset $asset): void
    {
        if ($asset->status === 'disposed') {
            throw new InvalidArgumentException('Disposed asset cannot be deleted.');
        }

        $asset->delete();
    }

    private function getValidAssetItem(int $companyId, int $assetItemId): AssetItem
    {
        $assetItem = AssetItem::query()
            ->where('company_id', $companyId)
            ->where('id', $assetItemId)
            ->where('is_active', true)
            ->with('assetCategory')
            ->first();

        if (! $assetItem) {
            throw new InvalidArgumentException('Invalid asset item selected.');
        }

        if (! $assetItem->assetCategory || ! $assetItem->assetCategory->is_active) {
            throw new InvalidArgumentException('Asset item category is not active.');
        }

        return $assetItem;
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = Asset::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'AST-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}