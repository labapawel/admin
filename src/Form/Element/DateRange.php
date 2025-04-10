<?php

namespace SleepingOwl\Admin\Form\Element;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request; // Chociaż nie używamy bezpośrednio w save, może być potrzebne w przyszłości
use SleepingOwl\Admin\Contracts\Initializable; // Ten interfejs powinien nadal istnieć
// use SleepingOwl\Admin\Facades\AdminTemplate; // <--- Dodano fasadę AdminTemplate
use SleepingOwl\Admin\Form\Element\NamedFormElement;
use Illuminate\Support\Facades\Log; // Do logowania błędów

class DateRange extends NamedFormElement implements Initializable
{
    /**
     * @var string Ścieżka do widoku Blade dla tego elementu
     */
    protected $view = 'admin.form.element.daterange'; // Upewnij się, że plik widoku istnieje w resources/views/admin/form/element/

    protected $fromField;
    protected $toField;
    protected $minDate;
    protected $maxDate;
    protected $holidays = [];
    protected $highlightWeekends = false;
    protected $format = 'Y-m-d'; // Format używany w JS i do parsowania wartości modelu dla inputa
    protected $databaseFormat = 'Y-m-d'; // Format zapisu do bazy danych (można zmienić na 'Y-m-d H:i:s' dla DATETIME)
    protected $separator = ' - '; // Separator używany przez Litepicker w inpucie

    /**
     * @param string $fromPath Nazwa atrybutu modelu dla daty początkowej
     * @param string $toPath   Nazwa atrybutu modelu dla daty końcowej
     * @param string|null $label Etykieta pola formularza
     */
    public function __construct($fromPath, $toPath, $label = null)
    {
        // Używamy $fromPath jako głównego 'path' dla niektórych mechanizmów SleepingOwl,
        // ale $toPath jest równie ważny i będzie używany w logice pobierania/zapisu.
        parent::__construct($fromPath, $label);

        $this->fromField = $fromPath;
        $this->toField = $toPath;
    }

    /**
     * Inicjalizacja elementu - rejestracja zasobów JS/CSS przy użyciu fasady AdminTemplate.
     */
    public function initialize()
    {
        // Nie potrzebujemy już app(AssetManagerInterface::class)
        $elementId = $this->getId(); // Pobierz ID elementu dla unikalności skryptu

        try {
            // Użyj fasady AdminTemplate do dodania stylów i skryptów
            AdminTemplate::addStyle('litepicker-css', 'https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css');

            // Dodaj główny skrypt Litepicker, zadeklaruj zależność (jeśli system jej wymaga)
            // Ostatni argument 'true' zwykle oznacza ładowanie w stopce
            // Zależność 'admin-default' może być specyficzna dla starszych wersji, nowsze mogą jej nie wymagać яввно
            AdminTemplate::addScript('litepicker-js', 'https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js', ['admin-default'], true);

            // Dodaj nasz niestandardowy skrypt inicjalizujący jako blok kodu JS
            // Używamy addScriptBody dla kodu inline, a nie URL
            // Upewnij się, że klucz 'litepicker-init-'.$elementId jest unikalny
            // Podaj zależność 'litepicker-js', aby upewnić się, że biblioteka jest załadowana wcześniej
            AdminTemplate::addScriptBody('litepicker-init-'.$elementId, $this->renderJs(), ['litepicker-js']);

        } catch (\Exception $e) {
             // Złap potencjalne błędy, jeśli fasada lub metody nie istnieją w danej wersji SO
             Log::error("DateRange ({$this->getName()}): Failed to register assets using AdminTemplate facade. Error: " . $e->getMessage());
             // Możesz tu dodać alternatywny sposób ładowania JS/CSS lub rzucić wyjątek
        }
    }


    /**
     * Ustaw minimalną możliwą do wybrania datę.
     * @param string|Carbon|\DateTime $date
     * @return $this
     */
    public function minDate($date): self
    {
        $this->minDate = $this->formatDateForJs($date);
        return $this;
    }

    /**
     * Ustaw maksymalną możliwą do wybrania datę.
     * @param string|Carbon|\DateTime $date
     * @return $this
     */
    public function maxDate($date): self
    {
        $this->maxDate = $this->formatDateForJs($date);
        return $this;
    }

