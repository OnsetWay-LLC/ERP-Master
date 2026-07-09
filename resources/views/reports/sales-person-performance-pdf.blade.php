<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ $locale === 'ar' ? 'مندوب المبيعات' : 'Sales Person' }}</th>
            <th>{{ $locale === 'ar' ? 'الموظف' : 'Employee' }}</th>
            <th>{{ $locale === 'ar' ? 'المستودع' : 'Warehouse' }}</th>
            <th>{{ $locale === 'ar' ? 'مجموعة الأصناف' : 'Item Group' }}</th>
            <th>{{ $locale === 'ar' ? 'الشهر' : 'Month' }}</th>
            <th>{{ $locale === 'ar' ? 'الهدف الكلي' : 'Target Amount' }}</th>
            <th>{{ $locale === 'ar' ? 'الهدف الشهري' : 'Monthly Target' }}</th>
            <th>{{ $locale === 'ar' ? 'إجمالي هدف الفترة' : 'Total Period Target' }}</th>
            <th>{{ $locale === 'ar' ? 'المبيعات الفعلية' : 'Actual Sales' }}</th>
            <th>{{ $locale === 'ar' ? 'نسبة العمولة' : 'Commission Rate' }}</th>
            <th>{{ $locale === 'ar' ? 'قيمة العمولة' : 'Commission Amount' }}</th>
            <th>{{ $locale === 'ar' ? 'عدد الفواتير' : 'Invoice Count' }}</th>
            <th>{{ $locale === 'ar' ? 'رقم القيد' : 'Journal Entry Number' }}</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $index => $row)
            @php
                $salesPersonName = $locale === 'ar'
                    ? ($row['sales_person_name_ar'] ?? $row['sales_person_name_en'] ?? '-')
                    : ($row['sales_person_name_en'] ?? $row['sales_person_name_ar'] ?? '-');

                $employeeName = $locale === 'ar'
                    ? ($row['employee_name_ar'] ?? $row['employee_name_en'] ?? '-')
                    : ($row['employee_name_en'] ?? $row['employee_name_ar'] ?? '-');

                $warehouseName = $locale === 'ar'
                    ? ($row['warehouse_name_ar'] ?? $row['warehouse_name_en'] ?? '-')
                    : ($row['warehouse_name_en'] ?? $row['warehouse_name_ar'] ?? '-');

                $itemGroupName = $locale === 'ar'
                    ? ($row['item_group_name_ar'] ?? $row['item_group_name_en'] ?? '-')
                    : ($row['item_group_name_en'] ?? $row['item_group_name_ar'] ?? '-');
            @endphp

            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $salesPersonName }}</td>
                <td>{{ $employeeName }}</td>
                <td>{{ $warehouseName }}</td>
                <td>{{ $itemGroupName }}</td>
                <td>{{ $row['month'] ?? '-' }}</td>
                <td>{{ number_format((float)($row['target_amount'] ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row['monthly_target'] ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row['total_period_target'] ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row['actual_sales'] ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row['commission_rate'] ?? 0), 2) }}%</td>
                <td>{{ number_format((float)($row['commission_amount'] ?? 0), 2) }}</td>
                <td>{{ $row['invoice_count'] ?? 0 }}</td>
                <td>{{ $row['journal_entry_number'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" style="text-align:center;">
                    {{ $locale === 'ar' ? 'لا توجد بيانات' : 'No data available' }}
                </td>
            </tr>
        @endforelse

        @if(!empty($rows))
            <tr style="font-weight:bold; background:#e8f3fb;">
               <tr style="font-weight:bold; background:#e8f3fb;">
    <td colspan="6">{{ $locale === 'ar' ? 'الإجمالي' : 'Total' }}</td>
    <td>{{ number_format((float)($summary['total_target_amount'] ?? 0), 2) }}</td>
    <td>{{ number_format((float)($summary['total_monthly_target'] ?? 0), 2) }}</td>
    <td>{{ number_format((float)($summary['total_period_target'] ?? 0), 2) }}</td>
    <td>{{ number_format((float)($summary['total_actual_sales'] ?? 0), 2) }}</td>
    <td>-</td>
    <td>{{ number_format((float)($summary['total_commission_amount'] ?? 0), 2) }}</td>
    <td>{{ $summary['total_invoice_count'] ?? 0 }}</td>
    <td>-</td>
</tr>
            </tr>
        @endif
    </tbody>
</table>