<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UniqueCustomer implements DataAwareRule, ValidationRule
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
        $userId = User::firstWhere('email', $this->data['email'])->id;
        $shopId = $value;

        if (DB::table('shop_user')->where('shop_id', $shopId)->where('user_id', $userId)->exists()) {
             $fail("Пользователь с e-mail {$this->data['email']} уже добавлен!");
        }
    }
}
