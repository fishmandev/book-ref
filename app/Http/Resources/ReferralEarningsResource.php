<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralEarningsResource extends JsonResource
{
    /**
     * @param array{total_earned: int, pending: int, paid: int, counted_referrals: int} $resource
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
