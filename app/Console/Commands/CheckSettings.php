<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckSettings extends Command
{
    protected $signature = 'app:check-settings {--clear-cache : Clear settings cache}';
    protected $description = 'Check and display app settings';

    public function handle()
    {
        if ($this->option('clear-cache')) {
            Cache::forget('app_settings_shared');
            $this->info('Settings cache cleared!');
        }

        $settings = Setting::whereIn('key', ['currency', 'currency_symbol', 'currency_position'])
            ->pluck('value', 'key')
            ->toArray();

        $this->info('Database Settings:');
        foreach ($settings as $key => $value) {
            $this->line("  $key: $value");
        }

        $cachedSettings = Cache::get('app_settings_shared');
        $this->info("\nCached Settings:");
        if ($cachedSettings) {
            $this->line("  currency: " . ($cachedSettings['currency'] ?? 'not set'));
            $this->line("  currency_symbol: " . ($cachedSettings['currency_symbol'] ?? 'not set'));
            $this->line("  currency_position: " . ($cachedSettings['currency_position'] ?? 'not set'));
        } else {
            $this->warn('  No cached settings found');
        }

        return 0;
    }
}