    /**
     * Ustaw konkretne daty do podświetlenia (np. święta).
     * Oczekuje tablicy stringów 'Y-m-d' lub obiektów Carbon/DateTime.
     * @param array $dates
     * @return $this
     */
    public function highlightHolidays(array $dates): self
    {
        $this->holidays = collect($dates)->map(function ($date) {
            return $this->formatDateForJs($date); // Użyj formatu 'Y-m-d'
        })->filter()->unique()->values()->all(); // Upewnij się, że są unikalne i w poprawnym formacie
        return $this;
    }

    /**
     * Włącz podświetlanie Sobót i Niedziel.
     * @param bool $highlight
     * @return $this
     */
    public function highlightWeekends(bool $highlight = true): self
    {
        $this->highlightWeekends = $highlight;
        return $this;
    }

    /**
     * Ustaw format daty używany do wyświetlania w inpucie i parsowania.
     * Uwaga: Zmiana formatu wymaga dostosowania parsowania w setModelValue i getValueFromModel.
     * Domyślnie 'Y-m-d' jest najbezpieczniejsze dla komunikacji z JS.
     * @param string $format
     * @return $this
     */
    public function format(string $format = 'Y-m-d'): self
    {
        $this->format = $format;
        // TODO: Jeśli zmienisz format, dostosuj Carbon::parse($value)->format($this->format)
        // oraz Carbon::createFromFormat($this->format, $dateStr) w setModelValue.
        // Na razie zostawiamy Y-m-d dla spójności z Litepickerem.
        return $this;
    }

    /**
     * Ustaw separator używany przez Litepicker do łączenia dat w inpucie.
     * @param string $separator
     * @return $this
     */
    public function separator(string $separator): self
    {
         $this->separator = $separator;
         return $this;
    }

    /**
     * Pobierz połączoną wartość z dwóch pól modelu do wyświetlenia w inpucie.
     * @return string|null
     */
    public function getValueFromModel()
    {
        $model = $this->getModel();
        $fromValue = $model->getAttribute($this->fromField);
        $toValue = $model->getAttribute($this->toField);

        // Spróbuj sparsować daty i sformatować je zgodnie z $this->format
        try {
            $from = $fromValue ? Carbon::parse($fromValue)->format($this->format) : null;
            $to = $toValue ? Carbon::parse($toValue)->format($this->format) : null;

            if ($from && $to) {
                // Jeśli obie daty istnieją, połącz je separatorem
                return $from . $this->separator . $to;
            } elseif ($from) {
                // Jeśli jest tylko data początkowa (np. zakres jednodniowy lub błąd danych)
                // Zwracamy tylko datę początkową, Litepicker powinien sobie z tym poradzić przy inicjalizacji
                 return $from;
            }
        } catch (\Exception $e) {
            Log::error("DateRange ({$this->getName()}): Error Parsing Model Value. From: [{$fromValue}], To: [{$toValue}] - " . $e->getMessage());
            return null; // Zwróć null w przypadku błędu parsowania
        }

        return null; // Zwróć null, jeśli obie daty są puste
    }

    /**
     * Ustawia wartości w modelu na podstawie połączonego stringu z inputa.
     * Nadpisuje domyślną logikę zapisu.
     *
     * @param Model $model
     * @param mixed $value Wartość z inputa (np. "2025-04-10 - 2025-04-15")
     */
    public function setModelValue(Model $model, $value)
    {
        $fromValue = null;
        $toValue = null;

        if (is_string($value) && !empty($value) && strpos($value, $this->separator) !== false) {
            // Podziel string na dwie części używając separatora
            list($fromStr, $toStr) = array_map('trim', explode($this->separator, $value, 2));

            try {
                // Parsuj daty używając formatu zdefiniowanego dla inputa ($this->format)
                // i sformatuj do zapisu w bazie danych ($this->databaseFormat)
                $fromValue = Carbon::createFromFormat($this->format, $fromStr)->format($this->databaseFormat);
                $toValue = Carbon::createFromFormat($this->format, $toStr)->format($this->databaseFormat);
            } catch (\Exception $e) {
                 Log::error("DateRange ({$this->getName()}): Error Parsing Submitted Range Value: '{$value}' - " . $e->getMessage());
                 // W przypadku błędu ustawiamy na null, walidacja serwerowa powinna to wyłapać
                 $fromValue = null;
                 $toValue = null;
            }
        } elseif (is_string($value) && !empty($value)) {
            // Obsługa przypadku, gdy przesłano tylko jedną datę (np. jeśli allowRepick=false i wybrano tylko start)
            // Lub jeśli separator nie został znaleziony. Traktujemy to jako błąd zakresu lub niekompletny wybór.
            Log::warning("DateRange ({$this->getName()}): Received single date value or invalid format: '{$value}'. Clearing range.");
            $fromValue = null; // Bez drugiej daty, zakres jest niekompletny
            $toValue = null;
        } else {
             // Jeśli wartość jest pusta lub nie jest stringiem, ustawiamy obie daty na null
             $fromValue = null;
             $toValue = null;
        }

        // Ustaw atrybuty w modelu
        $model->setAttribute($this->fromField, $fromValue);
        $model->setAttribute($this->toField, $toValue);
    }


