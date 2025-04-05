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
