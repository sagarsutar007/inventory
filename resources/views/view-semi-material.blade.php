<div class="col-md-12">
    <h5 class="text-dark"></h5>
    <div class="card card-primary card-outline">
        <div class="card-body box-profile">
            <div class="text-center">
                @php $imageFound = false; @endphp
                @foreach($attachments as $attachment)
                    @if($attachment->type === 'image')
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('assets/uploads/materials/' . $attachment->path) }}" alt="Attachment Image">
                        @php $imageFound = true; break; @endphp
                    @endif
                @endforeach
                @if (!$imageFound)
                    <!-- Default image if no image is found -->
                    <img class="profile-user-img img-fluid img-circle" src="{{ asset('assets/default-image.jpg') }}" alt="Default Image">
                @endif
            </div>
            <h3 class="profile-username text-center">{{ $material->description }}</h3>
            <p class="text-muted text-center">{{ $material->part_code }}</p>
            <hr>
            <strong>Commodity</strong>
            <p class="text-muted">{{ $commodity->commodity_name }}</p>
            <hr>
            <strong>Category</strong>
            <p class="text-muted">{{ $category->category_name }}</p>
            <hr>
            <strong>Measurement Unit</strong>
            <p class="text-muted">{{ $uom->uom_text }}</p>
            <hr>
            <strong>Additional Notes</strong>
            <p class="text-muted">{{ $material->additional_notes }}</p>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Attachments</h3>
        </div>
        <div class="card-body">
            <strong><i class="fas fa-file-pdf mr-1"></i> PDFs</strong>
            <p class="text-muted">
                @foreach($attachments as $attachment)
                    @if($attachment->type === 'pdf')
                        <a href="{{ asset('assets/uploads/pdf/' . $attachment->path) }}" class="btn btn-link" target="_blank">Download PDF</a> <br>
                    @endif
                @endforeach
            </p>
            <hr>
            <strong><i class="fas fa-file-word mr-1"></i> Documents</strong>
            <p class="text-muted">
                @foreach($attachments as $attachment)
                    @if($attachment->type === 'doc')
                        <a href="{{ asset('assets/uploads/doc/' . $attachment->path) }}" class="btn btn-link" target="_blank">Download File</a> <br>
                    @endif
                @endforeach
            </p>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Bill of Material</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Raw Material</th>
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