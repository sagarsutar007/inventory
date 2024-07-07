<table id="view-shortage-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>PO Number</th>
            <th>Status</th>
            <th>FG Code</th>
            <th>Description</th>
            <th>Unit</th>
            <th>Bom Qty</th>
            <th>QPA</th>
            <th>Issued Qty</th>
            <th>Quantity</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sumQpa = 0;
            $sumQtyIssued = 0;
            $sumReservedQty = 0;
        @endphp

        @if ($records)
        @foreach($records as $record)
        @php
            $sumQpa += $record['qpa'];
            $sumQtyIssued += $record['qty_issued'];
            $sumReservedQty += $record['reserved_qty'];
        @endphp
        <tr>
            <td>{{ $record['po_number'] }}</td>
            <td>{{ $record['status'] }}</td>
            <td>{{ $record['part_code'] }}</td>
            <td>{{ $record['description'] }}</td>
            <td>{{ $record['uom'] }}</td>
            <td class="text-right">{{ formatQuantity($record['bom_qty']) }}</td>
            <td class="text-right">{{ formatQuantity($record['qpa']) }}</td>
            <td class="text-right">{{ formatQuantity($record['qty_issued']) }}</td>
            <td class="text-right">{{ formatQuantity($record['reserved_qty']) }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right"><strong>{{ formatQuantity($sumQpa) }}</strong></td>
            <td class="text-right"><strong>{{ formatQuantity($sumQtyIssued) }}</strong></td>
            <td class="text-right"><strong>{{ formatQuantity($sumReservedQty) }}</strong></td>
        </tr>
        @endif
    </tbody>
</table>
