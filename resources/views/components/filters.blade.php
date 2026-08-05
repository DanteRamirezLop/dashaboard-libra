{{-- <div class="box @if (!empty($class)) {{$class}} @else box-solid @endif" id="accordion">
  <div class="box-header with-border" style="cursor: pointer;">
    <h3 class="box-title">
      <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
        @if (!empty($icon)) {!! $icon !!} @else <i class="fa fa-filter" aria-hidden="true"></i> @endif {{$title ?? ''}}
      </a>
    </h3>
  </div>
  @php
    if(isMobile()) {
      $closed = true;
    }
  @endphp
  <div id="collapseFilter" class="panel-collapse active collapse @if (empty($closed)) in @endif" aria-expanded="true">
    <div class="box-body">
      {{$slot}}
    </div>
  </div>
</div> --}}


<div
    class="filters-card tw-transition-all tw-mb-4 lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md tw-ring-gray-200">
    <a href="#collapseFilter" data-toggle="collapse" data-parent="#accordion" role="button"
        class="filters-card__toggle" aria-expanded="false" aria-controls="collapseFilter">
        <span class="filters-card__left">
            <span class="filters-card__icon">
                @if (!empty($icon))
                    {!! $icon !!}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                    </svg>
                @endif
            </span>
            <span class="filters-card__title">{{ $title ?? '' }}</span>
        </span>
        <svg class="filters-card__chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9" />
        </svg>
    </a>
    @php
        if (isMobile()) {
            $closed = true;
        }
        $closed = true;
    @endphp
    <div id="collapseFilter" class="panel-collapse collapse @if (empty($closed)) in @endif">
        <div class="filters-card__body">
            {{ $slot }}
        </div>
    </div>
</div>

<style>
    .filters-card__toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        cursor: pointer;
        text-decoration: none !important;
        color: inherit;
        border-bottom: 1px solid transparent;
    }

    .filters-card__toggle[aria-expanded="true"] {
        border-bottom-color: #e5e7eb;
    }

    .filters-card__toggle:hover,
    .filters-card__toggle:focus {
        text-decoration: none;
        color: inherit;
    }

    .filters-card__left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .filters-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background-color: #eef2ff;
        color: #4f46e5;
        flex-shrink: 0;
    }

    .filters-card__title {
        font-weight: 700;
        font-size: 14px;
        color: #1f2937;
    }

    .filters-card__chevron {
        color: #9ca3af;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .filters-card__body {
        padding: 18px 18px 18px;
    }

    .filters-card__body::before,
    .filters-card__body::after {
        content: " ";
        display: table;
    }

    .filters-card__body::after {
        clear: both;
    }

    .filters-card__toggle[aria-expanded="true"] .filters-card__chevron {
        transform: rotate(180deg);
    }
</style>

<script>
    (function() {
        var $toggle = $('.filters-card__toggle[href="#collapseFilter"]');
        var $target = $('#collapseFilter');

        $target.on('show.bs.collapse hide.bs.collapse', function(e) {
            if (e.target !== this) {
                return;
            }
            $toggle.attr('aria-expanded', e.type === 'show' ? 'true' : 'false');
        });

        $toggle.attr('aria-expanded', $target.hasClass('in') ? 'true' : 'false');
    })();
</script>
