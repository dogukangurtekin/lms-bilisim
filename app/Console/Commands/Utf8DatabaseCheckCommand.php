<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Utf8DatabaseCheckCommand extends Command
{
    protected $signature = 'app:utf8-db-check';

    protected $description = 'Check database connection, server charset, and collation for UTF-8 safety.';

    public function handle(): int
    {
        try {
            $row = DB::selectOne("
                SELECT
                    @@character_set_client as character_set_client,
                    @@character_set_connection as character_set_connection,
                    @@character_set_database as character_set_database,
                    @@character_set_results as character_set_results,
                    @@character_set_server as character_set_server,
                    @@collation_server as collation_server
            ");
        } catch (\Throwable $e) {
            $this->error('Database check failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $data = (array) $row;

        foreach ($data as $key => $value) {
            $this->line(str_pad($key, 28) . ': ' . $value);
        }

        $ok = true;
        foreach (['character_set_connection', 'character_set_database', 'character_set_results', 'character_set_server'] as $key) {
            if (($data[$key] ?? '') !== 'utf8mb4') {
                $ok = false;
            }
        }

        if (($data['collation_server'] ?? '') === '') {
            $ok = false;
        }

        $ok ? $this->info('UTF-8 database check passed.') : $this->warn('UTF-8 database check needs attention.');

        return self::SUCCESS;
    }
}
