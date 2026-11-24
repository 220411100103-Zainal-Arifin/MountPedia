@props([
    'label',
    'name',
    'value' => null,
    'hint' => null,
    'span' => true,
])

<div @class(['f-field', 'f-field--wide' => $span])>
    <label class="f-label" for="f-{{ $name }}">{{ $label }}</label>
    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="5"
              {{ $attributes->class(['f-input', 'f-textarea', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="f-error" role="alert">{{ $message }}</p>
    @elseif ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @enderror
</div>
