<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:update-currency-rates')]
#[Description('Fetch and update exchange rates for all supported currencies')]
class UpdateCurrencyRates extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating currency rates...');
        app(CurrencyService::class)->updateRates();
        $this->info('Currency rates updated successfully.');
    }
}
