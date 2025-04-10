<?php

// UWAGA: Zmieniono namespace zgodnie z prośbą.
// Pamiętaj, że umieszczanie tego pliku w katalogu vendor/sleepingowl
// spowoduje jego usunięcie przy aktualizacji przez Composer!
// Zalecany namespace to np. App\Admin\Form\Element
namespace SleepingOwl\Admin\Form\Element;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SleepingOwl\Admin\Contracts\Initializable;
// UWAGA: Jeśli przenosisz plik, upewnij się, że fasada PackageManager jest dostępna.
// Może być konieczne dodanie pełnego 'use' lub sprawdzenie aliasów w config/app.php
use KodiCMS\Assets\Facades\PackageManager;
use SleepingOwl\Admin\Form\Element\NamedFormElement;
use Illuminate\Support\Facades\Log;


// Zakładamy, że klasa NamedFormElement jest dostępna w tym samym namespace
// lub odpowiednie 'use' zostanie dodane, jeśli dziedziczy z innej ścieżki.

class DateRange extends NamedFormElement implements Initializable
{
    /**
     * @var string Ścieżka do widoku Blade dla tego elementu
     * UWAGA: Ścieżka widoku również może wymagać dostosowania, jeśli plik
     * zostanie przeniesiony do vendor. Może być konieczne użycie
     * pełnej ścieżki lub zarejestrowanie nowego namespace dla widoków.
     * Bezpieczniej jest zostawić widok w resources/views.
     */
    protected $view = 'admin.form.element.daterange'; // Domyślna ścieżka - może wymagać zmiany!

    protected $fromField;
    protected $toField;
    protected $minDate;
    protected $maxDate;
    protected $holidays = [];
    protected $highlightWeekends = false;
    protected $format = 'Y-m-d';
    protected $databaseFormat = 'Y-m-d';
    protected $separator = ' - ';

    /**
     * @param string $fromPath Nazwa atrybutu modelu dla daty początkowej
     * @param string $toPath   Nazwa atrybutu modelu dla daty końcowej
     * @param string|null $label Etykieta pola formularza
     */
    public function __construct($fromPath, $toPath, $label = null)
    {
        parent::__construct($fromPath, $label);
        $this->fromField = $fromPath;
        $this->toField = $toPath;
    }

