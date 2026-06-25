
<style>
    body{
        font-family: dejavusans;
        font-size:8px;
        direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
    }

    h2{
        text-align:center;
        margin-bottom:10px;
    }

    table{
        width:100%;
        border-collapse:collapse;
        border:1px solid #000;
    }

    thead{
        display:table-header-group;
    }

    tfoot{
        display:table-footer-group;
    }

    th{
        border:1px solid #000;
        background:#eeeeee;
        padding:5px;
        text-align:center;
        font-weight:bold;
    }

    td{
        border:1px solid #000;
        padding:4px;
        text-align:center;
        vertical-align:middle;
    }

    tbody tr:nth-child(even){
        background:#fafafa;
    }

    .total-row{
        background:#d9d9d9;
        font-weight:bold;
    }

    .no-data{
        text-align:center;
        padding:20px;
        font-weight:bold;
    }
</style>
<table>

    <thead>
        <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Item Group</th>
            <th>Invoice</th>
            <th>Posting Date</th>
            <th>Supplier</th>
            <th>Payable Account</th>
            <th>Mode Of Payment</th>
            <th>Purchase Order</th>
            <th>Purchase Receipt</th>
            <th>Expenses Account</th>
            <th>Stock Qty</th>
            <th>Rate</th>
            <th>Amount</th>
            <th>Tax Rate</th>
            <th>VAT Amount</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>

    @forelse($rows as $row)

        <tr>
            <td>{{ $row['item_code'] }}</td>
            <td>{{ $row['item_name'] }}</td>
            <td>{{ $row['item_group'] }}</td>
            <td>{{ $row['invoice'] }}</td>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $row['supplier_name'] }}</td>
            <td>{{ $row['payable_account'] }}</td>
            <td>{{ $row['mode_of_payment'] }}</td>
            <td>{{ $row['purchase_order_no'] }}</td>
            <td>{{ $row['purchase_receipt_no'] }}</td>
            <td>{{ $row['expenses_account'] }}</td>

            <td>{{ number_format($row['stock_qty'],2) }}</td>
            <td>{{ number_format($row['rate'],2) }}</td>
            <td>{{ number_format($row['amount'],2) }}</td>
            <td>{{ number_format($row['tax_rate'],2) }}</td>
            <td>{{ number_format($row['vat_amount'],2) }}</td>
            <td>{{ number_format($row['total'],2) }}</td>
        </tr>

    @empty

        <tr>
            <td colspan="17" class="no-data">
                {{ app()->getLocale() === 'ar'
                    ? 'لا توجد بيانات'
                    : 'No Data Available'
                }}
            </td>
        </tr>

    @endforelse

    </tbody>

    <tfoot>

        <tr class="total-row">
            <td colspan="11">Total</td>

            <td>{{ number_format($totals['stock_qty'],2) }}</td>
            <td>{{ number_format($totals['rate'],2) }}</td>
            <td>{{ number_format($totals['amount'],2) }}</td>
            <td>{{ number_format($totals['tax_rate'],2) }}</td>
            <td>{{ number_format($totals['vat_amount'],2) }}</td>
            <td>{{ number_format($totals['total'],2) }}</td>
        </tr>

    </tfoot>

</table>