<?php

namespace Config;

/**
 * Activity log action_type values and admin filter labels.
 */
class ActivityLogTypes
{
    public const LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    public const LOGIN_FAILED = 'LOGIN_FAILED';
    public const LOGOUT = 'LOGOUT';
    public const PROFILE_UPDATE = 'PROFILE_UPDATE';
    public const PASSWORD_CHANGE = 'PASSWORD_CHANGE';
    public const MFA_ENABLED = 'MFA_ENABLED';
    public const MFA_DISABLED = 'MFA_DISABLED';
    public const ACCOUNT_CREATED = 'ACCOUNT_CREATED';
    public const ACCOUNT_DELETED = 'ACCOUNT_DELETED';
    public const SECURITY_ALERT = 'SECURITY_ALERT';

    public const CART_ADD = 'CART_ADD';
    public const CART_UPDATE = 'CART_UPDATE';
    public const CART_REMOVE = 'CART_REMOVE';
    public const ORDER_PLACED = 'ORDER_PLACED';
    public const ORDER_PAID = 'ORDER_PAID';
    public const ORDER_CANCELLED = 'ORDER_CANCELLED';
    public const ORDER_COMPLETED = 'ORDER_COMPLETED';
    public const ORDER_REORDER = 'ORDER_REORDER';
    public const RETURN_REFUND_REQUESTED = 'RETURN_REFUND_REQUESTED';
    public const RETURN_REFUND_APPROVED = 'RETURN_REFUND_APPROVED';
    public const RETURN_REFUND_REJECTED = 'RETURN_REFUND_REJECTED';
    public const RETURN_REFUND_COMPLETED = 'RETURN_REFUND_COMPLETED';
    public const REVIEW_SUBMITTED = 'REVIEW_SUBMITTED';
    public const AGE_VERIFIED = 'AGE_VERIFIED';
    public const MESSAGE_SENT = 'MESSAGE_SENT';

    public const RIDER_ASSIGNED = 'RIDER_ASSIGNED';
    public const ORDER_HANDED_TO_RIDER = 'ORDER_HANDED_TO_RIDER';
    public const ADMIN_ORDER_PROCESSED = 'ADMIN_ORDER_PROCESSED';
    public const DELIVERY_ACCEPTED = 'DELIVERY_ACCEPTED';
    public const DELIVERY_PICKED_UP = 'DELIVERY_PICKED_UP';
    public const DELIVERY_STARTED = 'DELIVERY_STARTED';
    public const DELIVERY_COMPLETED = 'DELIVERY_COMPLETED';
    public const DELIVERY_FAILED = 'DELIVERY_FAILED';
    public const DELIVERY_RESCHEDULED = 'DELIVERY_RESCHEDULED';
    public const ORDER_CANCELLED_AT_DOOR = 'ORDER_CANCELLED_AT_DOOR';
    public const RETURN_PICKUP_ACCEPTED = 'RETURN_PICKUP_ACCEPTED';
    public const RETURN_PICKUP_COMPLETED = 'RETURN_PICKUP_COMPLETED';
    public const RIDER_ASSIGNMENT_DECLINED = 'RIDER_ASSIGNMENT_DECLINED';
    public const PRODUCT_CREATED = 'PRODUCT_CREATED';
    public const PRODUCT_UPDATED = 'PRODUCT_UPDATED';
    public const PRODUCT_DELETED = 'PRODUCT_DELETED';
    public const REVIEW_APPROVED = 'REVIEW_APPROVED';
    public const REVIEW_REJECTED = 'REVIEW_REJECTED';
    public const CHAT_STATUS_UPDATED = 'CHAT_STATUS_UPDATED';

