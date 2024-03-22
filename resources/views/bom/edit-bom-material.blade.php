<div class="col-md-12">
    <input type="hidden" id="bom-id" value="{{ $bom->bom_id }}">
    <div class="raw-material-item-container">
        <h6>Bill of Material</h6>
        <div class="raw-materials-container">
            @foreach($bom->bomRecords as $index => $bomRecord)
                <div class="raw-with-quantity">
                    <div class="row">
                        <div class="col-md-8">
                            <select class="form-control raw-materials" name="raw[]" style="width:100%;">
                                <option value=""></option>  
                                <option value="{{ $bomRecord->material_id }}" selected>{{ $bomRecord->material->description . "-" . $bomRecord->material->part_code . "(" . $bomRecord->material->uom->uom_shortcode . ")" }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="quantity[]" placeholder="Quantity" value="{{ $bomRecord->quantity }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-times remove-raw-quantity-item"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>                            
                </div>
            @endforeach
        </div>
    </div>
</div>