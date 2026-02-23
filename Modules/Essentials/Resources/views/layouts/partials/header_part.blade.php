
@if($__is_essentials_enabled && $is_employee_allowed) 
    <button 
        type="button" 
        class=" md:tw-inline-flex tw-items-center tw-ring-1 tw-ring-white/10 tw-justify-center tw-text-sm tw-font-medium tw-text-white hover:tw-text-white tw-transition-all tw-duration-200 tw-bg-primary-800 hover:tw-bg-primary-700 tw-p-1.5 tw-rounded-lg clock_in_btn
        @if(!empty($clock_in))
            hide
        @endif"
        data-type="clock_in"
        data-toggle="tooltip"
        data-placement="bottom"
        title="@lang('essentials::lang.clock_in')" >
        <span class="tw-sr-only" aria-hidden="true">
            Schedule
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"
         width="24px" fill="#fff"><path d="M340-520q42 0 71-29t29-71v-100H240v100q0 42 29 71t71 29ZM240-240h200v-100q0-42-29-71t-71-29q-42 0-71 29t-29 71v100Zm-140 80v-80h60v-100q0-42 18-78t50-62q-32-26-50-62t-18-78v-100h-60v-80h480v80h-60v100q0 42-18 78t-50 62q32 26 50 62t18 78v100h60v80H100Zm680 0L640-300l57-56 43 43v-487h80v488l44-44 56 56-140 140ZM340-720Zm0 480Z"/></svg>
    </button>

    <button 
        type="button" 
        class="clock_out_btn tw-inline-flex tw-items-center tw-justify-center tw-text-sm tw-font-medium tw-text-white tw-transition-all tw-duration-200 tw-bg-primary-800 hover:tw-bg-primary-700 tw-py-2 tw-px-3 tw-rounded-lg tw-ring-1 tw-ring-white/10 hover:tw-text-white
		bg-yellow clock_out_btn
        @if(empty($clock_in))
            hide
        @endif
        "   
        data-type="clock_out"
        data-toggle="tooltip"
        data-placement="bottom"
        data-html="true"
        title="@lang('essentials::lang.clock_out') @if(!empty($clock_in))
                    <br>
                    <small>
                        <b>@lang('essentials::lang.clocked_in_at'):</b> {{@format_datetime($clock_in->clock_in_time)}}
                    </small>
                    <br>
                    <small><b>@lang('essentials::lang.shift'):</b> {{ucfirst($clock_in->shift_name)}}</small>
                    @if(!empty($clock_in->start_time) && !empty($clock_in->end_time))
                        <br>
                        <small>
                            <b>@lang('restaurant.start_time'):</b> {{@format_time($clock_in->start_time)}}<br>
                            <b>@lang('restaurant.end_time'):</b> {{@format_time($clock_in->end_time)}}
                        </small>
                    @endif
                @endif" 
        >
        <i class="fas fa-hourglass-half fa-spin"></i>
    </button>
@endif
