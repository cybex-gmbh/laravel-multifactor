<?php

namespace CybexGmbh\LaravelTwoFactor\View\Components\Form;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    /**
     * Create a new component instance.
     *
     * @param string $id
     * @param string $field
     * @param string $label
     * @param string|null $autocomplete
     * @param string|null $value
     * @param string|null $type
     */
    public function __construct(
        public string $id,
        public string $field,
        public string $label = '',
        public ?string $autocomplete = '',
        public ?string $value = null,
        public ?string $type = null
    ) {
        $this->autocomplete = $this->autocomplete ?: $this->field;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('laravel-two-factor::components.form.input');
    }
}
