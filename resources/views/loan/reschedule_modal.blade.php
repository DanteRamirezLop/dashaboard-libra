<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fa fa-calendar"></i>
                Reprogramar fechas &mdash; {{ $loan->customer_name }}
            </h4>
        </div>

        <form action="{{ route('loan.reschedule.store', $loan->id) }}" method="POST" id="reschedule_form">
        @csrf
        <div class="modal-body">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>A partir de la cuota</label>
                        <select class="form-control" id="rs_from_quota" name="from_number_quota" required>
                            @foreach($paymentSchedules as $ps)
                                <option value="{{ $ps->number_quota }}" {{ ($nextPendingQuota ?? null) == $ps->number_quota ? 'selected' : '' }}>
                                    #{{ $ps->number_quota }} &mdash; {{ \Carbon\Carbon::parse($ps->sheduled_date)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Correr fechas (meses)</label>
                        <input type="number" class="form-control" id="rs_months" name="months" value="1" min="1" max="12" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Motivo</label>
                        <input type="text" class="form-control" name="reason" placeholder="Ej: Acuerdo con el cliente para postergar un mes" required maxlength="255">
                    </div>
                </div>
            </div>

            <p class="text-muted">
                Se correrán solo las cuotas <strong>desde la seleccionada en adelante</strong>; las anteriores mantienen su fecha.
                El cronograma vigente no se borra: queda guardado como historial y podrás consultarlo luego en <em>Ver historial de cronograma</em>.
            </p>

            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr style="background:#ecf0f1;">
                            <th width="50">#</th>
                            <th>Fecha actual</th>
                            <th>Nueva fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="rs_preview_body">
                        @foreach($paymentSchedules as $ps)
                        <tr data-num="{{ $ps->number_quota }}" data-date="{{ \Carbon\Carbon::parse($ps->sheduled_date)->format('Y-m-d') }}">
                            <td>{{ $ps->number_quota }}</td>
                            <td>{{ \Carbon\Carbon::parse($ps->sheduled_date)->format('d/m/Y') }}</td>
                            <td class="rs_new_date">&mdash;</td>
                            <td>{{ number_format($ps->getQuote(), 2) }}</td>
                            <td>{!! $ps->getLoanStatus() !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>{{-- /modal-body --}}

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                Cancelar
            </button>
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="rs_submit_btn">
                Aplicar reprogramación
            </button>
        </div>
        </form>
    </div>
</div>

<script>
(function () {
    function recalcPreview() {
        var months   = parseInt($('#rs_months').val(), 10) || 0;
        var fromNum  = parseInt($('#rs_from_quota').val(), 10) || 0;

        $('#rs_preview_body tr').each(function () {
            var $row = $(this);
            var num  = parseInt($row.data('num'), 10);
            var base = moment($row.data('date'), 'YYYY-MM-DD');

            if (num >= fromNum) {
                $row.find('.rs_new_date').text(base.add(months, 'months').format('DD/MM/YYYY'));
                $row.removeClass('text-muted');
            } else {
                $row.find('.rs_new_date').text(base.format('DD/MM/YYYY') + ' (sin cambio)');
                $row.addClass('text-muted');
            }
        });
    }

    $('#rs_months, #rs_from_quota').on('input change', recalcPreview);
    recalcPreview();
})();
</script>
