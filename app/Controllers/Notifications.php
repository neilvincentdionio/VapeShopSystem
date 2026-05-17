<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\ReviewModel;

class Notifications extends BaseController
{
    private NotificationModel $notifications;
    private ReviewModel $reviews;

    public function __construct()
    {
        $this->notifications = new NotificationModel();
        $this->reviews = new ReviewModel();
    }

    public function recent()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $role = strtolower((string) $session->get('user_role'));

        if (! $session->get('logged_in') || $userId <= 0 || $role === '') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false]);
        }

        $items = array_map(
            fn (array $notification): array => $this->formatNotification($notification, $role),
            $this->notifications->getRecentForUser($userId, $role, 10)
        );

        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $this->notifications->countUnreadForUser($userId, $role),
            'notifications' => $items,
        ]);
    }

    public function markRead(int $id)
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $role = strtolower((string) $session->get('user_role'));

        if (! $session->get('logged_in') || $userId <= 0 || $role === '') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false]);
        }

        $this->notifications->markReadForUser((int) $id, $userId, $role);

        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $this->notifications->countUnreadForUser($userId, $role),
        ]);
    }

    public function markAllRead()
    {
        $session = session();
        $userId = (int) $session->get('user_id');
        $role = strtolower((string) $session->get('user_role'));

        if (! $session->get('logged_in') || $userId <= 0 || $role === '') {
            return $this->response->setStatusCode(401)->setJSON(['success' => false]);
        }

        $this->notifications->markAllReadForUser($userId, $role);

        return $this->response->setJSON([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    private function formatNotification(array $notification, string $role): array
    {
        $createdAt = (string) ($notification['created_at'] ?? '');

        $type = (string) ($notification['type'] ?? 'info');
        $link = $this->resolveLink($notification, $role);

        return [
            'id' => (int) ($notification['id'] ?? 0),
            'category' => (string) ($notification['category'] ?? 'general'),
            'type' => $type,
            'title' => (string) ($notification['title'] ?? 'Notification'),
            'message' => (string) ($notification['message'] ?? ''),
            'link' => $link,
            'is_read' => (int) ($notification['is_read'] ?? 0) === 1,
            'created_at' => $createdAt,
            'created_label' => $createdAt !== '' ? date('M d, h:i A', strtotime($createdAt)) : '',
        ];
    }

    private function resolveLink(array $notification, string $role): string
    {
        $type = strtolower((string) ($notification['type'] ?? ''));
        $category = strtolower((string) ($notification['category'] ?? ''));
        $relatedType = strtolower((string) ($notification['related_type'] ?? ''));
        $relatedId = (int) ($notification['related_id'] ?? 0);
        $savedLink = (string) ($notification['link'] ?? '');

        if ($relatedType === 'review' || str_starts_with($type, 'review_')) {
            $reviewLink = $this->resolveReviewLink($relatedId, $role, $type);
            if ($reviewLink !== '') {
                return $reviewLink;
            }
        }

        if ($relatedType === 'conversation' || $category === 'messages') {
            $conversationLink = $this->resolveConversationLink($relatedId, $role);
            if ($conversationLink !== '') {
                return $conversationLink;
            }
        }

        if ($relatedType === 'order' || $this->isOrderNotification($type, $category)) {
            $orderLink = $this->resolveOrderLink($relatedId, $role, $type);
            if ($orderLink !== '') {
                return $orderLink;
            }
        }

        if ($relatedType === 'product') {
            $productLink = $this->resolveProductLink($relatedId, $role);
            if ($productLink !== '') {
                return $productLink;
            }
        }

        if ($relatedType === 'user' || str_starts_with($type, 'account_')) {
            $userLink = $this->resolveUserLink($relatedId, $role, $type);
            if ($userLink !== '') {
                return $userLink;
            }
        }

        return $savedLink;
    }

    private function resolveReviewLink(int $reviewId, string $role, string $type): string
    {
        $review = $reviewId > 0 ? $this->reviews->find($reviewId) : null;
        if (! $review) {
            return '';
        }

        $productId = (int) ($review['product_id'] ?? 0);
        if ($productId <= 0) {
            return '';
        }

        if ($this->isAdminRole($role)) {
            return site_url('products/view/' . $productId . '?review_id=' . $reviewId . '#review-' . $reviewId);
        }

        if ($role === 'customer') {
            if ($type === 'review_reply') {
                return site_url('customer/product/' . $productId . '?review_id=' . $reviewId . '#admin-reply-' . $reviewId);
            }

            if (in_array($type, ['review_approved', 'review_rejected'], true)) {
                return site_url('customer/product/' . $productId);
            }

            $orderId = (int) ($review['order_id'] ?? 0);
            return $orderId > 0
                ? site_url('customer/order-details/' . $orderId)
                : site_url('customer/product/' . $productId);
        }

        return site_url('customer/product/' . $productId);
    }

    private function resolveConversationLink(int $conversationId, string $role): string
    {
        if ($conversationId <= 0) {
            if ($role === 'customer') {
                return site_url('customer/messages');
            }

            if ($role === 'rider') {
                return site_url('rider/messages');
            }

            return $this->isAdminRole($role) ? site_url('admin/messages') : '';
        }

        if ($role === 'customer') {
            return site_url('customer/messages');
        }

        if ($role === 'rider') {
            return site_url('rider/messages/' . $conversationId);
        }

        if ($this->isAdminRole($role)) {
            return site_url('admin/messages/' . $conversationId);
        }

        return '';
    }

    private function resolveOrderLink(int $orderId, string $role, string $type = ''): string
    {
        if ($orderId <= 0) {
            return '';
        }

        if ($role === 'customer') {
            return site_url('customer/order-details/' . $orderId);
        }

        if ($role === 'rider') {
            if ($type === 'rider_assigned') {
                return site_url('rider/deliveries?order_id=' . $orderId . '#delivery-' . $orderId);
            }

            return site_url('rider/order-details/' . $orderId);
        }

        if ($this->isAdminRole($role)) {
            if ($type === 'new_order') {
                return site_url('orders?order_id=' . $orderId . '#order-' . $orderId);
            }

            if ($type === 'delivery_proof') {
                return site_url('admin/order-details/' . $orderId . '#delivery-proof');
            }

            return site_url('admin/order-details/' . $orderId);
        }

        return '';
    }

    private function resolveProductLink(int $productId, string $role): string
    {
        if ($productId <= 0) {
            return '';
        }

        return $this->isAdminRole($role)
            ? site_url('products/view/' . $productId)
            : site_url('customer/product/' . $productId);
    }

    private function resolveUserLink(int $userId, string $role, string $type): string
    {
        if ($this->isAdminRole($role) && $userId > 0) {
            return site_url('user-management/view/' . $userId);
        }

        if ($role === 'customer') {
            return $type === 'account_approved'
                ? site_url('customer/home')
                : site_url('dashboard/profile');
        }

        if ($role === 'rider') {
            return site_url('dashboard/profile');
        }

        return '';
    }

    private function isOrderNotification(string $type, string $category): bool
    {
        if (in_array($category, ['orders', 'payments', 'delivery', 'cancellations'], true)) {
            return true;
        }

        return in_array($type, [
            'approval',
            'cancellation',
            'delivery_accepted',
            'delivery_cancelled',
            'delivery_info',
            'delivery_proof',
            'delivery_status',
            'new_order',
            'order_cancelled',
            'order_created',
            'order_handed_to_rider',
            'order_picked_up',
            'order_received',
            'order_status',
            'out_for_delivery',
            'payment_received',
            'rider_assigned',
        ], true);
    }

    private function isAdminRole(string $role): bool
    {
        return in_array($role, ['admin', 'administrator', 'staff'], true);
    }
}
