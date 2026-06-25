<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: dejavusans;
            font-size: 12px;
            direction: rtl;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }

        .section-title {
            background-color: #eeeeee;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #000;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 7px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .amount {
            text-align: left;
            direction: ltr;
        }

        .total-row {
            font-weight: bold;
            background-color: #f7f7f7;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
        }

        .success {
            color: #0f7b0f;
            border-color: #0f7b0f;
        }

        .danger {
            color: #b00020;
            border-color: #b00020;
        }

        .no-data {
            text-align: center;
            padding: 25px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="title">
    تقرير الميزانية العمومية / Balance Sheet Report
</div>

<div class="subtitle">
    {{ $report['company']->company_name_ar ?? $report['company']->company_name_en ?? '' }}
    <br>
    السنة المالية:
    {{ $report['from_date'] }}
    إلى
    {{ $report['to_date'] }}
</div>

@if(! $report['has_data'])

    <div class="no-data">
        لا توجد بيانات
    </div>

@else

    <div class="section-title">
        الأصول / Assets
    </div>

    <table>
        <thead>
        <tr>
            <th>رقم الحساب</th>
            <th>الحساب</th>
            <th>القيمة</th>
        </tr>
        </thead>

        <tbody>
        @forelse($report['assets']['rows'] as $row)
            <tr>
                <td>{{ $row['account_number'] }}</td>
                <td>
                    {{ $row['account_name_ar'] ?? $row['account_name_en'] }}
                </td>
                <td class="amount">
                    {{ number_format($row['value'], 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-data">
                    لا توجد بيانات أصول
                </td>
            </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="2">
                إجمالي الأصول / Total Assets
            </td>
            <td class="amount">
                {{ number_format($report['assets']['total'], 2) }}
            </td>
        </tr>
        </tbody>
    </table>

    <div class="section-title">
        الخصوم / Liabilities
    </div>

    <table>
        <thead>
        <tr>
            <th>رقم الحساب</th>
            <th>الحساب</th>
            <th>القيمة</th>
        </tr>
        </thead>

        <tbody>
        @forelse($report['liabilities']['rows'] as $row)
            <tr>
                <td>{{ $row['account_number'] }}</td>
                <td>
                    {{ $row['account_name_ar'] ?? $row['account_name_en'] }}
                </td>
                <td class="amount">
                    {{ number_format($row['value'], 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-data">
                    لا توجد بيانات خصوم
                </td>
            </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="2">
                إجمالي الخصوم / Total Liabilities
            </td>
            <td class="amount">
                {{ number_format($report['liabilities']['total'], 2) }}
            </td>
        </tr>
        </tbody>
    </table>

    <div class="section-title">
        حقوق الملكية / Equity
    </div>

    <table>
        <thead>
        <tr>
            <th>رقم الحساب</th>
            <th>الحساب</th>
            <th>القيمة</th>
        </tr>
        </thead>

        <tbody>
        @forelse($report['equity']['rows'] as $row)
            <tr>
                <td>{{ $row['account_number'] }}</td>
                <td>
                    {{ $row['account_name_ar'] ?? $row['account_name_en'] }}
                </td>
                <td class="amount">
                    {{ number_format($row['value'], 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-data">
                    لا توجد بيانات حقوق ملكية
                </td>
            </tr>
        @endforelse

        <tr>
            <td colspan="2">
                صافي الربح / الخسارة من قائمة الدخل
                <br>
                Net Profit / Loss from Profit and Loss
            </td>
            <td class="amount">
                {{ number_format($report['equity']['net_profit_loss'], 2) }}
            </td>
        </tr>

        <tr class="total-row">
            <td colspan="2">
                إجمالي حقوق الملكية / Total Equity
            </td>
            <td class="amount">
                {{ number_format($report['equity']['total'], 2) }}
            </td>
        </tr>
        </tbody>
    </table>

    <div class="section-title">
        التحقق من المعادلة المحاسبية / Accounting Equation Check
    </div>

    <table>
        <tr>
            <td>
                إجمالي الأصول / Total Assets
            </td>
            <td class="amount">
                {{ number_format($report['equation']['assets'], 2) }}
            </td>
        </tr>

        <tr>
            <td>
                إجمالي الخصوم + إجمالي حقوق الملكية
                <br>
                Total Liabilities + Total Equity
            </td>
            <td class="amount">
                {{ number_format($report['equation']['liabilities_and_equity'], 2) }}
            </td>
        </tr>

        <tr>
            <td>
                الفرق / Difference
            </td>
            <td class="amount">
                {{ number_format($report['equation']['difference'], 2) }}
            </td>
        </tr>
    </table>

    @if($report['equation']['is_balanced'])
        <div class="message success">
            الميزانية متوازنة
            <br>
            Balance Sheet is balanced
        </div>
    @else
        <div class="message danger">
            الميزانية غير متوازنة
            <br>
            Balance Sheet is not balanced
        </div>
    @endif

@endif

</body>
</html>