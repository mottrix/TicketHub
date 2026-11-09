<?php

namespace Database\Factories;

use App\Models\ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ticket>
 */
class TicketFactory extends Factory
{
    protected $model = ticket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_code' => fake()->unique()->numberBetween(100000000, 999999999),
            'ticket_name' => fake()->sentence(4),
            'ticket_priority' => 'medium',
            'status' => 'open',
        ];
    }
}
