<?php

namespace Tests\Feature;

use App\Models\Master;
use App\Models\Payment;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralMyTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_gets_their_referrals_with_status_date_and_earning(): void
    {
        $referrer = $this->createMaster('Маша', 'MASHA10');
        $pendingMaster = $this->createMaster('Лена', 'LENA77');
        $rewardedMaster = $this->createMaster('Ира', 'IRA31');

        Referral::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $pendingMaster->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        Referral::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $rewardedMaster->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        Payment::create([
            'master_id' => $rewardedMaster->id,
            'amount' => 3000,
            'type' => Payment::TYPE_CARD,
        ]);

        $response = $this->withHeader('X-Master-Id', (string) $referrer->id)
            ->getJson('/api/referrals/my');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment([
                'name' => 'Ира',
                'is_rewarded' => true,
                'earned_amount' => 30000,
            ])
            ->assertJsonFragment([
                'name' => 'Лена',
                'is_rewarded' => false,
                'earned_amount' => 0,
            ]);

        foreach ($response->json('data') as $item) {
            $this->assertNotNull($item['attached_at']);
        }
        $this->assertStringContainsString('"name":"Ира"', $response->getContent());
        $this->assertStringNotContainsString('\\u0418', $response->getContent());
    }

    public function test_master_does_not_get_other_masters_referrals(): void
    {
        $referrer = $this->createMaster('Masha', 'MASHA10');
        $otherReferrer = $this->createMaster('Olya', 'OLYA22');
        $referred = $this->createMaster('Lena', 'LENA77');

        Referral::create([
            'referrer_master_id' => $otherReferrer->id,
            'referred_master_id' => $referred->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        $response = $this->withHeader('X-Master-Id', (string) $referrer->id)
            ->getJson('/api/referrals/my');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    private function createMaster(string $name, string $code): Master
    {
        return Master::create([
            'name' => $name,
            'referral_code' => $code,
        ]);
    }
}
