<table class="table table-bordered" cellspacing="0" border="1" style="width:100%;border:1px solid black;border-collapse:collapse;">
    <thead>
        <tr>
            <th colspan="6" class="text-center">{!! $title !!}</th>
        </tr>
        <tr>
            @if($warehouse->type == 'issue')
                <th>Issue Date:</th>
                <td>{{ date('d-m-Y', strtotime($warehouse->created_at)) }}</td>
                @if ($warehouse->po_kitting != 'true') 
                    <th colspan="4" align="left">Reason: <span class="font-weight-normal">{{$warehouse->reason}}</span></th>
                @else
                    <th colspan="4" align="left">PO No: <span class="font-weight-normal">{{$warehouse->popn}}</span></th>
                @endif
            @else
                <th align="left">Receipt Date:</th>
                <td align="left">{{ date('d-m-Y', strtotime($warehouse->created_at)) }}</td>
                <th colspan="2">
                    <div style="display:flex;align-items: center; justify-content: space-between;">
                        <span>Supplier Name: <span class="font-weight-normal">{{ $warehouse->vendor?->vendor_name }}</span></span>
                    </div>
                </th>
                <th colspan="2">
                    <div style="display:flex;align-items: center; justify-content: space-between;">
                        <span>PO No: <span class="font-weight-normal">{{ $warehouse->popn }}</span></span>
                    </div>
                </th>
            @endif
        </tr>
        <tr>
            <th>S.no</th>
            <th>Part no</th>
            <th colspan="2">Description</th>
            @if($warehouse->type == 'issue')
            <th>Qty Issued</th>
            @else
            <th>Qty Recieved</th>
            @endif
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
            <th colspan="3" align="left">
                Issued by:- <span class="font-weight-normal">{{$warehouse->createdBy->name}}</span>
                <p class="m-0 text-secondary text-sm font-weight-light">{{ date('d-m-Y h:i a', strtotime('+5 hours 30 minutes', strtotime($warehouse->created_at))) }}</p>
            </th>
            <th colspan="3" align="left">Recieved by:-</th>
        </tr>
    </tfoot>
</table>