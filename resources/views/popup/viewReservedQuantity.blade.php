<table id="view-shortage-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>PO Number</th>
            <th>Status</th>
            <th>FG Code</th>
            <th>Description</th>
            <th>Type</th>
            <th>Issued Qty</th>
            <th>Quantity</th>
            <th>Unit</th>
        </tr>
    </thead>
    <tbody>
        @if ($record) 
        <tr>
            <td>{{ $record['po_number'] }}</td>
            <td>{{ $record['po_status'] }}</td>
            <td>{{ $record['partcode'] }}</td>
            <td>{{ $record['description'] }}</td>
            <td>{{ $record['type'] }}</td>
            <td class="text-right">{{ formatQuantity($record['issued']) }}</td>
            <td class="text-right">{{ formatQuantity($record['quantity']) }}</td>
            <td class="text-center">{{ $record['unit'] }}</td>
        </tr>
        @endif
    </tbody>
</table>