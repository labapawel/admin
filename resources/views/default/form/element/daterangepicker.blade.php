
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

