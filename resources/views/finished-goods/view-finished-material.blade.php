<div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="home-tab">
    <div class="row">
        <div class="col-md-5">
            <h6>Material Information</h6>
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
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <h6>More Information</h6>
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <p class="text-muted mb-0"><strong class="text-dark">Make</strong> : {{ $material->make }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">MPN</strong> : {{ $material->mpn }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">Commodity</strong> : {{ $commodity->commodity_name }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">Category</strong> : {{ $category->category_name }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">Measurement Unit</strong> : {{ $uom->uom_text }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">Additional Notes</strong> : {{ $material->additional_notes }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="profile-tab">
    <h6>Uploaded Documents</h6>
    <div class="row">
        @foreach($attachments as $attachment)
            @if($attachment->type === 'image')
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <img class="img-fluid" src="{{ asset('assets/uploads/materials/' . $attachment->path) }}" alt="Attachment Image">
                        <div class="card-body">
                            <h5 class="card-title">Material Image</h5><br>
                            <a href="{{ asset('assets/uploads/materials/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                        </div>
                    </div>
                </div>
            @endif

            @if($attachment->type === 'pdf')
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <img class="img-fluid" src="{{ asset('assets/img/pdf.png') }}" alt="Attachment Image">
                        <div class="card-body">
                            <h5 class="card-title">Material PDF</h5><br>
                            <a href="{{ asset('assets/uploads/pdf/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                        </div>
                    </div>
                </div>
            @endif

            @if($attachment->type === 'doc')
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <img class="img-fluid" src="{{ asset('assets/img/documents.jpg') }}" alt="Attachment Image">
                        <div class="card-body">
                            <h5 class="card-title">Material Document</h5><br>
                            <a href="{{ asset('assets/uploads/doc/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
<div class="tab-pane fade" id="boms" role="tabpanel" aria-labelledby="boms-tab">
    <div class="d-flex align-items-center justify-content-between mb-3">
        {{-- <h6>Bill of materials</h6> --}}
        <div id="export-section"></div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped" id="boms-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Part Code</th>
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
                                <td class="text-right">{{ formatQuantity($bomRecord->quantity) }}</td>
                                <td class="text-center">{{ $bomRecord->material->uom->uom_text }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5">Bill of material records not found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>