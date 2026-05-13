
@php
    $currentSchedules    = $paymentSchedules->whereNull('refinanced_at');
    $refinancedSchedules = $paymentSchedules->whereNotNull('refinanced_at');
    $hasRefinanced       = $refinancedSchedules->isNotEmpty();
@endphp

@if($hasRefinanced)
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#tab-current" aria-controls="tab-current" role="tab" data-toggle="tab">
            <i class="fa fa-calendar-alt"></i> Cronograma Actual
            <span class="badge">{{ $currentSchedules->count() }}</span>
        </a>
    </li>
    <li role="presentation">
        <a href="#tab-refinanced" aria-controls="tab-refinanced" role="tab" data-toggle="tab">
            <i class="fa fa-history"></i> Cronograma Refinanciado
            <span class="badge">{{ $refinancedSchedules->count() }}</span>
        </a>
    </li>
</ul>
<div class="tab-content" style="padding-top:15px">
    <div role="tabpanel" class="tab-pane active" id="tab-current">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="loans_table">
                @include('loan.partials._schedule_thead')
                <tbody>
                    @foreach($currentSchedules as $item)
                        @include('loan.partials._schedule_row', ['item' => $item, 'showActions' => true])
                    @endforeach
                </tbody>
            </table>
    
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="tab-refinanced">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="loans_table_refinanced">
                @include('loan.partials._schedule_thead')
                <tbody>
                    @foreach($refinancedSchedules as $item)
                        @include('loan.partials._schedule_row', ['item' => $item, 'showActions' => false])
                    @endforeach
                </tbody>
            </table>
       
        </div>
    </div>
</div>

@else

<table class="table table-bordered table-striped" id="loans_table">
    @include('loan.partials._schedule_thead')
    <tbody>
        @foreach($currentSchedules as $item)
            @include('loan.partials._schedule_row', ['item' => $item, 'showActions' => true])
        @endforeach
    </tbody>
</table>

@endif
