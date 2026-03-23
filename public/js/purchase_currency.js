/**
 * purchase_currency.js
 * Módulo de cambio de moneda para compras.
 *
 * Funciona en dos contextos:
 *   1. purchase/create  — inputs de contexto a nivel de página (#p_currency_id, #diff_currency)
 *   2. Modal de pago (index) — inputs de contexto inyectados en el HTML del modal
 *      (.js-p-currency-id, .js-diff-currency)
 */

var PurchaseCurrency = (function () {

    // -------------------------------------------------------------------------
    // Helpers internos
    // -------------------------------------------------------------------------

    /**
     * Devuelve el estado de moneda para el contexto del trigger dado.
     * Busca primero dentro del contenedor más cercano (modal/formulario);
     * si no encuentra, usa los inputs de página (create.blade.php).
     *
     * @param  {jQuery|null} $trigger  Elemento que disparó el evento
     * @returns {{ diffCurrency: string, pageCurrencyId: string }}
     */
    function _getState($trigger) {
        if ($trigger && $trigger.length) {
            var $container = $trigger.closest('.modal-content, .box-body, form');
            if ($container.length) {
                var ctxCurrId = $container.find('.js-p-currency-id').val();
                if (ctxCurrId !== undefined) {
                    return {
                        diffCurrency  : $container.find('.js-diff-currency').val() || '',
                        pageCurrencyId: ctxCurrId,
                    };
                }
            }
        }
        // Fallback: inputs a nivel de página (purchase/create.blade.php)
        return {
            diffCurrency  : $('#diff_currency').val()  || '',
            pageCurrencyId: $('#p_currency_id').val()  || '',
        };
    }

    /**
     * Muestra u oculta el fieldset de tasa de cambio.
     * Busca el fieldset dentro del contenedor del trigger o en el documento.
     *
     * @param {jQuery} $container
     * @param {boolean} show
     */
    function _toggleExchangeRateSection($container, show) {
        var $fieldset = $container.find('#exchange_rate_for_payment');
        if (!$fieldset.length) {
            $fieldset = $('#exchange_rate_for_payment');
        }
        if (show) {
            $fieldset.prop('disabled', false).removeClass('hide');
        } else {
            $fieldset.prop('disabled', true).addClass('hide');
        }
    }

    /**
     * Convierte el monto ingresado en otra moneda, actualiza el campo de
     * pago y el saldo pendiente.
     *
     * @param {jQuery}  $trigger       Elemento que disparó el evento
     * @param {number}  amount         Cantidad en moneda origen
     * @param {number}  exchangeRate   Tasa de cambio
     * @param {boolean} isDiffCurrency La transacción está en moneda distinta a la base
     */
    function _recalculateAmountDue($trigger, amount, exchangeRate, isDiffCurrency) {
        if (isNaN(exchangeRate) || isNaN(amount)) return;

        var converted = isDiffCurrency
            ? amount * exchangeRate   // ej: soles → dólares
            : amount / exchangeRate;  // ej: dólares → soles

        var $container = $trigger.closest('.modal-content, .box-body, form');

        // Soporta tanto .payment-amount (create) como .payment_amount (modal)
        var $amountField = $container.length
            ? $container.find('.payment-amount, .payment_amount')
            : $('.payment-amount, .payment_amount');

        $amountField.val(converted.toFixed(3));

        var payment    = __number_uf(converted.toFixed(3), false);
        var grandTotal = __read_number($('input#grand_total_hidden'), true);
        var balance    = grandTotal - payment;

        $('#payment_due').text(__currency_trans_from_en(balance, true, true));
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    /**
     * Cambio del selector principal de moneda (#currency_id).
     * Recarga la página con el parámetro ?currency= en la URL.
     * Solo aplica en purchase/create.
     */
    function _onCurrencyIdChange() {
        $('#currency_id').on('change', function () {
            window.onbeforeunload = null;
            var value = $(this).val();
            var url   = new URL(window.location.href);
            if (value === '' || value == 2) {
                url.searchParams.delete('currency');
            } else {
                url.searchParams.set('currency', value);
            }
            window.location.href = url.toString();
        });
    }

    /**
     * Cambio del selector de moneda en el bloque de pagos (.currency_exchange_to_pay_dropdown).
     * Muestra/oculta la sección de tasa de cambio según si el pago coincide
     * con la moneda de la transacción.
     * Funciona tanto en create como en el modal de index.
     */
    function _onPaymentCurrencyChange() {
        $(document).on('change', '.currency_exchange_to_pay_dropdown', function () {
            var $this   = $(this);
            var state   = _getState($this);
            var $container = $this.closest('.modal-content, .box-body, form');
            var sameAsPage = state.pageCurrencyId == $this.val();

            // Selector de monto compatible con ambos contextos
            var $amountField = $container.length
                ? $container.find('.payment-amount, .payment_amount')
                : $('.payment-amount, .payment_amount');

            // Si se selecciona Soles (94), resetear el monto a 0
            //if ($this.val() == 94) {
                $amountField.val(0);
            //}

            if (sameAsPage) {
                $amountField.prop('readonly', false);
                _toggleExchangeRateSection($container.length ? $container : $(document), false);
            } else {
                $amountField.prop('readonly', true);
                _toggleExchangeRateSection($container.length ? $container : $(document), true);

                // Si la transacción tiene tasa propia, pre-llenar y bloquear
                if (state.diffCurrency !== '') {
                    var txRate = $container.length
                        ? $container.find('#exchange_rate').val()
                        : $('#exchange_rate').val();
                    var $rateField = $container.length
                        ? $container.find('#exchange_rate_sell')
                        : $('#exchange_rate_sell');

                    $rateField.prop('readonly', true);
                    $container.find('#amount_to_change').prop('readonly', false);
                }
            }
        });
    }

    /**
     * Cambio de la tasa de cambio principal (#exchange_rate) en create.
     * Sincroniza el valor con el campo de tasa del bloque de pagos.
     * Solo aplica en purchase/create.
     */
    function _onExchangeRateInput() {
        $('#exchange_rate').on('input', function () {
            $('#exchange_rate_sell').val(parseFloat($(this).val()));
        });
    }

    /**
     * Entrada de la cantidad a cambiar (#amount_to_change).
     * Convierte el monto y actualiza el campo de pago y el saldo pendiente.
     * Funciona tanto en create como en el modal de index.
     */
    function _onAmountToChangeInput() {
        $(document).on('input', '#amount_to_change', function () {

            var $this        = $(this);
            var state        = _getState($this);
            var $container   = $this.closest('.modal-content, .box-body, form');

            var $rateField = $container.length
                ? $container.find('#exchange_rate_sell')
                : $('#exchange_rate_sell');

            var exchangeRate = parseFloat($rateField.val());
            var amount       = parseFloat($this.val());

            _recalculateAmountDue($this, amount, exchangeRate, state.diffCurrency !== '');
        });
    }

    // -------------------------------------------------------------------------
    // API pública
    // -------------------------------------------------------------------------

    /**
     * Inicializa todos los listeners.
     * Llamar una vez en $(document).ready().
     * Los handlers delegados (.currency_exchange_to_pay_dropdown, #amount_to_change)
     * se activan automáticamente para contenido cargado dinámicamente (modales).
     */
    function init() {
        _onCurrencyIdChange();
        _onPaymentCurrencyChange();
        _onExchangeRateInput();
        _onAmountToChangeInput();
    }

    return { init: init };

})();
