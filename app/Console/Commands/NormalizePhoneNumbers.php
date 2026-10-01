<?php

namespace App\Console\Commands;

use App\Http\Services\FonnteService;
use App\Models\Konselis;
use App\Models\Konselors;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Rewrites phone numbers that are already in the database into the form the
 * gateway can dial.
 *
 * A number reaches the database through registration, profile edit and admin
 * edit, in whichever of the accepted formats the person happened to type, so
 * there is no single place to trust. The gateway client normalizes on the way
 * out regardless; this exists so the stored value and the delivered value can
 * be compared against each other, and so a historical record is deliverable
 * without anyone re-entering it.
 */
class NormalizePhoneNumbers extends Command
{
    protected $signature = 'phones:normalize';

    protected $description = 'Rewrite stored konseli and counselor phone numbers into dialable form';

    public function handle(FonnteService $fonnte): int
    {
        $totals = ['checked' => 0, 'normalized' => 0, 'alreadyDialable' => 0, 'skipped' => 0];

        foreach ([Konselis::class, Konselors::class] as $model) {
            $counts = $this->backfill($model, $fonnte);

            $this->line($this->report(class_basename($model), $counts));

            foreach ($totals as $key => $value) {
                $totals[$key] = $value + $counts[$key];
            }
        }

        $this->line($this->report('Total', $totals));

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Model>  $model
     * @return array{checked: int, normalized: int, alreadyDialable: int, skipped: int}
     */
    private function backfill(string $model, FonnteService $fonnte): array
    {
        $counts = ['checked' => 0, 'normalized' => 0, 'alreadyDialable' => 0, 'skipped' => 0];

        $model::query()
            ->select([(new $model)->getKeyName(), 'phone'])
            ->chunkById(200, function ($records) use ($model, $fonnte, &$counts) {
                foreach ($records as $record) {
                    $counts['checked']++;

                    $dialable = $fonnte->normalizePhoneNumber($record->phone);

                    // A number with nothing dialable in it is left exactly as it
                    // is: it is a person's record, not a value to be guessed at.
                    if ($dialable === null) {
                        $counts['skipped']++;

                        continue;
                    }

                    if ($dialable === $record->phone) {
                        $counts['alreadyDialable']++;

                        continue;
                    }

                    $model::query()->whereKey($record->getKey())->updateQuietly(['phone' => $dialable]);

                    $counts['normalized']++;
                }
            });

        return $counts;
    }

    /**
     * @param  array{checked: int, normalized: int, alreadyDialable: int, skipped: int}  $counts
     */
    private function report(string $label, array $counts): string
    {
        return sprintf(
            '%s: %d checked, %d normalized, %d already dialable, %d skipped',
            $label,
            $counts['checked'],
            $counts['normalized'],
            $counts['alreadyDialable'],
            $counts['skipped'],
        );
    }
}
