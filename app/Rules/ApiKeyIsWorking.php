<?php

namespace App\Rules;

use Closure;
use App\Services\WbApiService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

class ApiKeyIsWorking implements DataAwareRule, ValidationRule
{
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $apiKey = $this->data['key'];;
        $api = new WbApiService($apiKey);

        if (!$api->makeDiscountsPricesApiPing()) {
            $fail("Ключ Api не работает или сервис не доступен!");
        }
    }
}
