<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TicketSeeder extends Seeder
{
    /**
     * Seed ten root conversations without replacing existing tickets.
     * Root titles and each reply's parent/description identify records on repeated runs.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->orderBy('id')->get();
        $admin = User::where('role', 'admin')->orderBy('id')->first();

        if ($users->isEmpty() || ! $admin) {
            throw new RuntimeException('TicketSeeder requires at least one customer and one administrator.');
        }

        $tickets = [
            [
                'title' => 'Unable to sign in',
                'description' => 'I cannot sign in to my account. After entering my email and password, the page keeps loading and eventually displays an error.',
                'status' => 'open',
                'priority' => 'high',
            ],
            [
                'title' => 'Password reset email not received',
                'description' => 'I requested a password reset email several times, but nothing has arrived in my inbox or spam folder.',
                'status' => 'pending',
                'priority' => 'medium',
            ],
            [
                'title' => 'Update my account email',
                'description' => 'I would like to change the email address associated with my account. Please let me know what information you need.',
                'status' => 'open',
                'priority' => 'low',
            ],
            [
                'title' => 'Dashboard is loading slowly',
                'description' => 'The dashboard takes a long time to load, especially when I open the ticket list. Could you please investigate the performance?',
                'status' => 'pending',
                'priority' => 'medium',
            ],
            [
                'title' => 'Incorrect ticket status',
                'description' => 'My ticket was marked as closed, but the issue has not been resolved yet. Please review its status.',
                'status' => 'open',
                'priority' => 'high',
            ],
            [
                'title' => 'Attachment upload failed',
                'description' => 'I tried to attach a screenshot to my ticket, but the upload failed. The file is a PNG image and is smaller than 5 MB.',
                'status' => 'pending',
                'priority' => 'medium',
            ],
            [
                'title' => 'Request for account information',
                'description' => 'Could you help me find where I can view and update my profile information?',
                'status' => 'closed',
                'priority' => 'low',
            ],
            [
                'title' => 'Cannot access ticket details',
                'description' => 'When I click on one of my tickets, I receive an access denied message. I should be able to view my own ticket.',
                'status' => 'open',
                'priority' => 'high',
            ],
            [
                'title' => 'Notification emails are missing',
                'description' => 'I no longer receive email notifications when someone replies to my tickets. Please check whether notifications are enabled.',
                'status' => 'pending',
                'priority' => 'medium',
            ],
            [
                'title' => 'Thank you for the support',
                'description' => 'Thank you for your help. The issue has been resolved, and everything is working correctly now.',
                'status' => 'closed',
                'priority' => 'low',
            ],
        ];

        DB::transaction(function () use ($tickets, $users, $admin): void {
            foreach ($tickets as $index => $data) {
                if (Ticket::whereNull('parent_id')->where('title', $data['title'])->count() > 1) {
                    throw new RuntimeException('Multiple roots share a sample title; refusing an ambiguous conversation.');
                }

                $createdAt = now()->subDays($index + 1);
                $ticket = Ticket::firstOrCreate([
                    'title' => $data['title'],
                    'parent_id' => null,
                ], [
                    ...$data,
                    'user_id' => $users[$index % $users->count()]->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($ticket->user?->role !== 'user') {
                    throw new RuntimeException('A matching root ticket does not belong to a customer.');
                }

                $conversation = [
                    $data['description'],
                    'Thanks for reporting this. I have opened an investigation. When did you first notice the issue, and which browser are you using?',
                    'I first noticed it this morning. I am using the latest Chrome on Windows, and I can reproduce it after signing in again.',
                    'Thank you. I have found the affected request in our logs. Please avoid sending passwords or verification codes while we investigate.',
                    'Understood. I have not shared any credentials. Is there anything else you need from me?',
                    'I have enough information now. I am checking the account configuration and testing the same steps in our support environment.',
                    'Thanks for the update. Please let me know when I should try again.',
                    'The configuration has been reviewed and the required changes are in place. Please refresh the page and repeat the steps you described.',
                    'I have tried again and the latest attempt worked. I will keep an eye on it and let you know if anything changes.',
                    $ticket->status === 'closed'
                        ? 'Thank you for confirming. I have documented the resolution and closed this request. Contact us again if you need more help.'
                        : 'Thank you for checking. I have recorded the successful attempt and will keep this request under review until our final checks are complete.',
                ];

                foreach ($conversation as $replyIndex => $body) {
                    $replyTime = ($ticket->created_at ?? $createdAt)->copy()->addMinutes(($replyIndex + 1) * 2);
                    $authorId = $replyIndex % 2 === 0 ? $ticket->user_id : $admin->id;
                    $reply = $ticket->replies()->firstOrCreate([
                        'description' => $body,
                    ], [
                        'user_id' => $authorId,
                        'title' => null,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'created_at' => $replyTime,
                        'updated_at' => $replyTime,
                    ]);

                    if ($reply->parent_id !== $ticket->id || $reply->user_id !== $authorId || $reply->title !== null || $reply->description !== $body) {
                        throw new RuntimeException('A matching reply has a different author or a non-null title.');
                    }
                }

                if ($ticket->replies()->count() !== 10) {
                    throw new RuntimeException('A seeded root has additional replies; no existing records were removed.');
                }
            }
        });
    }
}
