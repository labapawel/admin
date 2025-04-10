<?php

namespace SleepingOwl\Admin\Form\Element;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SleepingOwl\Admin\Contracts\Assets\AssetManagerInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Element\NamedFormElement;

class DateRange extends NamedFormElement implements Initializable
{
    /**
     * @var string Path to view template
     */
    protected $view = 'admin.form.element.daterange'; // Ścieżka do widoku Blade

    protected $fromField;
    protected $toField;
    protected $minDate;
    protected $maxDate;
    protected $holidays = [];
    protected $highlightWeekends = false;
    protected $format = 'Y-m-d'; // Format daty dla JS i parsowania
    protected $separator = ' - '; // Separator używany przez Litepicker

    /**
     * @param string $fromPath Name of "date from" model attribute
     * @param string $toPath   Name of "date to" model attribute
     * @param string|null $label
     */
    public function __construct($fromPath, $toPath, $label = null)
    {
        // Używamy $fromPath jako głównego 'path' dla kompatybilności,
        // ale $toPath jest równie ważny i będzie używany w logice zapisu.
        parent::__construct($fromPath, $label);

        $this->fromField = $fromPath;
        $this->toField = $toPath;
    }

    /**
     * Initialize the element. For assets registration.
     */
    public function initialize()
    {
        $assetManager = app(AssetManagerInterface::class);

        // Dodaj CSS i JS Litepicker (lepiej globalnie, ale tu dla przykładu)
        $assetManager->addCss('litepicker-css', 'https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css');
        $assetManager->addJs('litepicker-js', 'https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js', ['admin-default']);
        // Można dodać zależność od moment.js jeśli potrzebne do zaawansowanego formatowania/lokalizacji
        // $assetManager->addJs('moment-js', 'https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js', ['admin-default']);
        // $assetManager->addJs('moment-pl-js', 'https://cdn.jsdelivr.net/npm/moment@2.30.1/locale/pl.js', ['moment-js']);
        $assetManager->addJs('litepicker-init-'.$this->getName(), $this->renderJs(), ['litepicker-js']);

    }

    /**
     * Set minimum selectable date.
     * @param string|Carbon|\DateTime $date
     * @return $this
     */
    public function minDate($date): self
    {
        $this->minDate = $this->formatDateForJs($date);
        return $this;
    }

    /**
     * Set maximum selectable date.
     * @param string|Carbon|\DateTime $date
     * @return $this
     */
    public function maxDate($date): self
    {
        $this->maxDate = $this->formatDateForJs($date);
        return $this;
    }

    /**
     * Set specific dates to highlight (e.g., holidays).
     * Expects array of 'Y-m-d' strings or Carbon/DateTime objects.
     * @param array $dates
     * @return $this
     */
    public function highlightHolidays(array $dates): self
    {
        $this->holidays = collect($dates)->map(function ($date) {
            return $this->formatDateForJs($date);
        })->filter()->all(); // Upewnij się, że są w poprawnym formacie
        return $this;
    }

    /**
     * Enable highlighting of Saturdays and Sundays.
     * @param bool $highlight
     * @return $this
     */
    public function highlightWeekends(bool $highlight = true): self
    {
        $this->highlightWeekends = $highlight;
        return $this;
    }

    /**
     * Set the date format used by JS picker and for display.
     * See Litepicker/Moment.js docs for format options.
     * @param string $format
     * @return $this
     */
    public function format(string $format = 'Y-M-D'): self
    {
        // Trzeba będzie dostosować formatowanie daty dla JS i PHP
        // Na razie zostawiamy Y-m-d dla prostoty
        //$this->format = $format;
        return $this;
    }


    /**
     * Get the combined value from the model's two fields.
     * @return string|null
     */
    public function getValueFromModel()
    {
        $model = $this->getModel();
        $fromValue = $model->getAttribute($this->fromField);
        $toValue = $model->getAttribute($this->toField);

        // Spróbuj sparsować daty, aby upewnić się, że są w poprawnym formacie
        try {
            $from = $fromValue ? Carbon::parse($fromValue)->format($this->format) : null;
            $to = $toValue ? Carbon::parse($toValue)->format($this->format) : null;

            if ($from && $to) {
                return $from . $this->separator . $to;
            } elseif ($from) {
                // Jeśli jest tylko data początkowa, można ją zwrócić
                // lub zwrócić pusty string, w zależności od wymagań UX
                return $from;
            }
        } catch (\Exception $e) {
            // Jeśli parsowanie się nie uda, zwróć pusty string lub loguj błąd
            \Log::error("DateRange Error Parsing Model Value: " . $e->getMessage());
            return null;
        }

        return null; // Zwróć null, jeśli obie daty są puste
    }

