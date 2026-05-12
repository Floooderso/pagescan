<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * This command is kept as a stub but is no longer functional.
 * Trial/subscription logic has been replaced by a waitlist model.
 * Safe to remove in a future cleanup pass.
 */
class SendTrialEndingEmails extends Command
{
    protected $signature   = 'trials:notify';
    protected $description = '[Deprecated] Trial-Ablauf-E-Mails — kein Effekt mehr (Waitlist-Modell aktiv)';

    public function handle(): void
    {
        $this->info('Trials sind deaktiviert. Dieses Command hat keinen Effekt mehr.');
    }
}
