<?php

namespace Tests\Feature;

use App\Livewire\KwhConfirmationModal;
use App\Models\ElectricityUsageCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfirmationModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function check(string $at, bool $estimated = false): void
    {
        ElectricityUsageCheck::forceCreate([
            'meter_number' => 'M1',
            'kwh_remaining' => 80,
            'is_estimated' => $estimated,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    public function test_modal_stays_hidden_when_the_reading_is_fresh(): void
    {
        // Dulu modal muncul di SETIAP kunjungan, bahkan ketika meteran baru
        // dicek kemarin -- tidak ada gunanya bertanya.
        $this->check(now()->subDay()->toDateTimeString());

        Livewire::test(KwhConfirmationModal::class)
            ->assertSet('showModal', false);
    }

    public function test_modal_appears_when_the_reading_is_stale(): void
    {
        $this->check(now()->subDays(5)->toDateTimeString());

        Livewire::test(KwhConfirmationModal::class)
            ->assertSet('showModal', true);
    }

    public function test_modal_appears_when_the_latest_value_is_only_an_estimate(): void
    {
        $this->check(now()->subHours(2)->toDateTimeString(), estimated: true);

        Livewire::test(KwhConfirmationModal::class)
            ->assertSet('showModal', true);
    }

    public function test_modal_stays_hidden_when_there_is_no_data_at_all(): void
    {
        Livewire::test(KwhConfirmationModal::class)
            ->assertSet('showModal', false);
    }

    public function test_confirming_the_prediction_marks_it_as_an_estimate(): void
    {
        $this->check(now()->subDays(5)->toDateTimeString());

        Livewire::test(KwhConfirmationModal::class)
            ->call('confirmYes');

        $this->assertTrue(ElectricityUsageCheck::latest('id')->first()->is_estimated);
    }

    public function test_typing_the_real_reading_is_not_marked_as_an_estimate(): void
    {
        $this->check(now()->subDays(5)->toDateTimeString());

        Livewire::test(KwhConfirmationModal::class)
            ->call('confirmNo')
            ->set('newKwhValue', 33.5)
            ->call('saveNewValue');

        $latest = ElectricityUsageCheck::latest('id')->first();

        $this->assertSame(33.5, $latest->kwh_remaining);
        $this->assertFalse($latest->is_estimated);
    }
}
