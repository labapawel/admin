<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
    <label for="{{ $id }}" class="control-label">
        {{ $label }} <span class="countHours"></span>

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    <div>
      
        <input type="hidden" id="{{ $id }}" data-start-hour="{{$startHour}}" data-end-hour="{{$endHour}}"   name="{{ $name }}" {!! $attributes !!} value="{{ $value }}">
        <div id="calendar-container-{{ $id }}" class="weekly-calendar-container"></div>
    </div>

    @if($errors->has($name))
        <span class="help-block">
            <strong>{{ $errors->first($name) }}</strong>
        </span>
    @endif
    
    @if($helpText)
        <p class="help-block">{{ $helpText }}</p>
    @endif
</div>

