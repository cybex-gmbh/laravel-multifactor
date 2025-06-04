<form
    action="{{ $action }}"
    method="POST"
    {{ $attributes->class(['form']) }}>
    @csrf
    @method($method)

    {{ $slot }}
</form>
