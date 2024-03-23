@if (count($bomRecords) > 0)
    <table id="bom-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Part Code</th>
                <th>Material Description</th>
                <th>UOM</th>
                <th>QPA</th>
                <th>PO Qty</th>
                <th>Issued</th>
                <th>Bal. to Issue</th>
                <th>Stock Qty.</th>
                <th>Reversal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bomRecords as $record)
                <tr>
                    <td>{{ $record['part_code'] }}</td>
                    <td>{{ $record['material_description'] }}</td>
                    <td>{{ $record['uom_shortcode'] }}</td>
                    <td>{{ $record['bom_qty'] }}</td>
                    <td>{{ $record['quantity'] }}</td>
                    <td>{{ $record['issued'] }}</td>
                    <td>{{ $record['balance'] }}</td>
                    <td>{{ $record['closing_balance'] }}</td>
                    <td></td>
                    <td>
                        @if ($record['quantity'] > $record['closing_balance'])
                            <span class="text-danger">Shortage</span>
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