    /**
     * Przygotuj dane do przekazania do widoku Blade.
     * @return array
     */
    public function toArray(): array
    {
        // Pobierz opcje dla Litepickera (bez 'highlightedDays')
        $pickerOptions = $this->getPickerOptions();

        // Przygotuj dane konfiguracyjne do zbudowania logiki JS w widoku
        $jsConfig = [
            'highlightWeekends' => $this->highlightWeekends,
            'holidays' => $this->holidays, // Przekaż sformatowaną tablicę świąt ('Y-m-d')
        ];

        // Połącz dane z rodzica z naszymi danymi
        $parentData = parent::toArray();
        return $parentData + [ // Używamy + aby nasze klucze nadpisały te z rodzica jeśli są takie same, ale dbamy by nie było konfliktów
            'id' => $this->getId(), // ID elementu HTML
            'value' => $this->getValueFromModel() ?? $this->getDefaultValue(), // Wartość dla inputa
            'readonly' => $this->isReadonly(),
            'attributes' => $this->getAttributes(), // Dodatkowe atrybuty HTML (np. class)
            'picker_options' => $pickerOptions, // Podstawowe opcje dla Litepickera
            'input_name' => $this->getName(), // Nazwa inputa (np. 'start_date')
            'config' => $jsConfig, // Dodatkowa konfiguracja dla logiki JS
            // Flaga 'required' powinna być dostępna z $parentData, jeśli metoda required() działa poprawnie
        ];
    }

    /**
     * Generuje podstawową tablicę opcji konfiguracyjnych dla Litepickera w JS.
     * @return array
     */
    protected function getPickerOptions(): array
    {
        $options = [
            // ID elementu zostanie dodane w JS, nie tutaj
            // 'element' => '#'.$this->getId(),
            'singleMode' => false, // Tryb zakresu dat
            'allowRepick' => true, // Pozwala zmienić datę końcową po wybraniu zakresu
            'numberOfMonths' => 2, // Liczba wyświetlanych miesięcy
            'numberOfColumns' => 2, // Liczba kolumn dla miesięcy
             // Używamy formatu rozpoznawanego przez Litepicker, np. 'YYYY-MM-DD'
             // Jeśli PHP $this->format jest inny, trzeba go tu przetłumaczyć na format Litepickera
            'format' => 'YYYY-MM-DD', // Twardo ustawiony, bo $this->format jest teraz tylko dla PHP
            'separator' => $this->separator,
            'minDate' => $this->minDate, // Ograniczenie minimalnej daty (np. "2025-04-01")
            'maxDate' => $this->maxDate, // Ograniczenie maksymalnej daty
            'showTooltip' => true, // Pokaż liczbę wybranych dni
            'lang' => 'pl-PL', // Ustawienie języka (może wymagać załadowania pliku lokalizacji)
             'buttonText' => [ // Polskie teksty przycisków
                 'apply' => 'Zastosuj',
                 'cancel' => 'Anuluj',
             ],
             'autoApply' => true, // Automatycznie zatwierdź wybór po kliknięciu drugiej daty
             'showWeekNumbers' => false, // Opcjonalnie: pokaż numery tygodni
             'startOfWeek' => 1, // Opcjonalnie: zacznij tydzień od poniedziałku (0=Niedziela, 1=Poniedziałek)

            // 'highlightedDays' zostanie dodane dynamicznie w JS
        ];

        // Usuń klucze z wartościami null, aby nie zaśmiecać obiektu JS
        return array_filter($options, function ($value) {
            return !is_null($value);
        });
    }

