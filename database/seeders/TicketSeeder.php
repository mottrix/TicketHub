<?php

namespace Database\Seeders;

use App\Models\ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // At least one user must already exist.
        $users = User::all();

        $tickets = [
            [
                'ticket_code' => '1234567',
                'ticket_name' => 'Unable to sign in',
                'description' => 'I cannot sign in to my account. After entering my email and password, the page keeps loading and eventually displays an error.',
                'status' => 'open',
                'ticket_priority' => 'high',
            ],
            [
                'ticket_code' => '12345678',
                'ticket_name' => 'Password reset email not received',
                'description' => 'I requested a password reset email several times, but nothing has arrived in my inbox or spam folder.',
                'status' => 'pending',
                'ticket_priority' => 'medium',
            ],
            [
                'ticket_code' => '12345679',
                'ticket_name' => 'Update my account email',
                'description' => 'I would like to change the email address associated with my account. Please let me know what information you need.',
                'status' => 'open',
                'ticket_priority' => 'low',
            ],
            [
                'ticket_code' => '123456710',
                'ticket_name' => 'Dashboard is loading slowly',
                'description' => 'The dashboard takes a long time to load, especially when I open the ticket list. Could you please investigate the performance?',
                'status' => 'pending',
                'ticket_priority' => 'medium',
            ],
            [
                'ticket_code' => '123456711',
                'ticket_name' => 'Incorrect ticket status',
                'description' => 'My ticket was marked as closed, but the issue has not been resolved yet. Please review its status.',
                'status' => 'open',
                'ticket_priority' => 'high',
            ],
            [
                'ticket_code' => '123456712',
                'ticket_name' => 'Attachment upload failed',
                'description' => 'I tried to attach a screenshot to my ticket, but the upload failed. The file is a PNG image and is smaller than 5 MB.',
                'status' => 'pending',
                'ticket_priority' => 'medium',
            ],
            [
                'ticket_code' => '123456713',
                'ticket_name' => 'Request for account information',
                'description' => 'Could you help me find where I can view and update my profile information?',
                'status' => 'closed',
                'ticket_priority' => 'low',
            ],
            [
                'ticket_code' => '123456714',
                'ticket_name' => 'Cannot access ticket details',
                'description' => 'When I click on one of my tickets, I receive an access denied message. I should be able to view my own ticket.',
                'status' => 'open',
                'ticket_priority' => 'high',
            ],
            [
                'ticket_code' => '123456715',
                'ticket_name' => 'Notification emails are missing',
                'description' => 'I no longer receive email notifications when someone replies to my tickets. Please check whether notifications are enabled.',
                'status' => 'pending',
                'ticket_priority' => 'medium',
            ],
            [
                'ticket_code' => '123456716',
                'ticket_name' => 'Thank you for the support',
                'description' => 'Thank you for your help. The issue has been resolved, and everything is working correctly now.',
                'status' => 'closed',
                'ticket_priority' => 'low',
            ],
        ];

        foreach ($tickets as $index => $data) {
            ticket::create([
                ...$data,
                'user_id' => $users[$index % $users->count()]->id,
            ]);
        }
    }
}
