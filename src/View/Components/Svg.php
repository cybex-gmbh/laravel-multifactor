<?php

namespace Cybex\LaravelMultiFactor\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Svg extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $icon)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view(sprintf('laravel-multi-factor::svgs.%s', $this->icon));
    }
}
