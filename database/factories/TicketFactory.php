<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Supply a basic open ticket for tests, creating an owner unless user_id is overridden.
     * Description is omitted because the schema allows it to be null. Tests currently
     * use TicketFactory::new() directly; Ticket does not include the HasFactory trait.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'priority' => 'medium',
            'status' => 'open',
        ];
    }
}
