<div class="modal-dialog" role="document">
  <div class="modal-content">
    {{ Form::open(['route' => ['pay.capital'], 'id' => 'transaction_payment_add_form']) }} 
    {!! Form::hidden('loan_id', $loan->id); !!}
    {!! Form::hidden('transaction_id', $transaction->id); !!}
    {!! Form::hidden('type_pay', $type); !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'purchase.add_payment' ) capital</h4>
    </div>
    <div class="modal-body">

     <div class="row">
        @if(!empty($transaction->contact))
          <div class="col-md-4">
            <div class="well">
              <strong>
              @if(in_array($transaction->type, ['purchase', 'purchase_return']))
                @lang('purchase.supplier') 
              @elseif(in_array($transaction->type, ['sell', 'sell_return']))
                @lang('contact.customer') 
              @endif
              </strong>:{{ $transaction->contact->full_name_with_business }}<br>
              <strong>@lang('business.business'): </strong>{{ $transaction->contact->supplier_business_name }}
            </div>
          </div>
        @endif
        <div class="col-md-4">
          <div class="well">
          @if(in_array($transaction->type, ['sell', 'sell_return']))
            <strong>@lang('sale.invoice_no'): </strong>{{ $transaction->invoice_no }}
          @else
            <strong>@lang('purchase.ref_no'): </strong>{{ $transaction->ref_no }}
          @endif
          @if(!empty($transaction->location))
            <br>
            <strong>@lang('purchase.location'): </strong>{{ $transaction->location->name }}
          @endif
          </div>
        </div>
        <div class="col-md-4">
          <div class="well">
            <strong>@lang('sale.total_amount'): </strong><span class="display_currency" data-currency_symbol="true">{{ $transaction->final_total }}</span><br>
            <strong>@lang('purchase.payment_note'): </strong>
            @if(!empty($transaction->additional_notes))
            {{ $transaction->additional_notes }}
            @else
              --
            @endif
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
        <div class="col-md-12 hide" id="calculate_dollars">
          <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  {!! Form::label('exchange_rate', 'Cambio frente al dolar' . ':*') !!} 
                  <div class="input-group">
                      <span class="input-group-addon">
                        <i class="fas fa-money-bill-alt"></i>
                      </span>
                       {!! Form::number("exchange_rate",  '', ['class' => 'form-control required']); !!}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  {!! Form::label("amount_var" , 'Monto a cambiar'. ':*') !!}
                  <div class="input-group">
                    <span class="input-group-addon">
                      <i class="fas fa-money-bill-alt"></i>
                    </span>
                    {!! Form::text("amount_var",  '', ['class' => 'form-control required']); !!}
                  </div>
                </div>
              </div>
              <div class="col-md-4">
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
                {!! Form::text("amount", @num_format($amount), ['class' => 'form-control input_number payment_amount','required', 'placeholder' => 'Amount', 'data-rule-max-value' => @num_format($amount), 'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated])]); !!}
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
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
    
    {!! Form::close() !!}
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->