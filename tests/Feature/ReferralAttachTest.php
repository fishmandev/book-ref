<?php

namespace Tests\Feature;

use App\Models\Master;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralAttachTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_attach_to_referral_code(): void
    {
        $referrer = $this->createMaster('Masha', 'MASHA10');
        $referred = $this->createMaster('Lena', 'LENA77');

        $response = $this->withHeader('X-Master-Id', (string) $referred->id)
            ->postJson('/api/referrals/attach', ['code' => $referrer->referral_code]);

        $response->assertNoContent(201);
        $this->assertDatabaseHas('referrals', [
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $referred->id,
            'program' => Referral::PROGRAM_MASTER_INVITE,
            'status' => Referral::STATUS_PENDING,
        ]);
    }

    public function test_repeated_attach_does_not_create_a_duplicate(): void
    {
        $referrer = $this->createMaster('Masha', 'MASHA10');
        $referred = $this->createMaster('Lena', 'LENA77');

        $this->withHeader('X-Master-Id', (string) $referred->id)
            ->postJson('/api/referrals/attach', ['code' => $referrer->referral_code])
            ->assertNoContent(201);

        $this->withHeader('X-Master-Id', (string) $referred->id)
            ->postJson('/api/referrals/attach', ['code' => $referrer->referral_code])
            ->assertNoContent(201);

        $this->assertSame(1, Referral::query()
            ->where('referred_master_id', $referred->id)
            ->count());
    }

    public function test_master_cannot_attach_to_own_referral_code(): void
    {
        $master = $this->createMaster('Masha', 'MASHA10');

        $response = $this->withHeader('X-Master-Id', (string) $master->id)
            ->postJson('/api/referrals/attach', ['code' => $master->referral_code]);

        $response->assertStatus(422)
            ->assertJson(['details' => 'Unable to attach referral']);
        $this->assertDatabaseCount('referrals', 0);
    }

    public function test_unknown_referral_code_returns_an_error(): void
    {
        $master = $this->createMaster('Lena', 'LENA77');

        $response = $this->withHeader('X-Master-Id', (string) $master->id)
            ->postJson('/api/referrals/attach', ['code' => 'UNKNOWN']);

        $response->assertStatus(422)
            ->assertJson(['details' => 'Unable to attach referral']);
    }

    public function test_request_without_current_master_returns_an_error(): void
    {
        $response = $this->postJson('/api/referrals/attach', ['code' => 'MASHA10']);

        $response->assertStatus(422)
            ->assertJson(['details' => 'Unable to attach referral']);
    }

    private function createMaster(string $name, string $code): Master
    {
        return Master::create([
            'name' => $name,
            'referral_code' => $code,
        ]);
    }
}
