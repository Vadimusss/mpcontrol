<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class NotOwner implements DataAwareRule, ValidationRule
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
        $userId = User::firstWhere('email', $value)->id;
        $shopId = $this->data['shopId'];

        if (DB::table('shops')->where('id', $shopId)->where('user_id', $userId)->exists()) {
             $fail("Пользователь с e-mail {$this->data['email']} владелец магазина!");
        }
    }
}