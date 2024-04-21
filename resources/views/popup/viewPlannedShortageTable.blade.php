@if (isset($combinedMaterials))
<div class="card mt-3">
    <div class="card-body" >
        <div class="table-responsive">
            <table id="material-shortage-tbl" class="table table-bordered table-striped" style="width: 100%;">
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
                        <td class="text-right">{{ formatQuantity($record['bom_qty']) }}</td>
                        <td class="text-right">{{ formatQuantity($record['req_qty']) }}</td>
                        <td class="text-right"><a href="#view-modal" data-toggle="modal" data-partcode="{{ $record['part_code'] }}">{{ formatQuantity($record['reserved_qty']) }}</a></td>
                        <td class="text-right"><button class="view-stock btn btn-link p-0" data-partcode="{{ $record['part_code'] }}">{{ formatQuantity($record['stock_qty']) }}</button></td>
                        <td class="text-right">{{ formatQuantity($record['short_qty']) }}</td>
                        <td class="text-danger">{{ $record['status'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if ($materials)
    @foreach ($materials as $index => $material)
        <h5 id="ms-head-{{ $index + 1 }}">FG Partcode: {{ $material['fg_partcode'] . " - " . $material['description'] . " - " . $material['quantity'] ." ". $material['unit']}}</h5>
        <div class="card mt-3">
            <div class="card-body">
                <table id="material-shortage-tbl-{{ $index + 1 }}" class="table table-bordered table-striped" style="width: 100%;">
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
                            <td class="text-right">{{ formatQuantity($record['bom_qty']) }}</td>
                            <td class="text-right">{{ formatQuantity($record['req_qty']) }}</td>
                            <td class="text-right"><a href="#view-modal" data-toggle="modal" data-partcode="{{ $record['part_code'] }}">{{ formatQuantity($record['reserved_qty']) }}</a></td>
                            <td class="text-right"><button class="view-stock btn btn-link p-0" data-partcode="{{ $record['part_code'] }}">{{ formatQuantity($record['stock_qty']) }}</button></td>
                            <td class="text-right">{{ formatQuantity($record['short_qty']) }}</td>
                            <td class="text-danger">{{ $record['status'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif