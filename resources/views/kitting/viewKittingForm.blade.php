@if (count($bomRecords) > 0)
<form action=""{{ route('kitting.issue') }} method="POST" id="issue-form">    
    @csrf
    <input type="hidden" name="production_id" value="{{ $prodId }}">
    <table id="bom-table" class="table table-bordered table-striped" style="width: 100%;">
        <thead>
            <tr>
                <th width="10%">Part Code</th>
                <th>Description</th>
                <th width="3%">UOM</th>
                <th width="5%">QPA</th>
                <th width="5%">PO QTY</th>
                <th width="5%">Issued</th>
                <th>Bal. to Issue</th>
                <th width="5%">Stock</th>
                {{-- <th>Reversal</th> --}}
                <th style="width: 10%;">Issue</th>
                <th></th>
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
                    {{-- <td></td> --}}
                    <td style="width: 10%;">
                        @if ( $record['balance'] != 0)
                        <input type="number" name="issue[]" max="{{ $record['balance'] }}" class="form-control" placeholder="Issue Quantity">
                        @else
                        <input type="number" name="issue[]" class="form-control" readonly placeholder="Issue Completed">
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" data-poid="{{ $record['po_id'] }}" data-matid="{{ $record['material_id'] }}" class="btn btn-link reverse-btn p-0" data-toggle="tooltip" data-placement="top" title="Reversal">
                            <img src="{{ asset('assets/img/return.png') }}" alt="Reversal of issued quantity" width="20px">
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</form>
    @else
    <p>No BOM records found.</p>
@endif
