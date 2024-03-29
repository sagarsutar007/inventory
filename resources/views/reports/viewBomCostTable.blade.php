@if (count($bomRecords) > 0)
    <table id="bom-table" class="table table-bordered table-striped" style="width:100%;">
        <thead>
            <tr>
                <th>S.no</th>
                <th width="10%">Part Code</th>
                <th>Material Description</th>
                <th>Category</th>
                <th>Commodity</th>
                <th width="10%">QPA</th>
                <th width="8%">UOM</th>
                <th width="10%">Avg Price</th>
                <th width="10%">Lowest Price</th>
                <th width="10%">Highest Price</th>
            </tr>
        </thead>
        <tbody>
        @php
            $total_avg = 0;
            $total_min = 0;
            $total_max = 0;
        @endphp

        @foreach ($bomRecords as $index => $record)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td width="10%">{{ $record['part_code'] }}</td>
                <td>{{ $record['material_description'] }}</td>
                <td>{{ $record['category'] }}</td>
                <td>{{ $record['commodity'] }}</td>
                <td width="10%">{{ $record['bom_qty'] }}</td>
                <td width="8%">{{ $record['uom_shortcode'] }}</td>
                <td width="10%">{{ $record['avg_price'] }}</td>
                <td width="10%">{{ $record['min_price'] }}</td>
                <td width="10%">{{ $record['max_price'] }}</td>
            </tr>
            @php
                $total_avg += $record['avg_price'];
                $total_min += $record['min_price'];
                $total_max += $record['max_price'];
            @endphp
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7"></td>
                <td>{{ number_format($total_avg, 2) }}</td>
                <td>{{ number_format($total_min, 2) }}</td>
                <td>{{ number_format($total_max, 2) }}</td>
            </tr>
        </tfoot>
    </table>
@else
    <p>No BOM records found.</p>
@endif
