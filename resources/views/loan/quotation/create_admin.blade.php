@extends('layouts.app')
@section('title', __('loans.add_quotation'))

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         Agregar cotización
    </h1>
</section>

<section class="content">  
    <!-- Main content -->
    @component('components.widget', ['class' => 'box-primary','title' =>'Buscar cliente'])
            <div class="row">
                <div class="col-md-4" id="person">
                    <div class="form-group col-sm-12">
                        {!! Form::label('filing_fee', 'Documento de Identidad' ) !!}
                        <div class="input-group">
                            <input type="number" step="any" name="dni" id="dni" placeholder="DNI / Carnet de extranjería" class="form-control">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat" id="search_dni">
                                    <i class="fa fa-search text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="business" style="display: none;">
                    <div class="form-group col-sm-12">
                        {!! Form::label('ruc_business', 'RUC' ) !!}
                        <div class="input-group">
                            <input type="number" step="any" name="ruc_business" id="ruc_business" placeholder="RUC" class="form-control"   >
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat" id="search_ruc">
                                    <i class="fa fa-search text-primary fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group col-sm-12">
                        {!! Form::label('customer_type',  'Tipo de cliente:*')!!}
                        <br>
                        <label class="radio-inline">
                            {!! Form::radio('customer_type', '1', false, [ 'class' => 'input-icheck', 'name'=>"optionCustomer", 'checked']); !!}
                            Persona natural
                        </label>
                        <label class="radio-inline">
                            {!! Form::radio('customer_type', '2', false, [ 'class' => 'input-icheck', 'name'=>"optionCustomer"]); !!}
                            Empresa 
                        </label>
                    </div>
                </div>
            </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\App\Http\Controllers\LoanQuotationController::class, 'storeAdmin']), 'method' => 'post', 'id'=>'cotizar_add_form' ]) !!}
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('mobile', 'Teléfono del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="mobile" id="mobile" class="form-control" placeholder="999-999-999" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('email', 'Correo del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="email" id="email" class="form-control" placeholder="ejemplo@gmail.com" required>
                    </div>
                </div> 
                <div class="col-md-6">
                    <div class="form-group col-sm-12">
                        {!! Form::label('customer', 'Nombre del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="customer" id="customer" class="form-control disabled_input" required>
                    </div>
                    <div>
                        <input type="text" id="contact_id" name="contact_id" class="hidden">
                    </div>
                </div> 
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Fuente de contacto' . ':*') !!} 
                        <select class="form-control" required name="contact_source">
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Whatsapp">Whatsapp</option>
                            <option value="Web de Libra International">Web de Libra International</option>
                            <option value="Contacto directo del vendedor">Contacto directo del vendedor</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Maquinaria' . ':*') !!} 
                        <select class="form-control" required name="product_id" id="product">
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            @foreach($products as $key=>$item)
                            <option value="{{$item->id}}" data-reposicion="{{ $item->product_custom_field10 ?? 0 }}" data-gps="{{ $item->product_custom_field11 ?? 0 }}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Precio en ' . $currency->code . ':*') !!} 
                        <select class="form-control" aria-label="prices" id="prices" name="prices" required>
							<option selected disabled >--- Precios ---</option>
						</select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Derivado a vendedor' . ':*') !!} 
                        <select class="form-control" required name="waiter" id="waiter">
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="Dante Bulnes">Dante Bulnes</option>
                            @foreach($waiters as $key=>$waiter)
                            <option value="{{$waiter}}">{{$waiter}}</option>
                            @endforeach
                        </select> 
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                            {!! Form::label('service_type',  'Tipo de cotización:*', ['style' => 'margin-left:20px;'])!!}
                        <br>
                        <label class="radio-inline">
                            {!! Form::radio('service_type', '1', false, [ 'class' => 'input-icheck', 'name'=>"option", 'checked']); !!}
                            Contado
                        </label>
                        <label class="radio-inline">
                            {!! Form::radio('service_type', '2', false, [ 'class' => 'input-icheck', 'name'=>"option"]); !!}
                            Credito
                        </label>
                    </div>
                </div>
            </div>
        
            <div id="credito" class="row mt-5" style="display: none;">
                <div class="col-md-3">
                    <div class="form-group ">
                            {!! Form::label('service_type',  'Tipo de Inicial:', ['style' => 'margin-left:20px;'])!!}
                        <br>
                        <label class="radio-inline">
                            {!! Form::radio('service_type', '1', false, [ 'class' => 'input-icheck', 'name'=>"type_initial", 'checked']); !!}
                             Monto fijo
                        </label>
                        <label class="radio-inline">
                            {!! Form::radio('service_type', '2', false, [ 'class' => 'input-icheck', 'name'=>"type_initial"]); !!}
                            Fracción 
                        </label>
                    </div>
                </div>

                 <div class="col-md-3">
                    <div class="form-group col-sm-7">
                        {!! Form::label('allow_decimal', 'Pago de inicial' . ':*') !!} 
                         <input type="number" id="pay_initial" name="pay_initial" class="form-control" placeholder="55000" required>
                    </div>
                    <div class="form-group col-sm-5">
                         {!! Form::label('allow_decimal', 'Inicial %' ) !!} 
                        <input type="text" id="porcentaje" class="form-control disabled_input">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="row" id="inicial" style="display: none;">
                        <div class="col-md-4">
                            <div class="form-group col-sm-12">
                                {!! Form::label('amount_fracction', 'Monto a fraccionar' . ':*') !!} 
                                <input type="number" id="amount_fracction" name="amount_fracction" class="form-control" placeholder="25000" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group col-sm-12">
                                {!! Form::label('rate_fracction', 'Tasa de la inicial' . ':* &nbsp;') !!} 
                                <input type="number" id="rate_fracction" name="rate_fracction" class="form-control" placeholder="15%" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group col-sm-12">
                                {!! Form::label('mounth_fracction', 'Meses a fraccionar' . ':*') !!} 
                                <select name="mounth_fracction" id="mounth_fracction" class="form-control" required>
                                    <option value="0" selected disabled>@lang('messages.please_select' )</option>
                                    <option value="1">1 meses</option>
                                    <option value="2">2 meses</option>
                                    <option value="3">3 meses</option>
                                    <option value="4">4 meses</option>
                                    <option value="5">5 meses</option>
                                    <option value="6">6 meses</option>
                                    <option value="7">7 meses</option>
                                    <option value="8">8 meses</option>
                                     <option value="9">9 meses</option>
                                     <option value="10">10 meses</option>
                                     <option value="11">11 meses</option>
                                    <option value="12">12 meses</option>
                                    <option value="13">13 meses</option>
                                    <option value="14">14 meses</option>
                                    <option value="15">15 meses</option>
                                    <option value="16">16 meses</option>
                                    <option value="17">17 meses</option>
                                    <option value="18">18 meses</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12"> </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Cuotas' . ':*') !!} 
                        <select name="number_month" id="number_month" class="form-control" required>
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="4">4 meses</option>
                            <option value="5">5 meses</option>
                            <option value="6">6 meses</option>
                            <option value="7">7 meses</option>
                            <option value="8">8 meses</option>
                            <option value="9">9 meses</option>
                            <option value="10">10 meses</option>
                            <option value="12">12 meses</option>
                            <option value="14">14 meses</option>
                            <option value="16">16 meses</option>
                            <option value="18">18 meses</option>
                            <option value="20">20 meses</option>
                            <option value="22">22 meses</option>
                            <option value="24">24 meses</option>
                            <option value="28">28 meses</option>
                            <option value="30">30 meses</option>
                            <option value="32">32 meses</option>
                            <option value="36">36 meses</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group col-sm-12">
                        {!! Form::label('allow_decimal', 'Tasa de interés anual' . ':*') !!} 
                        <select name="annual_interest_rate" id="annual_interest_rate" class="form-control" required>
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="20">20%</option>
                            <option value="19">19%</option>
                            <option value="18">18%</option>
                            <option value="17">17%</option>
                            <option value="16">16%</option>
                            <option value="15">15%</option>
                            <option value="14">14%</option>
                            <option value="13">13%</option>
                            <option value="12">12%</option>
                            <option value="11">11%</option>
                            <option value="10">10%</option>
                        </select>
                    </div>
                </div>
 
                
                        <div class="col-md-2">
                            <div class="form-group ">
                                {!! Form::label('option_tramite',  'Incluir coste tramite:', ['style' => 'margin-left:20px;'])!!}
                                <br>
                                <label class="radio-inline">
                                    {!! Form::radio('option_tramite', '1', false, [ 'class' => 'input-icheck', 'name'=>"option_tramite", 'checked']); !!}
                                    Si  
                                </label>
                                <label class="radio-inline">
                                    {!! Form::radio('option_tramite', '2', false, [ 'class' => 'input-icheck', 'name'=>"option_tramite"]); !!}
                                    No
                                </label>
                            </div>
                            <div class="form-group">
                                <div style="margin-left:20px; font-size: small;">
                                    <strong>Inicial:</strong> @format_currency($filing_fee_initial) <br>
                                    <strong>Financiar:</strong> @format_currency($filing_fee - $filing_fee_initial)
                                </div>    
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('option_gps', 'GPS (meses): ', ['style' => 'margin-left:20px;']) !!}
                                {!! Form::select('option_gps', ['0' => 'Sin GPS', '12' => '12 meses', '24' => '24 meses'], '0', ['id' => 'option_gps', 'class' => 'form-control', 'style' => 'margin-left:20px; width:calc(100% - 20px);']) !!}
                            </div>
                            <div class="form-group">
                                <div style="margin-left:20px; font-size: small;">
                                    <strong>Inicial:</strong> <span id="gps_inicial_display">—</span> <br>
                                    <strong>Financiar:</strong> <span id="gps_financiar_display">—</span>
                                </div>
                            </div>
                            <input type="hidden" name="gps_initial" id="gps_initial_input" value="0">
                            <input type="hidden" name="gps_finance" id="gps_finance_input" value="0">
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('option_seguro', 'Seguro (meses): ', ['style' => 'margin-left:20px;']) !!}
                                {!! Form::select('option_seguro', ['0' => 'Sin Seguro', '12' => '12 meses', '24' => '24 meses'], '0', ['id' => 'option_seguro', 'class' => 'form-control', 'style' => 'margin-left:20px; width:calc(100% - 20px);']) !!}
                            </div>
                            <div class="form-group">
                                <div style="margin-left:20px; font-size: small;">
                                    <strong>Inicial:</strong> <span id="seguro_inicial_display">—</span> <br>
                                    <strong>Financiar:</strong> <span id="seguro_financiar_display">—</span>
                                </div>
                            </div>
                            <input type="hidden" name="insurance_initial" id="insurance_initial_input" value="0">
                            <input type="hidden" name="insurance_finance" id="insurance_finance_input" value="0">
                        </div>

            </div>

    @endcomponent
        <div class="col-sm-12 text-center mt-5">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white submit_product_form" value="submit" id="save">
                @lang('messages.save')
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</section>
<!-- /.content -->
@stop
@section('javascript')

    <script src="{{ asset('js/pos.js') }}"></script>
    
    <script type="text/javascript">

       $.validator.addMethod("tieneReposicion", function(value, element) {
            if (!value || value == '0') return true;
            if ($('input[name="option"]:checked').val() != '2') return true; // solo aplica en crédito
            var reposicion = parseFloat($('#product option:selected').data('reposicion')) || 0;
            return reposicion > 0;
        }, "El producto seleccionado no tiene valor de reposición configurado.");

        $.validator.addMethod("tieneGPS", function(value, element) {
            if (!value || value == '0') return true;
            if ($('input[name="option"]:checked').val() != '2') return true; // solo aplica en crédito
            var gps = parseFloat($('#product option:selected').data('gps')) || 0;
            return gps > 0;
        }, "El producto seleccionado no tiene valor de GPS configurado.");

        $.validator.addMethod("noMayorQueNumberMonth", function(value, element) {
            var val = parseInt(value) || 0;
            if (val === 0) return true;
            if ($('input[name="option"]:checked').val() != '2') return true;
            var meses = parseInt($('#number_month').val()) || 0;
            return val <= meses;
        }, "No puede ser mayor al número de cuotas del préstamo.");

        $.validator.addMethod("noMayorQuePagoInicial", function(value, element) {
            var monto = parseFloat(value) || 0;
            if (monto <= 0) return true;
            var inicial = parseFloat($('#pay_initial').val()) || 0;
            return monto <= inicial;
        }, "El monto a fraccionar no puede ser mayor al pago de inicial.");

        $.validator.addMethod("menorOIgualQue", function(value, element, param) {
            // value = valor del 2do select (el que estás validando)
            let primero = parseFloat($('#mounth_fracction').val());
            let segundo = parseFloat($('#number_month').val());

            // Si alguno está vacío, deja que "required" lo maneje (o lo damos por válido)
            if (isNaN(primero) || isNaN(segundo)) return true;

            return primero <= segundo;
        }, "Tienes que selecionar una cuota mayor o igual a los meses a fraccionar de la inicial.");

        $('form#cotizar_add_form').validate({
            rules: {
                product_id: {
                    tieneReposicion: true,
                    tieneGPS: true,
                },
                number_month: {
                    menorOIgualQue: "#mounth_fracction"
                },
                option_gps: {
                    noMayorQueNumberMonth: true
                },
                option_seguro: {
                    noMayorQueNumberMonth: true
                },
                amount_fracction: {
                    noMayorQuePagoInicial: true
                }
            },
            errorPlacement: function(error, element) {
                if (element.parent('.iradio_square-blue').length) {
                    error.insertAfter($(".radio_btns"));
                } else if (element.hasClass('status')) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        $('#pay_initial').on('input', function() {
            if ($('#amount_fracction').val()) {
                $('#amount_fracction').valid();
            }
        });

        $('#mounth_fracction').on('change', function() {
            $('#number_month').valid();
            console.log('Validando number_month al cambiar mounth_fracction');
        });

        $(document).ready(function() {
            $('input[type=radio][name=type_initial]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#inicial").hide();
                } else {
                    $("#inicial").show();
                }
            });
            $('input[type=radio][name=option]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#credito").hide();
                } else {
                    $("#credito").show();
                }
            });
            $('#option_seguro').on('change', function(){
                if ($(this).val() == '0') {
                    $("#tramite").show();
                } else {
                    $("#tramite").hide();
                }
            });
             $('#option_gps').on('change', function(){
            });
            $('input[type=radio][name=option_tramite]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#initial").hide();
                } else {
                    $("#initial").show();
                }
            });

            $('input[type=radio][name=optionCustomer]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#person").show();
                    $("#business").hide();
                } else {
                    $("#person").hide();
                    $("#business").show();
                }
            });
            
             //Calculate % initial en porcentage
             $('#pay_initial').on('input', function() {
                let precio = parseFloat($('#prices').val());
                let inicial = parseFloat($(this).val());
                if (!isNaN(precio) && precio > 0 && !isNaN(inicial)) {
                    let porcentaje = (inicial / precio) * 100;
                    $('#porcentaje').val(porcentaje.toFixed(2) + '%');
                } else {
                    $('#porcentaje').val('0%');
                }
            });
        });

        $(function() {
            let token_location = $('meta[name="csrf-token"]').attr('content');
            $("#search_dni").on('click',function () {
                var dni = $("#dni").val();

                if(dni == ''){
                    swal("Oops...!!", "Tienes que ingresar el DNI del cliente", "warning");
                    return false;
                }

                if(dni.length < 8 || dni.length > 9){
                    swal("Oops...!!", "El DNI tiene 8 dígitos ó el Carnet de extranjeria tiene 9 dígitos", "warning");
                    return false;
                }

                swal({
                    title: 'Cargando...',
                    text: "",
                    timer: 2500,
                    allowOutsideClick:false,
                });

                $.ajax({
                    type: "post",
                    url: "/get-customer-sunat",
                    dataType: 'json',
                    data: {
                        _token: token_location,
                        dni:dni,
                        type:'dni',
                    },
                    success: function (response) {
                        $("#email").val("");
                        $("#mobile").val("");
                        if(response.status){
                            $("#customer").val(response.name);
                            $("#contact_id").val(response.contact_id);
                            if(response.email != 'ejemplo@gmail.com'){
                                 $("#email").val(response.email);
                             }
                             if(response.mobile != '999999999'){
                                $("#mobile").val(response.mobile);
                             }
                        }else{
                            swal("Oops...!!", response.msg, "warning");
                        }
                        $("#dni").val("");
                        
                    },
                    error: function () {
                        swal("Error...!!", 'Lo sentimos, algo salió mal inténtalo más tarde!', "error");
                        $("#dni").val("");
                        $("#email").val("");
                        $("#mobile").val("");
                    }
                });
            });

            $("#search_ruc").on('click',function () {
                var ruc = $("#ruc_business").val();
                
                if(ruc == ''){
                    swal("Oops...!!", "Tienes que ingresar el RUC del cliente", "warning");
                    return false;
                }
                if(ruc.length != 11){
                    swal("Oops...!!", "El RUC tiene 11 dígitos", "warning");
                    return false;
                }

                swal({
                    title: 'Cargando...',
                    text: "",
                    timer: 2500,
                    allowOutsideClick:false,
                });

                $.ajax({
                    type: "post",
                    url: "/get-customer-sunat",
                    dataType: 'json',
                    data: {
                        _token: token_location,
                        ruc:ruc,
                        type:'ruc',
                    },
                    success: function (response) {
                        if(response.status){
                            $("#customer").val(response.name);
                            $("#contact_id").val(response.contact_id);
                            if(response.email != 'ejemplo@gmail.com'){
                                 $("#email").val(response.email);
                             }
                             if(response.mobile != '999999999'){
                                $("#mobile").val(response.mobile);
                             }
                         }else{
                            swal("Oops...!!", response.msg, "warning");
                         }
                        $("#ruc_business").val("");
                    },
                    error: function () {
                        swal("Oops...!!", 'Lo sentimos, algo salió mal inténtalo más tarde!', "error");
                        $("#ruc_business").val("");
                        $("#email").val("");
                        $("#mobile").val("");
                    }
                });
            });

            function calcularGPS() {
                var gpsPorMes = parseFloat($('#product option:selected').data('gps')) || 0;
                var meses = parseInt($('#option_gps').val()) || 0;

                if (gpsPorMes <= 0 || meses <= 0) {
                    $('#gps_inicial_display').text('—');
                    $('#gps_financiar_display').text('—');
                    $('#gps_initial_input').val(0);
                    $('#gps_finance_input').val(0);
                    return;
                }

                var total = gpsPorMes * meses;
                var inicial = total / 2;
                var financiar = total / 2;
                var fmt = new Intl.NumberFormat('es-PE', { style: 'currency', currency: '{{ $currency->code }}', minimumFractionDigits: 2 });
                $('#gps_inicial_display').text(fmt.format(inicial));
                $('#gps_financiar_display').text(fmt.format(financiar));
                $('#gps_initial_input').val(inicial.toFixed(2));
                $('#gps_finance_input').val(financiar.toFixed(2));
            }

            function calcularSeguro() {
                var reposicion = parseFloat($('#product option:selected').data('reposicion')) || 0;
                var meses = parseInt($('#option_seguro').val()) || 0;

                if (reposicion <= 0 || meses <= 0) {
                    $('#seguro_inicial_display').text('—');
                    $('#seguro_financiar_display').text('—');
                    $('#insurance_initial_input').val(0);
                    $('#insurance_finance_input').val(0);
                    return;
                }

                var años = meses > 12 ? 2 : 1;
                var costoTotal = reposicion * 6.79 / 1000 * 1.2154 * años;
                var inicial = costoTotal / 2;
                var financiar = costoTotal / 2;

                var fmt = new Intl.NumberFormat('es-PE', { style: 'currency', currency: '{{ $currency->code }}', minimumFractionDigits: 2 });
                $('#seguro_inicial_display').text(fmt.format(inicial));
                $('#seguro_financiar_display').text(fmt.format(financiar));
                $('#insurance_initial_input').val(inicial.toFixed(2));
                $('#insurance_finance_input').val(financiar.toFixed(2));
            }

            //cargar precios
            $('#product').on('change', function() {
                var selectedValue = $(this).val();
                $.ajax({
                    type: "post",
                    url: "/get-prices",
                    dataType: 'json',
                    data: {
                        _token: token_location,
                        id: selectedValue
                        },
                    success: function(response) {
                        if (response.status) {
                           $('#prices').html(response.options);
                        }
                    }
                });
                calcularGPS();
                calcularSeguro();
            });

            $('#option_gps').on('change', function(){
                calcularGPS();
            });

            $('#option_seguro').on('change', function(){
                calcularSeguro();
            });

            function filtrarOpcionesGpsSeguro() {
                var meses = parseInt($('#number_month').val()) || 0;
                ['#option_gps', '#option_seguro'].forEach(function(id) {
                    var $select = $(id);
                    var valorActual = parseInt($select.val()) || 0;
                    $select.find('option').each(function() {
                        var val = parseInt($(this).val()) || 0;
                        $(this).prop('disabled', val > 0 && val > meses);
                    });
                    if (valorActual > meses) {
                        $select.val('0').trigger('change');
                    }
                });
            }

            $('#number_month').on('change', function() {
                filtrarOpcionesGpsSeguro();
            });

        });
    </script>
@endsection
