@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'span' => false,
])

<div @class(['f-field', 'f-field--wide' => $span])>
    <label class="f-label" for="f-{{ $name }}">{{ $label }}@if ($required)<span class="f-req" aria-hidden="true"> *</span>@endif</label>
    <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           {{ $attributes->class(['f-input', 'is-invalid' => $errors->has($name)]) }}
           @if ($required) required @endif
           @if ($errors->has($name)) aria-invalid="true" aria-describedby="f-{{ $name }}-msg" @endif>
    @error($name)
        <p class="f-error" id="f-{{ $name }}-msg" role="alert">{{ $message }}</p>
    @elseif ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @enderror
</div>
