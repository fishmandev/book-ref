<?php

namespace App\Http\Resources;

use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Referral $referral */
        $referral = $this->resource;

        return [
            'name' => $referral->referredMaster->name,
            'attached_at' => $referral->created_at,
            'is_rewarded' => $referral->status === Referral::STATUS_REWARDED,
            'earned_amount' => (int) ($referral->earnings_sum_amount ?? 0),
        ];
    }
}
