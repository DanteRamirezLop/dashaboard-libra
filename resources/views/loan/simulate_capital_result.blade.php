<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content" id="simulate_capital_result_content">
    <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title"><i class="fas fa-calculator"></i> Simulación de cronograma</h4>
    </div>
    <div class="modal-body">
      <div class="alert alert-warning">
        <i class="fa fa-info-circle"></i> Esta es solo una vista previa, no se guardó ningún cambio. Para aplicarlo usa el botón "Pagar a capital".
      </div>

      <table class="table table-bordered table-striped">
        <tbody>
          <tr>
            <th style="width:40%">Cuota actual</th>
            <td>@format_currency($old_installment)</td>
          </tr>
          <tr>
            <th>Nueva cuota estimada</th>
            <td>@format_currency($new_installment)</td>
          </tr>
          <tr>
            <th>Cuotas simuladas</th>
            <td>{{ $target_cuotas }} de {{ $pending_count }} pendientes</td>
          </tr>
          <tr>
            <th>Nuevo saldo a financiar</th>
            <td>@format_currency($new_balance)</td>
          </tr>
          <tr>
            <th>Interés ahorrado estimado</th>
            <td>@format_currency($interest_saved)</td>
          </tr>
          @if($loan_would_be_paid)
          <tr>
            <td colspan="2"><span class="label label-success">Con este monto el préstamo quedaría totalmente pagado</span></td>
          </tr>
          @endif
        </tbody>
      </table>

      <h4>Nuevo cronograma simulado</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          @include('loan.partials._schedule_thead', ['showActions' => false])
          <tbody>
            @foreach($paymentSchedules as $item)
              @include('loan.partials._schedule_row', ['item' => $item, 'showActions' => false])
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <style>
      @page { size: landscape; }
      @media print {
        #simulate_capital_result_content .table-responsive {
          overflow: visible !important;
        }
        #simulate_capital_result_content table {
          font-size: 10px;
        }
        #simulate_capital_result_content table th,
        #simulate_capital_result_content table td {
          padding: 3px 4px;
        }
      }
    </style>

    <div class="modal-footer">
      <button type="button" class="btn btn-default no-print back_to_simulate_form"
        data-href="{{ route('loan.simulate.capital.form', ['loan_id' => $loan->id, 'type' => $type]) }}">
        <i class="fa fa-arrow-left"></i> Volver a editar
      </button>
      <button type="button" class="btn btn-primary no-print" aria-label="Print"
        onclick="$(this).closest('div.modal-content').printThis({ importStyle: true });">
        <i class="fa fa-print"></i> Imprimir
      </button>
      <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
