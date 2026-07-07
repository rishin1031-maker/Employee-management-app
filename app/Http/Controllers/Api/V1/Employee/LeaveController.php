<?php

namespace App\Http\Controllers\Api\V1\Employee;

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
        $employee = Auth::guard('api_employee')->user();

        $filters = $request->validate([
            'type' => 'nullable | in:casual,sick,anual',
            'status' => 'nullable | in:pending,approved,rejected,cancelled',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);
        $leaves   = $this->leaveService->getEmployeeLeaves($employee->id, $filters);
        $balance  = $this->leaveService->getOrCreateBalance($employee->id);

        return $this->success([
            'balance' => ApiTransformer::leaveBalance($balance),
            'leaves'  => ApiTransformer::paginated($leaves, fn ($l) => ApiTransformer::leave($l)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'      => 'required|in:casual,sick,annual',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'reason'    => 'required|string|max:500',
        ]);

        try {
            $leave = $this->leaveService->applyLeave(
                Auth::guard('api_employee')->id(),
                $data
            );

            return $this->success(ApiTransformer::leave($leave), 'Leave request submitted.', 201);
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function show(LeaveRequest $leave): JsonResponse
    {
        if (!$this->leaveService->employeeOwnsLeave($leave->id, Auth::guard('api_employee')->id())) {
            return $this->error('Unauthorized.', 403);
        }

        return $this->success(ApiTransformer::leave($leave));
    }

    public function cancel(LeaveRequest $leave): JsonResponse
    {
        try {
            $this->leaveService->cancelLeave($leave->id, Auth::guard('api_employee')->id());

            return $this->success(null, 'Leave request cancelled.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function balance(): JsonResponse
    {
        $balance = $this->leaveService->getOrCreateBalance(Auth::guard('api_employee')->id());

        return $this->success(ApiTransformer::leaveBalance($balance));
    }
}
