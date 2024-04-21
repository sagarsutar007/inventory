@if (count($bomRecords) > 0)
    <table id="bom-table" class="table table-bordered table-striped" style="width:100%;">
        <thead>
            <tr>
                <th width="10%">Part Code</th>
                <th>Material Description</th>
                <th>Category</th>
                <th>Commodity</th>
                <th width="10%">QPA</th>
                <th width="8%">UOM</th>
                {{-- <th width="8%">PO Qty</th> --}}
                {{-- <th width="7%">Issued</th>
                <th width="10%">Bal. to Issue</th>
                <th width="6%">Stock Qty</th> --}}
                {{-- <th>Reversal</th> --}}
                {{-- <th>Status</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($bomRecords as $record)
                <tr>
                    <td width="10%">{{ $record['part_code'] }}</td>
                    <td>{{ $record['material_description'] }}</td>
                    <td>{{ $record['category'] }}</td>
                    <td>{{ $record['commodity'] }}</td>
                    <td width="10%" class="text-right">{{ formatQuantity($record['bom_qty']) }}</td>
                    <td width="8%" class="text-center">{{ $record['uom_shortcode'] }}</td>
                    {{-- <td width="8%">{{ $record['quantity'] }}</td> --}}
                    {{-- <td width="7%">{{ $record['issued'] }}</td>
                    <td width="10%">{{ $record['balance'] }}</td>
                    <td width="6%">{{ $record['closing_balance'] }}</td> --}}
                    {{-- <td></td> --}}
                    {{-- <td>
                        @if ($record['quantity'] > $record['closing_balance'])
                            <span class="text-danger">Shortage</span>
                        @else
                            Available
                        @endif
                    </td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No BOM records found.</p>
@endif
