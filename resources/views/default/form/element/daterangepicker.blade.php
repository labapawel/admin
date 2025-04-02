{{-- resources/views/admin/form/element/daterangepicker.blade.php --}}
<div class="form-group form-element-daterangepicker {{ $errors->has($startField) || $errors->has($endField) ? 'has-error' : '' }}" 
     data-id="{{ $id }}" 
     data-holidays="{{ $holidays }}" 
     data-disable-past="{{ $disablePastDates ? 'true' : 'false' }}">
    <label for="{{ $startField }}" class="control-label">
        {{ $label }}
        
        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    <div class="daterangepicker-container">
        <div class="calendars-container">
            <div class="calendar" id="{{ $id }}-start-calendar">
                <div class="calendar-header">
                    <div class="calendar-title">{{ $startLabel }}</div>
                </div>
                <div class="month-selector">
                    <button type="button" class="nav-btn prev-month">&lt;</button>
                    <div class="month-name"></div>
                    <button type="button" class="nav-btn next-month">&gt;</button>
                </div>
                <div class="weekdays">
                    <div>Pn</div>
                    <div>Wt</div>
                    <div>Śr</div>
                    <div>Cz</div>
                    <div>Pt</div>
                    <div>So</div>
                    <div>Nd</div>
                </div>
                <div class="days"></div>
                <input type="hidden" id="{{ $id }}-start-date-input" name="{{ $startField }}" value="{{ $startValue }}" class="form-control">
            </div>
            
            <div class="calendar" id="{{ $id }}-end-calendar">
                <div class="calendar-header">
                    <div class="calendar-title">{{ $endLabel }}</div>
                </div>
                <div class="month-selector">
                    <button type="button" class="nav-btn prev-month">&lt;</button>
                    <div class="month-name"></div>
                    <button type="button" class="nav-btn next-month">&gt;</button>
                </div>
                <div class="weekdays">
                    <div>Pn</div>
                    <div>Wt</div>
                    <div>Śr</div>
                    <div>Cz</div>
                    <div>Pt</div>
                    <div>So</div>
                    <div>Nd</div>
                </div>
                <div class="days"></div>
                <input type="hidden" id="{{ $id }}-end-date-input" name="{{ $endField }}" value="{{ $endValue }}" class="form-control">
            </div>
        </div>
    </div>
    
    @if($errors->has($startField))
        <div class="help-block">
            <p class="text-danger">{{ $errors->first($startField) }}</p>
        </div>
    @endif
    
    @if($errors->has($endField))
        <div class="help-block">
            <p class="text-danger">{{ $errors->first($endField) }}</p>
        </div>
    @endif
</div>

