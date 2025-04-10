@php
    $attributes = $attributes ?? [];
    $attributes['class'] = 'form-control daterange-input ' . ($errors->has($name) ? 'is-invalid' : '');
    $id = str_replace('.', '-', $name);
@endphp

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    <label for="{{ $id }}" class="control-label">
        {!! $label !!}

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    <div>
        <input {!! $attributes !!} id="{{ $id }}" name="{{ $name }}" data-datepicker-options="{{ json_encode($options) }}" />

        @if($errors->has($name))
            <span class="invalid-feedback">
                <strong>{{ $errors->first($name) }}</strong>
            </span>
        @endif

        @if($helpText)
            <small class="form-text text-muted">{!! $helpText !!}</small>
        @endif
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('{{ $id }}');
    if (!input) return;
    
    const options = JSON.parse(input.dataset.datepickerOptions || '{}');
    
    // Konfiguracja Litepicker
    const litepickerConfig = {
        element: input,
        singleMode: false,
        numberOfMonths: options.numberOfMonths || 2,
        numberOfColumns: options.numberOfColumns || 2,
        format: options.format || 'DD.MM.YYYY',
        tooltipText: options.tooltipText || {
            one: 'dzień',
            other: 'dni'
        },
        lang: options.locale || 'pl-PL',
        autoApply: options.autoApply !== undefined ? options.autoApply : true,
        showTooltip: options.showTooltip !== undefined ? options.showTooltip : true
    };

    // Dodaj minimalną datę, jeśli jest ustawiona
    if (options.minDate) {
        if (options.minDate === 'today') {
            litepickerConfig.minDate = new Date();
        } else {
            litepickerConfig.minDate = options.minDate;
        }
    }

    // Dodaj maksymalną datę, jeśli jest ustawiona
    if (options.maxDate) {
        litepickerConfig.maxDate = options.maxDate;
    }

    // Zablokowane dni
    if (options.lockedDays && options.lockedDays.length) {
        litepickerConfig.lockDays = options.lockedDays;
    }

    // Oznaczanie weekendów
    if (!options.highlightWeekends) {
        litepickerConfig.lockDaysFilter = (date) => {
            const day = date.getDay();
            return day === 0 || day === 6; // 0 = Niedziela, 6 = Sobota
        };
    } else {
        // Opcjonalnie: własny CSS do wyróżnienia weekendów
        litepickerConfig.setup = (picker) => {
            picker.on('render', (ui) => {
                const days = document.querySelectorAll('.day-item');
                days.forEach(day => {
                    const date = new Date(day.dataset.time);
                    if (date.getDay() === 0 || date.getDay() === 6) {
                        day.classList.add('weekend-day');
                    }
                });
            });
        };
    }

    // Inicjalizacja Litepicker
    const picker = new Litepicker(litepickerConfig);
});
</script>

<style>
    .litepicker .day-item.weekend-day {
        background-color: rgba(220, 220, 220, 0.3);
    }
</style>
@endpush