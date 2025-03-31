<?php

namespace SleepingOwl\Admin\Form\Element;

use SleepingOwl\Admin\Form\Element\NamedFormElement;
use SleepingOwl\Admin\Form\FormElement;

class WeeklyCalendar extends NamedFormElement
{
    protected $view = 'form.element.weekly_calendar';
    
    /**
     * @var int Godzina początkowa
     */
    protected $startHour = 7;
    
    /**
     * @var int Godzina końcowa
     */
    protected $endHour = 15;

    /**
     * @param string $path
     * @param string|null $label
     */
    public function __construct($path, $label = null)
    {
        parent::__construct($path, $label);
    }

    /**
     * Ustaw godzinę początkową
     *
     * @param int $hour
     * @return $this
     */
    public function setStartHour(int $hour)
    {
        $this->startHour = $hour;
        
        return $this;
    }
    
    /**
     * Ustaw godzinę końcową
     *
     * @param int $hour
     * @return $this
     */
    public function setEndHour(int $hour)
    {
        $this->endHour = $hour;
        
        return $this;
    }
    
    /**
     * Pobierz godzinę początkową
     *
     * @return int
     */
    public function getStartHour()
    {
        return $this->startHour;
    }
    
    /**
     * Pobierz godzinę końcową
     *
     * @return int
     */
    public function getEndHour()
    {
        return $this->endHour;
    }
    
    /**
     * Pobierz zaznaczone komórki jako JSON
     *
     * @return string
     */
    public function getValue()
    {
        return parent::getValue();
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'startHour' => $this->getStartHour(),
            'endHour' => $this->getEndHour(),
        ];
    }
    
    /**
     * Pobierz atrybuty elementu
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'class' => 'form-control weekly-calendar-input',
            'data-start-hour' => $this->getStartHour(),
            'data-end-hour' => $this->getEndHour(),
        ] + parent::getAttributes();
    }
}