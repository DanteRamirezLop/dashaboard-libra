
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

@if(empty($only) || in_array('only_repossessed', $only) || in_array('only_execution', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('loan_special_filter', __('loans.exceptional_states') . ':') !!}
        {!! Form::select('loan_special_filter', ['only_repossessed' => 'Solo Reposición', 'only_execution' => 'Solo Ejecución'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif

   
        

