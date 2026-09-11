<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fa fa-history"></i>
                Historial de cronograma &mdash; {{ $loan->customer_name }}
            </h4>
        </div>

        <div class="modal-body">
            @if(count($blocks) <= 1)
                <p class="text-muted">Este préstamo no tiene reprogramaciones ni cambios de cronograma: sigue con el cronograma original.</p>
            @endif

            <ul class="nav nav-tabs" role="tablist">
                @foreach($blocks as $i => $block)
                    <li role="presentation" class="{{ $loop->last ? 'active' : '' }}">
                        <a href="#sh-tab-{{ $i }}" aria-controls="sh-tab-{{ $i }}" role="tab" data-toggle="tab">
                            {{ $block['label'] }}
                            @if($block['status'] == 'active')
                                <span class="label label-success">vigente</span>
                            @else
                                <span class="label label-default">histórico</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" style="padding-top:15px">
                @foreach($blocks as $i => $block)
                    <div role="tabpanel" class="tab-pane {{ $loop->last ? 'active' : '' }}" id="sh-tab-{{ $i }}">
                        <p>
                            @if($block['reason'])
                                <strong>Motivo:</strong> {{ $block['reason'] }} &mdash;
                            @endif
                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($block['generated_at'])->format('d/m/Y H:i') }}
                        </p>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered table-striped">
                                @include('loan.partials._schedule_thead', ['showActions' => false])
                                <tbody>
                                    @foreach($block['rows'] as $item)
                                        @include('loan.partials._schedule_row', ['item' => $item, 'showActions' => false])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>{{-- /modal-body --}}

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                Cerrar
            </button>
        </div>
    </div>
</div>
