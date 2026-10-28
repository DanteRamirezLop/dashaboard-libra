<thead>
    <tr>
        <th>&nbsp;N° Letra</th>
        <th>Fecha vencimiento</th>
        <th>Estado</th>
        <th>Saldo inicial</th>
        <th>+Tramite&nbsp;</th>
        <th>+GPS&nbsp;&nbsp;</th>
        <th>+Seguro</th>
        <th>+Inicial</th>
        <th>Pago</th>
        <th>Capital</th>
        <th>Intereses</th>
        <th>Saldo final</th>
        @if(($showActions ?? true) && auth()->user()->can('loans.update'))
            <th>@lang('messages.action')</th>
        @endif
    </tr>
</thead>
