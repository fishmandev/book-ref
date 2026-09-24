<?php

namespace App\Exceptions\Referral;

class InvalidReferralCodeException extends ReferralException
{
    public function __construct(string $code)
    {
        parent::__construct(
            'Unable to attach referral',
            ['code' => $code],
        );
    }
}
