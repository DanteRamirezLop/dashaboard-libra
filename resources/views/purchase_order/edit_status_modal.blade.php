<div class="modal-dialog" role="document">
    {!! Form::open(['url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'postEditPurchaseOrderStatus'], ['id' => $id]), 'method' => 'put', 'id' => 'update_po_status_form']) !!}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('lang_v1.edit_status')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('status', __('sale.status') . ':') !!}
                        <select name="status" id="status" class="form-control" style="width: 100%;">
                            @foreach($statuses as $key => $po_status)
                                <option value="{{$key}}" @if($key == $status) selected @endif>
                                    {{$po_status['label']}} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-sm-12">
                    {!! Form::label('custom_field_2', 'Aprobado por:') !!}
                    <div class="form-group">
                        <select name="custom_field_2" id="custom_field_2" class="form-control" style="width: 100%;" required>
                            <option value="" @if($custom_field_2 == '') selected @endif>
                                Seleccione
                            </option>
                            <option value="Ricardo Guillermo Li Bravo" @if($custom_field_2 == 'Ricardo Guillermo Li Bravo') selected @endif>
                                Ricardo Guillermo Li Bravo
                            </option>
                        </select>
				    </div>
                </div> 

                <div class="col-sm-12">
                    {!! Form::label('delivery_date', 'Fecha de aprobación:') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('delivery_date', $delivery_date, ['class' => 'form-control', 'required']); !!}
                </div> 
                
            </div>
        </div>
         <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                @lang('messages.close')
            </button>
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white ladda-button">
                @lang('messages.update')
            </button>
        </div>
    </div><!-- /.modal-content -->
    {!! Form::close() !!}
      <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
</div><!-- /.modal-dialog -->
