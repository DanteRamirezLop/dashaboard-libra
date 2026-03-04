
<table class="table table-bordered table-striped" id="loans_table">
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
            <th>@lang( 'messages.action' )</th>
        </tr>
    </thead>
    <tbody>
        @foreach($paymentSchedules as $key=>$item)
            <tr>
                <td>{{$item->number_letter}} </td>                            
                <td>     
                    @php
                        $fecha = Carbon::parse($item->sheduled_date);
                        $date = $fecha->isoFormat('dddd MMMM D\, Y'); 
                    @endphp
                    {{$date}}
                </td>
                <td>
                    {!!$item->getLoanStatus() !!}
                </td>
                
                <td>@format_currency($item->opening_balance)</td>
                <td>@format_currency($item->admin_fee_quota)</td>
                <td>@format_currency($item->gps_quota)</td>
                <td>@format_currency($item->sure_quota)</td>
                <td>@format_currency($item->initial)</td>
                <td>@format_currency(($item->mount_quota + $item->gps_quota + $item->sure_quota + $item->admin_fee_quota + $item->initial )) </td> 
                <td>@format_currency($item->capital)</td>
                <td>@format_currency($item->interests)</td>
                <td>@format_currency($item->final_balance)</td>
                    
                <td>  
                    @if($item->status == "paid")
                        <span class="label bg-light-green"><i class="fas fa-check"></i> @lang( 'loans.paid_payment' )</span>
                    @else
                        <a href="{{route('add.pay.loan',$item->id)}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> Agregar Pago  &nbsp;</a> <br> 
                    @endif

                    @if($item->delay)
                        @if($item->delay->status == "late")
                            <a href="{{route('delays.show',$item->id)}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error">
                                <i class="fas fa fa-money-bill-alt"></i> Gestionar Mora
                            </a> 
                        @else
                            <a href="{{route('delays.show',$item->id)}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">
                                <i class="fa fa-eye"></i> Ver Mora
                            </a> 
                        @endif
                    @else
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info add-create-delay" 
                            data-href="{{route('add.delay.loan',$item->id)}}" 
                            data-container=".delay_modal">
                            <i class="fa fa-plus"></i> Agregar Mora &nbsp;&nbsp;
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>