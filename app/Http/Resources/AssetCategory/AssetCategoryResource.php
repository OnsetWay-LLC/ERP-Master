<?php

namespace App\Http\Resources\AssetCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,

            'finance_book' => $this->finance_book,
            'depreciation_method' => $this->depreciation_method,
            'frequency_month' => $this->frequency_month,
            'total_depreciation_count' => $this->total_depreciation_count,
            'depreciation_posting_day' => $this->depreciation_posting_day,
            'depreciation_rate' => $this->depreciation_rate,

            'fixed_asset_account_id' => $this->fixed_asset_account_id,
            'accumulated_depreciation_account_id' => $this->accumulated_depreciation_account_id,
            'depreciation_expense_account_id' => $this->depreciation_expense_account_id,

            'fixed_asset_account' => $this->whenLoaded('fixedAssetAccount'),
            'accumulated_depreciation_account' => $this->whenLoaded('accumulatedDepreciationAccount'),
            'depreciation_expense_account' => $this->whenLoaded('depreciationExpenseAccount'),
            'capital_work_in_progress_account_id' => $this->capital_work_in_progress_account_id,
            'capital_work_in_progress_account' => $this->whenLoaded('capitalWorkInProgressAccount'),

            'is_active' => $this->is_active,
            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}