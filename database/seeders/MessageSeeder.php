<?php
namespace Database\Seeders;

use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure a ticket and both users already exist.
        $ticket = Ticket::firstOrFail();

        $ziba = User::where('name', 'ziba')->firstOrFail();

        // Adjust this lookup if your admin role is stored differently.
        $admin = User::all()->first(fn (User $user) => $user->isAdmin());

        if (! $admin) {
            throw new \RuntimeException('No admin user was found.');
        }

        $messages = [
            ['user' => $ziba,  'body' => 'Hello, I am having trouble accessing my account.'],
            ['user' => $admin, 'body' => 'Hi Ziba, I can help you with that. What happens when you try to sign in?'],
            ['user' => $ziba,  'body' => 'The page keeps loading, and then I see an error message.'],
            ['user' => $admin, 'body' => 'Thanks for letting me know. Could you please tell me which browser you are using?'],
            ['user' => $ziba,  'body' => 'I am using Google Chrome on my laptop.'],
            ['user' => $admin, 'body' => 'Please try clearing your browser cache and signing in again.'],
            ['user' => $ziba,  'body' => 'I cleared the cache, but the issue is still happening.'],
            ['user' => $admin, 'body' => 'Understood. I will check your account and investigate the issue further.'],
            ['user' => $ziba,  'body' => 'Thank you for your help. Please let me know when there is an update.'],
            ['user' => $admin, 'body' => 'The issue has been resolved. Please try signing in again and let me know if it works.'],
        ];

        $baseTime = now()->subMinutes(count($messages) * 2);

        foreach ($messages as $index => $item) {
            $ticket->messages()->create([
                'user_id' => $item['user']->id,
                'message' => $item['body'],
                'created_at' => $baseTime->copy()->addMinutes($index * 2),
                'updated_at' => $baseTime->copy()->addMinutes($index * 2),
            ]);
        }
    }
}
