<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Services\ContinuousSessionPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingsController extends ApiController
{
    public function __construct(private ContinuousSessionPolicy $policy) {}

    public function show(): JsonResponse
    {
        return $this->success([
            'continuous_session' => $this->policy->toArray(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'continuous_session' => ['required', 'array'],
            'continuous_session.enabled' => ['sometimes', 'boolean'],
            'continuous_session.limit_minutes' => ['sometimes', 'integer', 'min:30', 'max:1440'],
            'continuous_session.reminder_before_minutes' => ['sometimes', 'integer', 'min:0', 'max:240'],
            'continuous_session.grace_minutes' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'continuous_session.min_break_minutes' => ['sometimes', 'integer', 'min:1', 'max:60'],
        ]);

        $updated = $this->policy->update($validated['continuous_session']);

        return $this->success(
            ['continuous_session' => $updated],
            'System settings updated.',
        );
    }
}
