<?php

namespace App\Controllers;

use App\Models\ChatNotificationModel;
use App\Models\MessageConversationModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use App\Libraries\NotificationService;
use App\Libraries\ActivityLogger;
use Config\ActivityLogTypes;

class Messages extends BaseController
{
    protected $session;
    protected MessageConversationModel $conversationModel;
    protected ChatNotificationModel $notificationModel;
    protected NotificationService $bellNotifications;
    protected OrderModel $orderModel;
    protected UserModel $userModel;
    protected ActivityLogger $activityLogger;

    public function __construct()
    {
        $this->session = session();
        $this->conversationModel = new MessageConversationModel();
        $this->notificationModel = new ChatNotificationModel();
        $this->bellNotifications = new NotificationService();
        $this->orderModel = new OrderModel();
        $this->userModel = new UserModel();
        $this->activityLogger = new ActivityLogger();
        helper(['form', 'text']);
    }

    public function customerInbox()
    {
        $guard = $this->requireRole(['customer']);
        if ($guard !== true) {
            return $guard;
        }

        $customerId = (int) $this->session->get('user_id');
        $conversation = $this->conversationModel->getOrCreateForCustomer($customerId);
        $selectedOrderId = (int) $this->request->getGet('order_id');
        if ($selectedOrderId > 0 && ! $this->customerOwnsOrder($customerId, $selectedOrderId)) {
            $selectedOrderId = 0;
        }

        $this->conversationModel->markMessagesReadForRole((int) $conversation['id'], 'admin');
        $this->conversationModel->markMessagesReadForRole((int) $conversation['id'], 'rider');
        $this->conversationModel->markMessagesReadForRole((int) $conversation['id'], 'chatbot');
        $this->notificationModel->markConversationRead((int) $conversation['id'], $customerId);

        return view('customer/messages', [
            'page_title' => 'Support Chat',
            'active_page' => 'messages',
            'user_name' => $this->session->get('user_name'),
            'conversation' => $conversation,
            'messages' => $this->conversationModel->getMessagesForConversation((int) $conversation['id']),
            'orders' => $this->orderModel->getCustomerOrders($customerId),
            'selected_order_id' => $selectedOrderId,
        ]);
    }

    public function customerSend()
    {
        $guard = $this->requireRole(['customer']);
        if ($guard !== true) {
            return $guard;
        }

        $customerId = (int) $this->session->get('user_id');
        $conversation = $this->conversationModel->getOrCreateForCustomer($customerId);
        $message = trim((string) $this->request->getPost('message'));
        $orderId = (int) $this->request->getPost('order_id');
        $supportTarget = strtolower((string) $this->request->getPost('support_target'));

        if ($message === '') {
            return $this->respondBack('Please enter a message.', false);
        }

        if ($orderId > 0 && ! $this->customerOwnsOrder($customerId, $orderId)) {
            $orderId = 0;
        }

        $this->addMessage((int) $conversation['id'], $customerId, 'customer', $message);

        $this->activityLogger->logUserAction(
            $customerId,
            'Sent support message',
            ActivityLogTypes::MESSAGE_SENT,
            [
                'actor_role' => 'customer',
                'conversation_id' => (int) $conversation['id'],
                'order_id' => $orderId > 0 ? $orderId : null,
                'support_target' => $supportTarget !== '' ? $supportTarget : 'default',
                'preview' => mb_substr($message, 0, 120),
            ]
        );

        $humanRequested = $supportTarget === 'human' || $this->shouldEscalate($message);
        $botRequested = $supportTarget === 'bot';

        if ($botRequested && ! $humanRequested) {
            $botReply = $this->buildBotReply($message, $customerId, $orderId)
                ?? 'I can answer FAQs about order status, delivery, payments, and refunds. For anything else, choose Human Support so an admin/seller can help.';

            $this->addMessage((int) $conversation['id'], 0, 'chatbot', $botReply, 'bot', true);
            $this->conversationModel->touchConversation((int) $conversation['id']);

            return $this->respondBack('Chatbot replied.');
        }

        if (($conversation['support_mode'] ?? 'bot') === 'human' && ! $humanRequested) {
            $payload = ['status' => 'pending'];
            if ($orderId > 0) {
                $payload['order_id'] = $orderId;
            }
            $this->conversationModel->update((int) $conversation['id'], $payload);

            $notify = $this->getAdminUserIds();
            if (! empty($conversation['assigned_rider_id'])) {
                $notify[] = (int) $conversation['assigned_rider_id'];
            }
            $this->notificationModel->notifyUsers((int) $conversation['id'], $notify, 'Customer replied to a support chat.');
            $this->bellNotifications->notifyUsers($notify, [
                'category' => 'messages',
                'type' => 'support_reply',
                'title' => 'Support chat reply',
                'message' => 'Customer replied to a support conversation.',
                'link' => site_url('admin/messages/' . (int) $conversation['id']),
                'related_type' => 'conversation',
                'related_id' => (int) $conversation['id'],
            ]);

            return $this->respondBack('Message sent.');
        }

        $botReply = $humanRequested ? null : $this->buildBotReply($message, $customerId, $orderId);

        if ($botReply !== null) {
            $this->addMessage((int) $conversation['id'], 0, 'chatbot', $botReply, 'bot', true);
            $this->conversationModel->touchConversation((int) $conversation['id']);
            return $this->respondBack('Message sent.');
        }

        $this->conversationModel->escalateToHuman((int) $conversation['id'], $orderId > 0 ? $orderId : null);
        $this->addMessage(
            (int) $conversation['id'],
            0,
            'chatbot',
            'I will connect you with an admin/seller. Please wait for a human reply here.',
            'system',
            true
        );
        $this->notificationModel->notifyUsers(
            (int) $conversation['id'],
            $this->getAdminUserIds(),
            'A customer support chat needs attention.'
        );
        $this->bellNotifications->notifyAdmins([
            'category' => 'messages',
            'type' => 'support_escalated',
            'title' => 'Support chat needs attention',
            'message' => 'A customer requested human support.',
            'link' => site_url('admin/messages/' . (int) $conversation['id']),
            'related_type' => 'conversation',
            'related_id' => (int) $conversation['id'],
        ]);

        return $this->respondBack('Your chat was escalated to admin support.');
    }

