@if (count($records) > 0)
    <table id="records-table" class="table table-bordered table-striped" style="width: 100%;">
        <thead>
            <tr>
                <td>Transaction ID</td>
                <td>Type</td>
                <td>Date</td>
                <td>Partcode</td>
                <td>Description</td>
                <td>Unit</td>
                <td>Qty</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td>{{ $record['transaction_id'] }}</td>
                <td>{{ ucfirst($record['type']) }}</td>
                <td>{{ $record['created_at'] }}</td>
                <td>{{ $record['part_code'] }}</td>
                <td>{{ $record['description'] }}</td>
                <td>{{ $record['uom'] }}</td>
                <td style="{{ $record['type'] === 'reversal' ? 'color: red;' : '' }}">{{ $record['quantity'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No records found.</p>
@endif