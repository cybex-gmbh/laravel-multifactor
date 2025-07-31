<?php

namespace Cybex\LaravelMultiFactor\View\Components;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\View\Component;

class Layout extends Component
{
    public function render()
    {
        return view('laravel-multi-factor::layouts.multi-factor');
    }
}
