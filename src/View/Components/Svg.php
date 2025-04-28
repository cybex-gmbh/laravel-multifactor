<?php

namespace CybexGmbh\LaravelMultiFactor\View\Components;

use CybexGmbh\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\View\Component;

class Svg extends Component
{
    /**
     * @param string $method
     */
    public function __construct(
        public string $method,
    ) {
        $this->twoFactorAuthMethod = MultiFactorAuthMethod::from($method);
    }

    /**
     * @return Application|Factory|Htmlable|View
     */
    public function render()
    {
        return view($this->twoFactorAuthMethod->getSvg());
    }
}
