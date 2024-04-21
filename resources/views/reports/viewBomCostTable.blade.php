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
                <td width="10%" class="text-right">{{ $record['bom_qty'] }}</td>
                <td width="8%" class="text-center">{{ $record['uom_shortcode'] }}</td>
                <td width="10%" class="text-right">{{ formatPrice($record['avg_price']) }}</td>
                <td width="10%" class="text-right">{{ formatPrice($record['min_price']) }}</td>
                <td width="10%" class="text-right">{{ formatPrice($record['max_price']) }}</td>
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
                <td colspan="7" class="text-right"><strong>Cost</strong></td>
                <td class="text-right">{{ formatPrice($total_avg) }}</td>
                <td class="text-right">{{ formatPrice($total_min) }}</td>
                <td class="text-right">{{ formatPrice($total_max) }}</td>
            </tr>
        </tfoot>
    </table>
@else
    <p>No BOM records found.</p>
@endif
