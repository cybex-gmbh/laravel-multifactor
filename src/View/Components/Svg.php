<?php

namespace CybexGmbh\LaravelMultiFactor\View\Components;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\View\Component;

class Svg extends Component
{
    public function __construct(
        public string $method,
    ) {
        $this->twoFactorAuthMethod = MultiFactorAuthMethod::from($method);
    }

    public function render()
    {
        return view($this->twoFactorAuthMethod->getSvg());
    }
}
