<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PrepareApplication extends Command
{
    protected $signature = 'app:prepare';

    protected $description = 'Run the shared database preparation steps under a PostgreSQL lock.';

    public function handle(): int
    {
        $lockKey = 917431002;

        DB::select('select pg_advisory_lock(?)', [$lockKey]);

        try {
            $migrationExitCode = Artisan::call('migrate', ['--force' => true]);
            $this->output->write(Artisan::output());

            if ($migrationExitCode !== self::SUCCESS) {
                return $migrationExitCode;
            }

            $seedExitCode = Artisan::call('db:seed', ['--force' => true]);
            $this->output->write(Artisan::output());

            return $seedExitCode;
        } finally {
            DB::select('select pg_advisory_unlock(?)', [$lockKey]);
        }
    }
}