    /**
     * Override setModelValue to split the combined date range string
     * from the input and save it to the two separate model fields.
     *
     * @param Model $model
     * @param mixed $value The combined string value from the form input.
     */
    public function setModelValue(Model $model, $value)
    {
        $fromValue = null;
        $toValue = null;

        if (is_string($value) && strpos($value, $this->separator) !== false) {
            list($fromStr, $toStr) = array_map('trim', explode($this->separator, $value, 2));

            try {
                // Parsuj używając formatu zdefiniowanego dla JS/wyświetlania
                // Można dostosować, jeśli format JS jest inny niż DB
                $fromValue = Carbon::parse($fromStr)->format('Y-m-d H:i:s'); // Format dla DB
                $toValue = Carbon::parse($toStr)->format('Y-m-d H:i:s');   // Format dla DB
            } catch (\Exception $e) {
                // Logowanie błędu parsowania
                 \Log::error("DateRange Error Parsing Submitted Value: {$value} - " . $e->getMessage());
                 // Można ustawić wartości na null lub rzucić wyjątek walidacji
                 $fromValue = null;
                 $toValue = null;
            }
        } elseif (is_string($value) && !empty($value)) {
            // Handle case where only one date might be selected (if allowed by Litepicker config)
             try {
                 $fromValue = Carbon::parse($value)->format('Y-m-d H:i:s');
                 $toValue = null; // Or $fromValue if range should default to single day
             } catch (\Exception $e) {
                 \Log::error("DateRange Error Parsing Single Submitted Value: {$value} - " . $e->getMessage());
                 $fromValue = null;
                 $toValue = null;
             }
        }

        $model->setAttribute($this->fromField, $fromValue);
        $model->setAttribute($this->toField, $toValue);
    }

    /**
     * Override save method to ensure setModelValue is called correctly.
     * We might not need this if setModelValue works as expected.
     *
     * @param Request $request
     */
    // public function save(Request $request)
    // {
    //     $value = $request->input($this->getPath()); // Get combined value
    //     $this->setModelValue($this->getModel(), $value);
    //     // No need to call parent::save() as we've handled the attributes
    // }


    /**
     * Prepare data for the view.
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + [
            'id' => $this->getId(), // Potrzebny dla JS
            'value' => $this->getValueFromModel() ?? $this->getDefaultValue(), // Pobierz połączoną wartość
            'readonly' => $this->isReadonly(),
            'attributes' => $this->getAttributes(), // Dodatkowe atrybuty HTML
            'picker_options' => $this->getPickerOptions(),
            // Przekazujemy nazwę pola, aby JS wiedział, które pole aktualizować
            'input_name' => $this->getName() // To będzie `fromField`
        ];
    }

    /**
     * Generate Litepicker configuration options.
     * @return array
     */
    protected function getPickerOptions(): array
    {
        $options = [
            'element' => '#'.$this->getId(), // JS użyje tego ID
            'singleMode' => false,
            'allowRepick' => true, // Pozwala zmienić datę końcową po wyborze zakresu
            'numberOfMonths' => 2, // Domyślnie 2 miesiące, można zmienić
            'numberOfColumns' => 2,
            'format' => 'YYYY-MM-DD', // Format używany przez Litepicker (ważne!)
            'separator' => $this->separator,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
            'showTooltip' => true,
            'lang' => 'pl-PL', // Ustawienie języka
             'buttonText' => [ // Opcjonalne polskie teksty przycisków
                 'apply' => 'Zastosuj',
                 'cancel' => 'Anuluj',
             ],
            'highlightedDays' => [], // Inicjalizujemy pustą tablicą
        ];

         // Dodaj logikę podświetlania weekendów i świąt
        if ($this->highlightWeekends || !empty($this->holidays)) {
             $highlighted = $this->holidays; // Zaczynamy od świąt
             // Funkcja JS, która będzie podświetlać weekendy, jeśli włączone
             $weekendHighlighterJs = $this->highlightWeekends
                 ? 'const day = date.getDay(); if (day === 0 || day === 6) { return true; }'
                 : '';

             // Funkcja JS do sprawdzania, czy data jest na liście świąt
             $holidayCheckerJs = !empty($this->holidays)
                 ? 'const dateString = date.format("YYYY-MM-DD"); if (highlightedHolidays.includes(dateString)) { return true; }'
                 : '';

             // Łączymy logikę w callback dla highlightedDays
             $options['highlightedDays'] = "js:function(date) {
                 const highlightedHolidays = " . json_encode($this->holidays) . ";
                 {$holidayCheckerJs}
                 {$weekendHighlighterJs}
                 return false;
             }";

             // Usuwamy 'js:' prefix przed wysłaniem do toArray() jeśli nie jest obsługiwany automatycznie
             // W nowszych wersjach SO może być konieczne specjalne traktowanie JS callbacks
        }


        // Usuń puste opcje
        return array_filter($options, function ($value) {
            return !is_null($value);
        });
    }

    /**
    * Generates the JavaScript code for initializing the Litepicker instance.
    * @return string
    */
    protected function renderJs(): string
    {
        $options = $this->getPickerOptions();
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES);

        // Obsługa funkcji JS w JSON (jeśli 'js:' prefix działa)
        // Jeśli nie, trzeba by to złożyć ręcznie jako string w JS
        $optionsJson = preg_replace('/"js:(.*?)"/', '$1', $optionsJson);


        return <<<JS
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('{$this->getId()}')) {
                 // Można dodać moment.js dla lepszej lokalizacji
                 // if (typeof moment !== 'undefined') {
                 //     moment.locale('pl');
                 // }
                 const picker = new Litepicker({$optionsJson});
            }
        });
        JS;
    }


    /**
     * Format date string or object into 'Y-m-d' for JS compatibility.
     * @param mixed $date
     * @return string|null
     */
    protected function formatDateForJs($date): ?string
    {
        if ($date instanceof Carbon) {
            return $date->format($this->format);
        }
        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->format);
        }
        if (is_string($date)) {
            try {
                // Spróbuj sparsować jako datę, aby upewnić się, że jest poprawna
                return Carbon::parse($date)->format($this->format);
            } catch (\Exception $e) {
                return null; // Ignoruj nieprawidłowe stringi
            }
        }
        return null;
    }

}