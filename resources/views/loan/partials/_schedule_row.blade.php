<tr>
    <td>{{ $item->number_letter }}</td>
    <td>
        @php
            $fecha = Carbon::parse($item->sheduled_date);
            $date  = $fecha->isoFormat('dddd MMMM D\, Y');
        @endphp
        @if(($showActions ?? true) && $item->status == 'pending')
            <span class="date-display-text">{{ $date }}</span>
            <button class="btn btn-xs btn-link edit-date-btn"
                data-id="{{ $item->id }}"
                title="Editar fecha">
                <i class="fas fa-edit"></i>
            </button>
            <span class="date-edit-form" style="display:none;">
                <input type="date" class="form-control input-sm date-input"
                    value="{{ $fecha->format('Y-m-d') }}"
                    style="width:150px;display:inline-block;vertical-align:middle;">
                <button class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success save-date-btn" data-id="{{ $item->id }}">
                    <i class="fas fa-check-circle"></i>
                </button>
                <button class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error cancel-date-btn">
                    <i class="fa fa-times-circle"></i>
                </button>
            </span>
        @else
            {{ $date }}
        @endif
    </td>
    <td>{!! $item->getLoanStatus() !!}</td>
    <td>@format_currency($item->opening_balance)</td>
    <td>@format_currency($item->admin_fee_quota)</td>
    <td>@format_currency($item->gps_quota)</td>
    <td>@format_currency($item->sure_quota)</td>
    <td>@format_currency($item->initial)</td>
    <td>@format_currency($item->mount_quota + $item->gps_quota + $item->sure_quota + $item->admin_fee_quota + $item->initial)</td>
    <td>@format_currency($item->capital)</td>
    <td>@format_currency($item->interests)</td>
    <td>@format_currency($item->final_balance)</td>
    @if(($showActions ?? true) && auth()->user()->can('loans.update'))
    <td>
        @if($item->status == 'paid')
            <span class="label bg-light-green"><i class="fas fa-check"></i> @lang('loans.paid_payment')</span>
        @else
            <a href="{{ route('add.pay.loan', $item->id) }}"
               class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-success add_payment_modal">
                <i class="fas fa-money-bill-alt"></i> Agregar Pago &nbsp;
            </a><br>
        @endif

        @if($item->status == 'overdue' && !$item->delay)
            <button type="button"
                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning revert-pending-btn mt-1"
                data-id="{{ $item->id }}">
                <i class="fa fa-undo"></i> Revertir a Pendiente
            </button><br>
        @endif

        @if($item->delay)
            @if($item->delay->status == 'late')
                <a href="{{ route('delays.show', $item->id) }}"
                   class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error">
                    <i class="fas fa fa-money-bill-alt"></i> Gestionar Mora
                </a>
            @else
                <a href="{{ route('delays.show', $item->id) }}"
                   class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info">
                    <i class="fa fa-eye"></i> Ver Mora
                </a>

            @endif

        @else
            <button type="button"
                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info add-create-delay"
                data-href="{{ route('add.delay.loan', $item->id) }}"
                data-container=".delay_modal">
                <i class="fa fa-plus"></i> Agregar Mora &nbsp;&nbsp;
            </button>
        @endif
    </td>

    @else
        <td> - </td>
    @endif

</tr>
