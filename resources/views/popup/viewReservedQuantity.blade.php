<table id="view-shortage-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>Partcode</th>
            <th>Description</th>
            <th>Make</th>
            <th>MPN</th>
            <th>Category</th>
            <th>Commodity</th>
            <th>Unit</th>
            <th>Reserved Qty</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $record['partcode'] }}</td>
            <td>{{ $record['description'] }}</td>
            <td>{{ $record['make'] }}</td>
            <td>{{ $record['mpn'] }}</td>
            <td>{{ $record['category'] }}</td>
            <td>{{ $record['commodity'] }}</td>
            <td>{{ $record['unit'] }}</td>
            <td>{{ number_format($record['quantity'], 3) }}</td>
        </tr>
    </tbody>
</table>