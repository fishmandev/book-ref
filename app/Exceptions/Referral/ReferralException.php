<?php

namespace App\Exceptions\Referral;

use RuntimeException;

abstract class ReferralException extends RuntimeException
{
    public function __construct(
        public readonly string $details,
        public readonly array $context = [],
    ) {
        parent::__construct($details);
    }
}