    public function adminInbox()
    {
        $guard = $this->requireRole(['admin', 'staff']);
        if ($guard !== true) {
            return $guard;
        }

        $status = strtolower((string) $this->request->getGet('status'));

        return view('admin/messages/index', [
            'page_title' => 'Support Conversations',
            'conversations' => $this->conversationModel->getAdminInbox($status ?: null),
            'active_status' => $status ?: 'all',
            'unread_notifications' => $this->notificationModel->countUnread((int) $this->session->get('user_id')),
        ]);
    }

    public function adminConversation(int $conversationId)
    {
        $guard = $this->requireRole(['admin', 'staff']);
        if ($guard !== true) {
            return $guard;
        }

        $conversation = $this->getConversationForAdmin($conversationId);
        if (! $conversation) {
            return redirect()->to('/admin/messages')->with('error', 'Conversation not found.');
        }

        $this->conversationModel->markMessagesReadForRole($conversationId, 'customer');
        $this->conversationModel->markMessagesReadForRole($conversationId, 'rider');
        $this->notificationModel->markConversationRead($conversationId, (int) $this->session->get('user_id'));

        return view('admin/messages/show', [
            'page_title' => 'Support Conversation',
            'conversation' => $conversation,
            'messages' => $this->conversationModel->getMessagesForConversation($conversationId),
            'order' => ! empty($conversation['order_id']) ? $this->orderModel->getOrder((int) $conversation['order_id']) : null,
            'riders' => $this->getRiders(),
        ]);
    }

    public function adminReply(int $conversationId)
    {
        $guard = $this->requireRole(['admin', 'staff']);
        if ($guard !== true) {
            return $guard;
        }

        $conversation = $this->getConversationForAdmin($conversationId);
        if (! $conversation) {
            return redirect()->to('/admin/messages')->with('error', 'Conversation not found.');
        }

        $message = trim((string) $this->request->getPost('message'));
        if ($message === '') {
            return $this->respondBack('Please enter a reply.', false);
        }

        $adminId = (int) $this->session->get('user_id');
        $this->addMessage($conversationId, $adminId, 'admin', $message);
        $this->conversationModel->touchConversation($conversationId, $adminId);
        $this->notificationModel->notifyUsers($conversationId, [(int) $conversation['customer_id']], 'Admin replied to your support chat.');
        $this->bellNotifications->notifyUsers([(int) $conversation['customer_id']], [
            'category' => 'messages',
            'type' => 'admin_reply',
            'title' => 'Admin replied',
            'message' => 'Admin replied to your support chat.',
            'link' => site_url('customer/messages'),
            'related_type' => 'conversation',
            'related_id' => $conversationId,
        ]);

        $this->activityLogger->logUserAction(
            $adminId,
            'Admin replied to support chat',
            ActivityLogTypes::MESSAGE_SENT,
            [
                'actor_role' => 'admin',
                'conversation_id' => $conversationId,
                'preview' => mb_substr($message, 0, 120),
            ]
        );

        return $this->respondBack('Reply sent.');
    }

