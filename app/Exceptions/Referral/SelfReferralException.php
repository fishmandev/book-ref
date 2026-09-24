<?php

namespace App\Exceptions\Referral;

class SelfReferralException extends ReferralException
{
    public function __construct(int $masterId)
    {
        parent::__construct(
            'Unable to attach referral',
            ['master_id' => $masterId],
        );
    }
}
