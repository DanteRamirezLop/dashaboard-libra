@extends('layouts.app')
@section('title', __('loans.create_loands'))

@section('content')

<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-flex tw-gap-2">
         {{__('loans.create_loands')}}
    </h1>
</section>

<section class="content">  
    <!-- Main content -->
    @component('components.widget', ['class' => 'box-primary','title' =>'Buscar cliente'])
            <div class="row">
                    <div class="col-md-4" id="person">
                        <div class="form-group col-sm-12">
                            {!! Form::label('filing_fee', 'DNI' ) !!}
                            <div class="input-group">
                                <input type="number" step="any" name="dni" id="dni" placeholder="DNI" class="form-control"   >
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

        @component('components.widget', ['class' => 'box-primary','title' =>''])
            {!! Form::open(['url' => action([\App\Http\Controllers\LoanController::class, 'store']), 'method' => 'post', 'id'=>'cotizar_add_form' ]) !!}
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
                    <div class="col-md-6">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Maquinaria' . ':*') !!} 
                            <select class="form-control" required name="product_id" id="product">
                                <option value="0" selected disabled>@lang('messages.please_select' )</option>
                                @foreach($products as $key=>$item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select> 
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Precio en ' . $currency->code . ':*') !!} 
                            <select class="form-control" aria-label="variation" id="variation" name="variation" required>
                                <option selected disabled >--- Precios ---</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Derivado a vendedor' . ':*') !!} 
                            <select class="form-control" required name="waiter" id="waiter">
                                <option value="0" selected disabled>@lang('messages.please_select' )</option>
                                <!-- <option value="Oficina">Oficina</option> -->
                                @foreach($waiters as $key=>$waiter)
                                <option value="{{$waiter}}">{{$waiter}}</option>
                                @endforeach
                            </select> 
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Cuotas' . ':*') !!} 
                            <select name="number_month" id="number_month" class="form-control" required>
                                <option value="0" selected disabled>@lang('messages.please_select' )</option>
                                <option value="6">6 meses</option>
                                <option value="10">10 meses</option>
                                <option value="12">12 meses</option>
                                <option value="14">14 meses</option>
                                <option value="16">16 meses</option>
                                <option value="18">18 meses</option>
                                <option value="20">20 meses</option>
                                <option value="22">22 meses</option>
                                <option value="24">24 meses</option>
                                <option value="30">30 meses</option>
                                <option value="32">32 meses</option>
                                <option value="36">36 meses</option>
                                <option value="38">38 meses</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Tasa de interés anual' . ':*') !!} 
                            <select name="multiplayer" id="multiplayer" class="form-control" required>
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
                    <div class="col-md-3">  
                        <div class="form-group col-sm-12">
                            <div class="form-group">
                                {!! Form::label("created_on",' Fecha del prestamo:*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('created_on',  @format_datetime($rightNow), ['class' => 'form-control', 'required']); !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('allow_decimal', 'Fuente de contacto' . ':*') !!} 
                            <select class="form-control" required name="contact_source">
                                <option value="0" selected disabled>@lang('messages.please_select' )</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Whatsapp">Whatsapp</option>
                                <option value="TikTok">TikTok</option>
                                <option value="Web de Libra International">Web de Libra International</option>
                                <option value="Contacto directo del vendedor">Contacto directo del vendedor</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                
                <div class="row" >
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
                                        <option value="18">18 meses</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
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
                        <div class="form-group" id="tramite">
                            <div style="margin-left:20px;">
                                <strong>Inicial:</strong> $ {{ number_format($filing_fee->amount_inicial,0)}} /
                                <strong>Financiar:</strong> $ {{ number_format($filing_fee->amount_total - $filing_fee->amount_inicial,0)}}
                            </div>    
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                                {!! Form::label('option_gps',  'Incluir coste GPS:', ['style' => 'margin-left:20px;'])!!}
                            <br>
                            <label class="radio-inline">
                                {!! Form::radio('option_gps', '1', false, [ 'class' => 'input-icheck', 'name'=>"option_gps", 'checked']); !!}
                                Si  
                            </label>
                            <label class="radio-inline">
                                {!! Form::radio('option_gps', '2', false, [ 'class' => 'input-icheck', 'name'=>"option_gps"]); !!}
                                No
                            </label>
                        </div>
                        <div class="form-group" id="gps">
                            <div style="margin-left:20px;">
                                <strong>Inicial:</strong> $ {{ number_format($gps->amount_inicial,0)}} /
                                <strong>Financiar:</strong> $ {{number_format($gps->amount_total - $gps->amount_inicial,0)}}
                            </div>    
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('option_seguro',  'Incluir coste Seguro:', ['style' => 'margin-left:20px;'])!!}
                            <br>
                            <label class="radio-inline">
                                {!! Form::radio('option_seguro', '1', false, [ 'class' => 'input-icheck', 'name'=>"option_seguro", 'checked']); !!}
                                Si 
                            </label>
                            <label class="radio-inline">
                                {!! Form::radio('option_seguro', '2', false, [ 'class' => 'input-icheck', 'name'=>"option_seguro"]); !!}
                                No
                            </label>
                        </div>
                        <div class="form-group" id="insurance">
                            <div style="margin-left:20px;">
                                <strong>Inicial:</strong> $ {{ number_format($insurance->amount_inicial,0)}} /
                                <strong>Financiar:</strong> $ {{ number_format($insurance->amount_total - $insurance->amount_inicial,0)}}
                            </div>    
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('mounth_expenses_financed', 'Financiar tramite, GPS y Seguro en cuotas de' . ':') !!} 
                            <select name="mounth_expenses_financed" id="mounth_expenses_financed" class="form-control" required>
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
                                <option value="18">18 meses</option>
                                <option value="24">24 meses</option>
                                <option value="30">30 meses</option>
                                <option value="32">32 meses</option>
                            </select>
                        </div>
                    </div> 
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group col-sm-12">
                            {!! Form::label('VIN', 'VIN'. ':*' ) !!}
                            <input type="text" step="any" name="vin" id="vin" class="form-control" placeholder="Ejemplo: 2CNCS13Z6M0246591" required>
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('anexo_1', 'Anexo 1'. ':' ) !!}
                            <input type="text" step="any" name="anexo_1" id="anexo_1" class="form-control" >
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="form-group col-sm-12">
                            {!! Form::label('anexo_2', 'Anexo 2'. ':' ) !!}
                            <input type="text" step="any" name="anexo_2" id="anexo_2" class="form-control" >
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="form-group col-sm-12">
                            {!! Form::label('anexo_3', 'Anexo 3'. ':' ) !!}
                            <input type="text" step="any" name="anexo_3" id="anexo_3" class="form-control">
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="form-group col-sm-12">
                            {!! Form::label('anexo_4', 'Anexo 4'. ':' ) !!}
                            <input type="text" step="any" name="anexo_4" id="anexo_4" class="form-control">
                        </div>
                    </div> 
                </div>
        @endcomponent

        <div class="col-sm-12 text-center mt-5">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white submit_product_form" value="submit" id="save">
                @lang('messages.save')
            </button>
        </div>
        {!! Form::close() !!}
 
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js')}}"></script>
    <script type="text/javascript">

        $('#created_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        $.validator.addMethod("menorOIgualQue", function(value, element, param) {
            // value = valor del 2do select (el que estás validando)
            let primero = parseFloat(value);
            let segundo = parseFloat($('#number_month').val());
            // Si alguno está vacío, deja que "required" lo maneje (o lo damos por válido)
            if (isNaN(primero) || isNaN(segundo)) return true;
            return primero <= segundo;
        }, "Los meses seleccionados no pueden ser mayor a las cuotas del Prestamo");

        $('form#cotizar_add_form').validate({

            rules: {
                mounth_fracction: {
                    menorOIgualQue:  "mounth_fracction"
                },

                mounth_expenses_financed: {
                    menorOIgualQue:  "mounth_expenses_financed"
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

        $(document).ready(function() {
            $('input[type=radio][name=type_initial]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#inicial").hide();
                } else {
                    $("#inicial").show();
                }
            });
            
            $('input[type=radio][name=option_seguro]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#insurance").show();
                } else {
                    $("#insurance").hide();
                }
            });

            $('input[type=radio][name=option_gps]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#gps").show();
                } else {
                    $("#gps").hide();
                }
            });

            $('input[type=radio][name=option_tramite]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#tramite").show();
                } else {
                    $("#tramite").hide();
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

            $('#pay_initial').on('input', function() {
                //let precio = parseFloat($('#variation').val());
                let precio = $('#variation option:selected').data('price');
                console.log(precio);
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
                if(dni.length != 8){
                    swal("Oops...!!", "El DNI tiene 8 dígitos", "warning");
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
            //cargar precios 
            $('#product').on('change', function() {
                var selectedValue = $(this).val();
                $.ajax({
                    type: "post",
                    url: "/loan/prices",
                    dataType: 'json',
                    data: { 
                        _token: token_location,
                        id: selectedValue
                        },
                    success: function(response) {
                        if (response.status) {
                           $('#variation').html(response.options);
                        }
                    }
                });
            });
        });
    </script>
@endsection
