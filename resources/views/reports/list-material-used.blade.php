<table id="materials-tbl" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Partcode</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Make</th>
            <th>MPN</th>
            <th>Category</th>
            <th>Commodity</th>
            <th>Stock</th>
            <th>Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($materials as $record)
        <tr>
            <td>{{ $record['part_code'] }}</td>
            <td>{{ $record['description'] }}</td>
            <td class="text-right">{{ $record['quantity'] }}</td>
            <td>{{ $record['unit'] }}</td>
            <td>{{ $record['make'] }}</td>
            <td>{{ $record['mpn'] }}</td>
            <td>{{ $record['category'] }}</td>
            <td>{{ $record['commodity'] }}</td>
            <td class="text-right">{{ $record['stock'] }}</td>  
            <td>{{ ucfirst($record['type']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>