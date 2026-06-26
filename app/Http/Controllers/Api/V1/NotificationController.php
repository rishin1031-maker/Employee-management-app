<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends ApiController
{
    public function __construct(private NotificationService $notificationService) {}

    private function currentUser()
    {
        return Auth::guard('api_admin')->user() ?? Auth::guard('api_employee')->user();
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->currentUser();

        if (!$user) {
            return $this->error('Unauthorized.', 401);
        }

        $notifications = $this->notificationService->getForUser($user);

        return $this->success(
            ApiTransformer::paginated($notifications, fn ($n) => ApiTransformer::notification($n))
        );
    }

    public function unreadCount(): JsonResponse
    {
        $user = $this->currentUser();

        if (!$user) {
            return $this->error('Unauthorized.', 401);
        }

        return $this->success([
            'count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $user = $this->currentUser();

        if (!$user) {
            return $this->error('Unauthorized.', 401);
        }

        $this->notificationService->markAllRead($user);

        return $this->success(null, 'All notifications marked as read.');
    }

    public function markRead(string $id): JsonResponse
    {
        $user = $this->currentUser();

        if (!$user) {
            return $this->error('Unauthorized.', 401);
        }

        $this->notificationService->markReadAndGetUrl($user, $id);

        return $this->success(null, 'Notification marked as read.');
    }
}
