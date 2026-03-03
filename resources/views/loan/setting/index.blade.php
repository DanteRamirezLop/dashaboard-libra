@extends('layouts.app')
@section('title', __('loans.loan_settings'))

@section('content')

<section class="content-header no-print">
    <h1  class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Configuración de préstamos y cotizaciones</h1>
</section>

<section class="content">  
    <!-- Main content -->
   @component('components.widget', ['class' => 'box-primary','title' =>''])
            <div class="row">
                <div class="col-sm-12">
                    {!! Form::label('product_description', 'Términos y condiciones:') !!}
                    {!! Form::textarea('product_description', $terms->description, ['class' => 'form-control']); !!}
                </div>
        
                <div class="col-sm-12 text-center mt-5">
                    <input id="id_terminos" type="hidden" value="{{$terms->id}}">
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-lg tw-text-white" id="terminos_edit" >
                        @lang('messages.save')
                    </button>
                </div>
            </div>
    @endcomponent
            <!-- <div class="row mt-5">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('cotización', 'Porcentaje de Inicial:') !!}
                    </div>
                </div>
            </div> -->

            <!-- <div class="row">
                <div class="col-sm-3">
                    <div class="input-group">
                        <div class="input-group-addon"><b>%</b></div>
                        <input type="number" id="tagInput" placeholder="Ejemplo: 25" class="form-control">
                    </div>
                </div>
                <div class="col-sm-3">
                    <button id="addTagBtn" class="btn btn-primary"> <i class="fa fa-plus"></i> Agregar </button>
                </div>
            </div>

            <div class="row m-5">
                <div class="col-md-12 mt-5">
                    <div class="row mb-3"> <span>Valores:</span> </div>
                    <div id="tagList" class="row">
                        @foreach($percentages as $key=>$percentage)
                            <div class="col-md-1"> {{$percentage}}% 
                                <button class="removeTag btn btn-xs btn-danger" data-value="{{$key}}" > <i class="glyphicon glyphicon-trash"></i></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div> -->
    @component('components.widget', ['class' => 'box-primary','title' =>''])             
        <div class="row">
            <div class="col-sm-12">
                <div >
                    {!! Form::label('cotización', 'Variables del prestamo y cotizaciones:') !!}
                </div>
            <ul class="list-group list-group-flush">
                @foreach($loanSettings as $item)
                    <li data-id="{{$item->id}}" class="list-group-item">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <h4> {{$item->description}}</h4>
                                    <input type="hidden" name="description" class="description" value="{{$item->description}}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="">
                                    {!! Form::label('filing_fee', 'Costo total' ) !!}
                                    <input type="number" step="any" name="amount_total"  value="{{$item->amount_total}}" class="form-control amount_total"   >
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="">
                                    {!! Form::label('filing_fee', 'Inicial' ) !!}  @show_tooltip(__('La inicial representa la cantidad que se añadirá al PAGO INICIAL del cliente en una cotización a crédito, la diferencia se fracciona en las cuotas del crédito, este monto tiene que ser igual o menor al monto total.'))
                                    <input type="number" step="any" name="amount_inicial"  value="{{$item->amount_inicial}}" class="form-control amount_inicial"   > 
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="">
                                    {!! Form::label('filing_fee', ' ') !!}
                                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-md tw-text-white mt-5 editar-btn" >
                                        @lang('messages.save')
                                    </button>
                                </div>
                            </div>

                        </div>
                    </li>
                @endforeach
            </ul>
            </div>
        </div>
    @endcomponent     
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script>
        
        $(document).on('click', '#terminos_edit', function() {
           var description = tinymce.get("product_description").getContent();
           var id = $("#id_terminos").val();
            $.ajax({
                method: 'POST',
                url: '/terms-update',
                dataType: 'json',
                data: {
                    id: id,
                    amount_total: 0,
                    amount_inicial:0,
                    description: description
                },
                success: function(result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click','.editar-btn', function () {
            let fila = $(this).closest("li"); // Obtener la fila
            let id = fila.data("id");
            let amount_total = parseFloat(fila.find(".amount_total").val());
            let amount_inicial = parseFloat(fila.find(".amount_inicial").val());
            let description = fila.find(".description").val();

            if (amount_inicial > amount_total){
                swal("Oops...!!", "La Inicial no puede ser mayor al costo total del "+description, "warning");
                return false;
            }

            $.ajax({
                type: "POST",
                url: "/terms-update",
                data: {
                    id: id,
                    amount_total: amount_total,
                    amount_inicial:amount_inicial,
                    description: description 
                },
                dataType: "json",
                success: function (result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        //REGISTRO DE LOS PORCENTAJES DE LA INICIAL
        // $(document).ready(function () {
        //     // let tagsArray = [];
        //     let token_location = $('meta[name="csrf-token"]').attr('content');
            
        //     function renderTags(tagsArray) {
        //         $("#tagList").empty();
        //         tagsArray.forEach(function (tag, index) {
        //             $("#tagList").append(`
        //                 <div class="col-md-1">
        //                     ${tag} %
        //                     <button class="removeTag btn btn-xs btn-danger" data-value="${index}"> <i class="glyphicon glyphicon-trash"></i></button>
        //                 </div>
        //             `);
        //         });
        //     }

        //     $("#addTagBtn").on("click", function () {
        //         let value = $("#tagInput").val().trim();
        //         if (value !== "") {
        //             $("#tagInput").val("");
        //             $.ajax({
        //                 type: "POST",
        //                 url: "/confi-initial",
        //                 data: {
        //                     _token: token_location,
        //                     value: value,
        //                     type: 'add'
        //                 },
        //                 dataType: "json",
        //                  success: function (result) {
        //                     //Actuliar el listado de iniciales
        //                     if (result.status == true) {
        //                         renderTags(result.values);
        //                         toastr.success(result.msg);
        //                     } else {
        //                         toastr.error(result.msg);
        //                     }
        //                 },
        //                 error: function(xhr, status, error){
        //                     console.error("❌ Error AJAX:", status, error);
        //                     console.log('status:', xhr.responseText);
        //                     toastr.error('Error en la peticion');
        //                 }
                        
        //             });
        //         }
        //     });

        //     // Eliminar etiqueta
        //     $("#tagList").on("click", ".removeTag", function () {
        //         let index = $(this).data("value");
        //          $.ajax({
        //             type: "POST",
        //             url: "/confi-initial",
        //             data: {
        //                 _token: token_location,
        //                 value: index,
        //                 type: 'delete'
        //             },
        //             dataType: "json",
        //                 success: function (result) {
        //                 //Actuliar el listado de iniciales
        //                 if (result.status == true) {
        //                     renderTags(result.values);
        //                     toastr.success(result.msg);
        //                 } else {
        //                     toastr.error(result.msg);
        //                 }
        //             },
        //             error: function(xhr, status, error){
        //                 console.error("❌ Error AJAX:", status, error);
        //                 console.log('status:', xhr.responseText);
        //                 toastr.error('Error en la peticion');
        //             }
                    
        //         });

        //     });
        // });
    </script>
@endsection
