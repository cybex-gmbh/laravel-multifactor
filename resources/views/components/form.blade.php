<form
    action="{{ $action }}"
    method="POST"
    {{ $attributes }}>
    @csrf
    @method($method)

    {{ $slot }}
</form>
