<?php

namespace App\Libraries;

use App\Models\NotificationModel;
use App\Models\UserModel;

class NotificationService
{
    private NotificationModel $notifications;
    private UserModel $users;

    public function __construct(?NotificationModel $notifications = null, ?UserModel $users = null)
    {
        $this->notifications = $notifications ?? new NotificationModel();
        $this->users = $users ?? new UserModel();
    }

    public function notifyUsers(array $userIds, array $payload): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), static fn (int $id): bool => $id > 0)));
        if ($userIds === []) {
            return;
        }

        $users = $this->users->whereIn('id', $userIds)
            ->where('is_active', 1)
            ->findAll();

        $this->notifications->createForUsers($users, $payload);
    }

    public function notifyRoles(array $roles, array $payload): void
    {
        $roles = array_values(array_unique(array_filter(array_map(
            static fn ($role): string => strtolower(trim((string) $role)),
            $roles
        ))));

        if ($roles === []) {
            return;
        }

        $users = $this->users->whereIn('role', $roles)
            ->where('is_active', 1)
            ->findAll();

        $this->notifications->createForUsers($users, $payload);
    }

    public function notifyAdmins(array $payload): void
    {
        $this->notifyRoles(['admin', 'staff'], $payload);
    }

    public function notifyOrderAudience(array $order, array $payload, bool $includeCustomer = true, bool $includeRider = false, bool $includeAdmins = false): void
    {
        if ($includeCustomer && ! empty($order['created_by'])) {
            $this->notifyUsers([(int) $order['created_by']], $payload);
        }

        if ($includeRider && ! empty($order['assigned_rider_id'])) {
            $this->notifyUsers([(int) $order['assigned_rider_id']], $payload);
        }

        if ($includeAdmins) {
            $this->notifyAdmins($payload);
        }
    }
}
