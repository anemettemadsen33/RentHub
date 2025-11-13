<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        $name = fake()->name();
        return [
            'user_id' => User::factory(),
            'bank_name' => fake()->randomElement(['BCR', 'BRD', 'Raiffeisen', 'ING', 'CEC Bank']),
            'account_name' => $name,
            'account_holder_name' => $name,
            'iban' => 'RO' . fake()->numerify('##AAAA################'),
            'bic_swift' => fake()->randomElement(['RNCBROBU', 'BRDEROBU', 'RZBROBU', 'INGBROBU', 'CECEROBU']),
            'bank_address' => fake()->address(),
            'currency' => 'RON',
            'account_type' => fake()->randomElement(['business', 'personal']),
            'notes' => null,
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
