<?php

namespace App\Services\Referral;

use App\Exceptions\Referral\CurrentMasterNotFoundException;
use App\Exceptions\Referral\InvalidReferralCodeException;
use App\Exceptions\Referral\SelfReferralException;
use App\Models\Master;
use App\Models\Referral;
use App\Models\ReferralEarning;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function listFor(Master $master, int $perPage = 15): LengthAwarePaginator
    {
        return $master->referrals()
            ->with('referredMaster:id,name')
            ->withSum('earnings', 'amount')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @return array{total_earned: int, pending: int, paid: int, counted_referrals: int}
     */
    public function earningsFor(?Master $master): array
    {
        //FIXME - remove it!
        if (!$master) {
            return [
                'total_earned' => 0,
                'pending' => 0,
                'paid' => 0,
                'counted_referrals' => 0,
            ];
        }

        $earnings = $master->referralEarnings();

        return [
            'total_earned' => (int) (clone $earnings)->sum('amount'),
            'pending' => (int) (clone $earnings)
                ->where('status', ReferralEarning::STATUS_PENDING)
                ->sum('amount'),
            'paid' => (int) (clone $earnings)
                ->where('status', ReferralEarning::STATUS_PAID)
                ->sum('amount'),
            'counted_referrals' => (int) $master->referrals()
                ->where('status', Referral::STATUS_REWARDED)
                ->count(),
        ];
    }
}
