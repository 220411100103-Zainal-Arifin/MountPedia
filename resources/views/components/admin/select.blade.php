@props([
    'label',
    'name',
    'hint' => null,
    'required' => false,
    'span' => false,
])

<div @class(['f-field', 'f-field--wide' => $span])>
    <label class="f-label" for="f-{{ $name }}">{{ $label }}@if ($required)<span class="f-req" aria-hidden="true"> *</span>@endif</label>
    <select id="f-{{ $name }}" name="{{ $name }}"
            {{ $attributes->class(['f-input', 'f-select', 'is-invalid' => $errors->has($name)]) }}
            @if ($required) required @endif>
        {{ $slot }}
    </select>
    @error($name)
        <p class="f-error" role="alert">{{ $message }}</p>
    @elseif ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @enderror
</div>
