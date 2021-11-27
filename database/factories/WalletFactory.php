<?php

namespace Database\Factories;



use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WalletFactory extends Factory
{
    private static $incV= 0;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        WalletFactory::$incV++;
        return [
            'owner_id' => WalletFactory::$incV,
        ];
    }
}
