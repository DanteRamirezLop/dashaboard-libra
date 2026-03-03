@extends('layouts.app')
@section('title', __('loans.loan'))


@section('content')
 <!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('loans.quotations')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
        <div class="nav-tabs-custom border-10" >
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#pending_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                        <i class="fas fa-credit-card text-orange"></i> @lang('loans.credit')
                    </a>
                </li>
                <li>
                    <a href="#completed_job_sheet_tab" data-toggle="tab" aria-expanded="true">
                        <i class="fa fas fa-money-bill-wave-alt text-success"></i> @lang('loans.cash')
                    </a>
                </li>
            </ul>
            <div>
            @component('components.widget', ['class' => 'box-primary','title' =>'Todas las cotizaciones'])
                @can('customer.view_own')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                                href="{{route('loans-quotations.create')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg> @lang('messages.add')  
                            </a>
                        </div>
                    @endslot
                @endcan
                <div class="tab-content">
                    <div class="tab-pane active" id="pending_job_sheet_tab">
                        @can('customer.view_own')
                            <div class="table-responsive">
                        
                                <table class="table table-bordered table-striped ajax_view" id="loan_table">
                                    <thead>
                                        <tr>
                                            <th>Fecha de la cotización</th>
                                            <th>Vendedor</th>
                                            <th>Cliente</th>
                                            <th>Atendido por</th>
                                            <th>Maquinaria</th>
                                            <th>Fuente de contacto</th>
                                            <th>Cuotas</th>
                                            <th>Importe del prestamo</th> 
                                            <th>Importe total de los intereses</th>
                                            <th>Coste total del préstamo</th> 
                                            <th>@lang( 'messages.action' )</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        @endcan
                    </div>
    
                    <div class="tab-pane" id="completed_job_sheet_tab">
                        @can('customer.view_own')
                            <div class="table-responsive ">
                                <table class="table table-bordered table-striped" id="loan_contado" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Fecha de la cotización</th>
                                            <th>Vendedor</th>
                                            <th>Cliente</th>
                                            <th>Atendido por</th>
                                            <th>Maquinaria</th>
                                            <th>Fuente de contacto</th>
                                            <th>Total</th>
                                            <th>@lang( 'messages.action' )</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        @endcan
                    </div>
                </div>
            @endcomponent
        </div> 
    </div>

    <!-- END Nueva tabla-->
    <div class="modal fade loan_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>
        
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready(function () {
    loan_table = $('#loan_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url:'/loans-quotations',
            "data": function ( d ) {
                 d.is_credit = 1;
            }
        },
    
        columnDefs: [{
                targets: 3,
                orderable: true,
                searchable: false,
        },],
        order: [
            [0, 'desc']
        ],
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'seller', name: 'seller' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'waiter', name: 'waiter' },
            { data: 'type_quotation', name: 'type_quotation' },
            { data: 'contact_source', name: 'contact_source' },
            { data: 'number_month', name: 'number_month' },
            { data: 'balance_to_financed', name: 'balance_to_financed' },
            { data: 'total_amount_interest', name: 'total_amount_interest' },
            { data: 'total_cost_loan', name: 'total_cost_loan' },
            { data: 'action', name: 'action' },
        ],
    });


    loan_contado = $('#loan_contado').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url:'/loans-quotations',
            "data": function ( d ) {
                 d.is_credit = 0;
            }
        },
        columnDefs: [{
                targets: 3,
                orderable: true,
                searchable: false,
            },],
        order: [
            [0, 'desc']
        ],
        columns: [
            { data: 'created_at', name: 'created_at' },
             { data: 'seller', name: 'seller' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'waiter', name: 'waiter' },
            { data: 'type_quotation', name: 'type_quotation' },
            { data: 'contact_source',name:'contact_source'},
            { data: 'product_price', name: 'product_price' },
            { data: 'action', name: 'action' },
        ],
        
    });


    $(document).on('click', 'button.delete_loan_button', function() {
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_loan,
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
                            loan_table.ajax.reload();
                            loan_contado.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});
    
</script>
@endsection
