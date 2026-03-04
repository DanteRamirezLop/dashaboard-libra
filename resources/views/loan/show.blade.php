@extends('layouts.app')
@section('title', __('loans.calendar_payments'))
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         <a href="{{route('loans.index')}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white "> <span class="fa fa-arrow-left"></span></a>
        {{$customer->supplier_business_name}}{{$customer->name}}
    </h1>
</section>
<!-- Main content -->
<section class="content no-print">

        <div class="row">
            <div class="col-lg-12">
                @component('components.widget', ['class' => 'box-success', 'title' => 'Resumen del préstamo']) 
                <div class="row"> 
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
                                    <td>  @format_currency($total) </td>
                                </tr>                            
                            </tbody>
                        </table>                
                    </div>

                    <div class="col-lg-6">                
                        <table class="table table-bordered table-striped dataTable">
                            <tbody>     
                                <tr>
                                    <th scope="row">Inicial + Coste tramite + Inicial GPS + Inicial seguro</th>
                                    <td> @format_currency($loan->initial_amount + $loan->initial_admin_fee + $loan->initial_gps + $loan->initial_insurance)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Saldo a Financiar</th>
                                    <td> @format_currency($loan->balance_to_financed) </td>
                                </tr>
                                <tr>
                                    <th scope="row">Número de pagos</th>
                                    <td>{{$loan->number_month}} </td>
                                </tr>
                                <tr>
                                    <th scope="row">Tasa de interés anual</th>
                                    <td>{{ number_format($loan->annual_interest_rate, 2)}}%</td>
                                </tr>
                                <tr>
                                    <th scope="row">Importe total de los intereses</th>
                                    <td> @format_currency($loan->total_amount_interest)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Coste del tramite</th>
                                <td> @format_currency($loan->initial_admin_fee + $loan->admin_fee_quotes)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Costo total del GPS</th>
                                    <td> @format_currency(($loan->initial_gps + $loan->gps_quotes))</td>
                                </tr>
                                <tr>
                                    <th scope="row">Costo total del Seguro</th>
                                    <td> @format_currency(($loan->initial_insurance + $loan->insurance_quotes))</td>
                                </tr>
                                <tr>
                                    <th scope="row">Tasa de inicial</th>
                                    <td> @format_currency($loan->start_rate) </td>
                                </tr>
                                <tr>
                                    <th scope="row">Coste total del préstamo</th>
                                    <td><strong>  @format_currency($loan->total_cost_loan)</strong>
                                    </td>
                                </tr>
                                @if($loan->interest_saved)
                                <tr>
                                    <th scope="row">Descuento por pago a capital</th>
                                    <td> @format_currency($loan->interest_saved) </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>                
                    </div>
                </div>
                @endcomponent
            </div>
        </div>
        
        @if($annexes)
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
                            <td> {{$annexes->anexo_1}}</td>
                            <td> {{$annexes->anexo_2}} </td>
                            <td> {{$annexes->anexo_3}}</td>
                            <td> {{$annexes->anexo_4}}</td>
                        </tr>
                    </tbody>
                </table> 
            @endcomponent
        @endif
       
        @component('components.widget', ['class' => 'box-primary', 'title' => __('loans.all_lletters_payments')]) 
            <div class="box-tools grap-2">
                <button class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="update-btn" data-id="{{$loan->id}}" > <i class="fa fa-sync "></i> Actualizar Estados</button>  
                <span class="label label-default text-center ">
                    @if($countVersion)
                         Nuevo conograma de pagos versión <span class="label" style="background-color: #fff !important;color: #615ca8 !important;">{{$countVersion}}</span>
                    @else
                        Conograma de pagos original
                    @endif
                </span>
            </div>
            <div class="tab-content mt-5">
                <div class="table-responsive" id="table_quotes"> 
                    @include('loan.table_quotes')
                </div>
            </div>  
        @endcomponent

        @if($there_is_mora)
            <div class="box box-warning" >
                <div class="box-body text-center">
                    <h3 class="text-center"> <i class="fa fa-exclamation-triangle text-yellow"></i>  Tienes deuda por concepto de mora</h3>
                    <p>Puede pagarla en Gestionar Mora</p>
                </div>
            </div>
        @else
            @if($canPayCapital)
            <div class="box box-primary" >
                <div class="box-header">
                    <span class="box-title mt-5">Pago a capital</span> 
                    <a class="margin-left-10 btn btn btn-success pull-right add_payment_modal"  href="{{route('add.capital.loan',['loan_id'=>$loan->id,'type'=>'total']) }}"> <i class="fas fa fa-money-bill-wave-alt"></i> Pagar todo </a> 
                    <a class="margin-left-10 btn btn btn-success pull-right add_payment_modal"  href="{{route('add.capital.loan',['loan_id'=>$loan->id,'type'=>'parcial']) }}"> <i class="fas fa fa-hand-holding-usd"></i> Pagar a capital</a> 
                </div>
            </div>
            @else
                <div class="box box-warning" >
                    <div class="box-body text-center">
                        <h3 class="text-center"> <i class="fa fa-exclamation-triangle text-yellow"></i>  Tienes que completar todos tus pagos parciales</h3>
                    </div>
                </div>
            @endif
        @endif
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade delay_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

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

        $(document).on('click', '.add_payment_modal', function(e) {
            e.preventDefault();
            var container = $('.payment_modal');
            $.ajax({
                url: $(this).attr('href'),
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'due') {
                        container.html(result.view).modal('show');
                         __currency_convert_recursively(container);
                        $('#paid_on').datetimepicker({
                            format: moment_date_format + ' ' + moment_time_format,
                            ignoreReadonly: true,
                        });
                        container.find('form#transaction_payment_add_form').validate();
                        set_default_payment_account();

                        $('.payment_modal')
                            .find('input[type="checkbox"].input-icheck')
                            .each(function() {
                                $(this).iCheck({
                                    checkboxClass: 'icheckbox_square-blue',
                                    radioClass: 'iradio_square-blue',
                                });
                            });
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        //cambios de Soles a dolares
        $(document).on('change', '.currency_types_dropdown', function(e) {
            console.log('CHANGE');

            var payment_type = $('#transaction_payment_add_form .currency_types_dropdown').val();
            calculate_dollars = $('#calculate_dollars');
            amount = $('#amount');
            // var is_pay_regulate = $('input[name="optionPay"]:checked').val();
            // if(is_pay_regulate == 1 ){
                if(payment_type == 'Dolar'){
                    calculate_dollars.addClass('hide');
                    amount.prop('readonly', false);
                }else{
                    calculate_dollars.removeClass('hide');
                    amount.prop('readonly', true);
                }
            // }else{
            //     amount.prop('readonly', true);
            //     if(payment_type == 'Dolar'){
            //         calculate_dollars.addClass('hide');
            //     }else{
            //         calculate_dollars.removeClass('hide');
            //     }
            // }

        });

         $(document).on('change', 'input[type=radio][name=optionPay]', function(e) {
            if ($(this).val() == 1) {
                //$(".regular_payment").removeClass('hide');
                $("#prepayment").addClass('hide');
               
            } else {
                //$(".regular_payment").addClass('hide');
                $("#prepayment").removeClass('hide');
                // $('#amount').prop('readonly', true); 
            }
        });

        $(document).on('change', '.currency_change_dropdown', function(e) {
            var payment_type = $('#transaction_payment_add_form .currency_change_dropdown').val();
            calculate_dollars = $('#calculate_dollars');
            amount = $('#amount');
            if(payment_type == 'Dolar'){
                calculate_dollars.addClass('hide');
                
            }else{
                 calculate_dollars.removeClass('hide');
                $('#amount_var').prop('readonly', true);
                
            }
        });

        $(document).on('click', '#calculate_sol', function(e) {
            const exchangeRate = parseFloat($('#exchange_rate').val());
            const amount_input = parseFloat($('#amount').val().replace(/,/g, ''));
            const amount = parseFloat(amount_input);
            if (isNaN(amount) || isNaN(exchangeRate)) {
                $('#resultado').text('Por favor ingresa valores válidos.');
                return;
            }
            const soles = amount * exchangeRate;
            $('#amount_var').val(soles.toFixed(2));
        });

        $(document).on('click', '#calculate', function(e) {
            const exchangeRate = parseFloat($('#exchange_rate').val());
            const amountVar = parseFloat($('#amount_var').val());
            if (isNaN(amountVar) || isNaN(exchangeRate) || exchangeRate === 0) {
                $('#resultado').text('Por favor ingresa valores válidos.');
                return;
            }
            const dolares = amountVar / exchangeRate;
            $('#amount').val(dolares.toFixed(2));
        });

        function set_default_payment_account() {
            var default_accounts = {};
            if (!_.isUndefined($('#transaction_payment_add_form #default_payment_accounts').val())) {
                default_accounts = JSON.parse($('#transaction_payment_add_form #default_payment_accounts').val());
            }
        }

        //Accion de ocultar
        $(document).on('change', '.payment_types_dropdown', function(e) {
            var payment_type = $('#transaction_payment_add_form .payment_types_dropdown').val();
            account_dropdown = $('#transaction_payment_add_form #account_id');
            if (payment_type == 'cash' || payment_type == 'cheque') {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', true);
                    account_dropdown.closest('.form-group').addClass('hide');
                }
            } else {
                if (account_dropdown) {
                    account_dropdown.prop('disabled', false); 
                    account_dropdown.closest('.form-group').removeClass('hide');
                }    
            }
        });

        //Crear deuda
        $(document).on('click', '.add-create-delay', function(e) {
            e.preventDefault();
            var refcontainer = $(this).data('container');
            var container = $(refcontainer);
            $.ajax({
                url: $(this).data('href'),
                dataType: 'html',
                success: function(result) {
                    container.html(result).modal('show');
                    __currency_convert_recursively(container);
                    $('#late_date').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                    });
                },
            });
        });

        $(document).ready(function () {
            $("#update-btn").on('click', function () {
                let id = $(this).data('id');
                swal({
                    title: "Estás seguro?",
                    text: "Las letras con fecha vencida y sin pagos cambiarán al estado EN MORA",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    $.ajax({
                        url: '/loans/' + id, // id dinámico
                        type: 'PUT',
                        data: {},
                        success: function (result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                $('#table_quotes').load("/loans/table/"+ id);
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                });
            });
        });
    </script>
@endsection
