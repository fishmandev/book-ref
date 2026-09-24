<?php

namespace Tests\Feature;

use App\Models\Master;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralEarning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralEarningsTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_gets_earnings_summary(): void
    {
        $referrer = $this->createMaster('Маша', 'MASHA10');
        $pendingReferral = $this->createMaster('Лена', 'LENA77');
        $paidReferral = $this->createMaster('Ира', 'IRA31');
        $pendingPayment = Payment::create([
            'master_id' => $pendingReferral->id,
            'amount' => 1000,
            'type' => Payment::TYPE_CARD,
        ]);
        $paidPayment = Payment::create([
            'master_id' => $paidReferral->id,
            'amount' => 2000,
            'type' => Payment::TYPE_CARD,
        ]);

        $pending = Referral::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $pendingReferral->id,
            'status' => Referral::STATUS_REWARDED,
        ]);
        $paid = Referral::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $paidReferral->id,
            'status' => Referral::STATUS_REWARDED,
        ]);
        ReferralEarning::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $pendingReferral->id,
            'referral_id' => $pending->id,
            'payment_id' => $pendingPayment->id,
            'payment_amount' => 1000,
            'amount' => 10000,
            'percent' => 10,
            'status' => ReferralEarning::STATUS_PENDING,
        ]);
        ReferralEarning::create([
            'referrer_master_id' => $referrer->id,
            'referred_master_id' => $paidReferral->id,
            'referral_id' => $paid->id,
            'payment_id' => $paidPayment->id,
            'payment_amount' => 2000,
            'amount' => 20000,
            'percent' => 10,
            'status' => ReferralEarning::STATUS_PAID,
        ]);

        $this->withHeader('X-Master-Id', (string) $referrer->id)
            ->getJson('/api/referrals/earnings')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'total_earned' => 30000,
                    'pending' => 10000,
                    'paid' => 20000,
                    'counted_referrals' => 2,
                ],
            ]);
    }

    public function test_missing_master_gets_empty_earnings_summary(): void
    {
        $this->getJson('/api/referrals/earnings')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'total_earned' => 0,
                    'pending' => 0,
                    'paid' => 0,
                    'counted_referrals' => 0,
                ],
            ]);
    }

    private function createMaster(string $name, string $code): Master
    {
        return Master::create([
            'name' => $name,
            'referral_code' => $code,
        ]);
    }
}
