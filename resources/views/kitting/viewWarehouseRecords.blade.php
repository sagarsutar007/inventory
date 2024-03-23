@if (count($records) > 0)
    <table id="records-table" class="table table-bordered table-striped" style="width: 100%;">
        <thead>
            <tr>
                <th width="10%">ID</th>
                <th width="7%">Type</th>
                <th width="14%">Date</th>
                <th width="10%">Partcode</th>
                <th>Description</th>
                <th width="7%">Unit</th>
                <th width="8%">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td width="10%">{{ $record['transaction_id'] }}</td>
                <td width="7%">{{ ucfirst($record['type']) }}</td>
                <td width="14%">{{ $record['created_at'] }}</td>
                <td width="10%">{{ $record['part_code'] }}</td>
                <td>{{ $record['description'] }}</td>
                <td width="7%">{{ $record['uom'] }}</td>
                <td width="8%" style="{{ $record['type'] === 'reversal' ? 'color: red;' : '' }}">{{ $record['quantity'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No records found.</p>
@endif