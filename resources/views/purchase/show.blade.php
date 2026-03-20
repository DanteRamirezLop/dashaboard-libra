<div class="modal-dialog modal-xl purchase-detail-modal" role="document"
    data-currency_code="{{ $currency_details->code ?? '' }}"
    data-currency_symbol="{{ $currency_details->symbol ?? '' }}"
    data-currency_thousand="{{ $currency_details->thousand_separator ?? ',' }}"
    data-currency_decimal="{{ $currency_details->decimal_separator ?? '.' }}">

  <div class="modal-content">
    @include('purchase.partials.show_details')
    <div class="modal-footer">
      <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    var element = $('div.modal-xl');

    window.__p_currency_symbol = element.data('currency_symbol');
    window.__p_currency_thousand_separator = element.data('currency_thousand');
    window.__p_currency_decimal_separator = element.data('currency_decimal');

    __currency_convert_recursively(element, true);
});
</script>