    public function updateStatus(int $conversationId)
    {
        $guard = $this->requireRole(['admin', 'staff']);
        if ($guard !== true) {
            return $guard;
        }

        $status = strtolower((string) $this->request->getPost('status'));
        if (! in_array($status, ['open', 'pending', 'resolved'], true)) {
            return redirect()->back()->with('error', 'Invalid chat status.');
        }

        if (! $this->conversationModel->find($conversationId)) {
            return redirect()->to('/admin/messages')->with('error', 'Conversation not found.');
        }

        $this->conversationModel->update($conversationId, ['status' => $status]);
        $this->addMessage($conversationId, (int) $this->session->get('user_id'), 'admin', 'Chat status changed to ' . ucfirst($status) . '.', 'system', true);
        $this->conversationModel->update($conversationId, ['status' => $status]);

        $this->activityLogger->logUserAction(
            (int) $this->session->get('user_id'),
            'Updated support chat status to ' . $status,
            ActivityLogTypes::CHAT_STATUS_UPDATED,
            ['conversation_id' => $conversationId, 'status' => $status, 'actor_role' => 'admin']
        );

        return redirect()->to('/admin/messages/' . $conversationId)->with('success', 'Chat status updated.');
    }

    public function assignRider(int $conversationId)
    {
        $guard = $this->requireRole(['admin', 'staff']);
        if ($guard !== true) {
            return $guard;
        }

        $riderId = (int) $this->request->getPost('rider_id');
        $rider = $riderId > 0 ? $this->userModel->find($riderId) : null;
        if (! $rider || strtolower((string) ($rider['role'] ?? '')) !== 'rider') {
            return redirect()->back()->with('error', 'Please choose a valid rider.');
        }

        $this->conversationModel->update($conversationId, ['assigned_rider_id' => $riderId, 'status' => 'open']);
        $this->addMessage($conversationId, (int) $this->session->get('user_id'), 'admin', 'Rider ' . ($rider['name'] ?? '') . ' was added for delivery support.', 'system', true);
        $this->notificationModel->notifyUsers($conversationId, [$riderId], 'You were added to a delivery support chat.');
        $this->bellNotifications->notifyUsers([$riderId], [
            'category' => 'messages',
            'type' => 'rider_assigned_chat',
            'title' => 'Delivery chat assigned',
            'message' => 'You were added to a delivery support chat.',
            'link' => site_url('rider/messages/' . $conversationId),
            'related_type' => 'conversation',
            'related_id' => $conversationId,
        ]);

        return redirect()->to('/admin/messages/' . $conversationId)->with('success', 'Rider added to conversation.');
    }

    public function riderInbox()
    {
        $guard = $this->requireRole(['rider']);
        if ($guard !== true) {
            return $guard;
        }

        return view('rider/messages/index', [
            'page_title' => 'Delivery Support Chats',
            'user_name' => $this->session->get('user_name'),
            'conversations' => $this->conversationModel->getRiderInbox((int) $this->session->get('user_id')),
        ]);
    }

    public function riderConversation(int $conversationId)
    {
        $guard = $this->requireRole(['rider']);
        if ($guard !== true) {
            return $guard;
        }

        $conversation = $this->conversationModel->getAdminConversation($conversationId);
        if (! $conversation || (int) ($conversation['assigned_rider_id'] ?? 0) !== (int) $this->session->get('user_id')) {
            return redirect()->to('/rider/messages')->with('error', 'Conversation not found.');
        }

        $this->conversationModel->markMessagesReadForRole($conversationId, 'admin');
        $this->conversationModel->markMessagesReadForRole($conversationId, 'customer');
        $this->notificationModel->markConversationRead($conversationId, (int) $this->session->get('user_id'));

        return view('rider/messages/show', [
            'page_title' => 'Delivery Support Chat',
            'user_name' => $this->session->get('user_name'),
            'conversation' => $conversation,
            'messages' => $this->conversationModel->getMessagesForConversation($conversationId),
        ]);
    }

