<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'event'       => 'nullable|string|max:50',
            'search'      => 'nullable|string|max:100',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
            'subject_type'=> 'nullable|string|max:100',
            'page'        => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = min((int) ($request->per_page ?? 25), 100);

        $query = ActivityLog::query()->with(['causer', 'subject'])->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%' . $request->subject_type . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate($perPage)->withQueryString();

        return $this->success(
            ApiTransformer::paginated($logs, fn (ActivityLog $log) => $this->transform($log))
        );
    }

    private function transform(ActivityLog $log): array
    {
        $causerName = null;
        if ($log->causer) {
            $causerName = $log->causer->name ?? $log->causer->email ?? null;
        }

        $subjectLabel = null;
        if ($log->subject) {
            $subjectLabel = $log->subject->name
                ?? $log->subject->employee_id
                ?? class_basename($log->subject_type) . ' #' . $log->subject_id;
        }

        return [
            'id'           => $log->id,
            'event'        => $log->event,
            'description'  => $log->description,
            'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
            'subject_id'   => $log->subject_id,
            'subject_label'=> $subjectLabel,
            'causer_type'  => $log->causer_type ? class_basename($log->causer_type) : null,
            'causer_id'    => $log->causer_id,
            'causer_name'  => $causerName,
            'properties'   => $log->properties,
            'ip_address'   => $log->ip_address,
            'created_at'   => $log->created_at?->toIso8601String(),
        ];
    }
}
