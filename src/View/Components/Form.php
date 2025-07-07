<?php

namespace Cybex\LaravelMultiFactor\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Form extends Component
{
    public function __construct(
        public string $action,
        public string $method = 'POST',
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('laravel-multi-factor::components.form');
    }
}
