@if (isset($combinedMaterials))
<div class="card mt-3">
    <div class="card-body" >
        <table id="material-shortage-tbl" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Partcode</th>
                    <th>Description</th>
                    <th>Make</th>
                    <th>MPN</th>
                    <th>Unit</th>
                    <th>BOM Qty</th>
                    <th>Required Qty</th>
                    <th>Reserved Qty</th>
                    <th>Stock Qty</th>
                    <th>Shortage Qty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($combinedMaterials as $record)
                <tr>
                    <td>{{ $record['part_code'] }}</td>
                    <td>{{ $record['description'] }}</td>
                    <td>{{ $record['make'] }}</td>
                    <td>{{ $record['mpn'] }}</td>
                    <td>{{ $record['unit'] }}</td>
                    <td>{{ number_format($record['bom_qty'], 3) }}</td>
                    <td>{{ number_format($record['req_qty'], 3) }}</td>
                    <td>{{ number_format($record['reserved_qty'], 3) }}</td>
                    <td>{{ number_format($record['stock_qty'], 3) }}</td>
                    <td>{{ number_format($record['short_qty'], 3) }}</td>
                    <td class="text-danger">{{ $record['status'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if ($materials)
    @foreach ($materials as $index => $material)
        <h5>FG Partcode: {{ $material['fg_partcode'] . " - " . $material['description'] . " - " . $material['quantity'] ." ". $material['unit']}}</h5>
        <div class="card mt-3">
            <div class="card-body">
                <table id="material-shortage-tbl-{{ $index + 1 }}" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Partcode</th>
                            <th>Description</th>
                            <th>Make</th>
                            <th>MPN</th>
                            <th>Unit</th>
                            <th>BOM Qty</th>
                            <th>Required Qty</th>
                            <th>Reserved Qty</th>
                            <th>Stock Qty</th>
                            <th>Shortage Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($material['records'] as $record)
                        <tr>
                            <td>{{ $record['part_code'] }}</td>
                            <td>{{ $record['description'] }}</td>
                            <td>{{ $record['make'] }}</td>
                            <td>{{ $record['mpn'] }}</td>
                            <td>{{ $record['unit'] }}</td>
                            <td>{{ number_format($record['bom_qty'], 3) }}</td>
                            <td>{{ number_format($record['req_qty'], 3) }}</td>
                            <td>{{ number_format($record['reserved_qty'], 3) }}</td>
                            <td>{{ number_format($record['stock_qty'], 3) }}</td>
                            <td>{{ number_format($record['short_qty'], 3) }}</td>
                            <td class="text-danger">{{ $record['status'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif