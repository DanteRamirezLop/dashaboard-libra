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
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label("paid_on", 'Fecha de pago' . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              {!! Form::date("paid_on", $paid_on, ['class' => 'form-control', 'required']); !!}
            </div>
            <span class="help-block" style="font-size:11px;">Del monto se descuentan primero los intereses de los días transcurridos desde la última cuota hasta la fecha de pago; el resto se aplica a capital.</span>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label("amount" , 'Monto total a pagar'. ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
              {!! Form::text("amount", @num_format($amount), ['class' => 'form-control input_number','required', 'data-rule-max-value' => @num_format($amount), 'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated])]); !!}
            </div>
            <span class="help-block" style="font-size:11px;">
              Intereses de <strong id="sim_accrued_days">{{ $accrued['days'] }}</strong> días transcurridos desde el {{ @format_date($accrued['from_date']) }} (próxima cuota {{ @format_date($next_due_date) }}):
              <strong id="sim_accrued_interest">{{ @num_format($accrued['interest']) }}</strong>.
              Capital aplicado: <strong id="sim_accrued_capital">{{ @num_format($amount - $accrued['interest']) }}</strong>
            </span>
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
<script type="text/javascript">
  // Recalcula los intereses de los días transcurridos según la fecha de pago elegida
  // (mismo cálculo que calcCapitalAccruedInterest; el servidor vuelve a calcularlos al simular)
  (function () {
    var $form = $('#simulate_capital_form');
    var balance = {{ (float) $accrued['balance'] }};
    var annualRate = {{ (float) $loan->annual_interest_rate }};
    var nextDue = moment('{{ \Carbon\Carbon::parse($next_due_date)->format('Y-m-d') }}', 'YYYY-MM-DD');
    var fromDate = moment('{{ \Carbon\Carbon::parse($accrued['from_date'])->format('Y-m-d') }}', 'YYYY-MM-DD');
    var maxInterest = {{ round((float) $accrued['max_interest'], 2) }};
    var periodDays = Math.max(0, nextDue.diff(fromDate, 'days'));
    var $amount = $form.find('input[name="amount"]');
    var lastMax = __read_number($amount);

    function refreshAccrued(dateChanged) {
      var paidOn = moment($form.find('input[name="paid_on"]').val(), 'YYYY-MM-DD').startOf('day');
      var days = paidOn.isValid() ? Math.min(periodDays, Math.max(0, paidOn.diff(fromDate, 'days'))) : 0;
      var interest = Math.min(maxInterest, Math.round(balance * (annualRate / 100) / 360 * days * 100) / 100);
      var newMax = Math.round((balance + interest) * 100) / 100;

      // Si el monto no fue editado a mano (sigue siendo el saldo total), se ajusta al de la nueva fecha
      if (dateChanged && Math.abs(__read_number($amount) - lastMax) < 0.005) {
        __write_number($amount, newMax);
      }
      lastMax = newMax;

      var maxAmount = __number_f(newMax);
      $amount.removeData('rule-max-value msg-max-value ruleMaxValue msgMaxValue')
        .attr('data-rule-max-value', maxAmount)
        .attr('data-msg-max-value', '{{ __('lang_v1.max_amount_to_be_paid_is', ['amount' => '__AMT__']) }}'.replace('__AMT__', maxAmount));

      $form.find('#sim_accrued_days').text(days);
      $form.find('#sim_accrued_interest').text(__number_f(interest));
      $form.find('#sim_accrued_capital').text(__number_f(__read_number($amount) - interest));
    }

    $form.find('input[name="paid_on"]').on('change', function () { refreshAccrued(true); });
    $amount.on('change keyup', function () { refreshAccrued(false); });
    refreshAccrued(false);
  })();
</script>
