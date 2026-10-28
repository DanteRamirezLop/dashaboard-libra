<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\DelayController::class, 'store']), 'method' => 'post', 'id' => 'delay_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"> Agregar mora  </h4>
    </div>

    <div class="modal-body">


        <div class="form-group">
          {!! Form::label('days_late', 'Días de mora' . ':*') !!}
          <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-clock"></i>
              </span>
              {!! Form::number('days_late', null, ['class' => 'form-control', 'required', 'placeholder' => '05' ]); !!}
          </div>
        </div>

        {!! Form::hidden('loan_id', $loan->id); !!}
        {!! Form::hidden('payment_schedule_id', $payment_schedule->id); !!}
  
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->