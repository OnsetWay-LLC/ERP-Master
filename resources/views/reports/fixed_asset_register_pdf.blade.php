<!DOCTYPE html>
<html lang="{{ $locale === 'en' ? 'en' : 'ar' }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <title>Fixed Asset Register Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111;
            direction: {{ $locale === 'en' ? 'ltr' : 'rtl' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .generated {
            text-align: center;
            font-size: 9px;
            margin-bottom: 15px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            direction: {{ $locale === 'en' ? 'ltr' : 'rtl' }};
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        th, td {
            border: 1px solid #999;
            padding: 5px;
            vertical-align: middle;
        }

        td {
            text-align: {{ $locale === 'en' ? 'left' : 'right' }};
        }

        .number {
            text-align: right;
            direction: ltr;
        }

        .total-row {
            font-weight: bold;
            background: #f7f7f7;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            font-size: 14px;
            border: 1px solid #999;
        }

        .en {
            direction: ltr;
            text-align: left;
        }

        .ar {
            direction: rtl;
            text-align: right;
        }
    </style>
</head>
<body>

@if($locale === 'ar')
    <h2>تقرير سجل الأصول الثابتة</h2>
@elseif($locale === 'en')
    <h2>Fixed Asset Register Report</h2>
@else
    <h2>
        تقرير سجل الأصول الثابتة
        <br>
        Fixed Asset Register Report
    </h2>
@endif

<div class="generated">
    @if($locale === 'ar')
        تاريخ الإنشاء: {{ $generatedAt }}
    @elseif($locale === 'en')
        Generated At: {{ $generatedAt }}
    @else
        تاريخ الإنشاء / Generated At: {{ $generatedAt }}
    @endif
</div>

@if($rows->isEmpty())
    <div class="no-data">
        @if($locale === 'ar')
            لا يوجد بيانات
        @elseif($locale === 'en')
            No Data Available
        @else
            لا يوجد بيانات
            <br>
            No Data Available
        @endif
    </div>
@else
    <table>
        <thead>
        <tr>
            <th>
                @if($locale === 'ar')
                    رقم الأصل
                @elseif($locale === 'en')
                    Asset ID
                @else
                    رقم الأصل<br>Asset ID
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    اسم الأصل
                @elseif($locale === 'en')
                    Asset Name
                @else
                    اسم الأصل<br>Asset Name
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    فئة الأصل
                @elseif($locale === 'en')
                    Asset Category
                @else
                    فئة الأصل<br>Asset Category
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    تاريخ الشراء
                @elseif($locale === 'en')
                    Purchase Date
                @else
                    تاريخ الشراء<br>Purchase Date
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    تاريخ بدء الاستخدام
                @elseif($locale === 'en')
                    Available for Use Date
                @else
                    تاريخ بدء الاستخدام<br>Available for Use Date
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    صافي قيمة الشراء
                @elseif($locale === 'en')
                    Net Purchase Amount
                @else
                    صافي قيمة الشراء<br>Net Purchase Amount
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    القيمة الدفترية
                @elseif($locale === 'en')
                    Asset Value
                @else
                    القيمة الدفترية<br>Asset Value
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    الإهلاك المتراكم الافتتاحي
                @elseif($locale === 'en')
                    Opening Accumulated Depreciation
                @else
                    الإهلاك المتراكم الافتتاحي<br>Opening Accumulated Depreciation
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    قيمة الإهلاك
                @elseif($locale === 'en')
                    Depreciation Amount
                @else
                    قيمة الإهلاك<br>Depreciation Amount
                @endif
            </th>

            <th>
                @if($locale === 'ar')
                    الموقع
                @elseif($locale === 'en')
                    Location
                @else
                    الموقع<br>Location
                @endif
            </th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['asset_id'] }}</td>

                <td>
                    @if($locale === 'ar')
                        {{ $row['asset_name_ar'] ?? '-' }}
                    @elseif($locale === 'en')
                        {{ $row['asset_name_en'] ?? '-' }}
                    @else
                        <div class="ar">{{ $row['asset_name_ar'] ?? '-' }}</div>
                        <div class="en">{{ $row['asset_name_en'] ?? '-' }}</div>
                    @endif
                </td>

                <td>
                    @if($locale === 'ar')
                        {{ $row['asset_category_ar'] ?? '-' }}
                    @elseif($locale === 'en')
                        {{ $row['asset_category_en'] ?? '-' }}
                    @else
                        <div class="ar">{{ $row['asset_category_ar'] ?? '-' }}</div>
                        <div class="en">{{ $row['asset_category_en'] ?? '-' }}</div>
                    @endif
                </td>

                <td>{{ $row['purchase_date'] ?? '-' }}</td>
                <td>{{ $row['available_for_use_date'] ?? '-' }}</td>

                <td class="number">{{ number_format($row['net_purchase_amount'], 2) }}</td>
                <td class="number">{{ number_format($row['asset_value'], 2) }}</td>
                <td class="number">{{ number_format($row['opening_accumulated_depreciation'], 2) }}</td>
                <td class="number">{{ number_format($row['depreciation_amount'], 2) }}</td>

                <td>
                    @if($locale === 'ar')
                        {{ $row['location_ar'] ?? '-' }}
                    @elseif($locale === 'en')
                        {{ $row['location_en'] ?? '-' }}
                    @else
                        <div class="ar">{{ $row['location_ar'] ?? '-' }}</div>
                        <div class="en">{{ $row['location_en'] ?? '-' }}</div>
                    @endif
                </td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="5">
                @if($locale === 'ar')
                    الإجمالي
                @elseif($locale === 'en')
                    Total
                @else
                    الإجمالي / Total
                @endif
            </td>

            <td class="number">{{ number_format($totals['net_purchase_amount'], 2) }}</td>
            <td class="number">{{ number_format($totals['asset_value'], 2) }}</td>
            <td class="number">{{ number_format($totals['opening_accumulated_depreciation'], 2) }}</td>
            <td class="number">{{ number_format($totals['depreciation_amount'], 2) }}</td>
            <td></td>
        </tr>
        </tbody>
    </table>
@endif

</body>
</html>