<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header" >
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fa fa-refresh"></i>
                Refinanciamiento &mdash; {{ $loan->customer_name }}
            </h4>
        </div>

        {{-- Resumen de deuda actual --}}
        <form action="{{ route('loan.refinance.store', $loan->id) }}" method="POST" id="refinance_form">
        @csrf
        <div class="modal-body">
            <div class="row" style="margin-bottom:15px;">
                <div class="col-md-4">
                    <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-4">

                                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-red-500 tw-bg-red-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                    <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path
                                            d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                        </path>
                                        <path
                                            d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                        </path>
                                        <path d="M12 6v10"></path>
                                    </svg>
                                </div>

                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                        Monto a refinanciar
                                    </p>
                                    <p class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                         {{ number_format($total_debt, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-4">
                                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-yellow-500 tw-bg-yellow-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 shrink-0">
                                    <svg aria-hidden="true" class="tw-w-6 tw-h-6"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 9v4" />
                                        <path
                                            d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                        <path d="M12 16h.01" />
                                    </svg>
                                </div>

                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                       Mora total
                                    </p>
                                    <p class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                      {{ number_format($total_mora, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md tw-rounded-xl hover:tw--translate-y-0.5 tw-ring-1 tw-ring-gray-200">
                        <div class="tw-p-4 sm:tw-p-5">
                            <div class="tw-flex tw-items-center tw-gap-4">
                                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-green-500 tw-bg-green-100 tw-rounded-full sm:tw-w-12 sm:tw-h-12 tw-shrink-0">
                                    <svg aria-hidden="true" class="tw-w-6 tw-h-6" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path
                                            d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2">
                                        </path>
                                        <path
                                            d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1">
                                        </path>
                                        <path d="M12 6v10"></path>
                                    </svg>
                                </div>

                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate tw-whitespace-nowrap">
                                        Deuda total actual
                                    </p>
                                    <p class="net tw-mt-0.5 tw-text-gray-900 tw-text-xl tw-truncate tw-font-semibold tw-tracking-tight tw-font-mono">
                                        {{ number_format($total_debt, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

                {{-- Inputs ocultos que se actualizan por JS --}}
                <input type="hidden" name="refinance_amount" id="rf_refinance_amount" value="{{ $total_debt }}">

                <div class="row">
                    {{-- Mora a condonar --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Mora a condonar ($)</label>
                            <input type="text"
                                   class="form-control"
                                   id="rf_forgiven_mora"
                                   name="forgiven_mora"
                                   value="0"
                                   placeholder="0.00">
                            <small class="text-muted">Máx: {{ number_format($total_mora, 2) }}</small>
                        </div>
                    </div>
                    {{-- Motivo --}}
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Motivo del refinanciamiento</label>
                            <input type="text"
                                   class="form-control"
                                   name="reason"
                                   placeholder="Ej: Acuerdo de reestructuración 2026"
                                   value="">
                        </div>
                    </div>
                </div>

                {{-- Resumen calculado --}}
                <div class="bg-green" style="padding:8px 15px; margin-bottom:10px;">
                    Deuda total: <strong id="rf_debt_label">{{ number_format($total_debt, 2) }}</strong>
                    &minus; Condonación: <strong id="rf_forgiven_label">0.00</strong>
                    = Monto a refinanciar: <strong id="rf_result_label">{{ number_format($total_debt, 2) }}</strong>
                </div>

                {{-- Tabla de cuotas arbitrarias --}}
                <div class="table-responsive">
                    
                    <table class="table table-bordered table-condensed" id="rf_installments_table">
                        <thead>
                            <tr style="background:#ecf0f1;">
                                <th width="50">#</th>
                                <th>Fecha de pago</th>
                                <th>Monto ($)</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="rf_installments_body">
                            {{-- Fila inicial --}}
                            <tr>
                                <td class="rf_row_num">1</td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm rf_date"
                                           name="installments[0][date]"
                                           placeholder="dd/mm/aaaa"
                                           autocomplete="off">
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm rf_amount"
                                           name="installments[0][amount]"
                                           placeholder="0.00">
                                </td>
                                <td>
                                    <button type="button" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error rf_remove_row"> 
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right"><strong>Total cuotas:</strong></td>
                                <td><strong id="rf_installments_total">0.00</strong></td>
                                <td></td>
                            </tr>
                            <tr id="rf_diff_row" style="display:none;">
                                <td colspan="2" class="text-right text-danger"><strong>Diferencia:</strong></td>
                                <td class="text-danger"><strong id="rf_diff_label">0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-xs" id="rf_add_row">
                    <i class="fa fa-plus"></i> Agregar cuota
                </button>

        </div>{{-- /modal-body --}}

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                Cancelar
            </button>

            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white " id="rf_submit_btn" disabled>
                 Aplicar refinanciamiento
            </button>
        </div>
        </form>
    </div>
</div>

<script>
(function () {
    var totalDebt  = {{ $total_debt }};
    var maxMora    = {{ $total_mora }};
    var rowIndex   = 1;

    function parseAmount(val) {
        return parseFloat((val + '').replace(/,/g, '')) || 0;
    }

    function recalc() {
        var forgiven  = Math.min(parseAmount($('#rf_forgiven_mora').val()), maxMora);
        var refinance = Math.max(0, totalDebt - forgiven);

        $('#rf_forgiven_label').text(forgiven.toFixed(2));
        $('#rf_result_label').text(refinance.toFixed(2));
        $('#rf_display_refinance').text(refinance.toFixed(2));
        $('#rf_refinance_amount').val(refinance.toFixed(4));

        var installTotal = 0;
        $('.rf_amount').each(function () {
            installTotal += parseAmount($(this).val());
        });

        $('#rf_installments_total').text(installTotal.toFixed(2));

        var diff = Math.round((installTotal - refinance) * 100) / 100;

        if (Math.abs(diff) < 0.02) {
            $('#rf_diff_row').hide();
            $('#rf_submit_btn').prop('disabled', false);
            $('#rf_installments_total').removeClass('text-danger').addClass('text-success');
        } else {
            $('#rf_diff_row').show();
            $('#rf_diff_label').text(diff.toFixed(2));
            $('#rf_submit_btn').prop('disabled', true);
            $('#rf_installments_total').removeClass('text-success').addClass('text-danger');
        }
    }

    function renumber() {
        $('#rf_installments_body tr').each(function (i) {
            $(this).find('.rf_row_num').text(i + 1);
            $(this).find('.rf_date').attr('name', 'installments[' + i + '][date]');
            $(this).find('.rf_amount').attr('name', 'installments[' + i + '][amount]');
        });
    }

    // Inicializar datepicker en la primera fila
    $('.rf_date').datepicker({ dateFormat: 'dd/mm/yy', autoclose: true });

    // Agregar fila
    $('#rf_add_row').on('click', function () {
        var html = '<tr>' +
            '<td class="rf_row_num">' + (rowIndex + 1) + '</td>' +
            '<td><input type="text" class="form-control input-sm rf_date" name="installments[' + rowIndex + '][date]" placeholder="dd/mm/aaaa" autocomplete="off"></td>' +
            '<td><input type="text" class="form-control input-sm rf_amount" name="installments[' + rowIndex + '][amount]" placeholder="0.00"></td>' +
            '<td><button type="button" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error rf_remove_row"><i class="fa fa-trash"></i></button></td>' +
            '</tr>';
        $('#rf_installments_body').append(html);
        $('#rf_installments_body tr:last .rf_date').datepicker({ dateFormat: 'dd/mm/yy', autoclose: true });
        rowIndex++;
        recalc();
    });

    // Eliminar fila
    $(document).on('click', '.rf_remove_row', function () {
        if ($('#rf_installments_body tr').length > 1) {
            $(this).closest('tr').remove();
            renumber();
            recalc();
        }
    });

    // Recalcular al cambiar mora o montos
    $('#rf_forgiven_mora').on('input', recalc);
    $(document).on('input', '.rf_amount', recalc);

    // Convertir fechas antes de enviar (dd/mm/yyyy → yyyy-mm-dd)
    $('#refinance_form').on('submit', function () {
        $('.rf_date').each(function () {
            var parts = $(this).val().split('/');
            if (parts.length === 3) {
                $(this).val(parts[2] + '-' + parts[1] + '-' + parts[0]);
            }
        });
    });

    recalc();
})();
</script>
