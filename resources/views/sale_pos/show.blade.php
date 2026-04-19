<div class="modal-dialog modal-xl no-print" role="document"
    data-currency_symbol="{{ $currency_details->symbol ?? '' }}"
    data-currency_thousand="{{ $currency_details->thousand_separator ?? ',' }}"
    data-currency_decimal="{{ $currency_details->decimal_separator ?? '.' }}">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle"> @lang('sale.sell_details') (<b>@if($sell->type == 'sales_order') @lang('restaurant.order_no') @else @lang('sale.invoice_no') @endif :</b> {{ $sell->invoice_no }})
    </h4>
</div>
<div class="modal-body">
    <div class="row">
      <div class="col-xs-12">
          <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($sell->transaction_date) }}</p>
      </div>
    </div>
    <div class="row">
      @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $export_custom_fields = [];
        if (!empty($sell->is_export) && !empty($sell->export_custom_fields_info)) {
            $export_custom_fields = $sell->export_custom_fields_info;
        }
        $currency_factor = !empty($sell->exchange_rate) ? (float) $sell->exchange_rate : 1;
      @endphp
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
        <b>@if($sell->type == 'sales_order') {{ __('restaurant.order_no') }} @else {{ __('sale.invoice_no') }} @endif:</b> #{{ $sell->invoice_no }}<br>
        <b>{{ __('sale.status') }}:</b>
          @if($sell->status == 'draft' && $sell->is_quotation == 1)
            {{ __('lang_v1.quotation') }}
          @else
            {{ $statuses[$sell->status] ?? __('sale.' . $sell->status) }}
          @endif
        <br>
        @if($sell->type != 'sales_order')
          <b>{{ __('sale.payment_status') }}:</b> @if(!empty($sell->payment_status)){{ __('lang_v1.' . $sell->payment_status) }}
          @endif
        @endif
        @if(!empty($sell->exchange_rate))
          <br><b>Tipo de cambio:</b> {{ number_format($sell->exchange_rate, 3) }}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_1']))
          <br><strong>{{$custom_labels['sell']['custom_field_1'] ?? ''}}: </strong> {{$sell->custom_field_1}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_2']))
          <br><strong>{{$custom_labels['sell']['custom_field_2'] ?? ''}}: </strong> {{$sell->custom_field_2}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_3']))
          <br><strong>{{$custom_labels['sell']['custom_field_3'] ?? ''}}: </strong> {{$sell->custom_field_3}}
        @endif
        @if(!empty($custom_labels['sell']['custom_field_4']))
          <br><strong>{{$custom_labels['sell']['custom_field_4'] ?? ''}}: </strong> {{$sell->custom_field_4}}
        @endif

        @if(!empty($sales_orders))
              <br><br><strong>@lang('lang_v1.sales_orders'):</strong>
             <table class="table table-slim no-border">
               <tr>
                 <th>@lang('lang_v1.sales_order')</th>
                 <th>@lang('lang_v1.date')</th>
               </tr>
               @foreach($sales_orders as $so)
                <tr>
                  <td>{{$so->invoice_no}}</td>
                  <td>{{@format_datetime($so->transaction_date)}}</td>
                </tr>
               @endforeach
             </table>
          @endif
        @if($sell->document_path)
          <br>
          <br>
          <a href="{{$sell->document_path}}" 
          download="{{$sell->document_name}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent pull-left no-print">
            <i class="fa fa-download"></i> 
              &nbsp;{{ __('purchase.download_document') }}
          </a>
        @endif
      </div>
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
        @if(!empty($sell->contact->supplier_business_name))
          {{ $sell->contact->supplier_business_name }}<br>
        @endif
        <b>{{ __('sale.customer_name') }}:</b> {{ $sell->contact->name }}<br>
        <b>{{ __('business.address') }}:</b><br>
        @if(!empty($sell->billing_address()))
          {{$sell->billing_address()}}
        @else
          {!! $sell->contact->contact_address !!}
          @if($sell->contact->mobile)
          <br>
              {{__('contact.mobile')}}: {{ $sell->contact->mobile }}
          @endif
          @if($sell->contact->alternate_number)
          <br>
              {{__('contact.alternate_contact_number')}}: {{ $sell->contact->alternate_number }}
          @endif
          @if($sell->contact->landline)
            <br>
              {{__('contact.landline')}}: {{ $sell->contact->landline }}
          @endif
          @if($sell->contact->email)
            <br>
              {{__('business.email')}}: {{ $sell->contact->email }}
          @endif
        @endif
        
      </div>
      <div class="@if(!empty($export_custom_fields)) col-sm-3 @else col-sm-4 @endif">
      @if(in_array('tables' ,$enabled_modules))
         <strong>@lang('restaurant.table'):</strong>
          {{$sell->table->name ?? ''}}<br>
      @endif
      @if(in_array('service_staff' ,$enabled_modules))
          <strong>@lang('restaurant.service_staff'):</strong>
          {{$sell->service_staff->user_full_name ?? ''}}<br>
      @endif

      <strong>@lang('sale.shipping'):</strong>
      <span class="label @if(!empty($shipping_status_colors[$sell->shipping_status])) {{$shipping_status_colors[$sell->shipping_status]}} @else {{'bg-gray'}} @endif">{{$shipping_statuses[$sell->shipping_status] ?? '' }}</span><br>
      @if(!empty($sell->shipping_address()))
        {{$sell->shipping_address()}}
      @else
        {{$sell->shipping_address ?? '--'}}
      @endif
      @if(!empty($sell->delivered_to))
        <br><strong>@lang('lang_v1.delivered_to'): </strong> {{$sell->delivered_to}}
      @endif

      @if(!empty($sell->delivery_person_user->first_name))
        <br><strong>@lang('lang_v1.delivery_person'): </strong> {{$sell->delivery_person_user->surname}} {{$sell->delivery_person_user->first_name}}     {{$sell->delivery_person_user->last_name}}
      @endif

      
      @if(!empty($sell->shipping_custom_field_1))
        <br><strong>{{$custom_labels['shipping']['custom_field_1'] ?? ''}}: </strong> {{$sell->shipping_custom_field_1}}
      @endif
      @if(!empty($sell->shipping_custom_field_2))
        <br><strong>{{$custom_labels['shipping']['custom_field_2'] ?? ''}}: </strong> {{$sell->shipping_custom_field_2}}
      @endif
      @if(!empty($sell->shipping_custom_field_3))
        <br><strong>{{$custom_labels['shipping']['custom_field_3'] ?? ''}}: </strong> {{$sell->shipping_custom_field_3}}
      @endif
      @if(!empty($sell->shipping_custom_field_4))
        <br><strong>{{$custom_labels['shipping']['custom_field_4'] ?? ''}}: </strong> {{$sell->shipping_custom_field_4}}
      @endif
      @if(!empty($sell->shipping_custom_field_5))
        <br><strong>{{$custom_labels['shipping']['custom_field_5'] ?? ''}}: </strong> {{$sell->shipping_custom_field_5}}
      @endif
      @php
        $medias = $sell->media->where('model_media_type', 'shipping_document')->all();
      @endphp
      @if(count($medias))
        @include('sell.partials.media_table', ['medias' => $medias])
      @endif

      @if(in_array('types_of_service' ,$enabled_modules))
        @if(!empty($sell->types_of_service))
          <strong>@lang('lang_v1.types_of_service'):</strong>
          {{$sell->types_of_service->name}}<br>
        @endif
        @if(!empty($sell->types_of_service->enable_custom_fields))
          <strong>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}:</strong>
          {{$sell->service_custom_field_1}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_2'] ?? __('lang_v1.service_custom_field_2' )}}:</strong>
          {{$sell->service_custom_field_2}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_3'] ?? __('lang_v1.service_custom_field_3' )}}:</strong>
          {{$sell->service_custom_field_3}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_4'] ?? __('lang_v1.service_custom_field_4' )}}:</strong>
          {{$sell->service_custom_field_4}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5])}}:</strong>
          {{$sell->service_custom_field_5}}<br>
          <strong>{{ $custom_labels['types_of_service']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6])}}:</strong>
          {{$sell->service_custom_field_6}}
        @endif
      @endif
      </div>
      @if(!empty($export_custom_fields))
          <div class="col-sm-3">
                @foreach($export_custom_fields as $label => $value)
                    <strong>
                        @php
                            $export_label = __('lang_v1.export_custom_field1');
                            if ($label == 'export_custom_field_1') {
                                $export_label =__('lang_v1.export_custom_field1');
                            } elseif ($label == 'export_custom_field_2') {
                                $export_label = __('lang_v1.export_custom_field2');
                            } elseif ($label == 'export_custom_field_3') {
                                $export_label = __('lang_v1.export_custom_field3');
                            } elseif ($label == 'export_custom_field_4') {
                                $export_label = __('lang_v1.export_custom_field4');
                            } elseif ($label == 'export_custom_field_5') {
                                $export_label = __('lang_v1.export_custom_field5');
                            } elseif ($label == 'export_custom_field_6') {
                                $export_label = __('lang_v1.export_custom_field6');
                            }
                        @endphp

                        {{$export_label}}
                        :
                    </strong> {{$value ?? ''}} <br>
                @endforeach
          </div>
      @endif
    </div>
    <br>
    <div class="row">
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.products') }}:</h4>
      </div>

      <div class="col-sm-12 col-xs-12">
        <div class="table-responsive">
          @php
            $total_before_tax = 0.00;
          @endphp
        <table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>#</th>
        <th>{{ __('sale.product') }}</th>
        @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
            <th>{{ __('lang_v1.lot_n_expiry') }}</th>
        @endif
        @if($sell->type == 'sales_order')
            <th>@lang('lang_v1.quantity_remaining')</th>
        @endif
        <th>{{ __('sale.qty') }}</th>
        @if(!empty($pos_settings['inline_service_staff']))
            <th>
                @lang('restaurant.service_staff')
            </th>
        @endif
        <th>{{ __('sale.unit_price') }}</th>
        <th>{{ __('sale.discount') }}</th>
        <th>{{ __('sale.tax') }}</th>
        <th>{{ __('sale.price_inc_tax') }}</th>
        <th>{{ __('sale.subtotal') }}</th>
    </tr>
    @foreach($sell->sell_lines as $sell_line)

        @php
            $line_subtotal_before_tax = ($sell_line->quantity * $sell_line->unit_price) * $currency_factor;
            $total_before_tax += $line_subtotal_before_tax;
        @endphp

        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                <strong>{{ $sell_line->product->product_custom_field1 }} </strong>
                {{ $sell_line->product->name }}
                @if( $sell_line->product->type == 'variable')
                - {{ $sell_line->variations->product_variation->name ?? ''}}
                - {{ $sell_line->variations->name ?? ''}},
                @endif
                {{ $sell_line->variations->sub_sku ?? ''}}
                @php
                $brand = $sell_line->product->brand;
                @endphp
                @if(!empty($brand->name))
                , {{$brand->name}}
                @endif

                @if(!empty($sell_line->sell_line_note))
                <br> {{$sell_line->sell_line_note}}
                @endif
                @if($is_warranty_enabled && !empty($sell_line->warranties->first()) )
                    <br><small>{{$sell_line->warranties->first()->display_name ?? ''}} - {{ @format_date($sell_line->warranties->first()->getEndDate($sell->transaction_date))}}</small>
                    @if(!empty($sell_line->warranties->first()->description))
                    <br><small>{{$sell_line->warranties->first()->description ?? ''}}</small>
                    @endif
                @endif

                @if(in_array('kitchen', $enabled_modules) && empty($for_ledger))
                    <br><span class="label @if($sell_line->res_line_order_status == 'cooked' ) bg-red @elseif($sell_line->res_line_order_status == 'served') bg-green @else bg-light-blue @endif">@lang('restaurant.order_statuses.' . $sell_line->res_line_order_status) </span>
                @endif
            </td>
            @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
                <td>{{ $sell_line->lot_details->lot_number ?? '--' }}
                    @if( session()->get('business.enable_product_expiry') == 1 && !empty($sell_line->lot_details->exp_date))
                    ({{@format_date($sell_line->lot_details->exp_date)}})
                    @endif
                </td>
            @endif
            @if($sell->type == 'sales_order')
                <td><span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity - $sell_line->so_quantity_invoiced }}</span> @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif</td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    {{@format_quantity($sell_line->quantity)}}
                @else
                    <span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity }}</span> 
                @endif
                    @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif

                @if(!empty($sell_line->product->second_unit) && $sell_line->secondary_unit_quantity != 0)
                    <br>
                    @if(!empty($for_ledger))
                        {{@format_quantity($sell_line->secondary_unit_quantity)}}
                    @else
                        <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $sell_line->secondary_unit_quantity }}</span> 
                    @endif
                    {{$sell_line->product->second_unit->short_name}}
                @endif
            </td>
            @if(!empty($pos_settings['inline_service_staff']))
                <td>
                {{ $sell_line->service_staff->user_full_name ?? '' }}
                </td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_before_discount * $currency_factor)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_before_discount * $currency_factor }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->get_discount_amount() * $currency_factor)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->get_discount_amount() * $currency_factor }}</span>
                @endif
                @if($sell_line->line_discount_type == 'percentage') ({{$sell_line->line_discount_amount}}%) @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->item_tax * $currency_factor)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->item_tax * $currency_factor }}</span>
                @endif
                @if(!empty($taxes[$sell_line->tax_id]))
                ( {{ $taxes[$sell_line->tax_id]}} )
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_inc_tax * $currency_factor)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_inc_tax * $currency_factor }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->quantity * $sell_line->unit_price_inc_tax * $currency_factor)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->quantity * $sell_line->unit_price_inc_tax * $currency_factor }}</span>
                @endif
            </td>
        </tr>
        @if(!empty($sell_line->modifiers))
        @foreach($sell_line->modifiers as $modifier)
            <tr>
                <td>&nbsp;</td>
                <td>
                    {{ $modifier->product->name }} - {{ $modifier->variations->name ?? ''}},
                    {{ $modifier->variations->sub_sku ?? ''}}
                </td>
                @if( session()->get('business.enable_lot_number') == 1)
                    <td>&nbsp;</td>
                @endif
                <td>{{ $modifier->quantity }}</td>
                @if(!empty($pos_settings['inline_service_staff']))
                    <td>
                        &nbsp;
                    </td>
                @endif
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price * $currency_factor)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->unit_price * $currency_factor }}</span>
                    @endif
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->item_tax * $currency_factor)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->item_tax * $currency_factor }}</span>
                    @endif
                    @if(!empty($taxes[$modifier->tax_id]))
                    ( {{ $taxes[$modifier->tax_id]}} )
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price_inc_tax * $currency_factor)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->unit_price_inc_tax * $currency_factor }}</span>
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->quantity * $modifier->unit_price_inc_tax * $currency_factor)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->quantity * $modifier->unit_price_inc_tax * $currency_factor }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        @endif
    @endforeach
