@extends('layouts.app')
@section('title', 'Detalle de la cotización')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         <a href="{{url('loans-quotations')}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white "> <span class="fa fa-arrow-left"></span></a>
        {{$customer->supplier_business_name}}{{$customer->name}}
    </h1>
</section>
<!-- Main content -->
<section class="content">    
	<div class="row">
            <div class="col-lg-12">
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Resumen de la cotización'])  
                <div class="col-lg-6">                
                    <table class="table table-bordered table-striped dataTable">
                        <tbody>
                            <tr>
                                <th scope="row">Cliente</th>
                                <td>{{$customer->supplier_business_name}}{{$customer->name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Maquinaria</th>
                                <td>{{$loan->product_name}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Fecha de cotización</th>
                                <td>
                                    @php
                                        $fecha = Carbon::parse($loan->created_at);
                                        $date = $fecha->isoFormat('dddd MMMM D\, Y'); 
                                    @endphp
                                    {{$date}}
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Total a pagar</th>
                                <td>  @format_currency($total) </td>
                            </tr>                            
                        </tbody>
                    </table>                
                </div>

                @if($loan->type_quotation == 2)  
                    <div class="col-lg-6">                
                        <table class="table table-bordered table-striped dataTable">
                            <tbody>     
                                <tr>
                                    <th scope="row">Inicial + Coste tramite + Inicial GPS + Inicial seguro</th>
                                    <td> 
                                       @format_currency($loan->initial_amount + $loan->initial_admin_fee + $loan->gps + $loan->insurance)
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Saldo a Financiar</th>
                                    <td>  @format_currency($loan->balance_to_financed) </td>
                                </tr>
                                <tr>
                                    <th scope="row">Número de pagos</th>
                                    <td>  {{$loan->number_month}} </td>
                                </tr>
                                <tr>
                                    <th scope="row">Tasa de interés anual</th>
                                    <td>{{ number_format($loan->annual_interest_rate, 2) }}%</td>
                                </tr>
                                <tr>
                                    <th scope="row">Importe total de los intereses</th>
                                    <td>@format_currency($loan->total_amount_interest)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Coste del tramite</th>
                                    <td>@format_currency($loan->admin_fee)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Costo total del GPS</th>
                                    <td>@format_currency($loan->initial_gps + $loan->gps_quotes)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Costo total del Seguro</th>
                                    <td>@format_currency($loan->initial_insurance + $loan->insurance_quotes)</td>
                                </tr>
                                <tr>
                                <th scope="row">Tasa de inicial</th>
                                    <td>@format_currency($loan->start_rate)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Coste total del préstamo</th>
                                    <td><strong> @format_currency($loan->total_cost_loan) </strong></td>
                                </tr>
                            </tbody>
                        </table>                
                    </div>
                @endif
                @endcomponent
            </div>
        </div>

    @if($loan->type_quotation == 2)                                
            <input type="hidden" value="{{$type}}" id="loan_type">
            @component('components.widget', ['class' => 'box-primary', 'title' => __( 'loand.schedule' )])        
            <table class="table table-bordered table-striped dataTable text-center" id="loans_table">
                <thead>
                    <tr>
                        <th>@lang( 'loand.letter' )</th>
                        <th>@lang( 'loand.payment_date' )</th>
                        <th>Saldo inicial</th>
                        <th>+GPS</th> 
                        <th>+Seguro</th>
                        <th>+Inicial</th> 
                        <th>Pago</th> 
                        <th>Capital</th> 
                        <th>Intereses</th> 
                        <th>Saldo final</th> 
                    </tr>
                </thead>
                <tbody>
                    @php
                        $count=0;
                    @endphp
                        @foreach($detail as $key=>$item)
                            <tr>
                                <td>{{$item->id}}</td>                            
                                <td>     
                                    @php
                                        $fecha = Carbon::parse($item->date);
                                        $date = $fecha->isoFormat('dddd MMMM D\, Y'); 
                                    @endphp
                                    {{$date}}
                                </td>
                                <td>@format_currency($item->saldo_inicial)</td>
                                <td>@format_currency($item->gps)</td>
                                <td>@format_currency($item->seguro) </td>
                                @if(isset($item->initial))
                                    <td>@format_currency($item->initial)</td>
                                    <td>@format_currency($item->amount + $item->gps + $item->seguro + $item->initial)</td>
                                @else
                                    <td>$0.00</td>
                                    <td>@format_currency($item->amount + $item->gps + $item->seguro)</td>
                                @endif
                                <td>@format_currency($item->capital)</td>
                                <td>@format_currency($item->interes)</td>
                                <td>@format_currency($item->saldo_final)</td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
        @endcomponent
    @endif
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        //Date range as a button
        $('#purchase_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            purchase_table.ajax.reload();
            }
        );
        $('#purchase_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#purchase_list_filter_date_range').val('');
            purchase_table.ajax.reload();
        });  

        $(document).on('click', 'button.pagar', function(){
            swal({
                title: LANG.sure,
                text: LANG.confirm_pagar,
                icon: "info",
                buttons: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var id = $(this).attr("data-id");
                    var quote = $(this).attr("data-id-quote");
                    $.ajax({
                        method: "POST",
                        url: "/loan_edit_quote",
                        dataType: "json",
                        data: {id: id,quote: quote},
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                location.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
