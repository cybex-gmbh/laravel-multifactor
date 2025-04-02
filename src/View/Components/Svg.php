<?php

namespace CybexGmbh\LaravelTwoFactor\View\Components;

use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
use Illuminate\View\Component;

class Svg extends Component
{
    public function __construct(
        public string $method,
    ) {
        $this->twoFactorAuthMethod = TwoFactorAuthMethod::from($method);
    }

    public function render()
    {
        return view($this->twoFactorAuthMethod->getSvg());
    }
}