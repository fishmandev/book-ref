<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReferralCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return ReferralResource::collection($this->collection)->resolve($request);
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
