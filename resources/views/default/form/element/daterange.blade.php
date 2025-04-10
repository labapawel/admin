<?php

namespace App\Admin\Form\Element; // Upewnij się, że namespace jest poprawny dla Twojej aplikacji

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request; // Chociaż nie używamy bezpośrednio w save, może być potrzebne w przyszłości
use SleepingOwl\Admin\Contracts\Assets\AssetManagerInterface;
use SleepingOwl\Admin\Contracts\Initializable;
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
     * Inicjalizacja elementu - głównie rejestracja zasobów JS/CSS.
     */
    public function initialize()
    {
        $assetManager = app(AssetManagerInterface::class);
        $elementId = $this->getId(); // Pobierz ID elementu

        // Dodaj CSS i JS Litepicker
        // W produkcji lepiej zarządzać przez npm/mix i ładować lokalne pliki
        $assetManager->addCss('litepicker-css', 'https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css');
        $assetManager->addJs('litepicker-js', 'https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js', ['admin-default']);

        // Dodaj skrypt inicjalizujący specyficzny dla tego elementu
        // Używamy unikalnego klucza, aby uniknąć konfliktów, jeśli jest wiele instancji
        $assetManager->addJs('litepicker-init-'.$elementId, $this->renderJs(), ['litepicker-js'], true); // true = render as body bottom script
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
                return $from . $this->separator . $to;
            } elseif ($from) {
                // Jeśli jest tylko data początkowa (np. zakres jednodniowy lub błąd danych)
                // Można zwrócić tylko ją, lub połączoną z samą sobą, albo null
                return $from; // Zwracamy tylko datę początkową
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
            // Lub jeśli separator nie został znaleziony. Traktujemy to jako błąd zakresu.
            Log::warning("DateRange ({$this->getName()}): Received single date value or invalid format: '{$value}'. Clearing range.");
            $fromValue = null;
            $toValue = null;
            // Można też spróbować ustawić obie daty na tę jedną wartość, jeśli taka logika jest pożądana:
            // try {
            //     $singleDate = Carbon::createFromFormat($this->format, $value)->format($this->databaseFormat);
            //     $fromValue = $singleDate;
            //     $toValue = $singleDate;
            // } catch (\Exception $e) { ... }
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
        return parent::toArray() + [
            'id' => $this->getId(), // ID elementu HTML
            'value' => $this->getValueFromModel() ?? $this->getDefaultValue(), // Wartość dla inputa
            'readonly' => $this->isReadonly(),
            'attributes' => $this->getAttributes(), // Dodatkowe atrybuty HTML (np. class)
            'picker_options' => $pickerOptions, // Podstawowe opcje dla Litepickera
            'input_name' => $this->getName(), // Nazwa inputa (np. 'start_date')
            'config' => $jsConfig, // Dodatkowa konfiguracja dla logiki JS
            // Przekazujemy również flagę required do widoku (dziedziczone z parent::toArray())
            // 'required' => $this->isRequired() // Nie trzeba dodawać, jeśli parent::toArray() działa
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
            'format' => strtoupper($this->format), // Format wyświetlania w inpucie (np. 'YYYY-MM-DD') - Litepicker używa innych tokenów niż PHP date()
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
        $config = $this->toArray()['config']; // Pobierz dane do logiki JS (weekendy, święta)

        // Bezpieczne kodowanie opcji i konfiguracji do formatu JSON
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        // Zbuduj kod JavaScript
        return <<<JS
        document.addEventListener('DOMContentLoaded', function() {
            const element = document.getElementById('{$elementId}');
            if (element) {
                // Klonujemy opcje, aby nie modyfikować globalnego obiektu, jeśli skrypt byłby reużywany
                let options = JSON.parse('{$optionsJson}');
                const config = JSON.parse('{$configJson}');

                // Dodaj element docelowy do opcji
                options.element = element;

                // Zbuduj funkcję highlightedDays w JS, jeśli potrzebna
                if (config.highlightWeekends || (config.holidays && config.holidays.length > 0)) {
                    options.highlightedDays = function(date) { // `date` to natywny obiekt Date
                        // Czasami Litepicker może przekazać null, np. przy czyszczeniu
                        if (!date || !(date instanceof Date)) {
                           return false;
                        }

                        let isHighlighted = false;

                        // Sprawdź weekendy
                        if (config.highlightWeekends) {
                            const day = date.getDay(); // 0 = Niedziela, 6 = Sobota
                            if (day === 0 || day === 6) {
                                isHighlighted = true;
                            }
                        }

                        // Sprawdź święta (jeśli nie jest już podświetlony jako weekend)
                        if (!isHighlighted && config.holidays && config.holidays.length > 0) {
                            try {
                                // Użyj niezawodnego formatowania YYYY-MM-DD z obiektu Date
                                // Dodaj obsługę strefy czasowej, aby uniknąć problemów +/- 1 dzień
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

                // Logowanie opcji do debugowania (można usunąć w produkcji)
                // console.log('Initializing Litepicker [{$elementId}] with options:', options);

                // Inicjalizuj Litepicker
                try {
                    const picker = new Litepicker(options);
                } catch (e) {
                     console.error("DateRange ({$elementId}): Failed to initialize Litepicker.", e, options);
                }

            } else {
                 console.error("DateRange: Element with ID '{$elementId}' not found.");
            }
        });
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