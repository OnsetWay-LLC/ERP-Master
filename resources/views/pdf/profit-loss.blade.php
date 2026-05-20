<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: dejavusans, sans-serif;
      font-size: 13px;
      color: #1a1a1a;
      background: #f0f4f8;
      padding: 32px;
    }

    /* === نظام التوجيه الديناميكي === */
    .lang-ar { direction: rtl; text-align: right; }
    .lang-en { direction: ltr; text-align: left; }

    .pl {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      max-width: 1000px;
      margin: 0 auto;
    }

    .pl-top {
      background: #0C447C;
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .pl-top .lbl {
      font-size: 10px;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #85B7EB;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .pl-top .title {
      font-size: 20px;
      font-weight: 500;
      color: #fff;
    }

    .pl-top-right {
      /* الاتجاه يتبع الأب تلقائياً */
    }

    .pl-top-right .date-lbl,
    .pl-top-right .fy {
      font-size: 10px;
      color: #85B7EB;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .pl-top-right .fy { margin-top: 4px; margin-bottom: 0; }

    .pl-top-right .date-val,
    .pl-top-right .fy {
      font-size: 12px;
      color: #E6F1FB;
      direction: ltr;
      display: inline-block;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      border-bottom: 1px solid #dde5ef;
    }

    .section-header.income { background: #EAF5F0; border-inline-start: 4px solid #0F6E56; }
    .section-header.expense { background: #EBF2FB; border-inline-start: 4px solid #185FA5; }

    .section-header .sec-icon { font-size: 14px; }
    .section-header .sec-title { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .section-header.income .sec-title { color: #085041; }
    .section-header.expense .sec-title { color: #0C447C; }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; }

    thead tr { background: #0C447C; }
    thead th {
      padding: 9px 12px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #B5D4F4;
      white-space: nowrap;
      /* يتبع اتجاه الجدول */
    }

    .income-head tr { background: #0F6E56; }
    .income-head th { color: #A7E8D4; }
    .expense-head tr { background: #185FA5; }
    .expense-head th { color: #B5D4F4; }

    tbody tr { border-bottom: 1px solid #dde5ef; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:nth-child(even) td { background: #F9FBFD; }

    tbody td {
      padding: 9px 12px;
      color: #1a1a1a;
      vertical-align: middle;
      word-break: break-word;
      /* يتبع اتجاه الجدول */
    }

    /* الخلايا التي تحتوي أرقام أو إنجليزي تبقى دائماً من اليسار للحفاظ على التنسيق المحاسبي */
    tbody td.r, tbody td.acc-no, tbody td.en-name {
      direction: ltr;
      text-align: left;
    }
    tbody td.acc-no { font-family: monospace; font-size: 11.5px; color: #555; }
    tbody td.r { font-family: monospace; font-size: 12px; white-space: nowrap; }
    tbody td.en-name { font-size: 12px; color: #444; }

    .cv { color: #0F6E56; }
    .dv { color: #185FA5; }

    .total-row-income td, .total-row-expense td {
      font-weight: 600;
      font-family: monospace;
      padding: 11px 12px;
      border-top: 2px solid currentColor;
    }
    .total-row-income td { background: #D8F2E9; border-color: #0F6E56; color: #085041; }
    .total-row-expense td { background: #DDEAF8; border-color: #185FA5; color: #0C447C; }

    .total-row-income .tl, .total-row-expense .tl {
      font-family: dejavusans, sans-serif;
      font-size: 11px;
      font-weight: 600;
      text-align: right; /* يبقى النص على الجهة الصحيحة */
    }
    .total-row-income .tl { color: #0F6E56; }
    .total-row-expense .tl { color: #185FA5; }

    .pl-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      border-top: 2px solid #dde5ef;
    }

    .pl-card {
      padding: 14px 16px;
      border-inline-start: 1px solid #dde5ef;
      background: #F5F8FC;
    }
    .pl-card:last-child { border-inline-start: none; }

    .pl-card-lbl {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #888;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .pl-card-val {
      font-size: 17px;
      font-weight: 500;
      font-family: monospace;
      direction: ltr;
      display: inline-block;
    }
    .pl-card-val.income-val { color: #0F6E56; }
    .pl-card-val.expense-val { color: #185FA5; }
    .pl-card-val.profit-val { color: #0F6E56; }
    .pl-card-val.loss-val { color: #B91C1C; }

    .closing {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 20px;
    }
    .closing.profit { background: #0F6E56; }
    .closing.loss { background: #991B1B; }

    .cl-lbl {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: rgba(255,255,255,0.65);
      font-weight: 600;
    }

    .cl-right {
      /* يتبع الاتجاه */
    }
    .cl-type {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.75);
      margin-bottom: 2px;
    }
    .cl-val {
      font-family: monospace;
      font-size: 20px;
      font-weight: 500;
      color: #fff;
      direction: ltr;
      display: inline-block;
    }
  </style>
</head>

{{-- إضافة كلاس language للـ body للتحكم في الاتجاه --}}
<body class="{{ app()->getLocale() === 'ar' ? 'lang-ar' : 'lang-en' }}">

<div class="pl">

  <div class="pl-top">
    <div>
      <div class="lbl">{{ app()->getLocale() === 'ar' ? 'قائمة الدخل' : 'Profit & Loss Statement' }}</div>
      <div class="title">{{ app()->getLocale() === 'ar' ? 'قائمة الدخل' : 'Profit & Loss Statement' }}</div>
    </div>

    <div class="pl-top-right">
      <div class="date-lbl">{{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}</div>
      <div class="date-val">
        {{ $report['from_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
        —
        {{ $report['to_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
      </div>
      @if(!empty($report['financial_year']))
        <div class="fy">
          {{ app()->getLocale() === 'ar' ? 'السنة المالية: ' : 'FY: ' }}{{ $report['financial_year'] }}
        </div>
      @endif
    </div>
  </div>

  <div class="section-header income">
    <span class="sec-icon">↑</span>
    <span class="sec-title">{{ app()->getLocale() === 'ar' ? 'الإيرادات' : 'Income' }}</span>
  </div>

  <table>
    <thead class="income-head">
      <tr>
        <th style="width:110px">{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account No.' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب عربي' : 'Account Name AR' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب إنجليزي' : 'Account Name EN' }}</th>
        <th class="r" style="width:120px">{{ app()->getLocale() === 'ar' ? 'القيمة' : 'Value' }}</th>
      </tr>
    </thead>

    <tbody>
      @forelse($report['income']['rows'] as $row)
        <tr>
          <td class="acc-no">{{ $row['account_number'] }}</td>
          <td>{{ $row['account_name_ar'] }}</td>
          <td class="en-name">{{ $row['account_name_en'] }}</td>
          <td class="r cv">{{ number_format($row['value'], 2) }}</td>
        </tr>
      @empty
        <tr><td colspan="4" style="text-align:center; color:#888; padding:20px;">{{ app()->getLocale() === 'ar' ? 'لا توجد إيرادات' : 'No income data' }}</td></tr>
      @endforelse

      <tr class="total-row-income">
        <td class="tl" colspan="3">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات' : 'Total Income' }}</td>
        <td class="r cv">{{ number_format($report['income']['total_income'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  <div class="section-header expense">
    <span class="sec-icon">↓</span>
    <span class="sec-title">{{ app()->getLocale() === 'ar' ? 'المصاريف' : 'Expenses' }}</span>
  </div>

  <table>
    <thead class="expense-head">
      <tr>
        <th style="width:110px">{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account No.' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب عربي' : 'Account Name AR' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب إنجليزي' : 'Account Name EN' }}</th>
        <th class="r" style="width:120px">{{ app()->getLocale() === 'ar' ? 'القيمة' : 'Value' }}</th>
      </tr>
    </thead>

    <tbody>
      @forelse($report['expenses']['rows'] as $row)
        <tr>
          <td class="acc-no">{{ $row['account_number'] }}</td>
          <td>{{ $row['account_name_ar'] }}</td>
          <td class="en-name">{{ $row['account_name_en'] }}</td>
          <td class="r dv">{{ number_format($row['value'], 2) }}</td>
        </tr>
      @empty
        <tr><td colspan="4" style="text-align:center; color:#888; padding:20px;">{{ app()->getLocale() === 'ar' ? 'لا توجد مصاريف' : 'No expenses data' }}</td></tr>
      @endforelse

      <tr class="total-row-expense">
        <td class="tl" colspan="3">{{ app()->getLocale() === 'ar' ? 'إجمالي المصاريف' : 'Total Expenses' }}</td>
        <td class="r dv">{{ number_format($report['expenses']['total_expenses'], 2) }}</td>
      </tr>
    </tbody>
  </table>

  @php $isProfit = $report['result_type'] === 'profit'; @endphp

  <div class="pl-cards">
    <div class="pl-card">
      <div class="pl-card-lbl">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات' : 'Total Income' }}</div>
      <div class="pl-card-val income-val">{{ number_format($report['income']['total_income'], 2) }}</div>
    </div>
    <div class="pl-card">
      <div class="pl-card-lbl">{{ app()->getLocale() === 'ar' ? 'إجمالي المصاريف' : 'Total Expenses' }}</div>
      <div class="pl-card-val expense-val">{{ number_format($report['expenses']['total_expenses'], 2) }}</div>
    </div>
    <div class="pl-card">
      <div class="pl-card-lbl">{{ app()->getLocale() === 'ar' ? 'صافي النتيجة' : 'Net Result' }}</div>
      <div class="pl-card-val {{ $isProfit ? 'profit-val' : 'loss-val' }}">{{ number_format(abs($report['net_result']), 2) }}</div>
    </div>
  </div>

  <div class="closing {{ $isProfit ? 'profit' : 'loss' }}">
    <span class="cl-lbl">{{ app()->getLocale() === 'ar' ? 'النتيجة النهائية' : 'Final Result' }}</span>
    <div class="cl-right">
      <div class="cl-type">
        @if($isProfit) {{ app()->getLocale() === 'ar' ? '▲ ربح' : '▲ Profit' }}
        @else {{ app()->getLocale() === 'ar' ? '▼ خسارة' : '▼ Loss' }} @endif
      </div>
      <div class="cl-val">{{ number_format(abs($report['net_result']), 2) }}</div>
    </div>
  </div>

</div>

</body>
</html>