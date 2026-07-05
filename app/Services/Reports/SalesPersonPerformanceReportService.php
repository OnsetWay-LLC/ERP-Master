<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class SalesPersonPerformanceReportService
{
    public function report(int $companyId, array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        $targets = DB::table('sales_person_targets as spt')
            ->join('sales_people as sp', 'sp.id', '=', 'spt.sales_person_id')
            ->leftJoin('employees as e', 'e.id', '=', 'sp.employee_id')
            ->leftJoin('warehouses as w', 'w.sales_person_id', '=', 'sp.id')
            ->join('item_groups as ig', 'ig.id', '=', 'spt.item_group_id')
            ->join('monthly_distributions as md', 'md.id', '=', 'spt.monthly_distribution_id')
            ->join('monthly_distribution_lines as mdl', 'mdl.monthly_distribution_id', '=', 'md.id')
            ->where('sp.company_id', $companyId)
            ->where('sp.is_active', true)
            ->where('md.is_active', true)
            ->where('mdl.percentage', '>', 0)
            ->when(!empty($filters['sales_person_id']), fn ($q) =>
                $q->where('sp.id', $filters['sales_person_id'])
            )
            ->when(!empty($filters['item_group_id']), fn ($q) =>
                $q->where('ig.id', $filters['item_group_id'])
            )
            ->select([
                'spt.id as target_id',
                'spt.target_amount',

                'sp.id as sales_person_id',
                'sp.sales_person_name_ar',
                'sp.sales_person_name_en',
                'sp.commission_rate',

                'e.id as employee_id',
                'e.full_name_ar as employee_name_ar',
                'e.full_name_en as employee_name_en',

                'w.id as warehouse_id',
                'w.name_ar as warehouse_name_ar',
                'w.name_en as warehouse_name_en',

                'ig.id as item_group_id',
                'ig.name_ar as item_group_name_ar',
                'ig.name_en as item_group_name_en',

                'mdl.month',
                'mdl.percentage as month_percentage',
            ])
            ->orderBy('sp.id')
            ->orderBy('mdl.month')
            ->get();

        $rows = [];

        foreach ($targets as $target) {
            $monthlyTarget = round(
                (float) $target->target_amount * ((float) $target->month_percentage / 100),
                2
            );

            $actualSales = $this->getActualSales(
                $companyId,
                (int) $target->sales_person_id,
                (int) $target->item_group_id,
                $target->month,
                $fromDate,
                $toDate,
                $filters['warehouse_id'] ?? null
            );

            $totalActualSales = $actualSales > 0
                ? $this->getTotalActualSales(
                    $companyId,
                    (int) $target->sales_person_id,
                    (int) $target->item_group_id,
                    $fromDate,
                    $toDate,
                    $filters['warehouse_id'] ?? null
                )
                : 0;

            $invoiceCount = $this->getInvoiceCount(
                $companyId,
                (int) $target->sales_person_id,
                (int) $target->item_group_id,
                $target->month,
                $fromDate,
                $toDate,
                $filters['warehouse_id'] ?? null
            );

            $commissionAmount = round(
                $monthlyTarget * ((float) $target->commission_rate / 100),
                2
            );

            $rows[] = [
                'sales_person_id' => $target->sales_person_id,
                'sales_person_name_ar' => $target->sales_person_name_ar,
                'sales_person_name_en' => $target->sales_person_name_en,

                'employee_id' => $target->employee_id,
                'employee_name_ar' => $target->employee_name_ar,
                'employee_name_en' => $target->employee_name_en,

                'warehouse_id' => $target->warehouse_id,
                'warehouse_name_ar' => $target->warehouse_name_ar,
                'warehouse_name_en' => $target->warehouse_name_en,

                'item_group_id' => $target->item_group_id,
                'item_group_name_ar' => $target->item_group_name_ar,
                'item_group_name_en' => $target->item_group_name_en,

                'month' => $target->month,

                'target_amount' => round((float) $target->target_amount, 2),
                'monthly_target' => $monthlyTarget,

                'actual_sales' => $actualSales,
                'total_actual_sales' => $totalActualSales,

                'commission_rate' => round((float) $target->commission_rate, 2),
                'commission_amount' => $commissionAmount,

                'invoice_count' => $invoiceCount,
                'journal_entry_number' => '-',
            ];
        }

        return [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'sales_person_id' => $filters['sales_person_id'] ?? null,
                'item_group_id' => $filters['item_group_id'] ?? null,
                'warehouse_id' => $filters['warehouse_id'] ?? null,
            ],
            'summary' => [
                'total_target_amount' => round(array_sum(array_column($rows, 'target_amount')), 2),
                'total_monthly_target' => round(array_sum(array_column($rows, 'monthly_target')), 2),
                'total_actual_sales' => round(array_sum(array_column($rows, 'actual_sales')), 2),
                'total_commission_amount' => round(array_sum(array_column($rows, 'commission_amount')), 2),
                'total_invoice_count' => array_sum(array_column($rows, 'invoice_count')),
            ],
            'data' => $rows,
        ];
    }

    private function getActualSales(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        int|string $month,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): float {
        return round((float) DB::table('sales_invoices as si')
            ->join('sales_invoice_items as sii', 'sii.sales_invoice_id', '=', 'si.id')
            ->join('items as i', 'i.id', '=', 'sii.item_id')
            ->where('si.company_id', $companyId)
            ->where('si.status', 'submitted')
            ->whereNull('si.deleted_at')
            ->where('si.sales_person_id', $salesPersonId)
            ->where('i.item_group_id', $itemGroupId)
            ->whereBetween('si.posting_date', [$fromDate, $toDate])
            ->whereMonth('si.posting_date', (int) $month)
            ->when($warehouseId, fn ($q) => $q->where('sii.warehouse_id', $warehouseId))
            ->sum('si.grand_total'), 2);
    }

    private function getTotalActualSales(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): float {
        return round((float) DB::table('sales_invoices as si')
            ->join('sales_invoice_items as sii', 'sii.sales_invoice_id', '=', 'si.id')
            ->join('items as i', 'i.id', '=', 'sii.item_id')
            ->where('si.company_id', $companyId)
            ->where('si.status', 'submitted')
            ->whereNull('si.deleted_at')
            ->where('si.sales_person_id', $salesPersonId)
            ->where('i.item_group_id', $itemGroupId)
            ->whereBetween('si.posting_date', [$fromDate, $toDate])
            ->when($warehouseId, fn ($q) => $q->where('sii.warehouse_id', $warehouseId))
            ->sum('si.grand_total'), 2);
    }

    private function getInvoiceCount(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        int|string $month,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): int {
        return DB::table('sales_invoices as si')
            ->join('sales_invoice_items as sii', 'sii.sales_invoice_id', '=', 'si.id')
            ->join('items as i', 'i.id', '=', 'sii.item_id')
            ->where('si.company_id', $companyId)
            ->where('si.status', 'submitted')
            ->whereNull('si.deleted_at')
            ->where('si.sales_person_id', $salesPersonId)
            ->where('i.item_group_id', $itemGroupId)
            ->whereBetween('si.posting_date', [$fromDate, $toDate])
            ->whereMonth('si.posting_date', (int) $month)
            ->when($warehouseId, fn ($q) => $q->where('sii.warehouse_id', $warehouseId))
            ->distinct('si.id')
            ->count('si.id');
    }
}