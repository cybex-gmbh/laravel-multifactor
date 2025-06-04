<div>
    <label for="{{ $field }}" class="text-sm">{{ $label }}</label>

    <input id="{{ $id }}"
           name="{{ $field }}"
           type="{{ $type ?? 'text' }}"
           value="{{ old($field, $value ?? '') }}"
           autocomplete="{{ $autocomplete }}"
           {{ $attributes->class(['input', 'has-errors' => $errors->has($field)]) }}
           {{ $attributes }}
    />

    @error($field)
        <span class="text-red-400 text-xs" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
