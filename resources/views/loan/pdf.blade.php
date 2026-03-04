<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estado de cuenta</title>
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
            font-size: 0.8rem;
        }
        main {
            font-family: Arial, sans-serif;
            margin: 0px 45px;
        }
        header {
            position: fixed;
            top:-120px;
            left: 0;
            right: 0;
        }
        .footer{
            position: fixed;
            background: #fff;
            bottom: -155px;
            left: 0;
            right: 0;
            height: 110px;
            z-index: 10;
        }
        hr {
            height: 0;
            border: 0;
            border-top: 0.5mm solid #034896;
            margin-left: 3rem;
            margin-right: 3rem;
        }
        .ml-1{
            margin-left: 0.5rem;
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
        h2{
          font-size: 1.1rem;
           margin-bottom: 0.5rem;
        }
        .sub-text-content {font-size: 0.75rem; margin: 0px;}
        p {font-size: 1rem; }
        .nota {font-size: 0.75rem; }
        .text-lg{
          font-size: 0.85rem;
        }
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
        .my-2{
            margin-top: 0.7rem;
            margin-bottom: 0.2rem;
        }
        .mt-2{
           margin-top: 0.7rem;
        }
        .my-3{
            margin-top: 0.65rem;
            margin-bottom: 0.65rem;
        }
        .my-5{
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .my-4{
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        .my-6{
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .mb-0{
            margin-bottom: 0;
        }
        .mb-1{
            margin-bottom: 0.6rem;
        }
        .mb-2{
            margin-bottom: 1rem;
        }
        .mb-3{
             margin-bottom: 1.4rem;
        }
        .texto{
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .bg-white{
            background: #fff;
        }
        .bg-gray{
           background: #d9d9d9;
        }
        .bg-gray-light{
           background: #f2f2f2;
        }
        .w-full{
            width: 100%;
        }
        .mt-5{
            margin-top: 20px;
        }
        .text-red{
            color: #e24141ff;
        }
    </style>
</head>
<body>
     <header>
        <div class="mx-3" >
            <table width="100%" >
                <tr>
                    <td align="left" style="width: 50%;">
                        <img class="mt-2" src="{{ public_path('images/logo-blue.png') }}" alt="Logo libra xcmg" width="290px" height="53px" />
                    <img src="" alt="">
                    </td>
                    <td align="right" style="width: 50%;">
                        <span class="text-lg">Fecha de impresión: <strong>{{date('d/m/Y', strtotime($dateNow))}}</strong></span>
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
                        <img src="{{ public_path('images/mapa.png') }}" alt="Ubicación" width="28px" height="28px">
                    </td>
                    <td align="left" style="width: 48%;" class="text-footer">
                        <span class="aling-footer">Local Central Administración, Taller, Exhibición  & Ventas, AAHH  8 de Setiembre. Calle Las Mercedes Lote 01, Tumbes</span>
                    </td>
                    <td align="left" style="width: 5%;">
                        <img src="{{ public_path('images/mapa.png') }}" alt="Ubicación" width="28px" height="28px">
                    </td>
                    <td align="left" style="width: 42%;" class="text-footer">
                        Local 2,  Exhibición y Ventas,  Carretera Panamericana Norte Km 1267 Pueblo Nuevo, Tumbes
                    </td>
                </tr>
            </table>
        </div>
    </div>

  <main>
      <table width="100%" class="w-full" cellpadding='2'>
        <tr>
          <td style="width: 65%;">
            <table width="100%" class="mb-1">
              <tr class="text-lg" >
                <td style="width: 30%;"><strong>Cliente:</strong></td>
                <td style="width: 70%;">
                    @if($customer->supplier_business_name)
                        {{$customer->supplier_business_name}}
                    @else
                        {{$customer->name}}
                    @endif
                </td>
              </tr>
            <tr  class="text-lg">
                <td style="width: 32%;"><strong>Producto:</strong></td>
                <td style="width: 68%;"> {{$loan->product_name}}</td>
              </tr>

              <tr  class="text-lg">
                <td style="width: 32%;"><strong>Código VIN:</strong></td>
                <td style="width: 68%;"> {{$loan->vin}}</td>
              </tr>
              <tr class="text-lg">
                <td style="width: 32%;"><strong>Período:</strong></td>
                <td style="width: 68%;"> {{$loan->number_month}} meses</td>
              </tr>

              <tr class="text-lg">
                <td style="width: 32%;"><strong>Tasa de interés:</strong></td>
                <td style="width: 68%;"> {{ number_format($loan->annual_interest_rate,2)}}% </td>
              </tr>

              <tr class="text-lg">
                <td style="width: 32%;"><strong>Vendedor a cargo:</strong></td>
                <td style="width: 68%;"> {{$loan->waiter}} </td>
              </tr>

              <tr class="text-lg">
                <td style="width: 60%;"><strong>Documentos adjuntos:</strong></td>
                <td style="width: 40%;"></td>
              </tr>

              <tr class="text-lg">
                <td style="width: 20%;"> Anexo 1:</td>
                <td style="width: 80%;">{{$annexes->anexo_1}}</td>
              </tr>
              <tr class="text-lg">
                <td style="width: 20%;"> Anexo 2:</td>
                <td style="width: 80%;">{{$annexes->anexo_2}}</td>
              </tr>
              <tr class="text-lg">
                <td style="width: 20%;"> Anexo 3:</td>
                <td style="width: 80%;"> {{$annexes->anexo_3}}</td>
              </tr>
              <tr class="text-lg">
                <td style="width: 20%;"> Anexo 4:</td>
                <td style="width: 80%;">{{$annexes->anexo_4}}</td>
              </tr>
              <tr></tr>
            </table>

          </td>
          <td style="width: 35%;">
            <table width="100%" class="w-full mb-1" border='1' align='center' cellpadding='2' cellspacing='0'>
              <tr>
                <td colspan="2" class="text-center bg-gray" > <strong>Estado de cuenta</strong></td>
              </tr>
              <tr>
                <td align="left">N° de referencia</td>
                <td align="center">#{{$sell->invoice_no}}</td>
              </tr>
              <tr>
                <td align="left" >Fecha de emisión</td>
                <td align="center"> {{ date('d/m/Y', strtotime($loan->date))}}</td>
              </tr>
              <tr>
                <td align="left" >Fecha de vencimiento</td>
                <td align="center">{{ date('d/m/Y', strtotime($endOfLoan))}}</td>
              </tr>
            </table>
            
            <table width="100%" class="w-full" border='1' align='center' cellpadding='2' cellspacing='0'>
              <tr class="bg-gray">
                <td align="center"><strong>Pago de {{$dateNow->isoFormat('MMMM')}}</strong></td>
                <td align="center"><strong>Dólares</strong></td>
              </tr>
              <tr>
                <td align="left">Pago por mora</td>
                <td align="center">@format_currency($default_interest)</td>
              </tr>
              <tr>
                <td align="left">Meses atrasados </td>
                <td align="center">@format_currency($months_behind)</td>
              </tr>
              <tr>
                <td align="left">Pago del mes</td>
                <td align="center">@format_currency($amount_to_pay)</td>
              </tr>
              <tr class="bg-gray-light">
                <td align="left"> <strong>Total</strong></td>
                <td align="center">@format_currency( $amount_to_pay + $months_behind + $default_interest)</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

       <h2 class="text-center">Resumen de la cuenta</h2>
       <table  width="100%" class="w-full" border='1' cellpadding='2' cellspacing='0'>
            <tr class="bg-gray">
                <th colspan="2">Detalle</th>
                <th>Dólares</th>
            </tr>
            <tr>
                <td align="left">{{ __('sale.total') }} </td>
                <td align="center"></td>
                <td align="center">@format_currency($sell->total_before_tax)</td>
            </tr>
            <tr>
                <td align="left">Descuento por capitalización</td>
                <td align="center"><b>(-)</b></td>
                <td align="center"><div><span @if( $sell->discount_type == 'fixed') data-currency_symbol="true" @endif> @format_currency($sell->discount_amount) </span> @if( $sell->discount_type == 'percentage') {{ '%'}} @endif</span></div></td>
            </tr>
            @if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge))
                <tr>
                <td align="left">{{ __('lang_v1.packing_charge') }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center"><div><span @if( $sell->packing_charge_type == 'fixed') data-currency_symbol="true" @endif>{{ $sell->packing_charge }}</span> @if( $sell->packing_charge_type == 'percent') {{ '%'}} @endif </div></td>
                </tr>
            @endif
            @if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) )
                <tr>
                <td align="left">{{session('business.rp_name')}}</td>
                <td align="center"><b>(-)</b></td>
                <td align="center">@format_currency($sell->rp_redeemed_amount)</td>
                </tr>
            @endif
            <tr>
                <td align="left">{{ __('sale.order_tax') }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">
                @if(!empty($order_taxes))
                    @foreach($order_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - {{ $v }}</span><br>
                    @endforeach
                @else
                   $ 0.00
                @endif
                </td>
            </tr>
            @if(!empty($line_taxes))
            <tr>
                <td align="left">{{ __('lang_v1.line_taxes') }}</td>
                <td align="center"></td>
                <td align="center">
                @if(!empty($line_taxes))
                    @foreach($line_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - {{ $v }}</span><br>
                    @endforeach
                @else
                   $ 0.00
                @endif
                </td>
            </tr>
            @endif
            <tr>
                <td align="left">{{ __('sale.shipping') }} @if($sell->shipping_details)({{$sell->shipping_details}}) @endif</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">@format_currency($sell->shipping_charges)</td>
            </tr>
            @if( !empty( $sell->additional_expense_value_1 )  && !empty( $sell->additional_expense_key_1 ))
                <tr>
                <td align="left">{{ $sell->additional_expense_key_1 }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">@format_currency($sell->additional_expense_value_1)</td>
                </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_2 )  && !empty( $sell->additional_expense_key_2 ))
                <tr>
                <td align="left">{{ $sell->additional_expense_key_2 }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">@format_currency($sell->additional_expense_value_2)</td>
                </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_3 )  && !empty( $sell->additional_expense_key_3 ))
                <tr>
                <td align="left">{{ $sell->additional_expense_key_3 }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">@format_currency($sell->additional_expense_value_3)</td>
                </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_4 ) && !empty( $sell->additional_expense_key_4 ))
                <tr>
                <td align="left">{{ $sell->additional_expense_key_4 }}</td>
                <td align="center"><b>(+)</b></td>
                <td align="center">@format_currency($sell->additional_expense_value_4) </td>
                </tr>
            @endif
            <tr>
                <td align="left">{{ __('sale.total_payable') }} </td>
                <td align="center"></td>
                <td align="center">@format_currency($sell->final_total)</td>
            </tr>
            @if($sell->type != 'sales_order')
            <tr class="bg-gray-light">
                <th align="left">{{ __('sale.total_paid') }}:</th>
                <td align="center"></td>
                <td align="center"><span>@format_currency($total_paid)</span></td>
            </tr>
            <tr class="bg-gray-light">
                <th align="left">{{ __('sale.total_remaining') }}:</th>
                <td align="center"></td>
                <td align="center">
                    <span>@format_currency(($sell->final_total - $total_paid))</span>
                </td>
            </tr>
            @endif
      </table>

      <h2 class="text-center">Letras</h2>
        <table  width="100%" class="w-full" border='1' align='center' cellpadding='3' cellspacing='0'>
            <tr class="bg-gray">
                <th style="width: 11%;">N° de letra</th>
                <th style="width: 18%;">Fecha de vencimiento</th>
                <th style="width: 18%;">Pago Dólares</th>
                <th style="width: 18%;">Estado</th>
                <th style="width: 35%;">N° Referencias</th>
            </tr>
            @foreach($paymentShedules as $key=>$paymentShedule)
            <tr>
                <td align="center"> 
                    {{$paymentShedule->number_letter}}
                 </td>
                <td align="center">{{  date('d/m/Y', strtotime($paymentShedule->sheduled_date)) }} </td>
                <td align="center">@format_currency($paymentShedule->getQuote())</td>
                <td align="center">
                @switch($paymentShedule->status)
                    @case("pending")
                        <strong>Pendiente</strong>
                        @break
                    @case("overdue")
                           <strong>En mora</strong>
                            @if($paymentShedule->delay)
                            <span>({{$paymentShedule->delay->days_late}} días)</span>
                            @endif
                    @break
                    @case("paid")
                        <strong>Pagado</strong>
                    @break
                    @case("partial")
                        <strong>Parcial</strong>
                    @break
                @endswitch
                </td>

                <td align="center">
                    @for($i = 0; $i< count($paymentShedule->references); $i++)
                        <span class="ml-1">{{$paymentShedule->references[$i]}}</span>
                    @endfor
                </td>
            </tr>
            @endforeach
        </table>

        <div class="my-6"></div>

      <h2 class="text-center">Transacciones</h2>
      <table  width="100%" class="w-full" border='1' align='center' cellpadding='3' cellspacing='0'>
        <tr class="bg-gray">
           <th>N° Referencia</th>
          <th>Fecha</th>
          <th>Nota</th>
          <th>Dólares</th>
        </tr>
        
        @foreach($sell->payment_lines as $transactionPayment)
        <tr>
          <td>{{$transactionPayment->payment_ref_no}}</td>
          <td align="center">{{  date('d/m/Y', strtotime($transactionPayment->paid_on)) }} </td>
          <td> {{ $transactionPayment->note }} </td>
          <td  align="center"> @format_currency($transactionPayment->amount)</td>
        </tr>
        @endforeach
      </table>

      @if($moras)
        <div class="my-6"></div>
        <h2>Intereses por mora</h2>
        <table width="100%" class="w-full" cellpadding='2'>
                <tr>
                <td style="width: 60%;">
                        <table width="100%" class="w-full" border='1' align='center' cellpadding='2' cellspacing='0'>
                                <tr>
                                    <td class="text-center bg-gray"><strong>N° de letra</strong></td>
                                    <td class="text-center bg-gray"><strong>Deuda Dolares</strong></td>
                                    <td class="text-center bg-gray"><strong>Días</strong></td>
                                </tr>

                                @foreach($moras as $mora)
                                <tr>
                                    <td align="left">   {{$mora->paymentSchedule->number_letter}}</td>
                                    <td align="center"> @format_currency($mora->late_amount)</td>
                                    <td align="center"> {{$mora->days_late}} </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td align="left"><strong>Total</strong></td>
                                    <td  colspan="2" align="center"> @format_currency($moras->sum('late_amount'))</td>
                                </tr>     
                            </table> 
                    </td>
                    <td style="width: 40%;"></td>
                </tr>
            </table> 
    @endif
    <div class="mt-5"></div>

    <h2>Información de contacto</h2>
    <table width="100%" class="w-full" cellpadding='2'>
        <tr>
          <td style="width: 60%;">
                <table width="100%" class="w-full" border='1' align='center' cellpadding='2' cellspacing='0'>
                        <tr>
                            <td colspan="2" class="text-center bg-gray"><strong>Detalle</strong></td>
                        </tr>
                        <tr>
                            <td align="left">Número de celular</td>
                            <td align="center">(+51) 957 233 959</td>
                        </tr>
                        <tr>
                            <td align="left" >Página web</td>
                            <td align="center">www.librainternational.com.pe</td>
                        </tr>
                        <tr>
                            <td align="left">Correo electrónico</td>
                            <td align="center">informes@librainternational.com.pe</td>
                        </tr>     
                    </table> 
            </td>
            <td style="width: 40%;"></td>
        </tr>
    </table> 

  </main>

</body>
</html>