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
            ->join('item_groups as ig', 'ig.id', '=', 'spt.item_group_id')
            ->join('monthly_distributions as md', 'md.id', '=', 'spt.monthly_distribution_id')
            ->join('monthly_distribution_lines as mdl', 'mdl.monthly_distribution_id', '=', 'md.id')
            ->where('sp.company_id', $companyId)
            ->where('sp.is_active', true)
            ->where('md.is_active', true)
            ->where('mdl.percentage', '>', 0)
            ->when(!empty($filters['sales_person_id']), function ($query) use ($filters) {
                $query->where('sp.id', $filters['sales_person_id']);
            })
            ->when(!empty($filters['item_group_id']), function ($query) use ($filters) {
                $query->where('ig.id', $filters['item_group_id']);
            })
            ->select([
                'spt.id as target_id',
                'sp.id as sales_person_id',
                'sp.sales_person_name_ar',
                'sp.sales_person_name_en',
                'sp.commission_rate',
                'e.id as employee_id',
                'ig.id as item_group_id',
                'ig.name_ar as item_group_name_ar',
                'ig.name_en as item_group_name_en',
                'spt.target_amount',
                DB::raw('SUM(mdl.percentage) as total_percentage'),
            ])
            ->groupBy(
                'spt.id',
                'sp.id',
                'sp.sales_person_name_ar',
                'sp.sales_person_name_en',
                'sp.commission_rate',
                'e.id',
                'ig.id',
                'ig.name_ar',
                'ig.name_en',
                'spt.target_amount'
            )
            ->get();

        $rows = [];

        foreach ($targets as $target) {
            $periodTarget = round(
                ((float) $target->target_amount * (float) $target->total_percentage) / 100,
                2
            );

            $salesQuery = DB::table('sales_invoice_items as sii')
                ->join('sales_invoices as si', 'si.id', '=', 'sii.sales_invoice_id')
                ->join('items as i', 'i.id', '=', 'sii.item_id')
                ->where('si.company_id', $companyId)
                ->where('si.status', 'submitted')
                ->whereNull('si.deleted_at')
                ->whereBetween('si.posting_date', [$fromDate, $toDate])
                ->where('si.sales_person_id', $target->sales_person_id)
                ->where('i.item_group_id', $target->item_group_id)
                ->when(!empty($filters['warehouse_id']), function ($query) use ($filters) {
                    $query->where('sii.warehouse_id', $filters['warehouse_id']);
                });

            $actualSales = (float) (clone $salesQuery)->sum('sii.amount');

            $invoiceCount = (clone $salesQuery)
                ->distinct('si.id')
                ->count('si.id');

            $achievementPercentage = $periodTarget > 0
                ? round(($actualSales / $periodTarget) * 100, 2)
                : 0;

            $commissionAmount = $achievementPercentage >= 100
                ? round(($actualSales * (float) $target->commission_rate) / 100, 2)
                : 0;

            $rows[] = [
                'sales_person_id' => $target->sales_person_id,
                'sales_person_name_ar' => $target->sales_person_name_ar,
                'sales_person_name_en' => $target->sales_person_name_en,

                'employee_id' => $target->employee_id,

                'item_group_id' => $target->item_group_id,
                'item_group_name_ar' => $target->item_group_name_ar,
                'item_group_name_en' => $target->item_group_name_en,

                'target_amount' => round((float) $target->target_amount, 2),
                'total_distribution_percentage' => round((float) $target->total_percentage, 2),
                'period_target' => $periodTarget,

                'actual_sales' => round($actualSales, 2),
                'achievement_percentage' => $achievementPercentage,

                'commission_rate' => round((float) $target->commission_rate, 2),
                'commission_amount' => $commissionAmount,

                'invoice_count' => $invoiceCount,
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
                'total_period_target' => round(array_sum(array_column($rows, 'period_target')), 2),
                'total_actual_sales' => round(array_sum(array_column($rows, 'actual_sales')), 2),
                'total_commission_amount' => round(array_sum(array_column($rows, 'commission_amount')), 2),
                'total_invoice_count' => array_sum(array_column($rows, 'invoice_count')),
            ],
            'data' => $rows,
        ];
    }
}