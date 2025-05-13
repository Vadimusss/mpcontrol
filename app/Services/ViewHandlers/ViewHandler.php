<?php

namespace App\Services\ViewHandlers;

use App\Models\WorkSpace;

interface ViewHandler
{
    public function prepareData(WorkSpace $workSpace): array;
    
    public function getComponent(): string;

    public function getDefaultViewState(): array;
}
