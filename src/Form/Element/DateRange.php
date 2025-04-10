<?php

namespace SleepingOwl\Admin\Form\Element;

use Carbon\Carbon;
use SleepingOwl\Admin\Form\FormElement;

class DateRange extends NamedFormElement
{
    /**
     * @var string
     */
    protected $view = 'form.element.daterange';

    /**
     * @var string
     */
    protected $format = 'DD.MM.YYYY';

    /**
     * @var int
     */
    protected $numberOfMonths = 2;

    /**
     * @var int
     */
    protected $numberOfColumns = 2;

    /**
     * @var string|null
     */
    protected $minDate = null;

    /**
     * @var string|null
     */
    protected $maxDate = null;

    /**
     * @var array
     */
    protected $lockedDays = [];

    /**
     * @var bool
     */
    protected $highlightWeekends = true;

    /**
     * @var string
     */
    protected $locale = 'pl-PL';

    /**
     * @var bool
     */
    protected $autoApply = true;

    /**
     * @var bool
     */
    protected $showTooltip = true;

    /**
     * @var array
     */
    protected $tooltipText = [
        'one' => 'dzień',
        'other' => 'dni',
    ];
    
    /**
     * @var string|null
     */
    protected $value = null;

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set date format.
     *
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfMonths()
    {
        return $this->numberOfMonths;
    }

    /**
     * Set number of months to display.
     *
     * @param int $numberOfMonths
     * @return $this
     */
    public function setNumberOfMonths($numberOfMonths)
    {
        $this->numberOfMonths = (int) $numberOfMonths;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfColumns()
    {
        return $this->numberOfColumns;
    }

    /**
     * Set number of columns.
     *
     * @param int $numberOfColumns
     * @return $this
     */
    public function setNumberOfColumns($numberOfColumns)
    {
        $this->numberOfColumns = (int) $numberOfColumns;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * Set minimum date.
     *
     * @param string|Carbon $minDate
     * @return $this
     */
    public function setMinDate($minDate)
    {
        if ($minDate instanceof Carbon) {
            $minDate = $minDate->format('Y-m-d');
        }

        $this->minDate = $minDate;

        return $this;
    }

    /**
     * Set current date as minimum date.
     *
     * @return $this
     */
    public function setTodayAsMinDate()
    {
        $this->minDate = 'today';

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }

    /**
     * Set maximum date.
     *
     * @param string|Carbon $maxDate
     * @return $this
     */
    public function setMaxDate($maxDate)
    {
        if ($maxDate instanceof Carbon) {
            $maxDate = $maxDate->format('Y-m-d');
        }

        $this->maxDate = $maxDate;

        return $this;
    }

    /**
     * @return array
     */
    public function getLockedDays()
    {
        return $this->lockedDays;
    }

    /**
     * Set locked days.
     *
     * @param array $lockedDays
     * @return $this
     */
    public function setLockedDays(array $lockedDays)
    {
        $this->lockedDays = $lockedDays;

        return $this;
    }

    /**
     * Lock specific dates.
     *
     * @param string|Carbon $date
     * @return $this
     */
    public function lockDay($date)
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        $this->lockedDays[] = $date;

        return $this;
    }

    /**
     * Lock weekends (Saturday and Sunday).
     *
     * @param bool $lock
     * @return $this
     */
    public function lockWeekends($lock = true)
    {
        $this->highlightWeekends = !$lock;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHighlightWeekends()
    {
        return $this->highlightWeekends;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale.
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoApply()
    {
        return $this->autoApply;
    }

    /**
     * Set auto apply option.
     *
     * @param bool $autoApply
     * @return $this
     */
    public function setAutoApply($autoApply)
    {
        $this->autoApply = (bool) $autoApply;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowTooltip()
    {
        return $this->showTooltip;
    }

    /**
     * Set show tooltip option.
     *
     * @param bool $showTooltip
     * @return $this
     */
    public function setShowTooltip($showTooltip)
    {
        $this->showTooltip = (bool) $showTooltip;

        return $this;
    }

    /**
     * @return array
     */
    public function getTooltipText()
    {
        return $this->tooltipText;
    }

    /**
     * Set tooltip text.
     *
     * @param array $tooltipText
     * @return $this
     */
    public function setTooltipText(array $tooltipText)
    {
        $this->tooltipText = $tooltipText;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Upewnij się, że wszystkie niezbędne zmienne są zdefiniowane
        $array['id'] = $this->getName();
        $array['name'] = $this->getName();
        $array['value'] = $this->getValue();
        $array['label'] = $this->getLabel();
        $array['required'] = $this->isRequired();
        $array['helpText'] = $this->getHelpText();
        $array['attributes'] = $this->getHtmlAttributes();

        // Przygotuj opcje dla datepickera
        $options = [
            'format' => $this->getFormat(),
            'numberOfMonths' => $this->getNumberOfMonths(),
            'numberOfColumns' => $this->getNumberOfColumns(),
            'highlightWeekends' => $this->isHighlightWeekends(),
            'locale' => $this->getLocale(),
            'autoApply' => $this->isAutoApply(),
            'showTooltip' => $this->isShowTooltip(),
            'tooltipText' => $this->getTooltipText()
        ];

        // Dodaj opcjonalne parametry tylko jeśli są ustawione
        if ($this->getMinDate() !== null) {
            $options['minDate'] = $this->getMinDate();
        }
        
        if ($this->getMaxDate() !== null) {
            $options['maxDate'] = $this->getMaxDate();
        }
        
        if (!empty($this->getLockedDays())) {
            $options['lockedDays'] = $this->getLockedDays();
        }

        $array['options'] = $options;

        return $array;
    }
    
    /**
     * Initialize the element.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        
        $this->setView('form.element.daterange');
        
        // Dodaj niezbędne assety
        $this->package(['litepicker' => [
            'assets/js/litepicker.js',
            'assets/css/litepicker.css',
        ]]);
    }
    
    /**
     * Prepare element options before rendering.
     *
     * @return void
     */
    public function prepareAttributes()
    {
        parent::prepareAttributes();
        
        $this->setHtmlAttributes([
            'class' => 'form-control daterange-input',
            'autocomplete' => 'off',
        ]);
        
        if ($value = $this->getValue()) {
            $this->setAttribute('value', $value);
        }
    }
    
    /**
     * Get element value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return parent::getValue() ?: $this->value;
    }
    
    /**
     * Set element value.
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        return parent::setValue($value);
    }
}