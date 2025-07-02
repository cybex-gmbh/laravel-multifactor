<div>
    <label for="{{ $id }}" class="mfa-text-sm">{{ $label }}</label>

    <input id="{{ $id }}"
           name="{{ $field }}"
           type="{{ $type ?? 'text' }}"
           value="{{ old($field, $value ?? '') }}"
           autocomplete="{{ $autocomplete }}"
           {{ $attributes->class(['mfa-input', 'mfa-has-errors' => $errors->has($field)]) }}
    />

    @error($field)
        <span class="mfa-text-danger mfa-text-sm" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
