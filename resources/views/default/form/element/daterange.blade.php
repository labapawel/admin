<div class="form-group {{ $errors->has($input_name) ? 'has-error' : '' }}">
    <label for="{{ $id }}" class="control-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <input {!! $attributes !!}
           type="text"
           id="{{ $id }}"
           name="{{ $input_name }}" {{-- Nazwa pola = fromField --}}
           value="{{ old($input_name, $value) }}" {{-- Użyj old() i wartości z modelu --}}
           @if($readonly) readonly @endif
    >

    @include(AdminTemplate::getViewPath('form.element.errors'))
</div>

{{-- Inicjalizacja JS będzie dodana przez Asset Manager dzięki metodzie initialize() w klasie PHP --}}
{{-- Jeśli Asset Manager nie działa poprawnie, można odkomentować poniższy JS --}}
{{--
@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('{{ $id }}')) {
            // Upewnij się, że Litepicker jest załadowany
            if (typeof Litepicker !== 'undefined') {
                // Przygotuj opcje, w tym obsługę funkcji JS dla highlightedDays
                let options = @json($picker_options, JSON_UNESCAPED_SLASHES);

                if (options.highlightedDays && typeof options.highlightedDays === 'string' && options.highlightedDays.startsWith('js:')) {
                    try {
                         // Wykonaj kod JS zdefiniowany w PHP, aby uzyskać funkcję callback
                         options.highlightedDays = eval('(' + options.highlightedDays.substring(3) + ')');
                    } catch (e) {
                        console.error("Error evaluating highlightedDays function:", e);
                         delete options.highlightedDays; // Usuń, jeśli błąd
                    }
                }

                // Można dodać moment.js dla lepszej lokalizacji
                // if (typeof moment !== 'undefined') {
                //     moment.locale('pl');
                //     if (options.format) {
                //         // Jeśli używasz moment.js, format może być inny
                //         // options.format = 'DD.MM.YYYY';
                //     }
                // }

                console.log('Initializing Litepicker with options:', options);
                const picker = new Litepicker(options);
            } else {
                console.error('Litepicker library not found.');
            }
        }
    });
</script>
@endpush
--}}