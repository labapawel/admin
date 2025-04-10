@php
    // Upewnij się, że wszystkie zmienne są zdefiniowane i mają odpowiedni typ
    $attributes = isset($attributes) && is_array($attributes) ? $attributes : [];
    $errors = isset($errors) ? $errors : app('errors');
    $name = isset($name) ? $name : '';
    $label = isset($label) ? $label : '';
    $required = isset($required) ? $required : false;
    $helpText = isset($helpText) ? $helpText : '';
    $options = isset($options) && is_array($options) ? $options : [];
    
    // Dodaj klasę do atrybutów
    $attributes['class'] = isset($attributes['class']) ? $attributes['class'] . ' form-control daterange-input' : 'form-control daterange-input';
    
    if ($errors->has($name)) {
        $attributes['class'] .= ' is-invalid';
    }
    
    // Wygeneruj ID
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
        <input {!! $attributes !!} id="{{ $id }}" name="{{ $name }}" data-datepicker-options="{{ json_encode($options ?? []) }}" />

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
    
    let options = {};
    try {
        options = JSON.parse(input.dataset.datepickerOptions || '{}');
    } catch (e) {
        console.error('Error parsing datepicker options:', e);
        options = {};
    }
    
    // Konfiguracja Litepicker
    const litepickerConfig = {
        element: input,
        singleMode: false,
        numberOfMonths: (options && options.numberOfMonths) || 2,
        numberOfColumns: (options && options.numberOfColumns) || 2,
        format: (options && options.format) || 'DD.MM.YYYY',
        tooltipText: (options && options.tooltipText) || {
            one: 'dzień',
            other: 'dni'
        },
        lang: (options && options.locale) || 'pl-PL',
        autoApply: (options && options.autoApply !== undefined) ? options.autoApply : true,
        showTooltip: (options && options.showTooltip !== undefined) ? options.showTooltip : true
    };

    // Dodaj minimalną datę, jeśli jest ustawiona
    if (options && options.minDate) {
        if (options.minDate === 'today') {
            litepickerConfig.minDate = new Date();
        } else {
            litepickerConfig.minDate = options.minDate;
        }
    }

    // Dodaj maksymalną datę, jeśli jest ustawiona
    if (options && options.maxDate) {
        litepickerConfig.maxDate = options.maxDate;
    }

    // Zablokowane dni
    if (options && options.lockedDays && Array.isArray(options.lockedDays) && options.lockedDays.length) {
        litepickerConfig.lockDays = options.lockedDays;
    }

    // Oznaczanie weekendów
    if (options && options.highlightWeekends === false) {
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