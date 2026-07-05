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
    <th>{{ $locale === 'ar' ? 'المبيعات الفعلية' : 'Actual Sales' }}</th>
    <th>{{ $locale === 'ar' ? 'إجمالي المبيعات' : 'Total Actual Sales' }}</th>
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
        <td>{{ $row['month'] }}</td>
        <td>{{ number_format($row['target_amount'], 2) }}</td>
        <td>{{ number_format($row['monthly_target'], 2) }}</td>
        <td>{{ number_format($row['actual_sales'], 2) }}</td>
        <td>{{ $row['total_actual_sales'] > 0 ? number_format($row['total_actual_sales'], 2) : '-' }}</td>
        <td>{{ number_format($row['commission_rate'], 2) }}%</td>
        <td>{{ number_format($row['commission_amount'], 2) }}</td>
        <td>{{ $row['invoice_count'] }}</td>
        <td>{{ $row['journal_entry_number'] }}</td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="no-data">
            {{ $locale === 'ar' ? 'لا توجد بيانات' : 'No data available' }}
        </td>
    </tr>
@endforelse

@if(!empty($rows))
    <tr style="font-weight: bold; background: #e8f3fb;">
        <td colspan="6">Total</td>
        <td>{{ number_format($summary['total_target_amount'], 2) }}</td>
        <td>{{ number_format($summary['total_monthly_target'], 2) }}</td>
        <td>{{ number_format($summary['total_actual_sales'], 2) }}</td>
        <td>-</td>
        <td>-</td>
        <td>{{ number_format($summary['total_commission_amount'], 2) }}</td>
        <td>{{ $summary['total_invoice_count'] }}</td>
        <td>-</td>
    </tr>
@endif
</tbody>