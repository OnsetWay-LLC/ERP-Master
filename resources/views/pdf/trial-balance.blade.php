<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'IBM Plex Sans', 'Tajawal', system-ui, sans-serif;
      font-size: 13px;
      color: #1a1a1a;
      background: #f0f4f8;
      padding: 32px;
      direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
      text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
    }

    .tb {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      max-width: 1000px;
      margin: 0 auto;
      direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
    }

    .tb-top {
      background: #0C447C;
      padding: 20px 24px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .tb-top .lbl {
      font-size: 10px;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #85B7EB;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .tb-top .title {
      font-size: 20px;
      font-weight: 500;
      color: #fff;
    }

    .tb-top-right {
      text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};
    }

    .tb-top-right .date-lbl {
      font-size: 10px;
      color: #85B7EB;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .tb-top-right .date-val {
      font-size: 12px;
      color: #E6F1FB;
      direction: ltr;
      display: inline-block;
    }

    .tb-status {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 8px 16px;
      background: #F5F8FC;
      border-bottom: 1px solid #dde5ef;
      gap: 8px;
    }

    .tb-status .status-lbl {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #888;
      font-weight: 600;
    }

    .status-badge {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 12px;
      border-radius: 4px;
      display: inline-block;
    }

    .status-balanced { background: #E1F5EE; color: #085041; }
    .status-unbalanced { background: #FDE8E8; color: #7A1C1C; }

    table {
      width: 100%;
      border-collapse: collapse;
      direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
    }

    thead tr { background: #0C447C; }

    thead th {
      padding: 10px 12px;
      text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #B5D4F4;
      white-space: nowrap;
    }

    thead th.right {
      text-align: right;
    }

    tbody tr { border-bottom: 1px solid #dde5ef; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:nth-child(even) td { background: #F9FBFD; }

    tbody td {
      padding: 9px 12px;
      color: #1a1a1a;
      vertical-align: middle;
      word-break: break-word;
      text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
    }

    tbody td.right {
      text-align: right;
      font-family: monospace;
      font-size: 12px;
      white-space: nowrap;
      direction: ltr;
    }

    tbody td.acc-no {
      font-family: monospace;
      font-size: 11.5px;
      color: #555;
      direction: ltr;
      text-align: left;
    }

    .ltr-cell {
      direction: ltr;
      text-align: left !important;
    }

    .rtl-cell {
      direction: rtl;
      text-align: right !important;
    }

    .dv { color: #185FA5; }
    .cv { color: #0F6E56; }
    .dash { color: #ccc; }

    .total-row td {
      background: #E6F1FB;
      border-top: 2px solid #0C447C;
      font-weight: 600;
      font-family: monospace;
      color: #0C447C;
      padding: 11px 12px;
    }

    .total-row .total-label {
      font-family: 'Tajawal', system-ui, sans-serif;
      font-size: 11px;
      color: #185FA5;
      font-weight: 600;
      text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
    }

    .tb-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      border-top: 2px solid #dde5ef;
    }

    .tb-card {
      padding: 14px 16px;
      background: #F5F8FC;
      border-right: {{ app()->getLocale() === 'ar' ? 'none' : '1px solid #dde5ef' }};
      border-left: {{ app()->getLocale() === 'ar' ? '1px solid #dde5ef' : 'none' }};
    }

    .tb-card:last-child {
      border-right: none;
      border-left: none;
    }

    .tb-card-lbl {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #888;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .tb-card-val {
      font-size: 17px;
      font-weight: 500;
      font-family: monospace;
      direction: ltr;
      display: inline-block;
      color: #0C447C;
    }

    .tb-card-val.dr { color: #185FA5; }
    .tb-card-val.cr { color: #0F6E56; }
    .tb-card-val.diff-ok { color: #0F6E56; }
    .tb-card-val.diff-err { color: #B91C1C; }

    .closing {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #0C447C;
      padding: 13px 20px;
    }

    .cl-lbl {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #85B7EB;
      font-weight: 600;
    }

    .cl-val {
      font-family: monospace;
      font-size: 15px;
      font-weight: 500;
      direction: ltr;
    }

    .cl-val.balanced { color: #6EE7C0; }
    .cl-val.unbalanced { color: #FCA5A5; }
  </style>
</head>

<body>

<div class="tb">

  <div class="tb-top">
    <div class="tb-top-left">
      <div class="lbl">
        {{ app()->getLocale() === 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}
      </div>
      <div class="title">
        {{ app()->getLocale() === 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}
      </div>
    </div>

    <div class="tb-top-right">
      <div class="date-lbl">
        {{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}
      </div>
      <div class="date-val">
        {{ $report['from_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
        —
        {{ $report['to_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
      </div>
    </div>
  </div>

  <div class="tb-status">
    <span class="status-lbl">
      {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
    </span>

    @if($report['is_balanced'] ?? true)
      <span class="status-badge status-balanced">
        {{ app()->getLocale() === 'ar' ? '✓ متوازن' : '✓ Balanced' }}
      </span>
    @else
      <span class="status-badge status-unbalanced">
        {{ app()->getLocale() === 'ar' ? '✗ غير متوازن' : '✗ Not Balanced' }}
      </span>
    @endif
  </div>

  <table>
    <thead>
      <tr>
        @if(app()->getLocale() === 'ar')
          <th style="width:110px">رقم الحساب</th>
          <th>اسم الحساب عربي</th>
          <th class="ltr-cell">اسم الحساب إنجليزي</th>
          <th class="right" style="width:110px">مدين</th>
          <th class="right" style="width:110px">دائن</th>
        @else
          <th style="width:110px">Account No</th>
          <th>Account Name AR</th>
          <th class="ltr-cell">Account Name EN</th>
          <th class="right" style="width:110px">Debit</th>
          <th class="right" style="width:110px">Credit</th>
        @endif
      </tr>
    </thead>

    <tbody>
      @forelse($report['rows'] ?? [] as $row)
        <tr>
          @if(app()->getLocale() === 'ar')
            <td class="acc-no">{{ $row['account_number'] ?? '' }}</td>

            <td class="rtl-cell">
              {{ $row['account_name_ar'] ?? '' }}
            </td>

            <td class="ltr-cell" style="font-size:12px; color:#444;">
              {{ $row['account_name_en'] ?? '' }}
            </td>

            <td class="right dv">
              {!! ($row['debit_balance'] ?? 0)
                  ? number_format($row['debit_balance'], 2)
                  : '<span class="dash">—</span>' !!}
            </td>

            <td class="right cv">
              {!! ($row['credit_balance'] ?? 0)
                  ? number_format($row['credit_balance'], 2)
                  : '<span class="dash">—</span>' !!}
            </td>
          @else
            <td class="acc-no">{{ $row['account_number'] ?? '' }}</td>

            <td class="rtl-cell">
              {{ $row['account_name_ar'] ?? '' }}
            </td>

            <td class="ltr-cell" style="font-size:12px; color:#444;">
              {{ $row['account_name_en'] ?? '' }}
            </td>

            <td class="right dv">
              {!! ($row['debit_balance'] ?? 0)
                  ? number_format($row['debit_balance'], 2)
                  : '<span class="dash">—</span>' !!}
            </td>

            <td class="right cv">
              {!! ($row['credit_balance'] ?? 0)
                  ? number_format($row['credit_balance'], 2)
                  : '<span class="dash">—</span>' !!}
            </td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="5" style="text-align:center; color:#888; padding:24px;">
            {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات' : 'No data found' }}
          </td>
        </tr>
      @endforelse

      <tr class="total-row">
        @if(app()->getLocale() === 'ar')
          <td class="total-label" colspan="3">المجموع الكلي</td>
          <td class="right dv">{{ number_format($report['totals']['total_debit'] ?? 0, 2) }}</td>
          <td class="right cv">{{ number_format($report['totals']['total_credit'] ?? 0, 2) }}</td>
        @else
          <td class="total-label" colspan="3">Grand Total</td>
          <td class="right dv">{{ number_format($report['totals']['total_debit'] ?? 0, 2) }}</td>
          <td class="right cv">{{ number_format($report['totals']['total_credit'] ?? 0, 2) }}</td>
        @endif
      </tr>
    </tbody>
  </table>

  <div class="tb-cards">
    <div class="tb-card">
      <div class="tb-card-lbl">
        {{ app()->getLocale() === 'ar' ? 'إجمالي المدين' : 'Total Debit' }}
      </div>
      <div class="tb-card-val dr">
        {{ number_format($report['totals']['total_debit'] ?? 0, 2) }}
      </div>
    </div>

    <div class="tb-card">
      <div class="tb-card-lbl">
        {{ app()->getLocale() === 'ar' ? 'إجمالي الدائن' : 'Total Credit' }}
      </div>
      <div class="tb-card-val cr">
        {{ number_format($report['totals']['total_credit'] ?? 0, 2) }}
      </div>
    </div>

    <div class="tb-card">
      <div class="tb-card-lbl">
        {{ app()->getLocale() === 'ar' ? 'الفرق' : 'Difference' }}
      </div>
      <div class="tb-card-val {{ ($report['is_balanced'] ?? true) ? 'diff-ok' : 'diff-err' }}">
        {{ number_format($report['totals']['difference'] ?? 0, 2) }}
      </div>
    </div>
  </div>

  <div class="closing">
    <span class="cl-lbl">
      {{ app()->getLocale() === 'ar' ? 'الحالة النهائية' : 'Final Status' }}
    </span>

    <span class="cl-val {{ ($report['is_balanced'] ?? true) ? 'balanced' : 'unbalanced' }}">
      @if($report['is_balanced'] ?? true)
        {{ app()->getLocale() === 'ar' ? '✓ الميزان متوازن' : '✓ Trial Balance is Balanced' }}
      @else
        {{ app()->getLocale() === 'ar' ? '✗ الميزان غير متوازن' : '✗ Trial Balance is Not Balanced' }}
      @endif
    </span>
  </div>

</div>

</body>
</html>