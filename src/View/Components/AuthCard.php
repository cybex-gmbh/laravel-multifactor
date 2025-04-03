<?php

namespace CybexGmbh\LaravelTwoFactor\View\Components;

use Illuminate\View\Component;

class AuthCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('laravel-two-factor::components.auth-card');
    }
}