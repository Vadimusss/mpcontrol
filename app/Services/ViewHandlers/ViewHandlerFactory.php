<?php

namespace App\Services\ViewHandlers;

use InvalidArgumentException;

class ViewHandlerFactory
{
    public static function make(string $viewType): ViewHandler
    {
        return match($viewType) {
            'main' => new MainViewHandler(),
            'sizes' => new SizesViewHandler(),
            default => throw new InvalidArgumentException("Unknown view type: $viewType"),
        };
    }
}
