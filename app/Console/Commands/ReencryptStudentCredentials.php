<?php

namespace App\Console\Commands;

use App\Models\StudentCredential;
use Illuminate\Console\Command;

class ReencryptStudentCredentials extends Command
{
    protected $signature = 'security:reencrypt-student-credentials {--dry-run : Count records without saving changes}';

    protected $description = 'Rewrites student credentials so plain_password is stored through the encrypted mutator';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;
        $updated = 0;

        StudentCredential::query()
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$total, &$updated, $dryRun): void {
                foreach ($rows as $credential) {
                    $total++;
                    $value = $credential->plain_password;
                    if (! is_string($value) || trim($value) === '') {
                        continue;
                    }

                    if ($dryRun) {
                        $updated++;
                        continue;
                    }

                    $credential->plain_password = $value;
                    $credential->save();
                    $updated++;
                }
            });

        $this->info(sprintf(
            '%s. Taranan: %d, yeniden yazilan: %d',
            $dryRun ? 'Dry-run tamamlandi' : 'Islem tamamlandi',
            $total,
            $updated
        ));

        return self::SUCCESS;
    }
}
