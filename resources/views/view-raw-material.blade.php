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
                    <p class="text-muted mb-0"><strong class="text-dark">Dependent Material</strong> : {{ $dm?->description }}</p>
                    <hr class="my-2">
                    <p class="text-muted mb-0"><strong class="text-dark">Frequency</strong> : {{ $dm?->frequency }}</p>
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
<div class="tab-pane fade" id="vendors" role="tabpanel" aria-labelledby="vendors-tab">
    <h6>Vendors</h6>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sno.</th>
                            <th>Vendor Name</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($purchases))
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $loop->iteration }}.</td>
                                    <td>{{ $purchase->vendor->vendor_name }}</td>
                                    <td>{{ $purchase->price }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3">No vendors records found!</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="tab-pane fade" id="used" role="tabpanel" aria-labelledby="used-tab">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="used-table" class="table table-bordered table-striped" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="6%">Partcode</th>
                            <th>Material Name</th>
                            <th>QPA</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($used))
                            @foreach($used as $mat)
                                <tr>
                                    <td width="6%">{{ $mat['part_code'] }}</td>
                                    <td>{{ $mat['description'] }}</td>
                                    <td width="6%" class="text-right">{{ $mat['quantity'] }}</td>
                                    <td width="6%">{{ $mat['unit'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">No records found!</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