    public function riderReply(int $conversationId)
    {
        $guard = $this->requireRole(['rider']);
        if ($guard !== true) {
            return $guard;
        }

        $conversation = $this->conversationModel->getAdminConversation($conversationId);
        if (! $conversation || (int) ($conversation['assigned_rider_id'] ?? 0) !== (int) $this->session->get('user_id')) {
            return redirect()->to('/rider/messages')->with('error', 'Conversation not found.');
        }

        $message = trim((string) $this->request->getPost('message'));
        if ($message === '') {
            return $this->respondBack('Please enter a reply.', false);
        }

        $this->addMessage($conversationId, (int) $this->session->get('user_id'), 'rider', $message);
        $this->conversationModel->touchConversation($conversationId);
        $notify = array_filter([(int) ($conversation['customer_id'] ?? 0), (int) ($conversation['assigned_admin_id'] ?? 0)]);
        $this->notificationModel->notifyUsers($conversationId, $notify, 'Rider replied to a delivery support chat.');
        $this->bellNotifications->notifyUsers([(int) ($conversation['customer_id'] ?? 0)], [
            'category' => 'messages',
            'type' => 'rider_reply',
            'title' => 'Rider replied',
            'message' => 'Rider replied to a delivery support chat.',
            'link' => site_url('customer/messages'),
            'related_type' => 'conversation',
            'related_id' => $conversationId,
        ]);
        $this->bellNotifications->notifyUsers([(int) ($conversation['assigned_admin_id'] ?? 0)], [
            'category' => 'messages',
            'type' => 'rider_reply',
            'title' => 'Rider replied',
            'message' => 'Rider replied to a delivery support chat.',
            'link' => site_url('admin/messages/' . $conversationId),
            'related_type' => 'conversation',
            'related_id' => $conversationId,
        ]);

        $this->activityLogger->logUserAction(
            (int) $this->session->get('user_id'),
            'Rider replied to delivery support chat',
            ActivityLogTypes::MESSAGE_SENT,
            [
                'actor_role' => 'rider',
                'conversation_id' => $conversationId,
                'preview' => mb_substr($message, 0, 120),
            ]
        );

        return $this->respondBack('Reply sent.');
    }

