<?php

namespace Cybex\LaravelMultiFactor\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Svg extends Component
{
    public function __construct(public string $icon)
    {
    }

    public function render(): View|Closure|string
    {
        return view(sprintf('laravel-multi-factor::svgs.%s', $this->icon));
    }
}
