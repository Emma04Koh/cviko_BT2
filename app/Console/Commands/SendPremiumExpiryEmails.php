<?php

namespace App\Console\Commands;

use App\Mail\PremiumExpiringMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPremiumExpiryEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-premium-expiry-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails to users whose premium expires tomorrow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->whereNotNull('premium_until')
            ->whereDate('premium_until', now()->addDay()->toDateString())
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)
                ->queue(new PremiumExpiringMail($user));
        }

        $this->info("Queued emails: {$users->count()}");

        return self::SUCCESS;
    }
}