</table>
        </div>
        <div>
      
        </div>
      </div>
    </div>
    <div class="row">
    @php
      $total_paid = 0;
    @endphp

    @if($sell->type != 'sales_order')
      <div class="col-sm-12 col-xs-12">
        <h4>{{ __('sale.payment_info') }}:</h4>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr class="bg-green">
              <th>#</th>
              <th>{{ __('messages.date') }}</th>
              <th>{{ __('purchase.ref_no') }}</th>
              <th>{{ __('sale.amount') }}</th>
              <th>{{ __('sale.detail_amount_paid') }}</th>
              <th>{{ __('sale.payment_mode') }}</th>
              <th>{{ __('sale.payment_note') }}</th>
            </tr>

            @foreach($sell->payment_lines as $payment_line)
              @php
                $payment_amount_converted = $payment_line->amount * $currency_factor;
                if($payment_line->is_return == 1){
                  $total_paid -= $payment_amount_converted;
                } else {
                  $total_paid += $payment_amount_converted;
                }
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ @format_date($payment_line->paid_on) }}</td>
                <td>{{ $payment_line->payment_ref_no }}</td>
                <td><span class="display_currency" data-currency_symbol="true">{{ $payment_amount_converted }}</span></td>
                
                <td>
                   Se cancelo {{number_format(($payment_line->amount * $payment_line->exchange_rate),2)}}
                   {{$payment_line->currency->currency}}
                </td>

                <td>
                  {{ $payment_types[$payment_line->method] ?? $payment_line->method }}
                  @if($payment_line->is_return == 1)
                    <br/>
                    ( {{ __('lang_v1.change_return') }} )
                  @endif
                </td>
                <td>@if($payment_line->note) 
                  {{ ucfirst($payment_line->note) }}
                  @else
                  --
                  @endif
                </td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
      @endif
    
      <div class="col-md-6 col-sm-12 col-xs-12 @if($sell->type == 'sales_order') col-md-offset-6 @endif">
        <div class="table-responsive">
          <table class="table bg-gray">
            <tr>
              <th>@lang('purchase.net_total_amount'): </th>
              <td></td>
              <td>
                <span class="display_currency pull-right" data-currency_symbol="true">{{$total_before_tax}}</span>
              </td>
            </tr>

           @if(!empty($line_taxes))
            <tr>
              <th>{{ __('lang_v1.line_taxes') }}:</th>
              <td></td>
              <td class="text-right">
                @if(!empty($line_taxes))
                  @foreach($line_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> &nbsp; <span class="display_currency pull-right" data-currency_symbol="true">{{ $v * $currency_factor }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
            </tr>
            @endif

            <tr>
              <th>{{ __('sale.total') }}:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->total_before_tax * $currency_factor }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.discount') }}: </th>
              <td><b>(-)</b></td>
              <td><div class="pull-right">
                @if($sell->discount_type == 'percentage')
                  <strong> <small> {{ number_format($sell->discount_amount * $currency_factor, 2) }}%</small></strong>   <span class="display_currency" data-currency_symbol="true">{{$discount * $currency_factor}}</span>
                @else
                  <span class="display_currency" data-currency_symbol="true">{{ $sell->discount_amount * $currency_factor }}</span>
                @endif
              </div></td>
            </tr>
            @if(in_array('types_of_service' ,$enabled_modules) && !empty($sell->packing_charge))
              <tr>
                <th>{{ __('lang_v1.packing_charge') }}:</th>
                <td><b>(+)</b></td>
                <td><div class="pull-right">
                  @if($sell->packing_charge_type == 'percent')
                    <span class="display_currency">{{ $sell->packing_charge }}</span> %
                  @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell->packing_charge * $currency_factor }}</span>
                  @endif
                </div></td>
              </tr>
            @endif
            @if(session('business.enable_rp') == 1 && !empty($sell->rp_redeemed) )
              <tr>
                <th>{{session('business.rp_name')}}:</th>
                <td><b>(-)</b></td>
                <td> <span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->rp_redeemed_amount * $currency_factor }}</span></td>
              </tr>
            @endif
            <tr>
              <th>{{ __('sale.order_tax') }}:</th>
              <td><b>(+)</b></td>
              <td class="text-right">
                @if(!empty($order_taxes))
                  @foreach($order_taxes as $k => $v)
                    <strong><small>{{$k}}</small></strong> - <span class="display_currency pull-right" data-currency_symbol="true">{{ $v * $currency_factor }}</span><br>
                  @endforeach
                @else
                0.00
                @endif
              </td>
            </tr>
   
            <tr>
              <th>{{ __('sale.shipping') }}: @if($sell->shipping_details)({{$sell->shipping_details}}) @endif</th>
              <td><b>(+)</b></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->shipping_charges * $currency_factor }}</span></td>
            </tr>

            @if( !empty( $sell->additional_expense_value_1 )  && !empty( $sell->additional_expense_key_1 ))
              <tr>
                <th>{{ $sell->additional_expense_key_1 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_1 * $currency_factor }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_2 )  && !empty( $sell->additional_expense_key_2 ))
              <tr>
                <th>{{ $sell->additional_expense_key_2 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_2 * $currency_factor }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_3 )  && !empty( $sell->additional_expense_key_3 ))
              <tr>
                <th>{{ $sell->additional_expense_key_3 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_3 * $currency_factor }}</span></td>
              </tr>
            @endif
            @if( !empty( $sell->additional_expense_value_4 ) && !empty( $sell->additional_expense_key_4 ))
              <tr>
                <th>{{ $sell->additional_expense_key_4 }}:</th>
                <td><b>(+)</b></td>
                <td><span class="display_currency pull-right" >{{ $sell->additional_expense_value_4 * $currency_factor }}</span></td>
              </tr>
            @endif
            <tr>
              <th>{{ __('lang_v1.round_off') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->round_off_amount * $currency_factor }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_payable') }}: </th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell->final_total * $currency_factor }}</span></td>
            </tr>
            @if($sell->type != 'sales_order')
            <tr>
              <th>{{ __('sale.total_paid') }}:</th>
              <td></td>
              <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $total_paid }}</span></td>
            </tr>
            <tr>
              <th>{{ __('sale.total_remaining') }}: </th>
              <td></td>
              <td>
                <!-- Converting total paid to string for floating point substraction issue -->
                @php
                  $total_paid = (string) $total_paid;
                @endphp
                <span class="display_currency pull-right" data-currency_symbol="true" >{{ round($sell->final_total * $currency_factor - $total_paid,2) }}</span>
              </td>
            </tr>
            @endif
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <strong>{{ __( 'sale.sell_note')}}:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          @if($sell->additional_notes)
            {!! nl2br($sell->additional_notes) !!}
          @else
            --
          @endif
        </p>
      </div>
      <div class="col-sm-6">
        <strong>{{ __( 'sale.staff_note')}}:</strong><br>
        <p class="well well-sm no-shadow bg-gray">
          @if($sell->staff_note)
            {!! nl2br($sell->staff_note) !!}
          @else
            --
          @endif
        </p>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
            <strong>{{ __('lang_v1.activities') }}:</strong><br>
            @includeIf('activity_log.activities', ['activity_type' => 'sell'])
        </div>
    </div>
  </div>
  <div class="modal-footer">
    @if($sell->type != 'sales_order')
    <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-success tw-text-white" data-href="{{route('sell.printInvoice', [$sell->id])}}?package_slip=true"><i class="fas fa-file-alt" aria-hidden="true"></i> @lang("lang_v1.packing_slip")</a>
    @endif
    @can('print_invoice')
      <a href="#" class="print-invoice tw-dw-btn tw-dw-btn-primary tw-text-white" data-href="{{route('sell.printInvoice', [$sell->id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("lang_v1.print_invoice")</a>
    @endcan
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    var element = $('div.modal-xl');

    window.__p_currency_symbol = element.data('currency_symbol');
    window.__p_currency_thousand_separator = element.data('currency_thousand');
    window.__p_currency_decimal_separator = element.data('currency_decimal');

    __currency_convert_recursively(element, true);
  });
</script>
