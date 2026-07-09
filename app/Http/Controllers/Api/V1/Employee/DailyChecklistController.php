<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\DailyChecklistItem;
use App\Services\DailyChecklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyChecklistController extends ApiController
{
    public function __construct(private DailyChecklistService $checklistService) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $data['date'] ?? now()->toDateString();
        $employeeId = Auth::guard('api_employee')->id();
        $summary = $this->checklistService->summaryForDate($employeeId, $date);

        return $this->success([
            'date'      => $summary['date'],
            'total'     => $summary['total'],
            'completed' => $summary['completed'],
            'pending'   => $summary['pending'],
            'items'     => $summary['items']->map(fn ($item) => ApiTransformer::checklistItem($item))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'task_date' => 'nullable|date',
        ]);

        try {
            $item = $this->checklistService->create(
                Auth::guard('api_employee')->id(),
                $data
            );

            return $this->success(ApiTransformer::checklistItem($item), 'Task added.', 201);
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function update(Request $request, DailyChecklistItem $checklist): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'is_completed' => 'sometimes|boolean',
        ]);

        try {
            $item = $this->checklistService->update(
                $checklist,
                Auth::guard('api_employee')->id(),
                $data
            );

            return $this->success(ApiTransformer::checklistItem($item), 'Task updated.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function toggle(DailyChecklistItem $checklist): JsonResponse
    {
        try {
            $item = $this->checklistService->toggle(
                $checklist,
                Auth::guard('api_employee')->id()
            );

            $message = $item->is_completed ? 'Task marked complete.' : 'Task marked incomplete.';

            return $this->success(ApiTransformer::checklistItem($item), $message);
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function destroy(DailyChecklistItem $checklist): JsonResponse
    {
        try {
            $this->checklistService->delete(
                $checklist,
                Auth::guard('api_employee')->id()
            );

            return $this->success(null, 'Task removed.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }
}
