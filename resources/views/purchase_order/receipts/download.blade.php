<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden de compra</title>
    <style>
        @page {
            size: A4;
             margin: 25px 0px;  /* Márgenes superiores e inferiores */
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
            background-repeat: no-repeat;
            background-size: cover;
        }
        main {
            font-family: Arial, sans-serif;
            margin: 0px 45px;
        }
        .mx-3{
            margin-left: 3rem;
            margin-right: 3rem;
        }
        .mx-2{
            margin-left: 1rem;
            margin-right: 1rem;
        }
        .mx-1{
            margin-left: 5px;
            margin-right: 5px;
        }
        .ml-3{
            margin-left: 1rem;
        }
        .ml-5{
            margin-left: 3rem;
        }
        .sub-text-content {font-size: 0.75rem; margin: 0; 0px;}

        p {font-size: 0.9rem; }
        
        .text-center{
            text-align: center;
        }
        .my-5{
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
         .my-3{
            margin-top: 0.65rem;
            margin-bottom: 0.65rem;
        }
        .my-2{
            margin-top: 0.4rem;
            margin-bottom: 0.4rem;
        }
        .mb-0{
            margin-bottom: 0;
        }
        .texto{
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .w-full{
            width: 100%;
        }
        .mt-5{
            margin-top: 20px;
        }
        table, th, td {
            border-style: solid;
            border-width: 1.2px;
            border-color: #000;
        }
        td {
            height: 32px;
        }
        .color-white{
            border-style: solid;
            border-color: #fff;
        }
        .px-1{
            padding-left: 2px;
            padding-right: 2px;
        }
    </style>
</head>

<body>
    <main>
        <table width="100%" class="color-white" cellpadding='2' border='0' >
            <tr>
                <td style="width: 35%;" class="color-white">
                     <img class="mt-2" src="{{ public_path('images/logo-blue.png') }}" alt="Logo libra xcmg" width="280px" height="50px" />
                </td>
                <td style="width: 65%;" class="color-white">
                    <div class="text-center"><h2>ORDEN DE COMPRA</h2></div>
                </td>
            </tr>
        </table>
        <div class="my-5"></div>
        <table width="100%" >
            <tr>
                <td style="font-size: 12px;height: 20px; border-color: #fff !important;"><strong>NOMBRE DE LA EMPRESA:</strong></td>
                <td style="height: 20px; font-size: 12px;border-color: #fff !important;">{!!$purchase->business->name!!} </td>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>ORDEN DE COMPRA N°:</strong></td>
                <td style="height: 20px;font-size: 12px;border-color: #fff !important;"> {{ $purchase->custom_field_4 }} </td>
            </tr>
                <tr>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong> RUC:</strong></td>
                <td style="height: 20px;font-size: 12px;border-color: #fff !important;"> {{$location_details->custom_field1}} </td>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong> FECHA:</strong></td>
                <td style="height: 20px; font-size: 12px;border-color: #fff !important;"> {{$date_print}} </td>
            </tr>
            <tr>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>TELÉFONO:</strong></td>
                <td style="height: 20px;font-size: 12px;border-color: #fff !important;">{!!$location_details->mobile!!}</td>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>EMAIL:</strong></td>
                <td style="height: 20px; font-size: 12px;border-color: #fff !important;">mdios@librainternational.com.pe</td>
            </tr>
             <tr>
                <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>DIRECCIÓN FISCAL:</strong></td>
                <td style="height: 20px;font-size: 12px;border-color: #fff !important;" colspan="3"> 
                     @if(!empty($purchase->location->landmark))
                        {{$purchase->location->landmark}}
                    @endif
                </td>
            </tr>
        </table>

        <div class="my-2"></div>
            <table width="100%" >
                <tr>
                    <td style="font-size: 12px;height: 20px; border-color: #fff !important;"><strong>PROVEEDOR:</strong></td>
                    <td style="height: 20px; font-size: 12px;border-color: #fff !important;"> 
                        @if (!empty($purchase->contact->supplier_business_name))
                        {{$purchase->contact->supplier_business_name}}
                        @endif
                    </td>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>CONTACTO DE VENTA:</strong></td>
                    <td style="height: 20px;font-size: 12px;border-color: #fff !important;"> 
                        @if (!empty($purchase->contact->custom_field1))
                        {{$purchase->contact->custom_field1}}
                        @endif
                    </td>
                </tr>
                    <tr>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong> RUC:</strong></td>
                    <td style="height: 20px;font-size: 12px;border-color: #fff !important;">     
                        @if (!empty($purchase->contact->custom_field2))
                        {{$purchase->contact->custom_field2}}
                        @endif
                    </td>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong> FAX:</strong></td>
                    <td style="height: 20px; font-size: 12px;border-color: #fff !important;">  @if (!empty($purchase->contact->landline))
                        {{$purchase->contact->landline}}
                        @endif </td>
                </tr>
                <tr>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>TELÉFONO:</strong></td>
                    <td style="height: 20px;font-size: 12px;border-color: #fff !important;">  
                        @if (!empty($purchase->contact->mobile))
                        {{$purchase->contact->mobile}}
                        @endif </td>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>EMAIL:</strong></td>
                    <td style="height: 20px; font-size: 12px;border-color: #fff !important;"> 
                        @if (!empty($purchase->contact->email))
                        {{$purchase->contact->email}}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 12px;height: 20px;border-color: #fff !important;"><strong>DIRECCIÓN:</strong></td>
                    <td style="height: 20px;font-size: 12px;border-color: #fff !important;" colspan="3"> 
                        @if (!empty($purchase->contact->address_line_1))
                        {{$purchase->contact->address_line_1}}
                        @endif
                    </td>
                </tr>
            </table>

            <div class="my-5"></div>

            <table width="100%"  border='1' cellspacing='0' >
                <tr>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Item </b> </th>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Producto </b> </th>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Cantidad</b> </th>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Unidad</b> </th>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Precio unitario</b> </th>
                    <th style="height: 25px;text-align: center;"><b style="font-size: 13px;">Valor Total </b> </th>
                </tr>
                @php 
                    $total_before_tax = 0.00;
                    $total_tax = 0.00;
                    $credit = (int)  $purchase->custom_field_3;
                    $total_neto = $purchase->final_total - $three_percent_withholding;
                    $amount_pay = ($total_neto * $credit) /100;
                @endphp

                               
                @foreach($purchase->purchase_lines as $key=>$purchase_line)
                <tr>
                    <td style="width: 5% !important; text-align: center; font-size: 12px;">
                        {{$key +1 }}
                    </td>
                    <td style="width: 38% !important; text-align: center; font-size: 12px;">
                        {{ $purchase_line->product->name }}
                        @if( $purchase_line->product->type == 'variable')
                        - {{ $purchase_line->variations->product_variation->name}}
                        - {{ $purchase_line->variations->name}}
                        @endif
                    </td>
                    <td style="width: 10% !important; text-align: center; font-size: 12px;">
                        {{@format_quantity($purchase_line->quantity)}}
                    </td>
                    <td style="width: 10% !important; text-align: center; font-size: 12px;">
                       {{ $purchase_line->product->unit->actual_name }}
                    </td>
                    <td style="width: 10% !important; text-align: center; font-size: 12px;">
                        @format_currency($purchase_line->purchase_price_inc_tax)
                    </td>
                    <td style="width: 10% !important; text-align: center; font-size: 12px;">
                         @format_currency( $purchase_line->purchase_price_inc_tax * $purchase_line->quantity)  
                    </td>
                </tr>
                @php 
                    $total_before_tax += ($purchase_line->quantity * $purchase_line->purchase_price);
                    $total_tax += ($purchase_line->quantity * $purchase_line->item_tax );
                @endphp
                @endforeach
                @php
					$i = 0;
					$is_empty_row_looped = false;
					$loop_until = 0;
					if (count($purchase->purchase_lines) == 1) {
						$loop_until = 3;
					} elseif (count($purchase->purchase_lines) == 2) {
						$loop_until = 2;
					} elseif (count($purchase->purchase_lines) == 3) {
						$loop_until = 1;
					} 
				@endphp
                @for($i; $i<= $loop_until ; $i++)
                    <tr><td></td><td></td><td></td><td></td> <td></td> <td></td></tr>
                @endfor
                <tr style="border-top: 2px solid black;">
                    <td colspan="2"></td>
                    <td style="text-align: center; font-size: 13px;"colspan="2"><b>Subtotal:</b></td>  <td  colspan="2" style="width: 10% !important; text-align: center; font-size: 12px;"> @format_currency($total_before_tax)</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center; font-size: 13px;"colspan="2">
                    <b>@if(!empty($taxes[$purchase_line->tax_id])) {{ $taxes[$purchase_line->tax_id]}}</b>@endif </td> 
                    <td style="width: 10% !important; text-align: center; font-size: 12px;" colspan="2">
                        @format_currency($total_tax)   
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; font-size: 12px;">{{$text_amount}}</td>
                    <td style="text-align: center; font-size: 13px;" colspan="2"><b>Total</b></td> 
                     <td colspan="2" style="width: 10% !important; text-align: center; font-size: 12px;">@format_currency($purchase->final_total)</td>
                </tr>
                @if($three_percent_withholding)
                <tr>
                    <td style="text-align: center; font-size: 12px;" colspan="2">
                        retencióm minima de  S/.700 con tipo de cambio SUNAT.  <b>Venta: {{number_format($exchange_rate_purchase,3)}}</b> 
                    </td>
                    <td style="text-align: center; font-size: 13px;"colspan="2">
                        <b>Retención 3%</b> 
                    </td> 
                    <td style="width: 10% !important; text-align: center; font-size: 12px;" colspan="2">
                       @format_currency($three_percent_withholding)
                    </td>
                </tr>
                @endif
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center; font-size: 13px;"colspan="2">
                        <b>Importe neto a cancelar</b> 
                    </td> 
                    <td style="width: 10% !important; text-align: center; font-size: 12px;" colspan="2">
                       @format_currency($total_neto)
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 13px;text-align: center" colspan="6">
                       <b> Forma de pago:</b>
                        @if($purchase->custom_field_3 == '0' || is_null($purchase->custom_field_3))
                            Contado
                        @else
                            Credito con inicial al {{$purchase->custom_field_3}}% 
                            <b style="margin-left:3px"> @format_currency($amount_pay) </b>
                        @endif
                    </td> 
                </tr>
               
            </table>

            <table width="100%"  border='1' cellspacing='0'>
                 <tr>
                    <td style="font-size: 13px;">
                         <b style="margin-left:2px"> Observaciones y/o comentarios:</b>
                    </td>
                    <td style="font-size: 13px; text-align:center" colspan="2">
                        @if($purchase->additional_notes)
                        {{ $purchase->additional_notes }}
                        @else
                        --
                        @endif
                    </td> 
                </tr>

                <tr>
                    <td style="font-size: 13px;" >
                        <b style="margin-left:2px">Depositar a:</b>
                    </td>
                    <td style="font-size: 13px; padding-left:5px" colspan="2">
                        @if (!empty($purchase->contact->custom_field3))
                            • {{$purchase->contact->custom_field3}} <br>
                        @endif
                        @if (!empty($purchase->contact->custom_field4))
                            • {{$purchase->contact->custom_field4}} <br>
                        @endif
                        @if (!empty($purchase->contact->custom_field5))
                            • {{$purchase->contact->custom_field5}} <br>
                        @endif
                        @if (!empty($purchase->contact->custom_field6))
                            • {{$purchase->contact->custom_field6}}
                        @endif
                    </td> 
                </tr>
                <tr>
                    <td style="width: 50%;height: 100px; text-align: left;"> 
                        <strong style="font-size: 13px;margin-left: 2px;">Requerido y aprobado por:</strong><br> 
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;">{{$purchase->sales_person->user_full_name}}</span><br>
                        <strong style="font-size: 13px;margin-left: 2px;">Area:</strong>
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;">{{$purchase->sales_person->custom_field_1}}  </span><br>
                        
                        <strong style="font-size: 13px;margin-left: 2px;">Fecha:</strong>
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;">{{$date_release}}</span> 
                    </td>
                    <td style="width: 50%;height: 100px; text-align: left;"> 
                       @if($purchase->status == 'completed' || $purchase->status == 'partial')
                        <strong style="font-size: 13px;margin-left: 2px;">Aprobado por:</strong><br> 
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;"> {{$purchase->custom_field_2}} </span><br>
                        <strong style="font-size: 13px;margin-left: 2px;">Fecha:</strong> <br>
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;">{{$date_delivery}}</span> 
                        @endif
                    </td>
                    <td style="width: 50%;height: 100px; text-align: left;"> 
                        <strong style="font-size: 13px;margin-left: 2px;">N° de requerimiento:</strong> <br>
                        <span style="text-align: center; font-size: 12px;margin-left: 2px;"> {{ $purchase->ref_no }} </span>
                    </td>
                </tr>
            </table>
             <p class="text-center" style="font-size: 11px;">Incorporado al Régimen de Agente de Retención de IGV (R.S N.º 000367-2025/SUNAT) a partir del 01 de febrero del 2026</p>
    
           
    </main>
</body>

</html>
