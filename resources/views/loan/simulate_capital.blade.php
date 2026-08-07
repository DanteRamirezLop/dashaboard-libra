<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['route' => ['loan.simulate.capital'], 'id' => 'simulate_capital_form']) !!}
    {!! Form::hidden('loan_id', $loan->id); !!}
    {!! Form::hidden('type_pay', $type); !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"><i class="fas fa-calculator"></i> Simular pago a capital</h4>
    </div>
    <div class="modal-body">
      <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Esta simulación no ejecuta ningún pago ni modifica el cronograma actual, solo calcula cómo quedaría.
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label("amount" , 'Monto a pagar a capital'. ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
              {!! Form::text("amount", @num_format($amount), ['class' => 'form-control input_number','required', 'data-rule-max-value' => @num_format($amount), 'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated])]); !!}
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label("mora_amount", 'Deuda moratoria a incluir' . ':') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-exclamation-triangle"></i>
              </span>
              {!! Form::text("mora_amount", '0', ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
            </div>
            <span class="help-block" style="font-size:11px;">Se suma al saldo inicial antes de recalcular el calendario</span>
          </div>
        </div>
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label("target_cuotas", 'Cuotas restantes tras el pago' . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-list-ol"></i>
              </span>
              {!! Form::number("target_cuotas", $pending_count, ['class' => 'form-control', 'required', 'min' => 1, 'max' => $pending_count]); !!}
            </div>
            <span class="help-block" style="font-size:11px;">
              Actualmente quedan {{ $pending_count }} cuotas de {{ @num_format($current_installment) }} c/u.
              Ingresa cuántas cuotas quedarían después de este pago (mín. 1, máx. {{ $pending_count }}).
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-info"><i class="fa fa-calculator"></i> Simular</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
