<?php

namespace App\Exceptions\Referral;

class CurrentMasterNotFoundException extends ReferralException
{
    public function __construct()
    {
        parent::__construct('Unable to attach referral');
    }
}
