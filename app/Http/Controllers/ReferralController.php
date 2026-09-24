<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachReferralRequest;
use App\Services\Referral\ReferralService;
use Symfony\Component\HttpFoundation\Response;

class ReferralController extends Controller
{
    public function attach(AttachReferralRequest $request, ReferralService $referralService): Response
    {
        $master = $request->attributes->get('current_master');
        $code = $request->validated('code');

        $referralService->registerReferral($master, $code);

        return response()->noContent(201);
    }
}