    /**
     * Inicjalizacja elementu - rejestracja zasobów JS/CSS przy użyciu fasady PackageManager.
     */
    public function initialize()
    {
        $elementId = $this->getId();

        try {
            PackageManager::css('litepicker-css', 'https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css');
            PackageManager::js('litepicker-js', 'https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js', []);
            PackageManager::js('litepicker-init-'.$elementId, '')
                          ->dependsOn(['litepicker-js'])
                          ->inline($this->renderJs());

        } catch (\Exception $e) {
             Log::error("DateRange ({$this->getName()}): Failed to register assets using PackageManager facade. Error: " . $e->getMessage());
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
     * @param array $dates
     * @return $this
     */
    public function highlightHolidays(array $dates): self
    {
        $this->holidays = collect($dates)->map(function ($date) {
            return $this->formatDateForJs($date);
        })->filter()->unique()->values()->all();
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
     * @param string $format
     * @return $this
     */
    public function format(string $format = 'Y-m-d'): self
    {
        $this->format = $format;
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

        try {
            $from = $fromValue ? Carbon::parse($fromValue)->format($this->format) : null;
            $to = $toValue ? Carbon::parse($toValue)->format($this->format) : null;

            if ($from && $to) {
                return $from . $this->separator . $to;
            } elseif ($from) {
                 return $from;
            }
        } catch (\Exception $e) {
            Log::error("DateRange ({$this->getName()}): Error Parsing Model Value. From: [{$fromValue}], To: [{$toValue}] - " . $e->getMessage());
            return null;
        }
        return null;
    }

    /**
     * Ustawia wartości w modelu na podstawie połączonego stringu z inputa.
     * @param Model $model
     * @param mixed $value
     */
    public function setModelValue(Model $model, $value)
    {
        $fromValue = null;
        $toValue = null;

        if (is_string($value) && !empty($value) && strpos($value, $this->separator) !== false) {
            list($fromStr, $toStr) = array_map('trim', explode($this->separator, $value, 2));
            try {
                $fromValue = Carbon::createFromFormat($this->format, $fromStr)->format($this->databaseFormat);
                $toValue = Carbon::createFromFormat($this->format, $toStr)->format($this->databaseFormat);
            } catch (\Exception $e) {
                 Log::error("DateRange ({$this->getName()}): Error Parsing Submitted Range Value: '{$value}' - " . $e->getMessage());
                 $fromValue = null;
                 $toValue = null;
            }
        } elseif (is_string($value) && !empty($value)) {
            Log::warning("DateRange ({$this->getName()}): Received single date value or invalid format: '{$value}'. Clearing range.");
            $fromValue = null;
            $toValue = null;
        } else {
             $fromValue = null;
             $toValue = null;
        }

        $model->setAttribute($this->fromField, $fromValue);
        $model->setAttribute($this->toField, $toValue);
    }


    /**
     * Przygotuj dane do przekazania do widoku Blade.
     * @return array
     */
    public function toArray(): array
    {
        $pickerOptions = $this->getPickerOptions();
        $jsConfig = [
            'highlightWeekends' => $this->highlightWeekends,
            'holidays' => $this->holidays,
        ];
        $parentData = parent::toArray();
        return $parentData + [
            'id' => $this->getId(),
            'value' => $this->getValueFromModel() ?? $this->getDefaultValue(),
            'readonly' => $this->isReadonly(),
            'attributes' => $this->getAttributes(),
            'picker_options' => $pickerOptions,
            'input_name' => $this->getName(),
            'config' => $jsConfig,
        ];
    }

    /**
     * Generuje podstawową tablicę opcji konfiguracyjnych dla Litepickera w JS.
     * @return array
     */
    protected function getPickerOptions(): array
    {
        $options = [
            'singleMode' => false,
            'allowRepick' => true,
            'numberOfMonths' => 2,
            'numberOfColumns' => 2,
            'format' => 'YYYY-MM-DD', // Format Litepickera
            'separator' => $this->separator,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
            'showTooltip' => true,
            'lang' => 'pl-PL',
             'buttonText' => [
                 'apply' => 'Zastosuj',
                 'cancel' => 'Anuluj',
             ],
             'autoApply' => true,
             'showWeekNumbers' => false,
             'startOfWeek' => 1,
        ];
        return array_filter($options, fn ($value) => !is_null($value));
    }

    /**
    * Generuje kod JavaScript do inicjalizacji instancji Litepicker.
    * @return string
    */
    protected function renderJs(): string
    {
        $elementId = $this->getId();
        $options = $this->getPickerOptions();
        $config = [
             'highlightWeekends' => $this->highlightWeekends,
             'holidays' => $this->holidays,
         ];
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT);
        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRETTY_PRINT);

        return <<<JS
(function() {
    function initPicker_{$elementId}() {
        const element = document.getElementById('{$elementId}');
        if (!element || element.litepickerInstance) return;

        try {
            let options = JSON.parse('{$optionsJson}');
            const config = JSON.parse('{$configJson}');
            options.element = element;

            if (config.highlightWeekends || (config.holidays && config.holidays.length > 0)) {
                options.highlightedDays = function(date) {
                    if (!date || !(date instanceof Date)) return false;
                    let isHighlighted = false;
                    const day = date.getDay();
                    if (config.highlightWeekends && (day === 0 || day === 6)) isHighlighted = true;
                    if (!isHighlighted && config.holidays && config.holidays.length > 0) {
                        try {
                            const year = date.getFullYear();
                            const month = (date.getMonth() + 1).toString().padStart(2, '0');
                            const dayOfMonth = date.getDate().toString().padStart(2, '0');
                            const dateString = `${year}-${month}-${dayOfMonth}`;
                            if (config.holidays.includes(dateString)) isHighlighted = true;
                        } catch (e) { console.error("DateRange ({$elementId}): Error formatting date for holiday check:", date, e); }
                    }
                    return isHighlighted;
                };
            }

            const picker = new Litepicker(options);
            element.litepickerInstance = picker;

        } catch (e) { console.error("DateRange ({$elementId}): Failed to initialize Litepicker.", e, JSON.parse('{$optionsJson}')); }
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        initPicker_{$elementId}();
    } else {
        document.addEventListener('DOMContentLoaded', initPicker_{$elementId});
    }
})();
JS;
    }


    /**
     * Formatuje datę do stringa 'Y-m-d' dla JS.
     * @param mixed $date
     * @return string|null
     */
    protected function formatDateForJs($date): ?string
    {
        if ($date instanceof Carbon) return $date->format('Y-m-d');
        if ($date instanceof \DateTimeInterface) return $date->format('Y-m-d');
        if (is_string($date) && !empty($date)) {
            try { return Carbon::parse($date)->format('Y-m-d'); }
            catch (\Exception $e) { Log::warning("DateRange ({$this->getName()}): Could not parse date string for JS options: '{$date}'"); return null; }
        }
        return null;
    }
} // Koniec klasy DateRange