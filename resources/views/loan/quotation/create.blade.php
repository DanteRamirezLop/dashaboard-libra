@extends('layouts.app')
@section('title', __('loand.add_quotation'))

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
                <div class="form-group ">
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
                <div class="form-group ">
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
                <div class="form-group ">
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

  
        {!! Form::open(['url' => action([\App\Http\Controllers\LoanQuotationController::class, 'store']), 'method' => 'post', 'id'=>'cotizar_add_form' ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('mobile', 'Teléfono del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="mobile" id="mobile" class="form-control" placeholder="999-999-999" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('email', 'Correo del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="email" id="email" class="form-control" placeholder="ejemplo@gmail.com">
                    </div>
                </div> 
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('customer', 'Nombre del cliente'. ':*' ) !!}
                        <input type="text" step="any" name="customer" id="customer" class="form-control disabled_input" required>
                    </div>
                    <div>
                        <input type="text" id="contact_id" name="contact_id" class="hidden">
                        <input type="number" name="option_tramite" value="1" class="hidden">
                        <input type="number" name="option_gps" value="1" class="hidden">
                        <input type="number" name="option_seguro" value="1" class="hidden">
                    </div>
                </div> 
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group ">
                        {!! Form::label('allow_decimal', 'Fuente de contacto' . ':*') !!} 
                        <select class="form-control" required name="contact_source">
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="TikTok">TikTok</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Web de Libra International">Web de Libra International</option>
                            <option value="Contacto directo del vendedor">Contacto directo del vendedor</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('allow_decimal', 'Maquinaria' . ':*') !!} 
                        <select class="form-control" required name="product_id" >
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            @foreach($products as $key=>$item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select> 
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group ">
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

            <div id="credito" class="row" style="display: none;">
                <div class="col-md-2">
                    <div class="form-group ">
                        {!! Form::label('allow_decimal', 'Pago inicial' . ':*') !!} 
                        <select name="pay_initial" id="pay_initial" class="form-control" required>
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="50">50%</option>
                            @can("business_settings.access")
                            <option value="20">20%</option>
                            @else
                            <option value="40" disabled>40% (No disponible para el usuario)</option>
                            <option value="30" disabled>30% (No disponible para el usuario)</option>
                            <option value="20" disabled>20% (No disponible para el usuario)</option>
                            @endcan
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('allow_decimal', 'Cuotas' . ':*') !!} 
                        <select name="number_month" id="number_month" class="form-control" required>
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="12">12 meses</option>
                            <option value="14">14 meses</option>
                            <option value="16">16 meses</option>
                            <option value="18">18 meses</option>
                            <option value="20" disabled>20 meses (No disponible para el usuario)</option>
                            <option value="22" disabled>22 meses (No disponible para el usuario)</option>
                            <option value="24" disabled>24 meses (No disponible para el usuario)</option>
                            <option value="32" disabled>32 meses (No disponible para el usuario)</option>
                        </select>
                    </div>
                </div>

                 <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('allow_decimal', 'Tasa de interés anual' . ':*') !!} 
                        <select name="annual_interest_rate" id="annual_interest_rate" class="form-control" required>
                            <option value="0" selected disabled>@lang('messages.please_select' )</option>
                            <option value="20">20%</option>
                            <option value="19" disabled>19% (No disponible para el usuario)</option>
                            <option value="18" disabled>18% (No disponible para el usuario)</option>
                            <option value="17" disabled>17% (No disponible para el usuario)</option>
                            <option value="16" disabled>16% (No disponible para el usuario)</option>
                            <option value="15" disabled>15% (No disponible para el usuario)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('filing_fee', 'Coste tramite' ) !!}
                        <input type="number" step="any" name="filing_fee" id="filing_fee" value="{{$filing_fee}}" class="form-control disabled_input"   >
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('filing_fee', 'Coste GPS' ) !!}
                        <input type="number" step="any" name="gps" id="gps" value="{{$gps}}" class="form-control disabled_input"   >
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group ">
                        {!! Form::label('filing_fee', 'Coste Seguro' ) !!}
                        <input type="number" step="any" name="insurance" id="insurance" value="{{$insurance}}" class="form-control disabled_input"   >
                    </div>
                </div>

            </div>
        @endcomponent
        <div class=" text-center mt-5">
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
        $('form#cotizar_add_form').validate({
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
            $('input[type=radio][name=option]').on('ifChecked', function(){
                if ($(this).val() == 1) {
                    $("#credito").hide();
                } else {
                    $("#credito").show();
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
                    error: function (error) {
                        console.log(error.responseText);
                        swal("Error...!!", 'Algo salió mal inténtalo más tarde!', "error");
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

        });
    </script>
@endsection
