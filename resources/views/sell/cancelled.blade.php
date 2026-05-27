@extends('layouts.app')
@section('title', __('messages.cancel') . ' - ' . __('sale.sells'))

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
         Ventas Canceladas
    </h1>
</section>

<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_customer_id', __('contact.customer') . ':') !!}
                {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('created_by', __('report.user') . ':') !!}
                {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Ventas Canceladas'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="cancelled_sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('sale.invoice_no')</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('lang_v1.contact_no')</th>
                        <th>@lang('sale.location')</th>
                        <th>@lang('sale.total_amount')</th>
                        <th>@lang('sale.payment_status')</th>
                        <th>@lang('lang_v1.total_items')</th>
                        <th>@lang('lang_v1.added_by')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endcomponent
</section>

<div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<section class="invoice print_section" id="receipt_section"></section>

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function(start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            cancelled_sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        cancelled_sell_table.ajax.reload();
    });

    var cancelled_sell_table = $('#cancelled_sell_table').DataTable({
        processing: true,
        serverSide: true,
        fixedHeader: false,
        aaSorting: [[0, 'desc']],
        ajax: {
            url: '/sells/cancelled-dt',
            data: function(d) {
                if ($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.created_by = $('#created_by').val();
            }
        },
        columnDefs: [{
            targets: 9,
            orderable: false,
            searchable: false
        }],
        columns: [
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'name', name: 'contacts.name' },
            { data: 'mobile', name: 'contacts.mobile' },
            { data: 'business_location', name: 'bl.name' },
            { data: 'final_total', name: 'final_total' },
            { data: 'payment_status', name: 'payment_status', orderable: false, searchable: false },
            { data: 'total_items', name: 'total_items', searchable: false },
            { data: 'added_by', name: 'added_by' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#cancelled_sell_table'));
        }
    });

    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #created_by', function() {
        cancelled_sell_table.ajax.reload();
    });

    $(document).on('click', '.restore-sell', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: false,
        }).then(function(willRestore) {
            if (willRestore) {
                $.ajax({
                    method: 'GET',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            cancelled_sell_table.ajax.reload();
                        }
                    },
                });
            }
        });
    });
});
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
