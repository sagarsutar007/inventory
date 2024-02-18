<div class="col-md-12">
    <input type="hidden" id="material-id" value="{{ $material->material_id }}">
    <div class="raw-material-item-container">
        <div class="row">
            <div class="col-12 col-md-9">
                <div class="form-group">
                    <label>Material Name</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="description" placeholder="Enter material name" value="{{ $material->description }}" readonly disabled>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Opening Balance</label>
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="opening_balance" placeholder="Opening Bal." value="{{ $material->opening_balance }}" readonly disabled>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Make</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="make" placeholder="Make" value="{{ $material->make }}" readonly disabled>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>MPN</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="mpn" placeholder="MPN" value="{{ $material->mpn }}" readonly disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Measurement Unit</label>
                    <div class="input-group mb-3">
                        <select class="form-control select2" id="uom" name="uom_id" readonly disabled>
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
            <div class="col-md-4">
                <div class="form-group">
                    <label>Commodity</label>
                    <div class="input-group mb-3">
                        <select class="form-control" id="commodity" name="commodity_id" readonly disabled>
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
            <div class="col-md-4">
                <div class="form-group">
                    <label>Category</label>
                    <div class="input-group mb-3">
                        <select class="form-control" id="category" name="category_id" readonly disabled>
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
        </div>
        <!-- <div class="row">
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
        </div> -->
        <div class="form-group">
            <label>Additional Notes</label>
            <div class="input-group mb-3">
                <textarea class="form-control" name="additional_notes" placeholder="Additional notes" readonly disabled>{{ $material->additional_notes }}</textarea>
            </div>
        </div>
        <h6>Vendors</h6>
        <div class="vendor-price-container">
            @if(count($purchases))
                @foreach($purchases as $purchase)
                    <div class="vendor-with-price">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" class="form-control sug-vendor" name="vendor[]" placeholder="Vendor name" value="{{ $purchase->vendor->vendor_name }}" readonly disabled>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-times remove-vendor-price-item"></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="price[]" placeholder="Price" value="{{ $purchase->price }}" readonly disabled>
                                </div>
                            </div>
                        </div>                            
                    </div>
                @endforeach
            @else
                <div class="vendor-with-price">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-center">No vendors found! Please add a new vendor.</p>
                        </div>
                    </div>                            
                </div>
            @endif
        </div>
    </div>
</div>