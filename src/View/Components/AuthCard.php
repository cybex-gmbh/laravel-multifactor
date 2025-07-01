<?php

namespace Cybex\LaravelMultiFactor\View\Components;

use Illuminate\View\Component;

class AuthCard extends Component
{
    public function render()
    {
        return view('laravel-multi-factor::components.auth-card');
    }
}
