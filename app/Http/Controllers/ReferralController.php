<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachReferralRequest;
use App\Http\Resources\ReferralCollection;
use App\Services\Referral\ReferralService;
use Illuminate\Http\Request;
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

    public function my(Request $request, ReferralService $referralService): ReferralCollection
    {
        $master = $request->attributes->get('current_master');
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return new ReferralCollection(
            $master ? $referralService->listFor($master, $perPage) : collect(),
        );
    }
}
