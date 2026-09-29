<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function isManager(): bool
    {
        return auth()->check() && auth()->user()->role?->name === 'Manager';
    }

    protected function requireManager(): void
    {
        abort_unless($this->isManager(), 403, 'Only the manager can do this.');
    }
}
