<table id="view-stock-qty" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th>Transaction ID</th>
            <th style="text-wrap: nowrap;">Date</th>
            <th>Purchase/Production Order Number</th>
            <th>Type</th>
            <th>Receipt</th>
            <th>Issue</th>            
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @php
            $balance = 0;
        @endphp
        @if ($transactions)
            <tr>
                <td><strong>Opening Balance</strong></td>
                <td style="text-wrap: nowrap;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    {{ formatQuantity($opening) }}
                    @php $balance += $opening; @endphp
                </td>
            </tr>
            @foreach ($transactions as $trans)
                <tr>
                    <td>{{ $trans->warehouse->transaction_id }}</td>
                    <td style="text-wrap: nowrap;">{{ date('d-m-Y', strtotime($trans->warehouse->date)) }}</td>
                    <td>{{ $trans->warehouse->popn }}</td>
                    <td>
                        @if ($trans->warehouse->po_kitting === "true")
                            {{ "PO Kitting" }}
                        @elseif ($trans->warehouse->kitting_reversal === "true")
                            {{ "Reversal" }}
                        @else 
                            {{ ucfirst($trans->warehouse->type) }}
                        @endif
                    </td>
                    <td>
                        @if ($trans->warehouse->type != "issue")
                            {{ formatQuantity($trans->quantity) }}
                            @php $balance += $trans->quantity; @endphp
                        @endif
                    </td>
                    <td>
                        @if ($trans->warehouse->type == "issue")
                            {{ formatQuantity($trans->quantity) }}
                            @php $balance -= $trans->quantity; @endphp
                        @endif
                    </td>
                    <td>{{ formatQuantity($balance) }}</td>
                </tr>
            @endforeach
        @endif

        
    </tbody>
</table>