<?php

namespace App\Services\Reports;

use App\Models\SalesInvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GrossProfitReportService
{
    public function generate(
        int $companyId,
        string $fromDate,
        string $toDate,
        string $filterBy
    ): array {
        $baseRows = $this->baseRows($companyId, $fromDate, $toDate);

        if ($baseRows->isEmpty()) {
            return [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'filter_by' => $filterBy,
                'rows' => [],
                'totals' => $this->emptyTotals(),
                'message' => 'No data found.',
            ];
        }

        $rows = match ($filterBy) {
            'sales_invoice' => $this->bySalesInvoice($baseRows),
            'item_code' => $this->byItemCode($baseRows),
            'item_group' => $this->byItemGroup($baseRows),
            'warehouse' => $this->byWarehouse($baseRows),
            'customer' => $this->byCustomer($baseRows),
            'sales_person' => $this->bySalesPerson($baseRows),
            default => collect(),
        };

        $totals = $this->calculateTotals($rows);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'filter_by' => $filterBy,
            'rows' => $rows->values()->toArray(),
            'totals' => $totals,
            'message' => $rows->isEmpty() ? 'No data found.' : 'Gross profit report generated successfully.',
        ];
    }

    private function baseRows(
        int $companyId,
        string $fromDate,
        string $toDate
    ): Collection {
        return SalesInvoiceItem::query()
            ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_items.sales_invoice_id')
            ->leftJoin('items', 'items.id', '=', 'sales_invoice_items.item_id')
            ->leftJoin('item_groups', 'item_groups.id', '=', 'items.item_group_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'sales_invoice_items.warehouse_id')
            ->leftJoin('customers', 'customers.id', '=', 'sales_invoices.customer_id')
            ->leftJoin('sales_people', 'sales_people.id', '=', 'sales_invoices.sales_person_id')
            ->where('sales_invoices.company_id', $companyId)
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', 'submitted')
            ->whereBetween('sales_invoices.posting_date', [$fromDate, $toDate])
            ->select([
                'sales_invoices.id as sales_invoice_id',
                'sales_invoices.invoice_number',
                'sales_invoices.posting_date',
                'sales_invoices.grand_total',
                'sales_invoices.net_total',
                'sales_invoices.customer_id',
                'customers.name_ar as customer_name_ar',
                'customers.name_en as customer_name_en',

                'sales_invoices.sales_person_id',
                'sales_people.sales_person_name_ar',
                'sales_people.sales_person_name_en',

                'sales_invoice_items.item_id',
                'sales_invoice_items.item_code',
                'sales_invoice_items.item_name_ar',
                'sales_invoice_items.item_name_en',
                'sales_invoice_items.warehouse_id',
                'sales_invoice_items.quantity',
                'sales_invoice_items.rate',
                'sales_invoice_items.amount',

                'items.item_group_id',
                'item_groups.name_ar as item_group_name_ar',
                'item_groups.name_en as item_group_name_en',

                'warehouses.name_ar as warehouse_name_ar',
                'warehouses.name_en as warehouse_name_en',

              DB::raw('COALESCE(items.purchase_price, 0) as valuation_rate'),
            ])
            ->get()
            ->map(function ($row) {
                $qty = (float) $row->quantity;
                $sellingRate = (float) $row->rate;
                $valuationRate = (float) $row->valuation_rate;

                $sellingAmount = (float) $row->amount;
                $buyingAmount = $qty * $valuationRate;
                $grossProfit = $sellingAmount - $buyingAmount;
                $grossProfitPercent = $sellingAmount == 0
                    ? 0
                    : ($grossProfit / $sellingAmount) * 100;

                return [
                    'sales_invoice_id' => $row->sales_invoice_id,
                    'sales_invoice' => $row->invoice_number,
                    'posting_date' => $row->posting_date,

                    'customer_id' => $row->customer_id,
                    'customer_name_ar' => $row->customer_name_ar,
                    'customer_name_en' => $row->customer_name_en,

                    'sales_person_id' => $row->sales_person_id,
                    'sales_person_name_ar' => $row->sales_person_name_ar,
                    'sales_person_name_en' => $row->sales_person_name_en,

                    'item_id' => $row->item_id,
                    'item_code' => $row->item_code,
                    'item_name_ar' => $row->item_name_ar,
                    'item_name_en' => $row->item_name_en,

                    'item_group_id' => $row->item_group_id,
                    'item_group_name_ar' => $row->item_group_name_ar,
                    'item_group_name_en' => $row->item_group_name_en,

                    'warehouse_id' => $row->warehouse_id,
                    'warehouse_name_ar' => $row->warehouse_name_ar,
                    'warehouse_name_en' => $row->warehouse_name_en,

                    'qty' => round($qty, 2),
                    'selling_rate' => round($sellingRate, 2),
                    'valuation_rate' => round($valuationRate, 2),
                    'selling_amount' => round($sellingAmount, 2),
                    'buying_amount' => round($buyingAmount, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_profit_percent' => round($grossProfitPercent, 2),

                    'allocated_amount' => round($sellingAmount, 2),
                ];
            });
    }

    private function calculateTotals(Collection $rows): array
    {
        $qty = (float) $rows->sum('qty');
        $sellingAmount = (float) $rows->sum('selling_amount');
        $buyingAmount = (float) $rows->sum('buying_amount');
        $grossProfit = $sellingAmount - $buyingAmount;

        return [
            'qty' => round($qty, 2),
            'selling_amount' => round($sellingAmount, 2),
            'buying_amount' => round($buyingAmount, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_profit_percent' => $sellingAmount == 0
                ? 0
                : round(($grossProfit / $sellingAmount) * 100, 2),
        ];
    }

    private function emptyTotals(): array
    {
        return [
            'qty' => 0,
            'selling_amount' => 0,
            'buying_amount' => 0,
            'gross_profit' => 0,
            'gross_profit_percent' => 0,
        ];
    }

    private function avgRate(float $amount, float $qty): float
    {
        return $qty == 0 ? 0 : round($amount / $qty, 2);
    }
       private function bySalesInvoice(Collection $baseRows): Collection
{
    return $baseRows
        ->groupBy('sales_invoice_id')
        ->map(function (Collection $items) {
            $first = $items->first();

            $qty = (float) $items->sum('qty');
            $sellingAmount = (float) $items->sum('selling_amount');
            $buyingAmount = (float) $items->sum('buying_amount');
            $grossProfit = $sellingAmount - $buyingAmount;

            $itemGroupsAr = $items->pluck('item_group_name_ar')->filter()->unique()->values();
            $itemGroupsEn = $items->pluck('item_group_name_en')->filter()->unique()->values();

            $warehousesAr = $items->pluck('warehouse_name_ar')->filter()->unique()->values();
            $warehousesEn = $items->pluck('warehouse_name_en')->filter()->unique()->values();

            return [
                'sales_invoice' => $first['sales_invoice'],
                'customer_name_ar' => $first['customer_name_ar'],
                'customer_name_en' => $first['customer_name_en'],
                'posting_date' => $first['posting_date'],

                'item_group_name_ar' => $itemGroupsAr->count() > 1
                    ? 'مجموعات متعددة'
                    : ($itemGroupsAr->first() ?? 'غير مصنف'),

                'item_group_name_en' => $itemGroupsEn->count() > 1
                    ? 'Multiple Groups'
                    : ($itemGroupsEn->first() ?? 'Uncategorized'),

                'warehouse_name_ar' => $warehousesAr->count() > 1
                    ? 'مستودعات متعددة'
                    : ($warehousesAr->first() ?? '-'),

                'warehouse_name_en' => $warehousesEn->count() > 1
                    ? 'Multiple Warehouses'
                    : ($warehousesEn->first() ?? '-'),

                'qty' => round($qty, 2),
                'selling_rate' => $this->avgRate($sellingAmount, $qty),
                'valuation_rate' => $this->avgRate($buyingAmount, $qty),
                'selling_amount' => round($sellingAmount, 2),
                'buying_amount' => round($buyingAmount, 2),
                'gross_profit' => round($grossProfit, 2),
                'gross_profit_percent' => $sellingAmount == 0
                    ? 0
                    : round(($grossProfit / $sellingAmount) * 100, 2),
            ];
        });
}
    private function byItemCode(Collection $baseRows): Collection
    {
        return $baseRows
            ->groupBy('item_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $qty = (float) $items->sum('qty');
                $sellingAmount = (float) $items->sum('selling_amount');
                $buyingAmount = (float) $items->sum('buying_amount');
                $grossProfit = $sellingAmount - $buyingAmount;

                return [
                    'item_code' => $first['item_code'],
                    'item_name_ar' => $first['item_name_ar'],
                    'item_name_en' => $first['item_name_en'],
                    'qty' => round($qty, 2),
                    'selling_rate' => $this->avgRate($sellingAmount, $qty),
                    'valuation_rate' => $this->avgRate($buyingAmount, $qty),
                    'selling_amount' => round($sellingAmount, 2),
                    'buying_amount' => round($buyingAmount, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_profit_percent' => $sellingAmount == 0 ? 0 : round(($grossProfit / $sellingAmount) * 100, 2),
                ];
            });
    }

    private function byItemGroup(Collection $baseRows): Collection
    {
        return $baseRows
            ->groupBy('item_group_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $qty = (float) $items->sum('qty');
                $sellingAmount = (float) $items->sum('selling_amount');
                $buyingAmount = (float) $items->sum('buying_amount');
                $grossProfit = $sellingAmount - $buyingAmount;

                return [
                    'item_group_name_ar' => $first['item_group_name_ar'] ?? 'غير مصنف',
                    'item_group_name_en' => $first['item_group_name_en'] ?? 'Uncategorized',
                    'qty' => round($qty, 2),
                    'selling_rate' => $this->avgRate($sellingAmount, $qty),
                    'valuation_rate' => $this->avgRate($buyingAmount, $qty),
                    'selling_amount' => round($sellingAmount, 2),
                    'buying_amount' => round($buyingAmount, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_profit_percent' => $sellingAmount == 0 ? 0 : round(($grossProfit / $sellingAmount) * 100, 2),
                ];
            });
    }

   private function byWarehouse(Collection $baseRows): Collection
{
    return $baseRows
        ->groupBy('warehouse_id')
        ->map(function (Collection $items) {

            $first = $items->first();

            $qty = (float) $items->sum('qty');

            $sellingAmount = (float) $items->sum('selling_amount');

            $buyingAmount = (float) $items->sum('buying_amount');

            $grossProfit = $sellingAmount - $buyingAmount;

            return [

                'warehouse_name_ar' => $first['warehouse_name_ar'],

                'warehouse_name_en' => $first['warehouse_name_en'],

                'qty' => round($qty, 2),

                'selling_rate' => $this->avgRate($sellingAmount, $qty),

                'valuation_rate' => $this->avgRate($buyingAmount, $qty),

                'selling_amount' => round($sellingAmount, 2),

                'buying_amount' => round($buyingAmount, 2),

                'gross_profit' => round($grossProfit, 2),

                'gross_profit_percent' => $sellingAmount == 0
                    ? 0
                    : round(($grossProfit / $sellingAmount) * 100, 2),

            ];
        })
        ->values();
}
    private function byCustomer(Collection $baseRows): Collection
    {
        return $baseRows
            ->groupBy('customer_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $qty = (float) $items->sum('qty');
                $sellingAmount = (float) $items->sum('selling_amount');
                $buyingAmount = (float) $items->sum('buying_amount');
                $grossProfit = $sellingAmount - $buyingAmount;

                return [
                    'customer_name_ar' => $first['customer_name_ar'],
                    'customer_name_en' => $first['customer_name_en'],
                    'qty' => round($qty, 2),
                    'selling_rate' => $this->avgRate($sellingAmount, $qty),
                    'valuation_rate' => $this->avgRate($buyingAmount, $qty),
                    'selling_amount' => round($sellingAmount, 2),
                    'buying_amount' => round($buyingAmount, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_profit_percent' => $sellingAmount == 0 ? 0 : round(($grossProfit / $sellingAmount) * 100, 2),
                ];
            });
    }

    private function bySalesPerson(Collection $baseRows): Collection
    {
        return $baseRows
            ->groupBy('sales_person_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                $qty = (float) $items->sum('qty');
                $sellingAmount = (float) $items->sum('selling_amount');
                $buyingAmount = (float) $items->sum('buying_amount');
                $grossProfit = $sellingAmount - $buyingAmount;

                return [
                    'sales_person_name_ar' => $first['sales_person_name_ar'],
                    'sales_person_name_en' => $first['sales_person_name_en'],
                    'allocated_amount' => round($sellingAmount, 2),
                    'qty' => round($qty, 2),
                    'selling_rate' => $this->avgRate($sellingAmount, $qty),
                    'valuation_rate' => $this->avgRate($buyingAmount, $qty),
                    'selling_amount' => round($sellingAmount, 2),
                    'buying_amount' => round($buyingAmount, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_profit_percent' => $sellingAmount == 0 ? 0 : round(($grossProfit / $sellingAmount) * 100, 2),
                ];
            });
    }
}