<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email? : Destination email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('email') ?? config('mail.from.address');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: [{$recipient}]");

            return Command::FAILURE;
        }

        $this->info('Testing Mail Configuration...');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mailer', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
                ['Destination', $recipient],
            ]
        );

        try {
            Mail::raw('Halo! Ini adalah email uji coba dari aplikasi Aurea untuk memastikan koneksi SMTP server berfungsi dengan baik saat online maupun lokal.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email - Aurea SMTP Verification');
            });

            $this->info("✅ Berhasil! Email uji coba berhasil dikirim ke: {$recipient}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Gagal mengirim email:');
            $this->line($e->getMessage());

            return Command::FAILURE;
        }
    }
}
