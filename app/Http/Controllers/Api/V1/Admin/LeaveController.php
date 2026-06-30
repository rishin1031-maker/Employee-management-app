<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends ApiController
{
    public function __construct(private LeaveService $leaveService) {}

    public function index(Request $request): JsonResponse
    {
        $leaves = $this->leaveService->getPaginatedForAdmin($request->all());

        return $this->success(
            ApiTransformer::paginated($leaves, fn ($l) => ApiTransformer::leave($l))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:casual,sick,annual',
            'from_date'   => 'required|date',
            'to_date'     => 'required|date|after_or_equal:from_date',
            'reason'      => 'required|string|max:500',
            'status'      => 'required|in:pending,approved,rejected',
        ]);

        $leave = $this->leaveService->createLeaveByAdmin($data, Auth::guard('api_admin')->id());

        return $this->success(ApiTransformer::leave($leave->load('employee')), 'Leave created.', 201);
    }

    public function show(LeaveRequest $leave): JsonResponse
    {
        $leave = $this->leaveService->getLeaveForAdminShow($leave->id);

        return $this->success(
            ApiTransformer::leave($leave)
        );
    }

    public function approve(Request $request, LeaveRequest $leave): JsonResponse
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        try {
            $updated = $this->leaveService->approveLeave(
                $leave->id,
                Auth::guard('api_admin')->id(),
                $request->admin_note
            );
        } catch (\Exception $e) {
            return $this->fromException($e);
        }

        return $this->success(ApiTransformer::leave($updated->load('employee')), 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leave): JsonResponse
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        try {
            $updated = $this->leaveService->rejectLeave(
                $leave->id,
                Auth::guard('api_admin')->id(),
                $request->admin_note
            );
        } catch (\Exception $e) {
            return $this->fromException($e);
        }

        return $this->success(ApiTransformer::leave($updated->load('employee')), 'Leave rejected.');
    }
}
