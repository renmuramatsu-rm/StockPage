<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Services\FinancialAnalysis\StockScoreRecorder;
use App\Services\FinancialData\FinancialStatementSyncer;
use Illuminate\Console\Command;

class SyncFinancials extends Command
{
    protected $signature = 'financials:sync {code? : Specific stock code (e.g. 7203 or 167A)} {--sleep=12000 : Milliseconds to wait between API calls (free plan allows 5 calls/min)} {--no-score : Skip recomputing the buy-judgment score (halves the API calls, so ~2x faster)} {--force : Re-sync every matching stock, even ones synced within the last 24 hours} {--limit= : Process at most this many stocks in this run (for short, schedulable batches)}';

    protected $description = 'Fetch and store financial statement history (and buy-judgment score) for tracked stocks';

    public function handle(FinancialStatementSyncer $syncer, StockScoreRecorder $scoreRecorder): int
    {
        $code = $this->argument('code');
        $stocks = $code ? Stock::where('code', $code)->get() : Stock::all();

        if ($stocks->isEmpty()) {
            $this->error('No matching stocks found.');

            return self::FAILURE;
        }

        $force = $code || $this->option('force');

        if (! $force) {
            $stocks = $stocks->filter(fn (Stock $stock) => $syncer->needsSync($stock))->values();

            if ($stocks->isEmpty()) {
                $this->info('All matching stocks were already synced within the last 24 hours. Use --force to re-sync anyway.');

                return self::SUCCESS;
            }
        }

        if ($limit = $this->option('limit')) {
            $stocks = $stocks->take((int) $limit)->values();
        }

        $withScore = ! $this->option('no-score');
        $sleepMs = (int) $this->option('sleep');
        $processed = 0;
        $upserted = 0;
        $scored = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($stocks->count());
        $bar->start();

        foreach ($stocks as $stock) {
            try {
                $upserted += $syncer->sync($stock);

                if ($withScore) {
                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }
                    $scoreRecorder->record($stock);
                    $scored++;
                }

                $processed++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("[{$stock->code}] failed to fetch: {$e->getMessage()}");
                $failed++;
            } finally {
                $bar->advance();

                // Always pace API calls, even after a failure — otherwise a
                // run of failing stocks fires requests back-to-back and
                // trips the free plan's 5 calls/min limit even harder.
                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Stocks processed', 'Statements upserted', 'Scores updated', 'Failures'],
            [[$processed, $upserted, $scored, $failed]]
        );

        return self::SUCCESS;
    }
}
