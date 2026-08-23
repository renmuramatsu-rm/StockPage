<?php

namespace App\Providers;

use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;
use App\Services\FinancialData\JQuantsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FinancialStatementProviderInterface::class, function () {
            $driver = config('financials.default');

            return match ($driver) {
                'jquants' => new JQuantsProvider(
                    baseUrl: config('services.jquants.base_url'),
                    apiKey: config('services.jquants.api_key'),
                ),
                default => throw new \InvalidArgumentException("Unknown financials provider [{$driver}]"),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
