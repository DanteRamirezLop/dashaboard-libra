<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\LoanPaymentController::class, 'store']), 'method' => 'post', 'id' => 'transaction_payment_add_form', 'files' => true ]) !!}
    {!! Form::hidden('payment_schedule_id', $payment_schedule->id) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'purchase.add_payment' )</h4>
    </div>
    <div class="modal-body">
      <div class="row payment_row">
        <div class="col-md-12 @if($payment_schedule->status != 'pending') hide  @endif" >
            <div class="form-group">
                {!! Form::label('optionPay',  'Tipo de pago:')!!}
                <div class="input-group">
                  <label class="radio-inline">
                      {!! Form::radio('optionPay', '1', false, [ 'class' => 'input-icheck', 'name'=>"optionPay", 'checked']); !!}
                      Pago regular
                  </label>
                  <label class="radio-inline">
                      {!! Form::radio('optionPay', '2', false, [ 'class' => 'input-icheck', 'name'=>"optionPay"]); !!}
                      Pago adelantado 
                  </label>
                </div>
            </div>
        </div>
      </div>
     <div class="row payment_row">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label("paid_on" , __('lang_v1.paid_on') . ':*') !!}
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </span>
                {!! Form::text('paid_on', @format_datetime($paid_on), ['class' => 'form-control', 'required']); !!}
              </div>
            </div>
          </div>

           <div class="col-md-4">
            <div class="form-group">
              {!! Form::label("method" , __('purchase.payment_method') . ':*') !!}
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fas fa-money-check-alt"></i>
                </span>
                {!! Form::select("method", $payment_types,'',['class' => 'form-control select2 payment_types_dropdown', 'required', 'style' => 'width:100%;']); !!}
              </div>
            </div>
          </div>

        @if(!empty($accounts))
          <div class="col-md-4">
            <div class="form-group hide">
              {!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
              
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fas fa-credit-card"></i>
                </span>
                {!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;']); !!}
              </div>
            </div>
          </div>
        @endif

       <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('currency', 'Moneda' . ':*') !!} 
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
              <select class="form-control currency_types_dropdown" name="currency" id="currency">
                  <option value="Dolar">Dolar</option>
                  <option value="Sol">Sol</option>
              </select> 
            </div>
          </div>
        </div>

        <div class="col-md-12 " >
          <div class="row hide" id="calculate_dollars">
              <div class="col-md-4">
                <div class="form-group">
                  {!! Form::label('exchange_rate', 'Tasa de cambio' . ':*') !!} 
                  <div class="input-group">
                      <span class="input-group-addon">
                        <i class="fas fa-money-bill-alt"></i>
                      </span>
                       {!! Form::number("exchange_rate", $exchange_rate, ['class' => 'form-control']); !!}
                  </div>
                </div>
              </div>

              <div class="col-md-4 ">
                <div class="form-group">
                  {!! Form::label("amount_var" , 'Monto a cambiar'. ':*') !!}
                  <div class="input-group">
                    <span class="input-group-addon">
                      <i class="fas fa-money-bill-alt"></i>
                    </span>
                    {!! Form::text("amount_var",  1, ['class' => 'form-control']); !!}
                  </div>
                </div>
              </div>

              <div class="col-md-4 ">
                <div class="form-group">
                  {!! Form::label("acction" , 'Acción'. ':') !!}
                  <div class="input-group">
                    <button type="button" class="btn btn-primary" id="calculate">Calcular a Dolares </button>
                  </div>
                </div>
              </div>
            </div>
        </div>

          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label("amount" , 'Monto total en dolares'. ':*') !!}
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fas fa-money-bill-alt"></i>
                </span>
                {!! Form::text("amount",  @num_format($amount), ['class' => 'form-control']); !!}
              </div>
            </div>
          </div>

          <div class="col-md-4 hide" id="prepayment">
            <div class="form-group">
              {!! Form::label('days_in_advance', 'Dias adelantados' . ':*') !!} 
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fas fa-calendar-check"></i>
                </span>
                 {!! Form::text("days_in_advance",  1, ['class' => 'form-control']); !!}
              </div>
            </div>
          </div> 
          
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label("note", __('lang_v1.payment_note') . ':') !!}
            {!! Form::textarea("note", '', ['class' => 'form-control', 'rows' => 3]); !!}
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.save' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
    
    {!! Form::close() !!}
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->