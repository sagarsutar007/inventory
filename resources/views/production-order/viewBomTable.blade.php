@if (count($bomRecords) > 0)
    <table id="bom-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Part Code</th>
                <th>Material Description</th>
                <th>UOM</th>
                <th>Quantity</th>
                <th>Stock Qty.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bomRecords as $record)
                <tr>
                    <td>{{ $record['part_code'] }}</td>
                    <td>{{ $record['material_description'] }}</td>
                    <td>{{ $record['uom_shortcode'] }}</td>
                    <td>{{ $record['quantity'] }}</td>
                    <td>{{ $record['closing_balance'] }}</td>
                    <td>
                        @if ($record['quantity'] > $record['closing_balance'])
                            Shortage
                        @else
                            Available
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No BOM records found.</p>
@endif
