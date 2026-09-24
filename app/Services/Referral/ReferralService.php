<?php

namespace App\Services\Referral;

use App\Exceptions\Referral\CurrentMasterNotFoundException;
use App\Exceptions\Referral\InvalidReferralCodeException;
use App\Exceptions\Referral\SelfReferralException;
use App\Models\Master;
use App\Models\Referral;

class ReferralService
{
    public function registerReferral(?Master $referred, string $code): Referral
    {
        throw_if(!$referred, new CurrentMasterNotFoundException());

        $referrer = Master::where('referral_code', $code)->first();

        throw_if(!$referrer, new InvalidReferralCodeException($code));

        throw_if($referrer->id === $referred->id, new SelfReferralException($referred->id));

        return Referral::firstOrCreate(
            [
                'referred_master_id' => $referred->id,
            ],
            [
                'referrer_master_id' => $referrer->id,
                'program' => Referral::PROGRAM_MASTER_INVITE,
                'status' => Referral::STATUS_PENDING,
            ]
        );
    }

    public function rewardAmount(int $paymentAmount): int
    {
        $percent = (int) config('referral.percent');

        return (int) round($paymentAmount * $percent);
    }
}
