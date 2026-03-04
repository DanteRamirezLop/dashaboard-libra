@extends('layouts.app')
@section('title', __('loans.number_letter'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         <a href="{{route('loans.index')}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white "> <span class="fa fa-arrow-left"></span></a>
        {{$customer->supplier_business_name}}{{$customer->name}}
    </h1>
</section>

<!-- Main content -->
<section class="content">    
	<div class="row">
        <div class="col-lg-12">
            @component('components.widget', ['class' => 'box-success', 'title' => 'Resumen del préstamo'])  
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
                                <th scope="row">Código VIN</th>
                                <td>{{$loan->vin}}</td>
                            </tr>
                            <tr>
                                <th scope="row">Fecha del prestamo</th>
                                <td>
                                    @php
                                        $fecha = Carbon::parse($loan->date);
                                        $date = $fecha->isoFormat('dddd MMMM D\, Y'); 
                                    @endphp
                                    {{$date}}
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Total a pagar</th>
                                <td> @format_currency($total)</td>
                            </tr>                            
                        </tbody>
                    </table>                
                </div>
    
                <div class="col-lg-6">                
                    <table class="table table-bordered table-striped dataTable">
                        <tbody>     
                            <tr>
                                <th scope="row">Inicial + Coste tramite + Inicial GPS + Inicial seguro</th>
                                <td>@format_currency($loan->initial_amount + $loan->initial_admin_fee + $loan->initial_gps + $loan->initial_insurance)</td>
                            </tr>
                            <tr>
                                <th scope="row">Saldo a Financiar</th>
                                <td>@format_currency($loan->balance_to_financed)</td>
                            </tr>
                            <tr>
                                <th scope="row">Número de pagos</th>
                                <td>{{$loan->number_month}} </td>
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
                                <td>@format_currency($loan->initial_admin_fee)</td>
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
                                <th scope="row">Coste total del préstamo</th>
                                <td><strong> @format_currency($loan->total_cost_loan) </strong></td>
                            </tr>
                        </tbody>
                    </table>                
                </div>
                @endcomponent
            </div>

            @if($annexes)
                <div class="col-lg-12">
                    @component('components.widget', ['class' => 'box-success', 'title' => 'Información adicional']) 
                        <table class="table table-bordered table-striped dataTable">
                            <thead>
                                <tr>
                                    <th>Anexo 1</th>
                                    <th>Anexo 2</th> 
                                    <th>Anexo 3</th>
                                    <th>Anexo 4</th> 
                                </tr>
                            </thead>
                            <tbody>
                               <tr>
                                    <td id="{{$loan->id}}" data-id="anexo_1"> 
                                        <div class="eq-height-col tw-gap-1">
                                            <input type="text" class="form-control annexe" value="{{$annexes->anexo_1}}" placeholder="Anexo 1"> 
                                            <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white editar-btn-annexes "> Actualizar </button>
                                        </div>
                                    </td>
                                    <td id="{{$loan->id}}" data-id="anexo_2"> 
                                        <div class="eq-height-col tw-gap-1">
                                            <input type="text" class="form-control annexe"  value="{{$annexes->anexo_2}}" placeholder="Anexo 2"> 
                                            <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white  editar-btn-annexes"> Actualizar </button>
                                        </div>
                                    </td>
                                     <td id="{{$loan->id}}" data-id="anexo_3"> 
                                        <div class="eq-height-col tw-gap-1">
                                        <input type="text" class="form-control annexe"  value="{{$annexes->anexo_3}}" placeholder="Anexo 3"> 
                                        <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white  editar-btn-annexes"> Actualizar </button>
                                        </div>
                                    </td>
                                     <td id="{{$loan->id}}" data-id="anexo_4">  
                                        <div class="eq-height-col tw-gap-1">
                                        <input type="text" class="form-control annexe"  value="{{$annexes->anexo_4}}" placeholder="Anexo 4"> 
                                        <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white  editar-btn-annexes"> Actualizar </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table> 
                    @endcomponent
                </div>
            @endif
        </div>
        
        @component('components.widget', ['class' => 'box-primary', 'title' => __( 'loans.all_lletters_payments' )]) 
           <div class="tab-content">
                <div class="table-responsive">        
                    <table class="table table-bordered table-striped dataTable" id="loans_table">
                        <thead>
                            <tr>
                                <th>Número de letra</th>
                                <th>Actualizar</th>
                                <th>@lang('loans.date_pay')</th>
                                <th>Saldo inicial</th>

                                <th>+Tramite&nbsp;</th> 
                                <th>+GPS&nbsp;&nbsp;</th> 
                                <th>+Seguro</th>    
                                <th>+Inicial</th>

                                <th>Pago</th> 
                                <th>Capital</th> 
                                <th>Intereses</th> 
                                <th>Saldo final</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentSchedules as $key=>$item)
                                <tr class="list-schedule" data-id="{{$item->id}}">
                                    <td>
                                        <input type="text" name="number_letter" class="form-control number_letter"  value="{{$item->number_letter}}" placeholder="001-0000">
                                    </td>
                                    <td class="text-center"> 
                                        <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white  editar-btn"> Actualizar </button>
                                    </td>                             
                                    <td>     
                                        @php
                                            $fecha = Carbon::parse($item->sheduled_date);
                                            $date = $fecha->isoFormat('dddd MMMM D\, Y'); 
                                        @endphp
                                        {{$date}}
                                    </td>
                                    <td>@format_currency($item->opening_balance)</td>

                                    <td>@format_currency($item->admin_fee_quota)</td>
                                    <td>@format_currency($item->gps_quota)</td>
                                    <td>@format_currency($item->sure_quota)</td>
                                    <td>@format_currency($item->initial)</td>

                                    <td>@format_currency(($item->mount_quota + $item->gps_quota + $item->sure_quota))</td>
                                    <td>@format_currency($item->capital)</td>
                                    <td>@format_currency($item->interests)</td>
                                    <td>@format_currency($item->final_balance)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endcomponent
</section>
@stop
@section('javascript')
    <script>
            $(document).ready(function () {
              $(".editar-btn").on('click', function () {
                let fila = $(this).closest(".list-schedule"); 
                let id = fila.data("id");
                let number_letter = fila.find(".number_letter").val();
                $.ajax({
                    type: "POST",
                    url: "/letter-annexe-update",
                    data: {
                        id: id,
                        value: number_letter,
                        type: 'letter',
                        celda: ''
                    },
                    dataType: "json",
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
            
            $(".editar-btn-annexes").on('click', function () {
               let fila = $(this).closest("td");
               let id = fila.attr("id");
               let celda = fila.data("id");
               let annexe = fila.find(".annexe").val(); 
                $.ajax({
                    type: "POST",
                    url: "/letter-annexe-update",
                    data: {
                        id: id,
                        value: annexe,
                        type: 'annexe',
                        celda: celda,
                    },
                    dataType: "json",
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
         });
    </script>
@endsection