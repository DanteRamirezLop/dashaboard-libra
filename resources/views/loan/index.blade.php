@extends('layouts.app')
@section('title', __('loans.loans'))

@section('content')
 <!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{__('loans.loans')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
        <input type="hidden" value="{{$type}}" id="loan_type">
        <!-- Nueva tabla -->
        @component('components.widget', ['class' => 'box-primary', 'title' => __('loans.all_loans')])
            @can('customer.view_own')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{route('loans.create')}}" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"> <i class="fa fa-plus"></i> @lang('messages.add') </a>
                    </div>
                @endslot
            @endcan
            @can('unit.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped " id="loan_table">
                        <thead>
                            <tr>
                                <th>@lang( 'messages.action' )</th>
                                <th>Creación</th>
                                <th>Cliente</th>
                                <th>Maquinaria</th>
                                <th>Código VIN</th> 
                                <th>Total venta</th>
                                <th>Total pagado</th>
                                <th>Total vencido</th>
                                <th>Mora</th>
                                <th>Total vencido <small style="color:#60687d">(Vencido+Mora)</small></th>
                                <th>Total por vencer</th>
                                <th>Total debido <small style="color:#60687d">(Vencido+Mora+Por&nbsp;vencer)</small></th>
                                <th>Vendedor</th>
                                <th>Cuotas</th>
                                <th>Importe prestamo</th> 
                                <th>Estado prestamo</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="bg-gray  footer-total text-center">
                                <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                <td class="footer_sale_total"></td>
                                <td class="footer_total_paid"></td>
                                <td class="footer_total_delay"></td>
                                <td class="footer_total_mora"></td>
                                <td class="footer_total_remaining"></td>
                                <td class="footer_total_to_delay"></td>
                                <td class="footer_total_remaining_mora"></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcan
        @endcomponent
         <!-- END Nueva tabla-->
        <div class="modal fade loan_modal" tabindex="-1" role="dialog" 
            aria-labelledby="gridSystemModalLabel">
        </div>
</section>

<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
     loan_table = $('#loan_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        ajax: '/loans',
        columnDefs: [
            {
                targets: 3,
                orderable: true,
                searchable: true,
            },
        ],
        scrollY:        "75vh",
        scrollX:        true,
        scrollCollapse: true,
        columns: [
            { data: 'action', name: 'action', orderable: false, "searchable": false},
            { data: 'created_at', name: 'created_at' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'product_name', name: 'product_name' },
            { data: 'vin', name: 'vin' },
            { data: 'final_total',name:'final_total'},
            { data: 'total_paid',name:'total_paid'},
            { data: 'total_delay',name:'total_delay'},
            { data: 'total_mora',name:'total_mora'},
            { data: 'total_remaining',name:'total_remaining'},
            { data: 'total_to_delay',name:'total_to_delay'},
            { data: 'total_remaining_mora',name:'total_remaining_mora'},
            { data: 'waiter', name: 'waiter' },
            { data: 'number_month', name: 'number_month' },
            { data: 'balance_to_financed', name: 'balance_to_financed' },
            { data: 'label',name:'label'},
        ],
         "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#loan_table'));
        },
         "footerCallback": function ( row, data, start, end, display ) {
            var footer_sale_total = 0;
            var footer_total_paid = 0;
            var footer_total_remaining = 0;
            var footer_total_sell_return_due = 0;
            var footer_total_delay = 0;
            var footer_total_to_delay = 0;
            var footer_total_mora = 0;
            var footer_total_remaining_mora = 0;
           
            for (var r in data){
                footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(data[r].final_total).data('orig-value')) : 0;
                footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(data[r].total_paid).data('orig-value')) : 0;
                footer_total_remaining += $(data[r].total_remaining).data('orig-value') ? parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due').data('orig-value') ? parseFloat($(data[r].return_due).find('.sell_return_due').data('orig-value')) : 0;

                footer_total_delay += $(data[r].total_delay).data('orig-value') ? parseFloat($(data[r].total_delay).data('orig-value')) : 0;
                footer_total_to_delay += $(data[r].total_to_delay).data('orig-value') ? parseFloat($(data[r].total_to_delay).data('orig-value')) : 0;

                footer_total_mora += $(data[r].total_mora).data('orig-value') ? parseFloat($(data[r].total_mora).data('orig-value')) : 0;
                footer_total_remaining_mora += $(data[r].total_remaining_mora).data('orig-value') ? parseFloat($(data[r].total_remaining_mora).data('orig-value')) : 0;
            }
            
            $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due));
            $('.footer_total_delay').html(__currency_trans_from_en(footer_total_delay));
            $('.footer_total_to_delay').html(__currency_trans_from_en(footer_total_to_delay));
            $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));

            $('.footer_total_mora').html(__currency_trans_from_en(footer_total_mora));
            $('.footer_total_remaining_mora').html(__currency_trans_from_en(footer_total_remaining_mora));

            $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
            $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));
            $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
        },
    });

    $(document).on('click', '.delete_loan_button', function(e) {
        e.preventDefault();
        console.log('daddaan');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).attr('href');
                var is_suspended = $(this).hasClass('is_suspended');
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            loan_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    //Metodo ajax para generear el pdf
    //  $(document).on('click', '.generate-pdf', function(e) {
    //         e.preventDefault();
    //         var href = $(this).attr('href');
    //         var id = $(this).data('id');
    //         $.ajax({
    //             method: 'POST',
    //             url: href,
    //             dataType: 'json',
    //             data: {
    //                 id:id,
    //             },
    //             success: function(result) {
    //                 console.log('bien');
    //             }
    //         });
    //  });
});
</script>
<script src="{{ asset('js/payment.js')}}"></script>
@endsection
