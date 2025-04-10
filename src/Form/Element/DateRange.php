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

        $array['format'] = $this->getFormat();
        $array['numberOfMonths'] = $this->getNumberOfMonths();
        $array['numberOfColumns'] = $this->getNumberOfColumns();
        $array['minDate'] = $this->getMinDate();
        $array['maxDate'] = $this->getMaxDate();
        $array['lockedDays'] = $this->getLockedDays();
        $array['highlightWeekends'] = $this->isHighlightWeekends();
        $array['locale'] = $this->getLocale();
        $array['autoApply'] = $this->isAutoApply();
        $array['showTooltip'] = $this->isShowTooltip();
        $array['tooltipText'] = $this->getTooltipText();

        return $array;
    }
}