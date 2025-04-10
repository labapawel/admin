<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
    <label for="{{ $id }}" class="control-label">
        {{ $label }} <span class="countHours"></span>

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    <div>
      
        <input type="hidden" id="{{ $id }}" data-start-hour="{{$startHour}}" data-end-hour="{{$endHour}}"   name="{{ $name }}" {!! $attributes !!} value="{{ $value }}">
        <div id="calendar-container-{{ $id }}" class="weekly-calendar-container"></div>
    </div>

    @if($errors->has($name))
        <span class="help-block">
            <strong>{{ $errors->first($name) }}</strong>
        </span>
    @endif
    
    @if($helpText)
        <p class="help-block">{{ $helpText }}</p>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const calendarId = "{{ $id }}";
    const calendarContainer = document.getElementById('calendar-container-'+calendarId);
    const calendarInput = document.getElementById(calendarId);
    
    const startHour = parseInt(calendarInput.getAttribute('data-start-hour') || 7);
    const endHour = parseInt(calendarInput.getAttribute('data-end-hour') || 15);
    
    const daysOfWeek = [
        "{{ __('sleeping_owl::weekly_calendar.monday') }}",
        "{{ __('sleeping_owl::weekly_calendar.tuesday') }}",
        "{{ __('sleeping_owl::weekly_calendar.wednesday') }}",
        "{{ __('sleeping_owl::weekly_calendar.thursday') }}",
        "{{ __('sleeping_owl::weekly_calendar.friday') }}",
        "{{ __('sleeping_owl::weekly_calendar.saturday') }}",
        "{{ __('sleeping_owl::weekly_calendar.sunday') }}"
    ];
    let calendarGenerated = false;
    
    // Zmienne do zaznaczania zakresu
    let rangeStartHour = null;
    let lastSelectedHour = null;
    let isMouseDown = false;
    let isSelecting = true;
    let selCount = "{{__('sleeping_owl::weekly_calendar.selCount')}}";
    
    // Funkcja do zapisywania stanu kalendarza do inputa
    function saveCalendarState() {
        if (!calendarGenerated) return;
        
        const selectedCells = calendarContainer.querySelectorAll('.weekly-calendar-cell.selected');
        const selection = Array.from(selectedCells).map(cell => {
            return {
                day: parseInt(cell.dataset.day),
                hour: parseInt(cell.dataset.hour)
            };
        });
        
        calendarInput.value = JSON.stringify(selection);
        
        // Wywołaj zdarzenie zmiany dla triggerowania walidacji formularza
        const event = new Event('change', { bubbles: true });
        calendarInput.dispatchEvent(event);
        // console.log(selection);
        
        document.querySelector('.countHours').innerHTML = `${selCount}  ${selection.length}`;
    }
    
    // Funkcja do wczytywania stanu kalendarza z inputa
    function loadCalendarState() {
        if (!calendarInput.value) return false;
        
        try {
            const selection = JSON.parse(calendarInput.value);
            document.querySelector('.countHours').innerHTML = `${selCount}  ${selection.length}`;

            return selection;
        } catch (error) {
            console.error("Błąd podczas wczytywania stanu kalendarza:", error);
        }
        
        return false;
    }
    
    // Funkcja formatująca godzinę
    function formatHour(hour) {
        return `${hour}:00`;
    }
    
    // Inicjalizacja kalendarza
    function initCalendar() {
        // Utwórz kontener kalendarza
        const calendar = document.createElement('div');
        calendar.className = 'weekly-calendar';
        
        // Dodaj pusty narożnik
        const cornerCell = document.createElement('div');
        cornerCell.className = 'weekly-calendar-header';
        cornerCell.textContent = "{{__('sleeping_owl::weekly_calendar.zero')}}";
        calendar.appendChild(cornerCell);
        
        // Dodaj nagłówki dni tygodnia
        daysOfWeek.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'weekly-calendar-header';
            dayHeader.textContent = day;
            calendar.appendChild(dayHeader);
        });
        

        // Dodaj wiersze godzin
        for (let hour = startHour; hour <= endHour; hour++) {
            // Dodaj komórkę z godziną
            const timeCell = document.createElement('div');
            timeCell.className = 'weekly-calendar-time';
            timeCell.textContent = formatHour(hour);
            timeCell.dataset.hour = hour;
            
            // Dodaj obsługę kliknięcia na komórkę godziny
            timeCell.addEventListener('click', handleTimeClick);
            
            calendar.appendChild(timeCell);
            
            // Dodaj komórki dla każdego dnia
            for (let day = 0; day < 7; day++) {
                const cell = document.createElement('div');
                cell.className = 'weekly-calendar-cell';
                cell.dataset.hour = hour;
                cell.dataset.day = day;
                
                // Obsługa zdarzeń myszy
                cell.addEventListener('mousedown', handleMouseDown);
                cell.addEventListener('mouseenter', handleMouseEnter);
                
                calendar.appendChild(cell);
            }
        }
        
        // Dodaj legendę
        const legend = document.createElement('div');
        legend.className = 'weekly-calendar-legend';
        
        const legendItem1 = document.createElement('div');
        legendItem1.className = 'weekly-calendar-legend-item';
        
        const legendColor1 = document.createElement('div');
        legendColor1.className = 'weekly-calendar-legend-color weekly-calendar-selected-example';
        
        const legendText1 = document.createElement('span');
        legendText1.textContent = "{{ __('sleeping_owl::weekly_calendar.selected_hours') }}";
        
        legendItem1.appendChild(legendColor1);
        legendItem1.appendChild(legendText1);
        
        const legendItem2 = document.createElement('div');
        legendItem2.className = 'weekly-calendar-legend-item';
        
        const legendColor2 = document.createElement('div');
        legendColor2.className = 'weekly-calendar-legend-color';
        
        const legendText2 = document.createElement('span');
        legendText2.textContent = "{{ __('sleeping_owl::weekly_calendar.available_hours') }}";
        
        legendItem2.appendChild(legendColor2);
        legendItem2.appendChild(legendText2);
        
        legend.appendChild(legendItem1);
        legend.appendChild(legendItem2);
        
        // Dodaj kalendarz i legendę do kontenera
        calendarContainer.appendChild(calendar);
        calendarContainer.appendChild(legend);
    }
    
    // Obsługa naciśnięcia przycisku myszy
    function handleMouseDown(e) {
        isMouseDown = true;
        const cell = e.target;
        
        // Ustal, czy zaznaczamy czy odznaczamy
        isSelecting = !cell.classList.contains('selected');
        
        // Zaznacz lub odznacz komórkę
        toggleCell(cell);
        
        // Zapisz stan do inputa
        saveCalendarState();
        
        // Zapobiegaj zaznaczaniu tekstu podczas przeciągania
        e.preventDefault();
    }
    
    // Obsługa wejścia kursora do komórki
    function handleMouseEnter(e) {
        if (isMouseDown) {
            const cell = e.target;
            
            // Zaznacz lub odznacz komórkę w zależności od początkowej akcji
            if (isSelecting) {
                cell.classList.add('selected');
            } else {
                cell.classList.remove('selected');
            }
            
            // Zapisz stan do inputa
            saveCalendarState();
        }
    }
    
    // Przełączanie stanu komórki
    function toggleCell(cell) {
        if (isSelecting) {
            cell.classList.add('selected');
        } else {
            cell.classList.remove('selected');
        }
    }
    
    // Obsługa kliknięcia na komórkę godziny
    function handleTimeClick(e) {
        const hour = parseInt(e.target.dataset.hour);
        
        // Jeśli to pierwsze kliknięcie zakresu lub reset zakresu
        if (rangeStartHour === null || (lastSelectedHour !== null && e.target.classList.contains('selected'))) {
            // Zaznaczamy tylko tę godzinę
            rangeStartHour = hour;
            lastSelectedHour = hour;
            
            // Wyczyść wszystkie zaznaczenia
            calendarContainer.querySelectorAll('.weekly-calendar-time').forEach(cell => {
                cell.classList.remove('selected');
            });
            
            // Zaznacz tę godzinę
            e.target.classList.add('selected');
            
            // Zaznacz komórki w tej godzinie
            selectHourCells(hour);
        } else {
            // To drugie kliknięcie - wybieramy zakres
            lastSelectedHour = hour;
            
            // Uporządkuj zakres (od mniejszej do większej)
            const startRange = Math.min(rangeStartHour, hour);
            const endRange = Math.max(rangeStartHour, hour);
            
            // Zaznacz wszystkie godziny w zakresie
            calendarContainer.querySelectorAll('.weekly-calendar-time').forEach(cell => {
                const cellHour = parseInt(cell.dataset.hour);
                if (cellHour >= startRange && cellHour <= endRange) {
                    cell.classList.add('selected');
                    
                    // Zaznacz komórki w tej godzinie
                    selectHourCells(cellHour);
                }
            });
        }
        
        // Zapisz stan do inputa
        saveCalendarState();
    }
    
    // Funkcja do zaznaczania komórek dla danej godziny
    function selectHourCells(hour) {
        calendarContainer.querySelectorAll(`.weekly-calendar-cell[data-hour="${hour}"]`).forEach(cell => {
            cell.classList.add('selected');
        });
    }
    
    // Zakończenie zaznaczania po puszczeniu przycisku myszy
    document.addEventListener('mouseup', function() {
        if (isMouseDown) {
            isMouseDown = false;
            saveCalendarState(); // Zapisz stan po zakończeniu zaznaczania
        }
    });
    
    // Inicjalizacja kalendarza
    initCalendar();
    calendarGenerated = true;
    
    // Wczytaj zapisany stan jeśli istnieje
    const savedSelection = loadCalendarState();

    if (savedSelection && Array.isArray(savedSelection)) {
        savedSelection.forEach(item => {
            const cell = calendarContainer.querySelector(`.weekly-calendar-cell[data-day="${item.day}"][data-hour="${item.hour}"]`);
            if (cell) {
                cell.classList.add('selected');
            }
        });
      

    }
});

</script>