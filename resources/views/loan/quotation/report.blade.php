@extends('layouts.app')
    @section('title', __('loans.report_quotations'))
@section('content')


<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
        @can('loans.all_quotations')
            {{__('loans.all_quotations')}}
        @else
            {{__('loans.my_quotations')}}
        @endcan
    </h1>
</section>

<section class="content">  
            <!-- Main content -->

        <div class="row no-print">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                {!! Form::open(['url' => action([\App\Http\Controllers\LoanQuotationController::class, 'report']), 'method' => 'get' ]) !!}
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('trending_product_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'trending_product_date_range', 'readonly']); !!}
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-md tw-text-white pull-right">@lang('report.apply_filters')</button>
                    </div> 
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => ''])
                    {!! $sells_chart_1->container() !!}
                @endcomponent
            </div>
        </div>
    
</section>
<!-- /.content -->
@stop

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    {!! $sells_chart_1->script() !!}
@endsection