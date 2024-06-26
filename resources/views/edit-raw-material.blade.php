<div class="col-md-12">
    <input type="hidden" id="material-id" value="{{ $material->material_id }}">
    <div class="raw-material-item-container">
        <div class="row">
            <div class="col-12 col-md-9">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="description" placeholder="Enter material name" value="{{ $material->description }}">
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="opening_balance" placeholder="Opening Bal." value="{{ $stock?->opening_balance }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="make" placeholder="Make" value="{{ $material->make }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="mpn" placeholder="MPN" value="{{ $material->mpn }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="re_order" placeholder="Re-Order" value="{{ $material->re_order }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <select class="form-control select2" id="uom" name="uom_id">
                            <option value=""></option>
                            @foreach($uoms as $unit)
                                @if($material->uom_id == $unit->uom_id)
                                    <option value="{{$unit->uom_id}}" selected>{{$unit->uom_text}}</option>
                                @else
                                    <option value="{{$unit->uom_id}}">{{$unit->uom_text}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <select class="form-control" id="commodity" name="commodity_id">
                            <option value=""></option>
                            @foreach($commodities as $cmdty)
                                @if($material->commodity_id == $cmdty->commodity_id)
                                    <option value="{{$cmdty->commodity_id}}" selected>{{$cmdty->commodity_name}}</option>
                                @else
                                    <option value="{{$cmdty->commodity_id}}">{{$cmdty->commodity_name}}</option>
                                @endif
                            @endforeach 
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <select class="form-control" id="category" name="category_id">
                            <option value=""></option>
                            @foreach($categories as $ctg)
                                @if($material->category_id == $ctg->category_id)
                                    <option value="{{$ctg->category_id}}" selected>{{$ctg->category_name}}</option>
                                @else
                                    <option value="{{$ctg->category_id}}">{{$ctg->category_name}}</option>
                                @endif
                            @endforeach  
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <select class="form-control" id="dependent" name="dm_id">
                            <option value=""></option>
                            @foreach($dependents as $dpt)
                                @if($material->dm_id == $dpt->dm_id)
                                    <option value="{{$dpt->dm_id}}" selected>{{ $dpt->description ." - ".$dpt->frequency }}</option>
                                @else
                                    <option value="{{$dpt->dm_id}}">{{ $dpt->description ." - ".$dpt->frequency }}</option>
                                @endif
                            @endforeach  
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="photo" class="custom-file-input" id="material-img" accept="image/*">
                            <label class="custom-file-label" for="material-img">Material Photo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="pdf" class="custom-file-input" id="material-pdf" accept="application/pdf">
                            <label class="custom-file-label" for="material-pdf">Material PDF</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="doc" class="custom-file-input" id="material-doc">
                            <label class="custom-file-label" for="material-doc">Material Document</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <textarea class="form-control" name="additional_notes" placeholder="Additional notes">{{ $material->additional_notes }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8"><h6>Vendors</h6></div>
            <div class="col-md-4">Price</div>
        </div>
        <div class="vendor-price-container">
            @can('edit-vendor')
            @if(count($purchases))
                @foreach($purchases as $purchase)
                    <div class="vendor-with-price">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="{{ $purchase->vendor->vendor_name }}">
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="price[]" placeholder="Price" value="{{ $purchase->price }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>                            
                    </div>
                @endforeach
            @else
                <div class="vendor-with-price">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="price[]" placeholder="Price" value="">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
                <div class="vendor-with-price">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="price[]" placeholder="Price" value="">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
                <div class="vendor-with-price">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="price[]" placeholder="Price" value="">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
            @endif
            @endcan
        </div>

        <h6>Uploaded Documents</h6>
        <p class="text-danger">Note: Deleting this picture will instantly delete the document from your account. Please make sure you want to do that before proceeding.</p>
        <div class="row">
            @foreach($attachments as $attachment)
                @if($attachment->type === 'image')
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <img class="img-fluid" src="{{ asset('assets/uploads/materials/' . $attachment->path) }}" alt="Attachment Image">
                            <div class="card-body">
                                <h5 class="card-title">Material Image</h5><br>
                                <div class="btn-group w-100">
                                    <a href="{{ asset('assets/uploads/materials/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                                    <button type="button" data-attid="{{ $attachment->mat_doc_id }}" class="btn btn-danger btn-block mt-3 btn-destroy-attachment">Delete</button>
                                </div>
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
                                <div class="btn-group w-100">
                                    <a href="{{ asset('assets/uploads/pdf/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                                    <button type="button" data-attid="{{ $attachment->mat_doc_id }}" class="btn btn-danger btn-block mt-3 btn-destroy-attachment">Delete</button>
                                </div>
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
                                <div class="btn-group w-100">
                                    <a href="{{ asset('assets/uploads/doc/' . $attachment->path) }}" target="_blank" class="btn btn-primary btn-block mt-3">View</a>
                                    <button type="button" data-attid="{{ $attachment->mat_doc_id }}" class="btn btn-danger btn-block mt-3 btn-destroy-attachment">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>