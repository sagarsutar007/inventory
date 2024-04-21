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
                        <th>PartCode</th>
                        <th width="35%">Material Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @if($boms)
                        @foreach($boms as $index => $bomRecord)
                            <tr>
                                <td>{{ $bomRecord->material->part_code }}</td>
                                <td width="35%">{{ $bomRecord->material->description }}</td>
                                <td>{{ $bomRecord->material->type }}</td>
                                <td class="text-right">{{ $bomRecord->quantity }}</td>
                                <td class="text-center">{{ $bomRecord->material->uom->uom_text }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>