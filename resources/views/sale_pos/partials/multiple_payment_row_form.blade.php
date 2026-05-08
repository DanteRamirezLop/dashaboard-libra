<div class="row">
    @php
        $col_class = !empty($accounts) ? 'col-md-4' : 'col-md-6';
        $currency_options = ['2' => 'Dolar (USD)', '94' => 'Sol (PE)'];
        $selected_currency_id = $payment_line['currency_id'] ?? ($currency_details->currency_id ?? null);
        $currency_label = $currency_options[$selected_currency_id] ?? $selected_currency_id;
    @endphp

    @if(!empty($show_date))
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("paid_on_ro_$row_index", __('lang_v1.paid_on') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                {!! Form::text("", isset($payment_line['paid_on']) ? @format_datetime($payment_line['paid_on']) : '', ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>
    @endif

    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("method_ro_$row_index", __('lang_v1.payment_method') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("", isset($payment_types[$payment_line['method']]) ? $payment_types[$payment_line['method']] : $payment_line['method'], ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>

    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("currency_ro_$row_index", 'Moneda:') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("", $currency_label, ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>

    @if(!empty($payment_line['exchange_rate']) && $payment_line['exchange_rate'] != 1)
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("exchange_rate_ro_$row_index", 'Tasa de cambio:') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("", @num_format($payment_line['exchange_rate']), ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>
    @endif

    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("amount_ro_$row_index", __('sale.amount') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("", @num_format($payment_line['amount']), ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>

    @if(!empty($accounts) && !empty($payment_line['account_id']))
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("account_ro_$row_index", __('lang_v1.payment_account') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                {!! Form::text("", isset($accounts[$payment_line['account_id']]) ? $accounts[$payment_line['account_id']] : '', ['class' => 'form-control', 'readonly', 'disabled']); !!}
            </div>
        </div>
    </div>
    @endif

    @if(!empty($payment_line['note']))
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("note_ro_$row_index", __('sale.payment_note') . ':') !!}
            {!! Form::textarea("", $payment_line['note'], ['class' => 'form-control', 'rows' => 2, 'readonly', 'disabled']); !!}
        </div>
    </div>
    @endif
</div>
