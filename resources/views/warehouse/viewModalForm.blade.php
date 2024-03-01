<table class="table table-bordered" cellspacing="0" border="1" style="width:100%;border:1px solid black;border-collapse:collapse;">
    <thead>
        <tr>
            <th colspan="6" class="text-center">{{ $title }}</th>
        </tr>
        <tr>
            @if($warehouse->type == 'issue')
                <th>Issue Date:</th>
                <th>{{ date('d-m-Y', strtotime($warehouse->created_at)) }}</th>
                <th colspan="4" align="left">Po no: {{$warehouse->popn}}</th>
            @else
                <th align="left">Issue Date:</th>
                <th align="left">{{ date('d-m-Y', strtotime($warehouse->created_at)) }}</th>
                <th colspan="4">
                    <div style="display:flex;align-items: center; justify-content: space-between;">
                        <span>Supplier Name: {{ $warehouse->vendor->vendor_name }}</span>
                        <span>Purchase Order no: {{ $warehouse->popn }}</span>
                    </div>
                </th>
            @endif
        </tr>
        <tr>
            <th>S.no</th>
            <th>RM Part no</th>
            <th colspan="2">Description</th>
            <th>QTY Issued</th>
            <th>UOM</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $index => $record)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $record->material->part_code }}</td>
            <td colspan="2">{{ $record->material->description }}</td>
            <td>{{ $record->quantity }}</td>
            <td>{{ $record->material->uom->uom_shortcode }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" align="left">Issued by:- {{$warehouse->createdBy->name}}</th>
            <th colspan="3" align="left">Recieved by:-</th>
        </tr>
    </tfoot>
</table>