<?php

namespace CybexGmbh\LaravelTwoFactor\View\Components;

use Illuminate\View\Component;

class Layout extends Component
{
    public function render()
    {
        return view('laravel-two-factor::layouts.two-factor');
    }
}