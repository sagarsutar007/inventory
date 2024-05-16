<table id="material-shortage-tbl" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>PO No</th>
            <th>PO Date</th>
            <th>RM Part Code</th>
            <th>Description</th>
            <th>Make</th>
            <th>MPN</th>
            <th>PO Qty</th>
            <th>Stock Qty</th>
            <th><div title="Balance to Issue">BTI Qty</div></th>
            <th>Shortage Qty</th>
            <th>Unit</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $record)
        <tr>
            <td>{{ $record['po_number'] }}</td>
            <td>{{ $record['po_date'] }}</td>
            <td>{{ $record['part_code'] }}</td>
            <td>{{ $record['description'] }}</td>
            <td>{{ $record['make'] }}</td>
            <td>{{ $record['mpn'] }}</td>
            <td class="text-right">{{ formatQuantity($record['quantity']) }}</td>
            <td class="text-right">{{ formatQuantity($record['stock']) }}</td>
            <td class="text-right">{{ formatQuantity($record['balance']) }}</td>
            <td class="text-right">{{ formatQuantity($record['shortage']) }}</td>
            <td>{{ $record['unit'] }}</td>
        </tr>
        @endforeach
    </tbody>
    {{-- <tfoot>

    </tfoot> --}}
</table>