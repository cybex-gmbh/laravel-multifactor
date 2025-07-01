<?php

namespace Cybex\LaravelMultiFactor\View\Components\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public function __construct(
        public string $field,
        public ?string $id = null,
        public string $label = '',
        public ?string $autocomplete = '',
        public ?string $value = null,
        public ?string $type = null
    ) {
        $this->id ??= $field;
        $this->autocomplete = $this->autocomplete ?: $this->field;
    }

    public function render(): View
    {
        return view('laravel-multi-factor::components.form.input');
    }
}
