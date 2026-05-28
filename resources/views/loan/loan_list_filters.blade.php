
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

@if(empty($only) || in_array('loan_list_filter_status', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('loan_list_filter_status', __('loans.loan_status') . ':') !!}
        {!! Form::select('loan_list_filter_status', ['partial' => __('lang_v1.partial'), 'in arrears' => __('lang_v1.overdue'), 'paid' => __('loans.paid_payment')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif

@if(empty($only) || in_array('only_repossessed', $only))
<div class="col-md-3" style="margin-bottom: 7px;">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
            {!! Form::checkbox('only_repossessed', 1, false,
            [ 'class' => 'input-icheck', 'id' => 'only_repossessed']); !!} Solo Reposición
            </label>
        </div>
    </div>
</div>
@endif

   
        

