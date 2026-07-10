<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Services\ContinuousSessionPolicy;
use Illuminate\Http\JsonResponse;

class ContinuousSessionPolicyController extends ApiController
{
    public function __construct(private ContinuousSessionPolicy $policy) {}

    public function show(): JsonResponse
    {
        return $this->success([
            'continuous_session' => $this->policy->toArray(),
        ]);
    }
}
