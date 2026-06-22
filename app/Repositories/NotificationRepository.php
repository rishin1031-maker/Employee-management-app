<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getForUser(Model $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()->paginate($perPage);
    }

    public function getRecent(Model $user, int $limit = 8): Collection
    {
        return $user->notifications()->take($limit)->get();
    }

    public function getUnreadCount(Model $user): int
    {
        return $user->unreadNotifications->count();
    }

    public function markAllRead(Model $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function markRead(Model $user, string $notificationId): void
    {
        $notification = $this->findForUser($user, $notificationId);
        $notification->markAsRead();
    }

    public function findForUser(Model $user, string $id): DatabaseNotification
    {
        return $user->notifications()->findOrFail($id);
    }
}