@section('footer-scripts')
    @parent
    <style>
        .daterangepicker-container {
            margin-bottom: 15px;
        }
        
        .calendars-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }
        
        .calendar {
            width: 300px;
            border: 1px solid #e9e9e9;
            background-color: #fff;
            border-radius: 3px;
            padding: 15px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #e9e9e9;
            padding-bottom: 10px;
        }
        
        .calendar-title {
            font-weight: 600;
            font-size: 14px;
            color: #1a2226;
        }
        
        .month-selector {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 3px;
            padding: 5px;
        }
        
        .month-name {
            flex-grow: 1;
            text-align: center;
            font-weight: 600;
            color: #333;
            padding: 5px 0;
            font-size: 12px;
        }
        
        .nav-btn {
            background: none;
            border: none;
            font-size: 14px;
            cursor: pointer;
            padding: 5px 10px;
            color: #3c8dbc;
            transition: background-color 0.3s;
        }
        
        .nav-btn:hover {
            background-color: #f4f4f4;
            border-radius: 3px;
        }
        
        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
            padding: 8px 0;
            background-color: #f5f5f5;
            border-radius: 3px;
            font-size: 11px;
        }
        
        .days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }
        
        .day {
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.2s;
            font-size: 12px;
            position: relative;
        }
        
        .day:hover:not(.empty):not(.selected):not(.in-range):not(.holiday):not(.disabled) {
            background-color: #f4f4f4;
        }
        
        .day.selected {
            background-color: #3c8dbc;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .day.in-range {
            background-color: rgba(60, 141, 188, 0.2);
            border-radius: 0;
            color: #1a2226;
        }
        
        .day.start-date {
            background-color: #3c8dbc;
            color: white;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-top-left-radius: 3px;
            border-bottom-left-radius: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .day.end-date {
            background-color: #3c8dbc;
            color: white;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 3px;
            border-bottom-right-radius: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .day.holiday {
            background-color: #dd4b39;
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .day.holiday::after {
            content: "";
            position: absolute;
            top: 3px;
            right: 3px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: white;
        }
        
        .day.disabled {
            color: #999;
            cursor: not-allowed;
            text-decoration: line-through;
            opacity: 0.5;
        }
        
        .day.empty {
            cursor: default;
        }
        
        @media (max-width: 768px) {
            .calendars-container {
                flex-direction: column;
            }
            
            .calendar {
                width: 100%;
            }
        }
    </style>

    <script>
        $(function() {
            // Inicjalizacja wszystkich kalendarzy na stronie
            $('.form-element-daterangepicker').each(function() {
                initDateRangePicker($(this));
            });
            
            function initDateRangePicker(container) {
                const id = container.data('id');
                const startCalendar = container.find(`#${id}-start-calendar`);
                const endCalendar = container.find(`#${id}-end-calendar`);
                const startDateInput = container.find(`#${id}-start-date-input`);
                const endDateInput = container.find(`#${id}-end-date-input`);
                
                // Pobranie ustawień dla kalendarza
                const holidaysJson = container.data('holidays') || '[]';
                const holidays = JSON.parse(holidaysJson);
                const disablePastDates = container.data('disable-past') === 'true';
                
                // Ustawienie dzisiejszej daty dla porównania
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Inicjalizacja zmiennych dat
                let startDate = parseDateFromInput(startDateInput.val());
                let endDate = parseDateFromInput(endDateInput.val());
                
                // Aktualne miesiące dla każdego kalendarza
                let startCalendarDate = startDate ? new Date(startDate) : new Date();
                let endCalendarDate = endDate ? new Date(endDate) : new Date(startCalendarDate);
                
                if (!endDate) {
                    endCalendarDate.setMonth(endCalendarDate.getMonth() + 1);
                }
                
                // Inicjalizacja kalendarzy
                initCalendar(startCalendar, startCalendarDate, true);
                initCalendar(endCalendar, endCalendarDate, false);
                
                // Aktualizacja wyglądu kalendarzy na podstawie początkowych wartości
                updateCalendarsDisplay();
                
                // Dodanie nasłuchiwania zdarzeń do przycisków nawigacji
                startCalendar.find('.prev-month').on('click', function() {
                    startCalendarDate.setMonth(startCalendarDate.getMonth() - 1);
                    initCalendar(startCalendar, startCalendarDate, true);
                    updateCalendarsDisplay();
                });
                
                startCalendar.find('.next-month').on('click', function() {
                    startCalendarDate.setMonth(startCalendarDate.getMonth() + 1);
                    initCalendar(startCalendar, startCalendarDate, true);
                    updateCalendarsDisplay();
                });
                
                endCalendar.find('.prev-month').on('click', function() {
                    endCalendarDate.setMonth(endCalendarDate.getMonth() - 1);
                    initCalendar(endCalendar, endCalendarDate, false);
                    updateCalendarsDisplay();
                });
                
                endCalendar.find('.next-month').on('click', function() {
                    endCalendarDate.setMonth(endCalendarDate.getMonth() + 1);
                    initCalendar(endCalendar, endCalendarDate, false);
                    updateCalendarsDisplay();
                });
                
                // Funkcja inicjalizująca kalendarz
                function initCalendar(calendarElement, date, isStartCalendar) {
                    const monthName = calendarElement.find('.month-name');
                    const daysContainer = calendarElement.find('.days');
                    
                    // Ustawienie nazwy miesiąca i roku
                    const months = [
                        'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
                        'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'
                    ];
                    monthName.text(`${months[date.getMonth()]} ${date.getFullYear()}`);
                    
                    // Wyczyszczenie kontenera dni
                    daysContainer.empty();
                    
                    // Uzyskanie pierwszego dnia miesiąca
                    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
                    // Dostosowanie do polskiego kalendarza (pierwszy dzień to poniedziałek)
                    let dayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
                    
                    // Ostatni dzień miesiąca
                    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                    const daysInMonth = lastDay.getDate();
                    
                    // Dodanie pustych dni przed pierwszym dniem miesiąca
                    for (let i = 0; i < dayOfWeek; i++) {
                        const emptyDay = $('<div class="day empty"></div>');
                        daysContainer.append(emptyDay);
                    }
                    
                    // Dodanie dni miesiąca
                    for (let i = 1; i <= daysInMonth; i++) {
                        const dayElement = $('<div class="day">' + i + '</div>');
                        
                        const currentDate = new Date(date.getFullYear(), date.getMonth(), i);
                        currentDate.setHours(0, 0, 0, 0);
                        
                        // Sprawdzenie, czy dzień jest dniem wolnym
                        const formattedDate = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;
                        const isHoliday = holidays.includes(formattedDate);
                        
                        // Sprawdzenie, czy data jest przed dzisiejszą i czy blokujemy takie daty
                        const isPastDate = disablePastDates && currentDate < today;
                        
                        // Dodanie odpowiednich klas
                        if (isHoliday) {
                            dayElement.addClass('holiday');
                        }
                        
                        if (isPastDate) {
                            dayElement.addClass('disabled');
                        }
                        
                        // Dodanie obsługi kliknięć tylko dla dozwolonych dni
                        if (!isHoliday && !isPastDate) {
                            dayElement.on('click', function() {
                                if (isStartCalendar) {
                                    // Jeśli kliknięto start kalendarz
                                    startDate = new Date(currentDate);
                                    
                                    // Jeśli wybrano datę końcową przed nową datą początkową, resetuj datę końcową
                                    if (endDate && startDate > endDate) {
                                        endDate = null;
                                        endDateInput.val('');
                                    }
                                    
                                    // Aktualizacja inputa z datą
                                    startDateInput.val(formatDate(startDate));
                                } else {
                                    // Jeśli kliknięto koniec kalendarz
                                    endDate = new Date(currentDate);
                                    
                                    // Jeśli wybrano datę początkową po nowej dacie końcowej, ustaw datę początkową na tę samą
                                    if (startDate && endDate < startDate) {
                                        startDate = new Date(currentDate);
                                        startDateInput.val(formatDate(startDate));
                                    }
                                    
                                    // Aktualizacja inputa z datą
                                    endDateInput.val(formatDate(endDate));
                                }
                                
                                // Aktualizacja wyglądu kalendarzy
                                updateCalendarsDisplay();
                            });
                        }
                        
                        daysContainer.append(dayElement);
                    }
                }
                
                // Funkcja parsująca datę z formatu DD.MM.YYYY
                function parseDateFromInput(dateString) {
                    if (!dateString) return null;
                    
                    const parts = dateString.split('.');
                    if (parts.length !== 3) return null;
                    
                    const day = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10) - 1;
                    const year = parseInt(parts[2], 10);
                    
                    return new Date(year, month, day);
                }
                
                // Funkcja formatująca datę do formatu DD.MM.YYYY
                function formatDate(date) {
                    if (!date) return '';
                    
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear();
                    
                    return `${day}.${month}.${year}`;
                }
                
                // Funkcja aktualizująca wygląd kalendarzy na podstawie wybranych dat
                function updateCalendarsDisplay() {
                    const allDays = container.find('.day');
                    
                    // Usunięcie wszystkich klas stanu
                    allDays.removeClass('selected in-range start-date end-date');
                    
                    // Jeśli nie ma wybranych dat, zakończ
                    if (!startDate && !endDate) return;
                    
                    // Iteracja przez wszystkie dni w obu kalendarzach
                    allDays.each(function() {
                        const day = $(this);
                        
                        if (day.hasClass('empty')) return;
                        
                        // Uzyskanie kalendarza, do którego należy ten dzień
                        const calendarElement = day.closest('.calendar');
                        const isStartCalendar = calendarElement.attr('id') === `${id}-start-calendar`;
                        const calendarDate = isStartCalendar ? startCalendarDate : endCalendarDate;
                        
                        // Uzyskanie pełnej daty dla tego dnia
                        const dayNumber = parseInt(day.text());
                        const currentDate = new Date(calendarDate.getFullYear(), calendarDate.getMonth(), dayNumber);
                        
                        // Sprawdzenie, czy jest to data początkowa lub końcowa
                        const isStartDate = startDate && currentDate.getTime() === startDate.getTime();
                        const isEndDate = endDate && currentDate.getTime() === endDate.getTime();
                        
                        // Sprawdzenie, czy data jest w zakresie
                        const isInRange = startDate && endDate && 
                                          currentDate > startDate && 
                                          currentDate < endDate;
                        
                        // Zastosowanie odpowiednich klas
                        if (isStartDate && isEndDate) {
                            day.addClass('selected');
                        } else if (isStartDate) {
                            day.addClass('start-date');
                        } else if (isEndDate) {
                            day.addClass('end-date');
                        } else if (isInRange) {
                            day.addClass('in-range');
                        }
                    });
                }
            }
        });
    </script>
@endsection