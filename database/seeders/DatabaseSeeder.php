<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The default seed command creates one factory-backed customer account.
     * TicketSeeder is not called here and must be run explicitly
     * after their required users/tickets have been prepared. No admin is created here.
     */
    public function run(): void
    {
        User::firstOrCreate(['email' => 'test@example.com'], User::factory()->raw([
            'name' => 'Test User',
        ]));
    }
}
