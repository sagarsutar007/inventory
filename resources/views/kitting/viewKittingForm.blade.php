@if (count($bomRecords) > 0)
<form action=""{{ route('kitting.issue') }} method="POST" id="issue-form">    
    @csrf
    <input type="hidden" name="production_id" value="{{ $prodId }}">
    <table id="bom-table" class="table table-bordered table-striped" style="width: 100%;">
        <thead>
            <tr>
                <th>Part Code</th>
                <th>Description</th>
                <th>UOM</th>
                <th>QPA</th>
                <th>PO QTY</th>
                <th>Issued</th>
                <th>Bal. to Issue</th>
                <th>Stock</th>
                <th style="width: 15%;">Issue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bomRecords as $record)
                <tr>
                    <input type="hidden" name="material[]" value="{{ $record['material_id'] }}">
                    <td>{{ $record['part_code'] }}</td>
                    <td>{{ $record['material_description'] }}</td>
                    <td>{{ $record['uom_shortcode'] }}</td>
                    <td>{{ $record['bom_qty'] }}</td>
                    <td>{{ $record['quantity'] }}</td>
                    <td>{{ $record['issued'] }}</td>
                    <td>{{ $record['balance'] }}</td>
                    <td>{{ $record['closing_balance'] }}</td>
                    <td style="width: 15%;">
                        @if ( $record['balance'] != 0)
                        <input type="number" name="issue[]" max="{{ $record['closing_balance'] }}" class="form-control" placeholder="Issue Quantity">
                        @else
                        <input type="number" name="issue[]" class="form-control" readonly placeholder="Issue Completed">
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</form>
    @else
    <p>No BOM records found.</p>
@endif
