<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotizar</title>
    <style>
        @page {
            size: A4;
            margin: 120px 0px; /* Márgenes superiores e inferiores */
            
        }
        @page :first {
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
            }
        }

        @page {
            @bottom-right {
                content: "Página " counter(page) " de " counter(pages);
            }
        }

        body{
            background-image: url("{{public_path('/images/logo-color.png')}}");
            background-repeat: no-repeat;
            background-size: cover;
        }

        main {
            font-family: Arial, sans-serif;
            margin: 0px 48px;
        }

        header {
            position: fixed;
            top:-120px;
            left: 0;
            right: 0;
            background: #034896;
        }

        .footer{
            position: fixed;
            background: #fff;
            bottom: -150px;
            left: 0;
            right: 0;
            height: 120px;
            z-index: 10;
        }

        hr {
            height: 0;
            border: 0;
            border-top: 0.5mm solid #034896;
            margin-left: 3rem;
            margin-right: 3rem;
        }
        .mx-3{
            margin-left: 3rem;
            margin-right: 3rem;
        }
        .mx-2{
            margin-left: 1rem;
            margin-right: 1rem;
        }
        .ml-3{
            margin-left: 1rem;
        }
        .ml-5{
            margin-left: 3rem;
        }
        h1 {
            color: navy;
        }
        .sub-text-content {font-size: 0.75rem; margin: 0; 0px;}

        p {font-size: 0.9rem; }
        .nota {font-size: 0.75rem; }
        .text-footer{
            color:#034896;
            font-size: 0.8rem;
            font-family: Arial, sans-serif;
        }
        .aling-footer{
            margin-top: auto;
            margin-bottom: auto;
        }
        .margin-bottom{
            margin-bottom: 5px
        }
        .etiqueta-title{
            background:#034896;
            padding-left:15px;
            width: 400px;
            border-radius: 0.75rem 0 0.75rem 0;
        }
        .etiqueta{
            background:#034896;
            padding-left:15px;
            width: 225px;
            border-radius: 0.75rem 0 0.75rem 0;
        }
        .title{
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            color:#fff;
        }
        .contentText{
            padding-left: 1rem;
        }
        .text-center{
            text-align: center;
        }
        .text-business{
            padding-top: 20px;
            padding-bottom:5px;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .text-texminos{
            font-weight: 300;
            font-size: 1rem;
        }
        .my-5{
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .my-3{
            margin-top: 0.65rem;
            margin-bottom: 0.65rem;
        }
        .my-4{
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        .mb-0{
            margin-bottom: 0;
        }
        .texto{
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .bg-white{
            background: #fff;
        }
        .w-full{
            width: 100%;
        }
        .mt-5{
            margin-top: 20px;
        }
         .percentage{
            margin-right: 8px;
            color: #6c6c6cff;
            font-size: 12px;
        }

    </style>
</head>

<body>
    <header>
        <div class="mx-3" >
            <table width="100%" >
                <tr>
                    <td align="left" style="width: 50%;"></td>
                    <td align="right" style="width: 50%;">
                        <img class="my-5" src="{{ public_path('images/logo.png') }}" alt="Logo xcmg" width="250px" height="70px" />
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <div class="footer">
        <hr/>
        <div class="mx-3" >
            <table width="100%">
                <tr>
                     <td align="left" style="width: 5%;">
                        <img src="{{ public_path('images/mapa.png') }}" alt="Teléfono" width="28px" height="28px">
                    </td>
                    <td align="left" style="width: 48%;" class="text-footer">
                        <span class="aling-footer">Local Central Administración , Taller , Exhibición  & Ventas AAHH  8 de Setiembre. Calle Las Mercedes Lote 01, Tumbes</span>
                    </td>
                    
                    
                    <td align="left" style="width: 5%;">
                        <img src="{{ public_path('images/mapa.png') }}" alt="Teléfono" width="28px" height="28px">
                    </td>
                    <td align="left" style="width: 42%;" class="text-footer">
                        Local 2,  Exhibición y Ventas,  Carretera Panamericana Norte Km 1267 Pueblo Nuevo, Tumbes
                    </td>
                    
                   
                </tr>
            </table>
        </div>
    </div>

    <main>

        <div class="text-business">LIBRA INTERNATIONAL PERU E.I.R.L</div>
        <p class="sub-text-content">• Local Central Administración, Taller, Exhibición & Ventas AAHH 8 de Setiembre. Calle Las Mercedes Lote
01, Tumbes.</p>
        <p class="sub-text-content">• Local 2, Exhibición y Ventas, Carretera
Panamericana Norte Km 1267 Pueblo Nuevo,
Tumbes</p>
        <p class="sub-text-content">• RUC: 20608737619</p>
        <p class="sub-text-content margin-bottom">• Teléfono: (072) 632239 / (+51) 957233959</p>

        <div class="etiqueta-title">
            <h2 class="title">COTIZACIÓN N° 002-{{str_pad($loan->id, 3, '0', STR_PAD_LEFT)}}-{{$anio}}</h2>
        </div>
        <div class="subtitle">
            <div class="ml-3 texto"><strong>Fecha de la Cotización:</strong>{{$date}}</div>
            <div class="ml-3 texto"><strong>Valido hasta:</strong> {{$date_valid}}</div>
            
           @if($user->getRoleNameAttribute() == 'Admin')
                <div class="ml-3 texto"><strong>Vendedor:</strong> Oficina</div>
                <div class="ml-3 texto"><strong>Vendedor derivado :</strong> {{$loan->waiter}}</div>
            @else
                 <div class="ml-3 texto"><strong>Vendedor:</strong>{{$user->first_name}} {{$user->last_name}}</div>
            @endif
            
            
            <div class="ml-3 texto"><strong>Forma de Pago:</strong> 
                @if($loan->type_quotation == 1) 
                    Contado 
                @else
                    Credito
                @endif
            </div>
        </div>
        <div class="etiqueta">
            <h4 class="title">Datos Del Cliente:</h4>
        </div>

        <div class="subtitle">
            <div class="ml-3 texto"><strong>Señor (es): </strong> 
                @if($customer->supplier_business_name)
                    {{$customer->supplier_business_name}}
                @else
                    {{$customer->name}}
                @endif
            </div>
            <div class="ml-3 texto"><strong>DNI / RUC: </strong> {{$customer->contact_id}}</div>
            <div class="ml-3 texto"><strong>Celular: </strong> {{$customer->zip_code}} {{$customer->mobile}}</div>
            <div class="ml-3 texto"><strong>Correo: </strong> {{$customer->email}}</div>
        </div>
       
        <div class="etiqueta">
            <h4 class="title">Descripción del Producto:</h4>
        </div>
        
        <div class="ml-3">
            En atención a su amable solicitud de cotización a nuestra representada se permite en hacerle llegar
            nuestra oferta comercial con respecto a:
        </div>
        <div class="ml-5 mt-5">
            <table>
                <tr>
                    <th align='left'>• TIPO DE MAQUINA </th>
                    <th align='left' style="font-weight: 400;font-size: 1rem;">
                       <span class="mx-2">:</span>  {{$product->sub_category->name}}  
                    </th>
                </tr>
                <tr>
                    <th align='left'>• MARCA </th>
                    <th align='left' style="font-weight: 400;font-size: 1rem;">
                        <span class="mx-2">:</span> {{$product->brand->name}}  
                    </th>
                </tr>
                <tr>
                    <th align='left'>• MODELO </th>
                    <th align='left' style="font-weight: 400;font-size: 1rem;">
                        <span class="mx-2">:</span> {{$product->product_custom_field7}}
                    </th>
                </tr>
                <tr>
                    <th align='left'>• AÑO </th>
                    <th align='left' style="font-weight: 400;font-size: 1rem;">
                        <span class="mx-2">:</span> {{$product->product_custom_field8}}
                    </th>
                </tr>
            </table>
        </div>

        <div>
            <table width="100%" >
                <tr>
                    <td align="center" style="width: 20%;"></td>
                    <td align="center" style="width: 60%;">
                        <img class="my-5" src="" alt="" width="100%" />
                    </td>
                    <td align="center" style="width: 20%;"></td>
                </tr>
            </table>
        </div>

        <div>
            <table width="100%" >
                <tr>
                    <td align="center" style="width: 20%;"></td>
                    <td align="center" style="width: 60%;">
                        <img class="my-5" src="{{ public_path('uploads/img/'.$product->image) }}" alt="{{$product->image}}" width="100%" />
                    </td>
                    <td align="center" style="width: 20%;"></td>
                </tr>
            </table>
        </div>

        <div class="ml-3">
            {!! $product->product_description !!}
        </div>

        <div>
            <table width="100%" >
                @foreach($images as $image)
                <tr>
                    <td align="center" style="width: 20%;"></td>
                    <td align="center" style="width: 60%;">
                        <img class="my-5" src="{{ public_path('uploads/media/'.$image->file_name) }}" alt="Imagen tecnica" width="100%" />
                    </td>
                    <td align="center" style="width: 20%;"></td>
                </tr>
                @endforeach
            </table>
        </div>
        
        @if($loan->type_quotation == 2)
            <div class="etiqueta">
                <h4 class="title">Precio CREDITO</h4>
            </div>
        @else
            <div class="etiqueta">
                <h4 class="title">Precio Unitario CONTADO</h4>
            </div>
        @endif

        <table class="w-full" border='1' align='center' cellpadding='6' cellspacing='0'>
            <tr style="background-color: #034896;color:#fff">
                <th style='font-size: 0.8rem;'>Producto</th>
                <th style='font-size: 0.8rem;'>Valor de la venta</th>
                <th style='font-size: 0.8rem;'>IGV</th>
                <th style='font-size: 0.8rem;'>Precio Total</th>
                <th style='font-size: 0.8rem;'>Cantidad</th>
                <th style='font-size: 0.8rem;'>Total</th>
            </tr>
            <tr style="background-color: #F2F2F2;">
                <td align='center' style='font-size: 0.8rem;'> {{$loan->product_name}} </td>
                <td align='center' style='font-size: 0.8rem;'> ${{ number_format(($total/1.18),2)}} </td>
                <td align='center' style='font-size: 0.8rem;'> ${{ number_format((($total/1.18)*0.18),2)}}</td>
                <td align='center' style='font-size: 0.8rem;'> ${{ number_format($total,2)}}</td>
                <td align='center' style='font-size: 0.8rem;'> 1 </td>
                <td align='center' style='font-size: 0.8rem;'> ${{number_format($total,2)}}</td>
            </tr>
        </table>
    
        @if($loan->type_quotation == 2)
            <div class="etiqueta">
                <h4 class="title">Resumen del prestamo</h4>
            </div>
            <div class="ml-3">
                <table class="w-full"  cellpadding='2' cellspacing='0'>
                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Inicial
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                           <span class="percentage"> ({{number_format($loan->initial_percentage,2) }}%)</span>  ${{number_format($loan->initial_amount,2)}}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Coste del tramite
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->initial_admin_fee,2)}}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Inicial del GPS
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->initial_gps,2)}}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Inicial del seguro
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->initial_insurance,2)}}
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            <strong> Inicial + Coste tramite + Inicial GPS + Inicial seguro </strong>
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            <strong> ${{number_format($loan->initial_amount + $loan->initial_admin_fee + $loan->gps + $loan->insurance ,2)}} </strong>
                        </td>
                    </tr>
                </table>

                <div class="my-4"></div>


                <table class="w-full"  cellpadding='2' cellspacing='0'>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Saldo a Financiar
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                             ${{number_format($loan->balance_to_financed,2)}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Tasa de interés anual
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            {{$loan->type_quotation}}%
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Número de pagos
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            {{$loan->number_month}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Importe total de los intereses
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->total_amount_interest,2)}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            GPS
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->gps_quotes,2)}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Seguro
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->insurance_quotes,2)}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Coste total del préstamo
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->total_cost_loan,2)}}
                        </td>
                    </tr>

                    <tr style="border-bottom: 1px solid #606060;">
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            Tasa de inicial
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            ${{number_format($loan->start_total_amount_interest,2)}}
                        </td>
                    </tr>
                    
                    <tr>
                        <td align="left" style="width: 60%; height: 25px; font-size: 0.9rem;">
                            <strong>Total a Pagar</strong>
                        </td>
                        <td align="right" style="width: 40%; height: 25px; font-size: 0.9rem;">
                            <strong> ${{number_format($total,2)}} </strong>
                        </td>
                    </tr>
                </table>

            </div>

            <div class="etiqueta">
                <h4 class="title">Resumen de los pagos</h4>
            </div>

            <div class="ml-3">
                <table width="100%"  border='1' cellspacing='0' >
                <tr style="background-color: #034896;">
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">N° de pago</b> </th>
                    <th style="height: 20px;color: #fff;padding-left:20px;"><b style="font-size: 12px;">Fecha de Pago</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">Saldo inicial</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">+GPS</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">+Seguro</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">+Inicial</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">&nbsp; Pago &nbsp; </b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">Capital</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">Intereses</b> </th>
                    <th style="height: 20px;color: #fff;text-align: center;padding: 10px;"><b style="font-size: 12px;">Saldo final</b> </th>
                </tr>
                    @php
                        $count = 0;
                    @endphp
                    @foreach($quotes as $key=>$item)
                    <tr style="background-color: #F2F2F2;">      
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">{{$key+1}} </td>        
                        <td  align='center' style="font-size: 12px; padding-left: 3px; padding-right: 3px;">
                            @php
                                $fecha = Carbon::parse($item->date);
                                $date = $fecha->isoFormat('D/MM/Y');  
                                $count = $item->amount + $count;
                            @endphp
                            {{$date}} 
                        </td>
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->saldo_inicial,2)}} </td>
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->gps,2)}} </td>
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->seguro,2)}} </td>
                        @if(isset($item->initial))
                            <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->initial,2)}}</td>
                            <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format(($item->amount + $item->gps + $item->seguro + $item->initial),2)}} </td>
                        @else
                            <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">$0.00</td>
                            <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format(($item->amount + $item->gps + $item->seguro),2)}} </td>
                        @endif
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->capital,2)}} </td>
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->interes ,2)}} </td>
                        <td  align='center' style="font-size: 12px; padding-left: 2px; padding-right: 2px;">${{number_format($item->saldo_final,2)}} </td>
                    </tr>
                    @endforeach
                </table> 
            </div>
        @endif

        <div class="etiqueta">
            <h4 class="title">Términos y Condiciones</h4>
        </div>

        <div class="ml-3">
            {!! $loan->terms !!}
        </div>

        <div class="etiqueta">
            <h4 class="title">Nota:</h4>
        </div>
        <div class="ml-3">
            <p class="nota"> • Se han promulgado normas que prohíben y sancionan la minería ilegal. Los bienes y/o servicios detallados en esta cotización no
                podrán ser destinados ni directa ni indirectamente a una actividad considerada de minería ilegal.
            </p>
            <p class="nota">
                • Nos reservamos el derecho de modificar las especificaciones, términos y condiciones contenidos en la presente cotización, sin previo
                aviso.
            </p>
            <p class="nota">
                • Esta cotización señala las características técnicas del(los) Producto(s). En los manuales de operación, figuran las recomendaciones
                que debe seguir el cliente para el buen funcionamiento de los equipos, bajo responsabilidad del cliente.
            </p>
            <p class="nota">
                • Nuestra empresa está facultada a variar los precios contemplados en la presente cotización debido a causas ajenas a nosotros, tales
                como variación del tipo de cambio y/o cambios en los derechos de importación, siempre que dicha variación supere el cinco por
                ciento (5%). Cualquier nuevo tributo, o la modificación de uno existente, deberá ser asumido por el Cliente.
            </p>
        </div>

        <div class="etiqueta">
            <h4 class="title">Datos Adicionales:</h4>
        </div>

        <div class="ml-3">
            <p>Cualquier información adicional, visite nuestra página web: <a href="">https://librainternational.com.pe/</a></p>
            <table width="100%" >
                <tr>
                    <td align="center" style="width: 2%;"></td>
                    <td align="center" style="width: 96%;">
                        <img class="my-5" src="{{ public_path('images/bancos.png') }}" alt="Bancos" width="100%" />
                    </td>
                    <td align="center" style="width: 2%;"></td>
                </tr>
            </table>
        </div>
    </main>
</body>

</html>
