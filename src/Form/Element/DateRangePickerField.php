<?php

namespace SleepingOwl\Admin\Form\Element;

use SleepingOwl\Admin\Form\Element\DateTime;
use SleepingOwl\Admin\Form\FormElement;

class DateRangePickerField extends FormElement
{
    /**
     * @var string
     */
    protected $view = 'form.element.daterangepicker';

    /**
     * @var string
     */
    protected $startField;

    /**
     * @var string
     */
    protected $endField;

    /**
     * @var string
     */
    protected $format = 'd.m.Y';
    
    /**
     * @var string
     */
    protected $startLabel = 'Data początkowa';
    
    /**
     * @var string
     */
    protected $endLabel = 'Data końcowa';
    
    /**
     * @var array
     */
    protected $holidays = [];
    
    /**
     * @var bool
     */
    protected $disablePastDates = false;

    /**
     * DateRangePickerField constructor.
     * 
     * @param string $startField
     * @param string $endField
     */
    public function __construct($startField, $endField)
    {
        parent::__construct();
        
        $this->setStartField($startField);
        $this->setEndField($endField);
    }

    /**
     * @return string
     */
    public function getStartField()
    {
        return $this->startField;
    }

    /**
     * @param string $startField
     * 
     * @return $this
     */
    public function setStartField($startField)
    {
        $this->startField = $startField;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getEndField()
    {
        return $this->endField;
    }

    /**
     * @param string $endField
     * 
     * @return $this
     */
    public function setEndField($endField)
    {
        $this->endField = $endField;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * 
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        
        return $this;
    }
    
    /**
     * @param string $startLabel
     * 
     * @return $this
     */
    public function setStartLabel($startLabel)
    {
        $this->startLabel = $startLabel;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStartLabel()
    {
        return $this->startLabel;
    }
    
    /**
     * @param string $endLabel
     * 
     * @return $this
     */
    public function setEndLabel($endLabel)
    {
        $this->endLabel = $endLabel;
        
        return $this;
    }
    
    /**
     * Set labels for start and end fields
     * 
     * @param string $startLabel
     * @param string $endLabel
     * 
     * @return $this
     */
    // public function setLabel($startLabel)
    // {
    //     $this->label = "dasdasd";
    //     //$this->setStartLabel($startLabel);
    //     // $this->setEndLabel($endLabel);
        
    //     return $this;
    // }

    /**
     * @return string
     */
    public function getEndLabel()
    {
        return $this->endLabel;
    }
    
    /**
     * Set holidays for the calendar
     * 
     * @param array $holidays Array of dates in 'Y-m-d' format
     * 
     * @return $this
     */
    public function setHolidays(array $holidays)
    {
        $this->holidays = $holidays;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getHolidays()
    {
        return $this->holidays;
    }
    
    /**
     * Disable selection of dates before today
     * 
     * @param bool $disable
     * 
     * @return $this
     */
    public function disablePastDates($disable = true)
    {
        $this->disablePastDates = $disable;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isPastDatesDisabled()
    {
        return $this->disablePastDates;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'startField' => $this->getStartField(),
            'endField'   => $this->getEndField(),
            'format'     => $this->getFormat(),
            'startLabel' => $this->getStartLabel(),
            'endLabel'   => $this->getEndLabel(),
            'startValue' => old($this->getStartField()) ?: $this->getModel()->{$this->getStartField()},
            'endValue'   => old($this->getEndField()) ?: $this->getModel()->{$this->getEndField()},
            'id'         => $this->getStartField().$this->getEndField(),
            'holidays'   => json_encode($this->getHolidays()),
            'disablePastDates' => $this->isPastDatesDisabled(),
        ];
    }

    /**
     * @return string
     */
    public function getValueFromModel()
    {
        return [
            $this->getStartField() => $this->getModel()->{$this->getStartField()},
            $this->getEndField()   => $this->getModel()->{$this->getEndField()},
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function save(\Illuminate\Http\Request $request)
    {
        $this->getModel()->{$this->getStartField()} = $request->input($this->getStartField());
        $this->getModel()->{$this->getEndField()} = $request->input($this->getEndField());
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return [
            $this->getStartField() => $this->getModel()->{$this->getStartField()},
            $this->getEndField()   => $this->getModel()->{$this->getEndField()},
        ];
    }
}