    /**
     * @return array<string, string> value => label (grouped with optgroup keys as "— Group —")
     */
    public static function filterOptions(): array
    {
        return [
            '— Authentication —' => '',
            self::LOGIN_SUCCESS => 'Login Success',
            self::LOGIN_FAILED => 'Login Failed',
            self::LOGOUT => 'Logout',
            self::SECURITY_ALERT => 'Security Alert',
            '— Account —' => '',
            self::PROFILE_UPDATE => 'Profile Update',
            self::PASSWORD_CHANGE => 'Password Change',
            self::MFA_ENABLED => 'MFA Enabled',
            self::MFA_DISABLED => 'MFA Disabled',
            self::ACCOUNT_CREATED => 'Account Created',
            self::ACCOUNT_DELETED => 'Account Deleted',
            self::AGE_VERIFIED => 'Age Verified',
            '— Shopping —' => '',
            self::CART_ADD => 'Cart: Add Item',
            self::CART_UPDATE => 'Cart: Update Quantity',
            self::CART_REMOVE => 'Cart: Remove Item',
            self::ORDER_PLACED => 'Order Placed',
            self::ORDER_PAID => 'Order Paid',
            self::ORDER_CANCELLED => 'Order Cancelled',
            self::ORDER_COMPLETED => 'Order Completed',
            self::ORDER_REORDER => 'Order Reorder',
            '— Returns & Reviews —' => '',
            self::RETURN_REFUND_REQUESTED => 'Return/Refund Requested',
            self::RETURN_REFUND_APPROVED => 'Return/Refund Approved',
            self::RETURN_REFUND_REJECTED => 'Return/Refund Rejected',
            self::RETURN_REFUND_COMPLETED => 'Return/Refund Completed',
            self::REVIEW_SUBMITTED => 'Product Review',
            '— Messaging —' => '',
            self::MESSAGE_SENT => 'Message Sent',
            self::CHAT_STATUS_UPDATED => 'Chat Status Updated',
            '— Admin —' => '',
            self::RIDER_ASSIGNED => 'Rider Assigned to Order',
            self::ORDER_HANDED_TO_RIDER => 'Order Handed to Rider',
            self::ADMIN_ORDER_PROCESSED => 'Admin Processed Order',
            self::PRODUCT_CREATED => 'Product Created',
            self::PRODUCT_UPDATED => 'Product Updated',
            self::PRODUCT_DELETED => 'Product Deleted',
            self::REVIEW_APPROVED => 'Review Approved',
            self::REVIEW_REJECTED => 'Review Rejected',
            '— Rider —' => '',
            self::DELIVERY_ACCEPTED => 'Delivery Accepted',
            self::DELIVERY_PICKED_UP => 'Order Picked Up',
            self::DELIVERY_STARTED => 'Out for Delivery',
            self::DELIVERY_COMPLETED => 'Delivery Completed',
            self::DELIVERY_FAILED => 'Delivery Failed',
            self::DELIVERY_RESCHEDULED => 'Delivery Rescheduled',
            self::ORDER_CANCELLED_AT_DOOR => 'Customer Cancelled at Delivery',
            self::RETURN_PICKUP_ACCEPTED => 'Return Pickup Accepted',
            self::RETURN_PICKUP_COMPLETED => 'Return Item Picked Up',
            self::RIDER_ASSIGNMENT_DECLINED => 'Rider Declined Assignment',
        ];
    }

    /**
     * Human-readable label for an action_type value.
     */
    public static function label(string $actionType): string
    {
        $options = self::filterOptions();
        if (isset($options[$actionType]) && $options[$actionType] !== '') {
            return $options[$actionType];
        }

        return ucwords(strtolower(str_replace('_', ' ', $actionType)));
    }

    /**
     * @return array<string, string> action_type => label (no group headers)
     */
    public static function labelsMap(): array
    {
        return array_filter(
            self::filterOptions(),
            static fn (string $label): bool => $label !== ''
        );
    }

    /**
     * @return array<string, string> badge color map for admin UI
     */
    public static function badgeColors(): array
    {
        return [
            self::LOGIN_SUCCESS => 'success',
            self::LOGIN_FAILED => 'danger',
            self::LOGOUT => 'info',
            self::SECURITY_ALERT => 'warning',
            self::PROFILE_UPDATE => 'primary',
            self::PASSWORD_CHANGE => 'warning',
            self::MFA_ENABLED => 'success',
            self::MFA_DISABLED => 'warning',
            self::ACCOUNT_CREATED => 'success',
            self::ACCOUNT_DELETED => 'danger',
            self::AGE_VERIFIED => 'success',
            self::CART_ADD => 'primary',
            self::CART_UPDATE => 'primary',
            self::CART_REMOVE => 'secondary',
            self::ORDER_PLACED => 'success',
            self::ORDER_PAID => 'success',
            self::ORDER_CANCELLED => 'danger',
            self::ORDER_COMPLETED => 'success',
            self::ORDER_REORDER => 'info',
            self::RETURN_REFUND_REQUESTED => 'warning',
            self::RETURN_REFUND_APPROVED => 'info',
            self::RETURN_REFUND_REJECTED => 'danger',
            self::RETURN_REFUND_COMPLETED => 'success',
            self::REVIEW_SUBMITTED => 'primary',
            self::MESSAGE_SENT => 'info',
            self::CHAT_STATUS_UPDATED => 'info',
            self::RIDER_ASSIGNED => 'primary',
            self::ORDER_HANDED_TO_RIDER => 'primary',
            self::ADMIN_ORDER_PROCESSED => 'success',
            self::PRODUCT_CREATED => 'success',
            self::PRODUCT_UPDATED => 'primary',
            self::PRODUCT_DELETED => 'danger',
            self::REVIEW_APPROVED => 'success',
            self::REVIEW_REJECTED => 'warning',
            self::DELIVERY_ACCEPTED => 'info',
            self::DELIVERY_PICKED_UP => 'info',
            self::DELIVERY_STARTED => 'primary',
            self::DELIVERY_COMPLETED => 'success',
            self::DELIVERY_FAILED => 'danger',
            self::ORDER_CANCELLED_AT_DOOR => 'warning',
            self::RETURN_PICKUP_ACCEPTED => 'info',
            self::RETURN_PICKUP_COMPLETED => 'success',
            self::RIDER_ASSIGNMENT_DECLINED => 'warning',
        ];
    }
}
