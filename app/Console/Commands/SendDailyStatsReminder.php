<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyStatsReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminder email to check new stats';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipients = [
            'jordi@gloobus.net',
            'hello@codepassion.io',
        ];

        foreach ($recipients as $recipient) {
            Mail::send('emails.daily-stats-reminder', [], function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Recordatori: Revisa les noves estadístiques');
            });
        }

        $this->info('Recordatori enviat a ' . count($recipients) . ' destinataris.');

        return self::SUCCESS;
    }
}

