<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1a2a3a;
            background: #f0f7ff;
            padding: 28px;
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, #0D2E5A 60%, #1565C0 100%);
            color: white;
            padding: 0;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px rgba(13,46,90,0.18);
        }

        .header-inner {
            padding: 22px 30px 18px;
            position: relative;
            z-index: 2;
        }

        .header::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 38%;
            background: linear-gradient(135deg, transparent 30%, rgba(33,150,243,0.22) 100%);
            clip-path: polygon(18% 0, 100% 0, 100% 100%, 0% 100%);
            z-index: 1;
        }

        .header h2 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .header h4 {
            font-size: 13px;
            font-weight: 400;
            opacity: 0.78;
            letter-spacing: 0.3px;
        }

        /* ── META BAR ── */
        .meta-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }

        .meta-card {
            background: white;
            border-radius: 7px;
            padding: 10px 16px;
            border-left: 3px solid #2196F3;
            box-shadow: 0 1px 6px rgba(13,46,90,0.07);
            flex: 1;
        }

        .meta-card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1565C0;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .meta-card .value {
            font-size: 12px;
            font-weight: 600;
            color: #0D2E5A;
        }

        /* ── MAIN TABLE ── */
        .table-wrap {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(13,46,90,0.09);
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #0D2E5A;
        }

        th {
            color: white;
            padding: 11px 10px;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.3px;
            border: none;
        }

        th .title {
            display: block;
            font-size: 8.5px;
            opacity: 0.65;
            font-weight: 400;
            margin-top: 2px;
            letter-spacing: 0.2px;
        }

        tbody tr {
            border-bottom: 1px solid #e8f0f9;
        }

        tbody tr:nth-child(even) {
            background: #f5f9ff;
        }

        tbody tr:hover {
            background: #deeeff;
        }

        td {
            padding: 9px 14px;
            font-size: 10.5px;
            color: #1a2a3a;
            border: none;
        }

        td .ar {
            display: block;
            font-size: 9px;
            color: #5a85b0;
            margin-top: 2px;
        }

        td.number {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-family: 'Courier New', monospace;
            font-size: 10.5px;
            font-weight: 600;
            color: #0D2E5A;
        }

        /* ── SUMMARY TABLE ── */
        .summary-wrap {
            width: 50%;
            margin-left: auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(13,46,90,0.09);
            border-top: 3px solid #1565C0;
        }

        .summary-wrap table thead tr {
            background: #1565C0;
        }

        .summary-wrap tbody tr {
            border-bottom: 1px solid #e8f0f9;
        }

        .summary-wrap tbody tr:nth-child(even) {
            background: #f5f9ff;
        }

        .summary-wrap tbody tr:last-child {
            background: #0D2E5A !important;
        }

        .summary-wrap tbody tr:last-child td {
            color: white !important;
            font-size: 12px;
        }

        .summary-wrap tbody td {
            padding: 10px 14px;
            font-size: 10.5px;
            font-weight: 600;
            color: #0D2E5A;
            border: none;
        }

        .summary-wrap tbody td .ar {
            display: block;
            font-size: 8.5px;
            opacity: 0.65;
            font-weight: 400;
            margin-top: 2px;
        }

        .profit-positive {
            color: #0a6e2e;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-inner">
        <h2>Statement of Owner's Equity</h2>
        <h4>تقرير حقوق الملكية</h4>
    </div>
</div>

<!-- META BAR -->
<div class="meta-bar">
    <div class="meta-card">
        <div class="label">Financial Year | السنة المالية</div>
        <div class="value">{{ $report['financial_year'] }}</div>
    </div>
    <div class="meta-card">
        <div class="label">From | من</div>
        <div class="value">{{ $report['from_date'] }}</div>
    </div>
    <div class="meta-card">
        <div class="label">To | إلى</div>
        <div class="value">{{ $report['to_date'] }}</div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="table-wrap">
<table>
<thead>
<tr>
    <th style="text-align:left; width:70%">
        Account
        <span class="title">الحساب</span>
    </th>
    <th style="width:30%">
        Value
        <span class="title">القيمة</span>
    </th>
</tr>
</thead>
<tbody>
    @foreach($report['rows'] as $row)
    <tr>
        <td>
            {{ $row['account'] }}
            <span class="ar">{{ $row['account_ar'] }}</span>
        </td>
        <td class="number">{{ number_format($row['value'], 2) }}</td>
    </tr>
    @endforeach
</tbody>
</table>
</div>

<!-- SUMMARY TABLE -->
<div class="summary-wrap">
<table>
<thead>
<tr>
    <th style="text-align:left">Summary <span class="title">الملخص</span></th>
    <th>Value <span class="title">القيمة</span></th>
</tr>
</thead>
<tbody>
<tr>
    <td>Capital <span class="ar">رأس المال</span></td>
    <td class="number">{{ number_format($report['capital'], 2) }}</td>
</tr>
<tr>
    <td>Net Profit / Loss <span class="ar">صافي الربح / الخسارة</span></td>
    <td class="number profit-positive">{{ number_format($report['net_result'], 2) }}</td>
</tr>
<tr>
    <td>Drawings <span class="ar">السحوبات الشخصية</span></td>
    <td class="number">{{ number_format($report['drawings'], 2) }}</td>
</tr>
<tr>
    <td>Ending Owner's Equity <span class="ar">حقوق الملكية النهائية</span></td>
    <td class="number">{{ number_format($report['ending_equity'], 2) }}</td>
</tr>
</tbody>
</table>
</div>

</body>
</html>