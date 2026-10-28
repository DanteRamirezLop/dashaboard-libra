@extends('layouts.app')
@section('title', __('loans.manage_arrears'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         <a href="{{route('loans.show',$loan->id)}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white "> <span class="fa fa-arrow-left"></span></a>
         {{$customer->supplier_business_name}}{{$customer->name}} <span class="tw-text-gray-500">{{date("d  M, Y",strtotime($payment_schedule->sheduled_date))}}</span>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <!-- Nueva tabla -->
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Gestionar pago moratorio'])
        @can('unit.view')
            @if($delay)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="delay_table">
                        <thead>
                            <tr>
                                <th>Inicio de la deuda</th>
                                <th>Días atrasado</th>
                                <th>Fecha a pagar</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>@lang( 'messages.action' )</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    @if($delay->start_date)
                                        {{ date('d/m/Y', strtotime($delay->start_date)) }}
                                    @endif
                                </td>
                                <td>{{$delay->days_late}}</td>
                                <td>{{ date('d/m/Y', strtotime($delay->late_date)) }}</td>
                                <td>{{$delay->late_amount}}</td>
                                <td>
                                    @switch($delay->status)
                                        @case("late")
                                            <span class="label label-danger">Atrasado</span>
                                            @break
                                        @case("regularized")
                                            <span class="label label-success">Regularizado</span>
                                            @break
                                        @case("condone")
                                            <span class="label label-warning">Condonado</span>
                                            @break
                                        @case("partial")
                                            <span class="label label-warning">Condonado Parcial</span>
                                    @endswitch
                                </td>
                                <td >
                                    @if($delay->status == "late")

                                        <a href="{{route('add.pay.delay',$delay->id)}}" data-type="pay" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success add_payment_modal">
                                            <i class="fas fa-money-bill-alt"></i> Agregar Pago
                                        </a> 

                                         <a href="{{route('add.pay.delay',$delay->id)}}" data-type="partial" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info add_payment_modal">
                                            <i class="fas fa-money-bill-alt"></i> Agregar y Condonar Parcial
                                        </a> 

                                        <button type="button" id="button-condonar" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-warning " data-id="{{$delay->id}}">
                                            <i class="fas fa-money-bill-alt"></i> Condonar
                                        </button> 

                                        <button data-href="{{action('App\Http\Controllers\DelayController@destroy', [$delay->id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_delay_button">
                                            <i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")
                                        </button>
                                    @else
                                         -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center"> Esta letra de pago no tiene ninguna deuda por concepto de mora</p>
            @endif
        @endcan
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

@stop

@section('javascript')
  <script type="text/javascript">
    //Eliminar Deuda
     $(document).on('click', 'button.delete_delay_button', function() {
        swal({
            title: 'Estás seguro ?',
            text: 'Esta deuda por concepto de mora será eliminada',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            location.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

     //Condonar deuda
     $("#button-condonar").click(function(e) {
        e.preventDefault();
        let token_location = $('meta[name="csrf-token"]').attr('content');
        var id = $(this).data("id");
        swal({
                title: "Estás seguro?",
                text: "Cambiará el estado de la deuda a condonada",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        type: "post",
                        url: "/condonar",
                        dataType: 'json',
                        data: {
                            _token: token_location,
                            id:id,
                        },
                        success: function (response) {
                            if(response.success){
                                toastr.success(response.msg);
                                location.reload();
                            }else{
                                toastr.error(response.msg);
                            }
                        },
                        error: function(xhr, status, error){
                            swal("Error...!!", 'Lo sentimos, algo salió mal inténtalo más tarde!', "error");
                        }
                    });
                } 
        });
    });

    //Date range as a button
    $('#purchase_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#purchase_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        purchase_table.ajax.reload();
        }
    );

    $(document).on('click', '.add_payment_modal', function(e) {
            e.preventDefault();
            var container = $('.payment_modal');
            var type = $(this).data('type');
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

                            $('[name=type_pay]').val(type);

                            if(type == 'partial'){
                                amount_sol = $('#amount_var');
                                amount = $('#amount');
                                amount.prop('readonly', false);
                                amount_sol.prop('readonly', false);
                            }
                            
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

          //cambios de Soles a dolares
        $(document).on('change', '.currency_types_dropdown', function(e) {
            var payment_type = $('#transaction_payment_add_form .currency_types_dropdown').val();
            calculate_dollars = $('#calculate_dollars');
            amount = $('#amount');
            type = $('[name="type_pay"]').val();

            if(payment_type == 'Dolar'){
                calculate_dollars.addClass('hide');
                if(type == 'partial'){
                    amount.prop('readonly',false);
                }
            }else{
                calculate_dollars.removeClass('hide');
                amount.prop('readonly', true);
            }
        });

        $(document).on('click', '#calculate', function(e) {

            const exchangeRate = parseFloat($('#exchange_rate').val());
            type = $('[name="type_pay"]').val();
            if(type == 'pay' ){
                const amount = parseFloat($('#amount').val());
                if (isNaN(amount) || isNaN(exchangeRate) || exchangeRate === 0) {
                    $('#resultado').text('Por favor ingresa valores válidos.');
                    return;
                }
                const soles = amount * exchangeRate;
                $('#amount_var').val(soles.toFixed(2));
            }else{
                 const amountVar = parseFloat($('#amount_var').val());
                if (isNaN(amountVar) || isNaN(exchangeRate) || exchangeRate === 0) {
                    $('#resultado').text('Por favor ingresa valores válidos.');
                    return;
                }
                const dolares = amountVar / exchangeRate;
                $('#amount').val(dolares.toFixed(2));
            }

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
</script>

@endsection

