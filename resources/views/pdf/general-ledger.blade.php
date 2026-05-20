<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'IBM Plex Sans', 'Tajawal', system-ui, sans-serif; font-size: 13px; color: #1a1a1a; background: #f0f4f8; padding: 32px; }
    .gl { background: #fff; border-radius: 10px; overflow: hidden; max-width: 1200px; margin: 0 auto; }

    .gl-top { background: #0C447C; padding: 20px 24px; display: flex; justify-content: space-between; align-items: flex-end; }
    .gl-top .lbl { font-size: 10px; letter-spacing: .05em; text-transform: uppercase; color: #85B7EB; font-weight: 600; margin-bottom: 4px; }
    .gl-top .acc-name { font-size: 20px; font-weight: 500; color: #fff; }
    .gl-top .acc-no { font-size: 12px; color: #85B7EB; margin-top: 3px; font-family: monospace; direction: ltr; display: inline-block; }
    .gl-top-right { text-align: end; }
    .gl-top-right .date-lbl { font-size: 10px; color: #85B7EB; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 2px; }
    .gl-top-right .date-val { font-size: 12px; color: #E6F1FB; direction: ltr; display: inline-block; }

    .gl-meta { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #dde5ef; }
    .gl-meta-item { padding: 12px 16px; border-inline-end: 1px solid #dde5ef; }
    .gl-meta-item:last-child { border-inline-end: none; }
    .gl-meta-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #888; font-weight: 600; margin-bottom: 3px; }
    .gl-meta-val { font-size: 13px; font-weight: 500; color: #1a1a1a; }

    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    thead tr { background: #0C447C; }
    thead th { padding: 10px 8px; text-align: start; font-size: 10px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #B5D4F4; white-space: nowrap; }
    thead th.r { text-align: end; }
    tbody tr { border-bottom: 1px solid #dde5ef; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:nth-child(even) td { background: #F9FBFD; }
    tbody td { padding: 9px 8px; color: #1a1a1a; vertical-align: top; word-break: break-word; }
    tbody td.r { text-align: end; font-family: monospace; font-size: 12px; white-space: nowrap; direction: ltr; }

    .dt { color: #555; font-size: 11.5px; white-space: nowrap; direction: ltr; display: inline-block; }
    .vc { font-family: monospace; font-size: 11.5px; color: #555; direction: ltr; display: inline-block; }
    .usr { font-size: 11.5px; color: #888; }

    .account-cell .acc-num { font-family: monospace; font-size: 11px; color: #0C447C; direction: ltr; display: inline-block; }
    .account-cell .acc-name { font-size: 11.5px; color: #1a1a1a; margin-top: 2px; display: block; }

    .badge { font-size: 10px; padding: 2px 8px; border-radius: 3px; font-weight: 600; display: inline-block; white-space: nowrap; }
    .b-j { background: #E6F1FB; color: #0C447C; }
    .b-p { background: #FDF0D5; color: #633806; }
    .b-r { background: #E1F5EE; color: #085041; }
    .b-d { background: #F0F0EE; color: #555; }

    .dv { color: #185FA5; }
    .cv { color: #0F6E56; }
    .bv { font-weight: 500; color: #0C447C; }
    .dash { color: #ccc; }

    .total-row td { background: #E6F1FB; border-top: 2px solid #0C447C; font-weight: 600; font-family: monospace; color: #0C447C; padding: 11px 8px; direction: ltr; text-align: end; }
    .total-row .tl { font-family: 'Tajawal', system-ui, sans-serif; font-size: 11px; color: #185FA5; font-weight: 600; direction: inherit; text-align: start; }

    .gl-cards { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 2px solid #dde5ef; }
    .gl-card { padding: 14px 16px; border-inline-end: 1px solid #dde5ef; background: #F5F8FC; }
    .gl-card:last-child { border-inline-end: none; }
    .gl-card-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #888; font-weight: 600; margin-bottom: 4px; }
    .gl-card-val { font-size: 17px; font-weight: 500; color: #0C447C; font-family: monospace; direction: ltr; display: inline-block; }
    .gl-card-val.dr { color: #185FA5; }
    .gl-card-val.cr { color: #0F6E56; }

    .closing { display: flex; justify-content: space-between; align-items: center; background: #0C447C; padding: 13px 20px; }
    .cl-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #85B7EB; font-weight: 600; }
    .cl-val { font-family: monospace; font-size: 16px; font-weight: 500; color: #fff; direction: ltr; }
  </style>
</head>
<body>

<div class="gl">

  <div class="gl-top">
    <div>
      <div class="lbl">
        {{ app()->getLocale() === 'ar' ? 'دفتر الأستاذ العام' : 'General Ledger' }}
      </div>
      <div class="acc-name">
        {{ app()->getLocale() === 'ar' ? $report['account']['name_ar'] : $report['account']['name_en'] }}
      </div>
      <div class="acc-no">{{ $report['account']['account_number'] }}</div>
    </div>

    <div class="gl-top-right">
      <div class="date-lbl">
        {{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}
      </div>
      <div class="date-val">
        {{ $report['filters']['from_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
        —
        {{ $report['filters']['to_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
      </div>
    </div>
  </div>

  <div class="gl-meta">
    <div class="gl-meta-item">
      <div class="gl-meta-lbl">{{ app()->getLocale() === 'ar' ? 'نوع الحساب' : 'Account type' }}</div>
      <div class="gl-meta-val">{{ $report['account']['account_type'] }}</div>
    </div>

    <div class="gl-meta-item">
      <div class="gl-meta-lbl">{{ app()->getLocale() === 'ar' ? 'التصنيف الرئيسي' : 'Root category' }}</div>
      <div class="gl-meta-val">{{ $report['account']['root_category'] }}</div>
    </div>

    <div class="gl-meta-item">
      <div class="gl-meta-lbl">{{ app()->getLocale() === 'ar' ? 'التصنيف الفرعي' : 'Sub category' }}</div>
      <div class="gl-meta-val">{{ $report['account']['sub_category'] ?? '—' }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:82px">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
        <th style="width:88px">{{ app()->getLocale() === 'ar' ? 'رقم القيد' : 'Voucher' }}</th>
        <th style="width:82px">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
        <th style="width:135px">{{ app()->getLocale() === 'ar' ? 'الحساب' : 'Account' }}</th>
        <th style="width:95px">{{ app()->getLocale() === 'ar' ? 'أنشئ بواسطة' : 'Created by' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
        <th class="r" style="width:88px">{{ app()->getLocale() === 'ar' ? 'مدين' : 'Debit' }}</th>
        <th class="r" style="width:88px">{{ app()->getLocale() === 'ar' ? 'دائن' : 'Credit' }}</th>
        <th class="r" style="width:96px">{{ app()->getLocale() === 'ar' ? 'الرصيد' : 'Balance' }}</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($report['rows'] as $row)
        @php
          $t = strtolower($row['voucher_type'] ?? '');

          $bc = str_contains($t, 'journal') ? 'b-j'
              : (str_contains($t, 'payment') ? 'b-p'
              : (str_contains($t, 'receipt') ? 'b-r' : 'b-d'));

          $typeLabel = app()->getLocale() === 'ar'
            ? match(true) {
                str_contains($t, 'journal') => 'قيد يومية',
                str_contains($t, 'payment') => 'سند صرف',
                str_contains($t, 'receipt') => 'سند قبض',
                default => $row['voucher_type'],
              }
            : $row['voucher_type'];

          $rowAccountName = app()->getLocale() === 'ar'
              ? ($row['account']['name_ar'] ?? '—')
              : ($row['account']['name_en'] ?? '—');
        @endphp

        <tr>
          <td><span class="dt">{{ $row['date'] }}</span></td>

          <td><span class="vc">{{ $row['voucher_no'] }}</span></td>

          <td>
            <span class="badge {{ $bc }}">{{ $typeLabel }}</span>
          </td>

          <td class="account-cell">
            <span class="acc-num">{{ $row['account']['account_number'] ?? '—' }}</span>
            <span class="acc-name">{{ $rowAccountName }}</span>
          </td>

          <td class="usr">{{ $row['created_by']['name'] ?? '—' }}</td>

          <td>{{ $row['description'] }}</td>

          <td class="r dv">{!! $row['debit'] ?: '<span class="dash">—</span>' !!}</td>

          <td class="r cv">{!! $row['credit'] ?: '<span class="dash">—</span>' !!}</td>

          <td class="r bv">{{ $row['balance'] }}</td>
        </tr>
      @endforeach

      <tr class="total-row">
        <td class="tl" colspan="6">
          {{ app()->getLocale() === 'ar' ? 'المجموع الكلي' : 'Grand total' }}
        </td>
        <td class="r dv">{{ $report['grand_total']['total_debit'] }}</td>
        <td class="r cv">{{ $report['grand_total']['total_credit'] }}</td>
        <td class="r">{{ $report['closing_balance']['display'] }}</td>
      </tr>
    </tbody>
  </table>

  <div class="gl-cards">
    <div class="gl-card">
      <div class="gl-card-lbl">{{ app()->getLocale() === 'ar' ? 'الرصيد الافتتاحي' : 'Opening balance' }}</div>
      <div class="gl-card-val">{{ $report['opening_balance']['display'] }}</div>
    </div>

    <div class="gl-card">
      <div class="gl-card-lbl">{{ app()->getLocale() === 'ar' ? 'إجمالي المدين' : 'Total debit' }}</div>
      <div class="gl-card-val dr">{{ $report['grand_total']['total_debit'] }}</div>
    </div>

    <div class="gl-card">
      <div class="gl-card-lbl">{{ app()->getLocale() === 'ar' ? 'إجمالي الدائن' : 'Total credit' }}</div>
      <div class="gl-card-val cr">{{ $report['grand_total']['total_credit'] }}</div>
    </div>
  </div>

  <div class="closing">
    <span class="cl-lbl">{{ app()->getLocale() === 'ar' ? 'الرصيد الختامي' : 'Closing balance' }}</span>
    <span class="cl-val">{{ $report['closing_balance']['display'] }}</span>
  </div>

</div>

</body>
</html>