<?php

namespace CybexGmbh\LaravelMultiFactor\View\Components;

use Illuminate\View\Component;

class AuthCard extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('laravel-multi-factor::components.auth-card');
    }
}
