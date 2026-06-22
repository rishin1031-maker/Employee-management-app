<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function getForUser(Model $user, int $perPage = 20): LengthAwarePaginator;
    public function getRecent(Model $user, int $limit = 8): \Illuminate\Support\Collection;
    public function getUnreadCount(Model $user): int;
    public function markAllRead(Model $user): void;
    public function markRead(Model $user, string $notificationId): void;
    public function findForUser(Model $user, string $id): \Illuminate\Notifications\DatabaseNotification;
}