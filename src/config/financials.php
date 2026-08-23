<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Financial Statement Provider
    |--------------------------------------------------------------------------
    |
    | Which provider in the "providers" array below to use when fetching
    | financial statement data (revenue, profit, EPS, ROE, etc.) for stocks.
    |
    */

    'default' => env('FINANCIALS_PROVIDER', 'jquants'),

    /*
    |--------------------------------------------------------------------------
    | Financial Statement Providers
    |--------------------------------------------------------------------------
    |
    | Each provider must implement
    | App\Services\FinancialData\Contracts\FinancialStatementProviderInterface.
    | Swapping the default provider (e.g. to an EDINET implementation) does
    | not require any schema or ingestion-command changes.
    |
    */

    'providers' => [
        'jquants' => \App\Services\FinancialData\JQuantsProvider::class,
    ],

];
