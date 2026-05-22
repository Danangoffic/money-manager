<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring';

    protected $description = 'Process due recurring transactions and create actual transactions';

    public function handle(RecurringTransactionService $service): int
    {
        $count = $service->processDue();

        $this->info("Processed {$count} recurring transaction(s).");

        return self::SUCCESS;
    }
}
