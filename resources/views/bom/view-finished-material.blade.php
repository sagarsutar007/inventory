<div class="col-md-12">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6>Bill of materials</h6>
        <div id="export-section"></div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="boms-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>PartCode</th>
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
                                <td>{{ $bomRecord->material->part_code }}</td>
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