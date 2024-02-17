<div class="col-md-12">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Bill of Material</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Material Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @if($boms)
                        @foreach($boms as $index => $bomRecord)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bomRecord->material->description }}</td>
                                <td>{{ $bomRecord->material->type }}</td>
                                <td>{{ $bomRecord->quantity }}</td>
                                <td>{{ $bomRecord->material->uom->uom_text }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>