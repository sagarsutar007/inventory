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
        @php 
            $totalQty= 0;
            $totalStock= 0;
            $totalBalance= 0;
            $totalShortage= 0;
        @endphp
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
            @php
                $totalQty += $record['quantity'];
                $totalStock = $record['stock'];
                $totalBalance += $record['balance'];
                $totalShortage = $totalBalance - $totalStock;
            @endphp
            @endforeach
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="text-right">{{ formatQuantity($totalQty) }}</th>
                <th class="text-right">{{ formatQuantity($totalStock) }}</th>
                <th class="text-right">{{ formatQuantity($totalBalance) }}</th>
                <th class="text-right">{{ formatQuantity($totalShortage) }}</th>
                <th>{{ $record['unit'] }}</th>
            </tr>
    </tbody>
    {{-- <tfoot>

    </tfoot> --}}
</table>