    public function poll(int $conversationId)
    {
        $guard = $this->requireRole(['customer', 'admin', 'staff', 'rider']);
        if ($guard !== true) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        if (! $this->canAccessConversation($conversationId)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        $afterId = (int) $this->request->getGet('after_id');
        $messages = $this->conversationModel->getMessagesAfterId($conversationId, $afterId);

        return $this->response->setJSON([
            'success' => true,
            'messages' => array_map([$this, 'formatMessageForJson'], $messages),
            'unread_notifications' => $this->notificationModel->countUnread((int) $this->session->get('user_id')),
        ]);
    }

    private function addMessage(int $conversationId, int $senderId, string $role, string $message, string $type = 'text', bool $read = false): int
    {
        $id = $this->conversationModel->addMessage([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId > 0 ? $senderId : (int) $this->session->get('user_id'),
            'sender_role' => $role,
            'message' => mb_substr($message, 0, 2000),
            'message_type' => $type,
            'is_read' => $read ? 1 : 0,
            'read_at' => $read ? date('Y-m-d H:i:s') : null,
        ]);

        $this->conversationModel->touchConversation($conversationId);
        return $id;
    }

    private function buildBotReply(string $message, int $customerId, int $orderId): ?string
    {
        $text = strtolower($message);

        if (str_contains($text, 'order') || str_contains($text, 'status') || str_contains($text, 'track')) {
            $order = $orderId > 0 ? $this->orderModel->getOrder($orderId) : $this->getLatestCustomerOrder($customerId);
            if (! $order) {
                return 'I could not find an order yet. Choose an order in the dropdown or type "human support" so an admin can help.';
            }

            return sprintf(
                'Order %s is currently %s. Payment is %s. Delivery status is %s.',
                $order['reference_number'] ?? ('#' . $order['id']),
                $this->humanizeStatus((string) ($order['status'] ?? 'pending')),
                $this->humanizeStatus((string) ($order['payment_status'] ?? 'unpaid')),
                $this->humanizeStatus((string) ($order['delivery_status'] ?? 'to_pay'))
            );
        }

        if (str_contains($text, 'delivery') || str_contains($text, 'ship') || str_contains($text, 'rider')) {
            return 'For delivery concerns, you can check your Orders page for the latest tracking status. If your order is delayed or the address is wrong, type "human support" and an admin can coordinate with a rider.';
        }

        if (str_contains($text, 'payment') || str_contains($text, 'gcash') || str_contains($text, 'cash')) {
            return 'We support the payment options shown during checkout. If your payment was sent but still appears unpaid, send the reference number and type "human support" so an admin can verify it.';
        }

        if (str_contains($text, 'refund') || str_contains($text, 'return') || str_contains($text, 'cancel')) {
            return 'For returns, refunds, or cancellations, please include your order number and reason. Type "human support" if you want an admin to review the request.';
        }

        if (str_contains($text, 'hello') || str_contains($text, 'hi') || str_contains($text, 'help')) {
            return 'Hi! I can help with order status, delivery, payments, and refunds. You can also type "human support" anytime to talk to an admin/seller.';
        }

        return null;
    }

    private function shouldEscalate(string $message): bool
    {
        $text = strtolower($message);
        foreach (['human', 'admin', 'seller', 'agent', 'support staff', 'representative', 'cannot answer'] as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function getLatestCustomerOrder(int $customerId): ?array
    {
        $orders = $this->orderModel->getCustomerOrders($customerId);
        return $orders[0] ?? null;
    }

    private function customerOwnsOrder(int $customerId, int $orderId): bool
    {
        $order = $this->orderModel->getOrder($orderId);
        return $order && (int) ($order['created_by'] ?? 0) === $customerId;
    }

    private function getAdminUserIds(): array
    {
        $rows = $this->userModel->whereIn('role', ['admin', 'staff'])
            ->where('is_active', 1)
            ->findAll();

        return array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows);
    }

    private function getRiders(): array
    {
        return $this->userModel->where('role', 'rider')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function canAccessConversation(int $conversationId): bool
    {
        $conversation = $this->conversationModel->getAdminConversation($conversationId);
        if (! $conversation) {
            return false;
        }

        $userId = (int) $this->session->get('user_id');
        $role = strtolower((string) $this->session->get('user_role'));

        if (in_array($role, ['admin', 'staff'], true)) {
            return true;
        }

        if ($role === 'customer') {
            return (int) ($conversation['customer_id'] ?? 0) === $userId;
        }

        if ($role === 'rider') {
            return (int) ($conversation['assigned_rider_id'] ?? 0) === $userId;
        }

        return false;
    }

    private function getConversationForAdmin(int $conversationId): ?array
    {
        return $this->conversationModel->getAdminConversation($conversationId);
    }

    private function requireRole(array $roles)
    {
        if (! $this->session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $role = strtolower((string) $this->session->get('user_role'));
        if (! in_array($role, $roles, true)) {
            // Allow custom back-office roles for admin/staff areas if permitted.
            if (in_array('admin', $roles, true) || in_array('staff', $roles, true)) {
                $isBackOffice = $role !== '' && !in_array($role, ['customer', 'rider'], true);
                $hasMessagingPermission = $this->hasPermission('manage_orders')
                    || $this->hasPermission('manage_users')
                    || $this->hasPermission('activity_logs.manage');
                if ($isBackOffice && $hasMessagingPermission) {
                    return true;
                }
            }
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        return true;
    }

    private function respondBack(string $message, bool $success = true)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => $success, 'message' => $message]);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }

    private function humanizeStatus(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    private function formatMessageForJson(array $message): array
    {
        return [
            'id' => (int) ($message['id'] ?? 0),
            'sender_role' => (string) ($message['sender_role'] ?? ''),
            'sender_name' => (string) ($message['sender_name'] ?? ''),
            'message' => (string) ($message['message'] ?? ''),
            'message_type' => (string) ($message['message_type'] ?? 'text'),
            'created_at' => (string) ($message['created_at'] ?? ''),
            'created_label' => ! empty($message['created_at']) ? date('M d, Y h:i A', strtotime((string) $message['created_at'])) : '',
        ];
    }
}
