<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class JobSucceeded
{
    use Dispatchable;

    public function __construct(
        public string $jobName,
        public float $duration,
        public ?string $message = null
    ) {}
}
