
@if(empty($only) || in_array('service_staffs', $only))
@if(!empty($service_staffs))
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('service_staffs', 'Vendedor' . ':') !!}
            {!! Form::select('service_staffs', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
        </div>
    </div>
@endif
@endif

@if(empty($only) || in_array('only_delay', $only))
<div class="col-md-3" style="margin-bottom: 7px;">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
            {!! Form::checkbox('only_delay', 1, false, 
            [ 'class' => 'input-icheck', 'id' => 'only_delay']); !!} Solo Vencidos
            </label>
        </div>
    </div>
</div>
@endif

   
        

