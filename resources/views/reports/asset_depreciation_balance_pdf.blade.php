<!DOCTYPE html>
<html lang="{{ $locale === 'en' ? 'en' : 'ar' }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <title>Asset Depreciation and Balance Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
            color: #111;
            direction: {{ $locale === 'en' ? 'ltr' : 'rtl' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .meta {
            text-align: center;
            font-size: 9px;
            margin-bottom: 12px;
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
            padding: 4px;
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
    <h2>تقرير إهلاك الأصول والأرصدة</h2>
@elseif($locale === 'en')
    <h2>Asset Depreciation and Balance Report</h2>
@else
    <h2>
        تقرير إهلاك الأصول والأرصدة
        <br>
        Asset Depreciation and Balance Report
    </h2>
@endif

<div class="meta">
    @if($locale === 'ar')
        من تاريخ: {{ $dateFrom }} | إلى تاريخ: {{ $dateTo }} | تاريخ الإنشاء: {{ $generatedAt }}
    @elseif($locale === 'en')
        From: {{ $dateFrom }} | To: {{ $dateTo }} | Generated At: {{ $generatedAt }}
    @else
        من تاريخ / From: {{ $dateFrom }} |
        إلى تاريخ / To: {{ $dateTo }} |
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
        <th>@if($locale === 'ar') فئة الأصل @elseif($locale === 'en') Asset Category @else فئة الأصل<br>Asset Category @endif</th>
        <th>@if($locale === 'ar') قيمة الأصل بداية الفترة @elseif($locale === 'en') Value Start @else قيمة الأصل بداية الفترة<br>Value Start @endif</th>
        <th>@if($locale === 'ar') مشتريات جديدة @elseif($locale === 'en') New Purchase @else مشتريات جديدة<br>New Purchase @endif</th>
        <th>@if($locale === 'ar') أصول مباعة @elseif($locale === 'en') Sold Asset @else أصول مباعة<br>Sold Asset @endif</th>
        <th>@if($locale === 'ar') أصول متلفة @elseif($locale === 'en') Scrapped Asset @else أصول متلفة<br>Scrapped Asset @endif</th>
        <th>@if($locale === 'ar') رسملة جديدة @elseif($locale === 'en') New Capitalization @else رسملة جديدة<br>New Capitalization @endif</th>
        <th>@if($locale === 'ar') قيمة الأصل نهاية الفترة @elseif($locale === 'en') Value End @else قيمة الأصل نهاية الفترة<br>Value End @endif</th>

        <th>@if($locale === 'ar') إهلاك متراكم بداية الفترة @elseif($locale === 'en') Acc. Dep. Start @else إهلاك متراكم بداية الفترة<br>Acc. Dep. Start @endif</th>
        <th>@if($locale === 'ar') إهلاك خلال الفترة @elseif($locale === 'en') Dep. During Period @else إهلاك خلال الفترة<br>Dep. During Period @endif</th>
        <th>@if($locale === 'ar') إهلاك مستبعد بالبيع/الإتلاف @elseif($locale === 'en') Dep. Eliminated Disposal @else إهلاك مستبعد بالبيع/الإتلاف<br>Dep. Eliminated Disposal @endif</th>
        <th>@if($locale === 'ar') إهلاك ملغى بعكس القيد @elseif($locale === 'en') Dep. Reversal @else إهلاك ملغى بعكس القيد<br>Dep. Reversal @endif</th>
        <th>@if($locale === 'ar') إهلاك متراكم نهاية الفترة @elseif($locale === 'en') Acc. Dep. End @else إهلاك متراكم نهاية الفترة<br>Acc. Dep. End @endif</th>

        <th>@if($locale === 'ar') صافي القيمة بداية الفترة @elseif($locale === 'en') Net Value Start @else صافي القيمة بداية الفترة<br>Net Value Start @endif</th>
        <th>@if($locale === 'ar') صافي القيمة نهاية الفترة @elseif($locale === 'en') Net Value End @else صافي القيمة نهاية الفترة<br>Net Value End @endif</th>
    </tr>
    </thead>

    <tbody>
    @foreach($rows as $row)
        <tr>
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

            <td class="number">{{ number_format($row['value_start'], 2) }}</td>
            <td class="number">{{ number_format($row['new_purchase'], 2) }}</td>
            <td class="number">{{ number_format($row['sold_asset'], 2) }}</td>
            <td class="number">{{ number_format($row['scrapped_asset'], 2) }}</td>
            <td class="number">{{ number_format($row['new_capitalization'], 2) }}</td>
            <td class="number">{{ number_format($row['value_end'], 2) }}</td>

            <td class="number">{{ number_format($row['accumulated_depreciation_start'], 2) }}</td>
            <td class="number">{{ number_format($row['depreciation_during_period'], 2) }}</td>
            <td class="number">{{ number_format($row['depreciation_eliminated_disposal'], 2) }}</td>
            <td class="number">{{ number_format($row['depreciation_eliminated_reversal'], 2) }}</td>
            <td class="number">{{ number_format($row['accumulated_depreciation_end'], 2) }}</td>

            <td class="number">{{ number_format($row['net_asset_value_start'], 2) }}</td>
            <td class="number">{{ number_format($row['net_asset_value_end'], 2) }}</td>
        </tr>
    @endforeach

    <tr class="total-row">
        <td>@if($locale === 'ar') الإجمالي @elseif($locale === 'en') Total @else الإجمالي / Total @endif</td>

        <td class="number">{{ number_format($totals['value_start'], 2) }}</td>
        <td class="number">{{ number_format($totals['new_purchase'], 2) }}</td>
        <td class="number">{{ number_format($totals['sold_asset'], 2) }}</td>
        <td class="number">{{ number_format($totals['scrapped_asset'], 2) }}</td>
        <td class="number">{{ number_format($totals['new_capitalization'], 2) }}</td>
        <td class="number">{{ number_format($totals['value_end'], 2) }}</td>

        <td class="number">{{ number_format($totals['accumulated_depreciation_start'], 2) }}</td>
        <td class="number">{{ number_format($totals['depreciation_during_period'], 2) }}</td>
        <td class="number">{{ number_format($totals['depreciation_eliminated_disposal'], 2) }}</td>
        <td class="number">{{ number_format($totals['depreciation_eliminated_reversal'], 2) }}</td>
        <td class="number">{{ number_format($totals['accumulated_depreciation_end'], 2) }}</td>

        <td class="number">{{ number_format($totals['net_asset_value_start'], 2) }}</td>
        <td class="number">{{ number_format($totals['net_asset_value_end'], 2) }}</td>
    </tr>
    </tbody>
</table>
@endif

</body>
</html>