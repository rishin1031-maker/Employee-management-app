<?php

namespace App\Services;

use App\Models\DailyChecklistItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DailyChecklistService
{
    public function listForDate(int $employeeId, string $date): Collection
    {
        return DailyChecklistItem::query()
            ->where('employee_id', $employeeId)
            ->whereDate('task_date', $date)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function create(int $employeeId, array $data): DailyChecklistItem
    {
        $date = Carbon::parse($data['task_date'] ?? now()->toDateString())->toDateString();
        $title = trim($data['title']);

        if ($title === '') {
            throw new \Exception('Task title is required.');
        }

        $maxOrder = DailyChecklistItem::query()
            ->where('employee_id', $employeeId)
            ->whereDate('task_date', $date)
            ->max('sort_order');

        return DailyChecklistItem::create([
            'employee_id'  => $employeeId,
            'task_date'    => $date,
            'title'        => $title,
            'is_completed' => false,
            'sort_order'   => ($maxOrder ?? 0) + 1,
        ]);
    }

    public function update(DailyChecklistItem $item, int $employeeId, array $data): DailyChecklistItem
    {
        $this->assertOwnership($item, $employeeId);

        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                throw new \Exception('Task title is required.');
            }
            $item->title = $title;
        }

        if (array_key_exists('is_completed', $data)) {
            $completed = (bool) $data['is_completed'];
            $item->is_completed = $completed;
            $item->completed_at = $completed ? ($item->completed_at ?? now()) : null;
        }

        $item->save();

        return $item->fresh();
    }

    public function toggle(DailyChecklistItem $item, int $employeeId): DailyChecklistItem
    {
        $this->assertOwnership($item, $employeeId);

        $item->is_completed = ! $item->is_completed;
        $item->completed_at = $item->is_completed ? now() : null;
        $item->save();

        return $item->fresh();
    }

    public function delete(DailyChecklistItem $item, int $employeeId): void
    {
        $this->assertOwnership($item, $employeeId);
        $item->delete();
    }

    public function summaryForDate(int $employeeId, string $date): array
    {
        $items = $this->listForDate($employeeId, $date);
        $total = $items->count();
        $completed = $items->where('is_completed', true)->count();

        return [
            'date'      => $date,
            'total'     => $total,
            'completed' => $completed,
            'pending'   => $total - $completed,
            'items'     => $items,
        ];
    }

    public function reorder(int $employeeId, string $date, array $orderedIds): Collection
    {
        $items = $this->listForDate($employeeId, $date);
        $ownedIds = $items->pluck('id')->all();

        foreach ($orderedIds as $id) {
            if (! in_array((int) $id, $ownedIds, true)) {
                throw new \Exception('Invalid task id in reorder list.');
            }
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                DailyChecklistItem::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return $this->listForDate($employeeId, $date);
    }

    public function employeeOwns(DailyChecklistItem $item, int $employeeId): bool
    {
        return (int) $item->employee_id === $employeeId;
    }

    private function assertOwnership(DailyChecklistItem $item, int $employeeId): void
    {
        if (! $this->employeeOwns($item, $employeeId)) {
            abort(403, 'Unauthorized.');
        }
    }
}
