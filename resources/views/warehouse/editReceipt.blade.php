<form action="{{ route('wh.update', ['warehouse' => $warehouse->warehouse_id]) }}" id="update-form" method="post">
    @csrf
    <input type="hidden" id="form-type" value="receipt">
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <label for="vendor">Vendor</label>
                <select name="vendor" id="vendor" class="form-control select2" style="width: 100%;">
                    <option value=""></option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->vendor_id  }}" {{ $warehouse->vendor_id == $vendor->vendor_id ? 'selected' : '' }}> {{ $vendor->vendor_name }} </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="date">Purchase number:</label><br />
                <input type="text" name="popn" class="form-control" placeholder="Enter Purchase Number" value="{{ $warehouse->popn }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="date">Issue Date: *</label><br />
                <input type="text" name="date" class="form-control" placeholder="Enter Date" value="{{ date('d-m-Y', strtotime($warehouse->date)) }}" readonly required>
            </div>
        </div>
    </div>
    <hr>
    <div class="material-quantity-container">
        @if($records->count() > 0)
            @foreach($records as $record)
                <div class="material-with-quantity">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="text" name="part_code[]" class="form-control suggest-partcode" value="{{ $record->material->part_code }}" placeholder="Partcode" readonly>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <input type="text" class="form-control material-name" value="{{ $record->material->description }}" placeholder="Material name" disabled>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="text" class="form-control material-unit" value="{{ $record->material->uom->uom_shortcode }}" placeholder="Unit" disabled>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input name="quantity[]" required type="number" class="form-control" placeholder="Qty." step="0.001" value="{{ $record->quantity }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-material-quantity-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
            @endforeach
        @else 
            <!-- No records found -->
        @endif


    </div>
</form>