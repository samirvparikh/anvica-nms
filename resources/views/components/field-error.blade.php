@props(['name'])

@error($name)
    <div {{ $attributes->merge(['class' => 'field-error-message']) }}>{{ $message }}</div>
@enderror
