<table id="view-stock-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th>POPN</th>
            <th>Type</th>
            <th>PO Kitting</th>
            <th>Date</th>
            <th>Quantity</th>
        </tr>
    </thead>
    <tbody>
        @if ($transactions)
            @foreach ($transactions as $trans)
            <tr>
                <td>{{ $trans->warehouse->transaction_id }}</td>
                <td>{{ $trans->warehouse->popn }}</td>
                <td>{{ $trans->warehouse->type }}</td>
                <td>{{ $trans->warehouse->po_kitting }}</td>
                <td>{{ date('d-m-Y', strtotime($trans->warehouse->date)) }}</td>\
                <td>{{ $trans->quantity }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>