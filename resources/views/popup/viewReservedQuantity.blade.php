<table id="view-shortage-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>PO Number</th>
            <th>Status</th>
            <th>FG Code</th>
            <th>Description</th>
            <th>Unit</th>
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
            <td>{{ $record['unit'] }}</td>
            <td>{{ $record['type'] }}</td>
            <td>{{ number_format($record['issued'], 3) }}</td>
            <td>{{ number_format($record['quantity'], 3) }}</td>
            <td>{{ $record['unit'] }}</td>
        </tr>
        @endif
    </tbody>
</table>