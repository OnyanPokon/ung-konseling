<?php

namespace Tests\Feature;

use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * A number already sitting in the database was typed by a person, in whichever
 * of the accepted formats they happened to use. The backfill rewrites those in
 * place so nobody has to re-enter anything, and so what is stored can be
 * compared against what was actually delivered.
 *
 * The stored value is the backfill's deliverable, so these tests read it
 * directly rather than inferring it from a delivery: the gateway client
 * normalizes on the way out regardless, so an outbound request cannot tell a
 * working backfill apart from one that did nothing.
 */
class PhoneNumberBackfillTest extends TestCase
{
    use RefreshDatabase;

    private const COMMAND = 'phones:normalize';

    public function test_it_normalizes_existing_konseli_and_konselor_numbers_in_place(): void
    {
        $konseli = $this->aKonseliWithPhone('0812 3456-7890');
        $counselor = $this->aCounselorWithPhone('+62812345678');

        $this->runTheBackfill();

        $this->assertSame('6281234567890', $konseli->fresh()->phone);
        $this->assertSame('62812345678', $counselor->fresh()->phone);
    }

    public function test_it_normalizes_every_accepted_format_to_the_same_stored_number(): void
    {
        $konselis = collect(['081234567890', '6281234567890', '+6281234567890', '(0812) 3456-7890'])
            ->map(fn (string $phone) => $this->aKonseliWithPhone($phone));

        $this->runTheBackfill();

        foreach ($konselis as $konseli) {
            $this->assertSame('6281234567890', $konseli->fresh()->phone);
        }
    }

    public function test_running_it_twice_changes_nothing_further(): void
    {
        $konseli = $this->aKonseliWithPhone('0812-3456-7890');
        $this->aCounselorWithPhone('0812 3456 9999');

        $this->runTheBackfill();
        $afterTheFirstRun = $this->everyStoredPhoneNumber();

        $secondRun = $this->runTheBackfill();

        $this->assertSame($afterTheFirstRun, $this->everyStoredPhoneNumber());
        $this->assertStringContainsString(
            'Total: 2 checked, 0 normalized, 2 already dialable, 0 skipped',
            $secondRun,
        );
    }

    public function test_it_leaves_a_number_it_cannot_make_sense_of_untouched(): void
    {
        $konseli = $this->aKonseliWithPhone('bukan nomor');

        $this->runTheBackfill();

        $this->assertSame('bukan nomor', $konseli->fresh()->phone);
    }

    public function test_it_reports_how_many_numbers_it_changed_and_how_many_it_skipped(): void
    {
        $this->aKonseliWithPhone('081234567890');
        $this->aKonseliWithPhone('bukan nomor');
        $this->aCounselorWithPhone('+62812345678');

        $output = $this->runTheBackfill();

        $this->assertStringContainsString('Konselis: 2 checked, 1 normalized, 0 already dialable, 1 skipped', $output);
        $this->assertStringContainsString('Konselors: 1 checked, 1 normalized, 0 already dialable, 0 skipped', $output);
    }

    public function test_it_ignores_a_record_that_has_no_phone_number_at_all(): void
    {
        $this->aCounselorWithPhone(null);
        $this->aKonseliWithPhone('081234567890');

        $output = $this->runTheBackfill();

        $this->assertStringContainsString('Konselis: 1 checked, 1 normalized, 0 already dialable, 0 skipped', $output);
        $this->assertStringContainsString('Konselors: 1 checked, 0 normalized, 0 already dialable, 1 skipped', $output);
    }

    private function runTheBackfill(): string
    {
        Artisan::call(self::COMMAND);

        return Artisan::output();
    }

    /**
     * @return array<string, string|null>
     */
    private function everyStoredPhoneNumber(): array
    {
        return [
            'konselis' => Konselis::pluck('phone')->implode(','),
            'konselors' => Konselors::pluck('phone')->implode(','),
        ];
    }

    private function aKonseliWithPhone(?string $phone): Konselis
    {
        return Konselis::create([
            'user_id' => User::factory()->create()->id,
            'nim' => '22101100000'.Konselis::count(),
            'phone' => $phone,
            'domisili' => 'Gorontalo',
            'jurusan' => 'Teknik Informatika',
            'umur' => 21,
            'jenis_kelamin' => 'P',
        ]);
    }

    private function aCounselorWithPhone(?string $phone): Konselors
    {
        return Konselors::create([
            'user_id' => User::factory()->create()->id,
            'nip' => '19800101200501'.str_pad((string) Konselors::count(), 3, '0', STR_PAD_LEFT),
            'phone' => $phone,
            'jenis_kelamin' => 'P',
        ]);
    }
}
