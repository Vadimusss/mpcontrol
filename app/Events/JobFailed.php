<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

class JobFailed
{
    use Dispatchable;

    public function __construct(
        public string $jobName,
        public Throwable $exception,
        public ?string $message = null,
    ) {}
}
