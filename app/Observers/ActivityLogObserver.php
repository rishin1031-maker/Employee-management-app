<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function created(Model $model): void
    {
        $this->activityLog->logModelEvent('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->activityLog->logModelEvent('updated', $model, $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->activityLog->logModelEvent('deleted', $model);
    }
}
