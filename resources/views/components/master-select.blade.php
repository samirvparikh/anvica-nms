@props([
    'name',
    'type',
    'label' => null,
    'required' => false,
    'selected' => null,
    'emptyLabel' => 'Select',
    'id' => null,
    'class' => 'form-control',
])

@php
    $fieldId = $id ?? $name;
    $options = master_options($type, true, $emptyLabel);
    $selectedValue = old($name, $selected);
    $hasError = $errors->has($name);
    $controlClass = trim($class . ($hasError ? ' is-invalid' : ''));
@endphp

<div {{ $attributes->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $fieldId }}" style="font-weight: 600;">
            {{ $label }}
            @if($required)
                <span style="color: var(--status-down);">*</span>
            @endif
        </label>
    @endif
    <select name="{{ $name }}" id="{{ $fieldId }}" class="{{ $controlClass }}" @if($required) required @endif @if($hasError) aria-invalid="true" @endif>
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ (string) $selectedValue === (string) $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
    <x-field-error :name="$name" />
</div>