    /**
    * Generuje kod JavaScript do inicjalizacji instancji Litepicker.
    * @return string
    */
    protected function renderJs(): string
    {
        $elementId = $this->getId();
        $options = $this->getPickerOptions(); // Pobierz podstawowe opcje
        // Musimy ponownie pobrać konfigurację, ponieważ toArray() jest wywoływane osobno
        $config = [
             'highlightWeekends' => $this->highlightWeekends,
             'holidays' => $this->holidays,
         ];

        // Bezpieczne kodowanie opcji i konfiguracji do formatu JSON
        // JSON_PRETTY_PRINT jest tylko dla czytelności podczas debugowania
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT);
        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT);

        // Zbuduj kod JavaScript
        // Używamy funkcji anonimowej, aby uniknąć zanieczyszczania globalnego scope
        return <<<JS
(function() {
    function initPicker_{$elementId}() {
        const element = document.getElementById('{$elementId}');
        if (!element) {
            // console.error("DateRange: Element with ID '{$elementId}' not found.");
            // Element może jeszcze nie istnieć, jeśli JS jest w <head>, spróbujemy ponownie na DOMContentLoaded
            return;
        }
        if (element.litepickerInstance) {
            // Już zainicjowano, zapobiegaj podwójnej inicjalizacji
            return;
        }

        try {
            // Klonujemy opcje, aby nie modyfikować globalnego obiektu, jeśli skrypt byłby reużywany
            // Użycie JSON.parse(JSON.stringify(obj)) jest prostym sposobem na głębokie klonowanie prostych obiektów JSON
            let options = JSON.parse('{$optionsJson}');
            const config = JSON.parse('{$configJson}');

            // Dodaj element docelowy do opcji
            options.element = element;

            // Zbuduj funkcję highlightedDays w JS, jeśli potrzebna
            if (config.highlightWeekends || (config.holidays && config.holidays.length > 0)) {
                options.highlightedDays = function(date) { // `date` to natywny obiekt Date
                    if (!date || !(date instanceof Date)) return false;

                    let isHighlighted = false;
                    const day = date.getDay(); // 0 = Niedziela, 6 = Sobota

                    // Sprawdź weekendy
                    if (config.highlightWeekends && (day === 0 || day === 6)) {
                        isHighlighted = true;
                    }

                    // Sprawdź święta (jeśli nie jest już podświetlony jako weekend)
                    if (!isHighlighted && config.holidays && config.holidays.length > 0) {
                        try {
                            // Użyj niezawodnego formatowania YYYY-MM-DD z obiektu Date
                            const year = date.getFullYear();
                            const month = (date.getMonth() + 1).toString().padStart(2, '0');
                            const dayOfMonth = date.getDate().toString().padStart(2, '0');
                            const dateString = `${year}-${month}-${dayOfMonth}`;

                            if (config.holidays.includes(dateString)) {
                                isHighlighted = true;
                            }
                        } catch (e) {
                            console.error("DateRange ({$elementId}): Error formatting date for holiday check:", date, e);
                        }
                    }
                    return isHighlighted;
                };
            } // koniec budowania highlightedDays

            // Inicjalizuj Litepicker
            const picker = new Litepicker(options);
            element.litepickerInstance = picker; // Zapisz instancję na elemencie, aby uniknąć re-inicjalizacji

        } catch (e) {
             console.error("DateRange ({$elementId}): Failed to initialize Litepicker.", e, JSON.parse('{$optionsJson}'));
        }
    } // koniec funkcji initPicker

    // Spróbuj zainicjować od razu, jeśli DOM jest gotowy
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initPicker_{$elementId}();
    } else {
        // W przeciwnym razie, poczekaj na DOMContentLoaded
        document.addEventListener('DOMContentLoaded', initPicker_{$elementId});
    }
})();
JS;
    }


    /**
     * Formatuje datę (string, Carbon, DateTime) do stringa 'Y-m-d'
     * bezpiecznego do użycia w opcjach JS Litepickera (minDate, maxDate, holidays).
     * @param mixed $date
     * @return string|null Zwraca string 'Y-m-d' lub null w przypadku błędu.
     */
    protected function formatDateForJs($date): ?string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }
        if ($date instanceof \DateTimeInterface) {
            // Użyj formatowania z obiektu DateTime
            return $date->format('Y-m-d');
        }
        if (is_string($date) && !empty($date)) {
            try {
                // Spróbuj sparsować jako datę i sformatować
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("DateRange ({$this->getName()}): Could not parse date string for JS options: '{$date}'");
                return null; // Ignoruj nieprawidłowe stringi
            }
        }
        return null; // Zwróć null dla innych typów lub pustych wartości
    }
}