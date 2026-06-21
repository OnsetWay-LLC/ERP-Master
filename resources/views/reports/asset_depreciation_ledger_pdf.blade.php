<!DOCTYPE html>
<html lang="{{ $locale === 'en' ? 'en' : 'ar' }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <title>Asset Depreciation Ledger</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
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
    <h2>تقرير دفتر إهلاك الأصول</h2>
@elseif($locale === 'en')
    <h2>Asset Depreciation Ledger Report</h2>
@else
    <h2>
        تقرير دفتر إهلاك الأصول
        <br>
        Asset Depreciation Ledger Report
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
            <th>@if($locale === 'ar') الأصل @elseif($locale === 'en') Asset @else الأصل<br>Asset @endif</th>
            <th>@if($locale === 'ar') اسم الأصل @elseif($locale === 'en') Asset Name @else اسم الأصل<br>Asset Name @endif</th>
            <th>@if($locale === 'ar') تاريخ الإهلاك @elseif($locale === 'en') Depreciation Date @else تاريخ الإهلاك<br>Depreciation Date @endif</th>
            <th>@if($locale === 'ar') قيمة الشراء @elseif($locale === 'en') Purchase Amount @else قيمة الشراء<br>Purchase Amount @endif</th>
            <th>@if($locale === 'ar') مجمع الإهلاك الافتتاحي @elseif($locale === 'en') Opening Accumulated Depreciation @else مجمع الإهلاك الافتتاحي<br>Opening Accumulated Depreciation @endif</th>
            <th>@if($locale === 'ar') قيمة الإهلاك @elseif($locale === 'en') Depreciation Amount @else قيمة الإهلاك<br>Depreciation Amount @endif</th>
            <th>@if($locale === 'ar') مجمع الإهلاك @elseif($locale === 'en') Accumulated Depreciation @else مجمع الإهلاك<br>Accumulated Depreciation @endif</th>
            <th>@if($locale === 'ar') القيمة بعد الإهلاك @elseif($locale === 'en') Value After Depreciation @else القيمة بعد الإهلاك<br>Value After Depreciation @endif</th>
            <th>@if($locale === 'ar') قيد الإهلاك @elseif($locale === 'en') Depreciation Entry @else قيد الإهلاك<br>Depreciation Entry @endif</th>
            <th>@if($locale === 'ar') فئة الأصل @elseif($locale === 'en') Asset Category @else فئة الأصل<br>Asset Category @endif</th>
            <th>@if($locale === 'ar') تاريخ الشراء @elseif($locale === 'en') Purchase Date @else تاريخ الشراء<br>Purchase Date @endif</th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['asset'] }}</td>

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

                <td>{{ $row['depreciation_date'] ?? '-' }}</td>
                <td class="number">{{ number_format($row['purchase_amount'], 2) }}</td>
                <td class="number">{{ number_format($row['opening_accumulated_depreciation'], 2) }}</td>
                <td class="number">{{ number_format($row['depreciation_amount'], 2) }}</td>
                <td class="number">{{ number_format($row['accumulated_depreciation'], 2) }}</td>
                <td class="number">{{ number_format($row['value_after_depreciation'], 2) }}</td>
                <td>{{ $row['depreciation_entry'] }}</td>

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
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="3">
                @if($locale === 'ar')
                    الإجمالي
                @elseif($locale === 'en')
                    Total
                @else
                    الإجمالي / Total
                @endif
            </td>

            <td class="number">{{ number_format($totals['purchase_amount'], 2) }}</td>
            <td class="number">{{ number_format($totals['opening_accumulated_depreciation'], 2) }}</td>
            <td class="number">{{ number_format($totals['depreciation_amount'], 2) }}</td>
            <td class="number">{{ number_format($totals['accumulated_depreciation'], 2) }}</td>
            <td class="number">{{ number_format($totals['value_after_depreciation'], 2) }}</td>
            <td colspan="3"></td>
        </tr>
        </tbody>
    </table>
@endif

</body>
</html>