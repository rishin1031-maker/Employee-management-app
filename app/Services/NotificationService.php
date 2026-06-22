<?php

namespace App\Services;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $notifRepo
    ) {}

    public function getForUser(Model $user): LengthAwarePaginator
    {
        $notifications = $this->notifRepo->getForUser($user);
        $this->notifRepo->markAllRead($user);
        return $notifications;
    }

    public function getRecent(Model $user): Collection
    {
        return $this->notifRepo->getRecent($user);
    }

    public function getUnreadCount(Model $user): int
    {
        return $this->notifRepo->getUnreadCount($user);
    }

    public function markAllRead(Model $user): void
    {
        $this->notifRepo->markAllRead($user);
    }

    public function markReadAndGetUrl(Model $user, string $id): string
    {
        $notification = $this->notifRepo->findForUser($user, $id);
        $notification->markAsRead();
        return $notification->data['url'] ?? '/';
    }
}