<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\BuyerNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification to buyer
     */
    public function sendNotification($buyerId, $type, $title, $message, $data = [], $channels = ['database', 'realtime'])
    {
        $buyer = Buyer::find($buyerId);
        if (!$buyer) {
            return false;
        }

        try {
            $notification = [
                'id' => uniqid('notif_'),
                'buyer_id' => $buyerId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'read' => false,
                'created_at' => now(),
                'channels' => $channels
            ];

            // Store in database (simplified - would use a notifications table)
            $this->storeNotification($notification);

            // Send via different channels
            foreach ($channels as $channel) {
                switch ($channel) {
                    case 'realtime':
                        $this->sendRealtimeNotification($notification);
                        break;
                    case 'email':
                        $this->sendEmailNotification($buyer, $notification);
                        break;
                    case 'push':
                        $this->sendPushNotification($buyer, $notification);
                        break;
                    case 'sms':
                        $this->sendSMSNotification($buyer, $notification);
                        break;
                }
            }

            Log::info("Notification sent successfully", [
                'buyer_id' => $buyerId,
                'type' => $type,
                'title' => $title,
                'channels' => $channels
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send notification", [
                'buyer_id' => $buyerId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Store notification in cache/database
     */
    private function storeNotification($notification)
    {
        $buyerNotifications = Cache::get("buyer_notifications_{$notification['buyer_id']}", []);
        array_unshift($buyerNotifications, $notification);
        
        // Keep only last 50 notifications
        $buyerNotifications = array_slice($buyerNotifications, 0, 50);
        
        Cache::put("buyer_notifications_{$notification['buyer_id']}", $buyerNotifications, 86400); // 24 hours
    }

    /**
     * Get buyer notifications
     */
    public function getBuyerNotifications($buyerId, $limit = 20, $filter = 'all')
    {
        $notifications = Cache::get("buyer_notifications_{$buyerId}", []);
        
        // Apply filters
        if ($filter !== 'all') {
            $notifications = array_filter($notifications, function($notification) use ($filter) {
                if ($filter === 'unread') {
                    return !$notification['read'];
                }
                return $notification['type'] === $filter;
            });
        }
        
        return array_slice($notifications, 0, $limit);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($buyerId, $notificationId)
    {
        $notifications = Cache::get("buyer_notifications_{$buyerId}", []);
        
        foreach ($notifications as &$notification) {
            if ($notification['id'] === $notificationId) {
                $notification['read'] = true;
                break;
            }
        }
        
        Cache::put("buyer_notifications_{$buyerId}", $notifications, 86400);
        return true;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($buyerId)
    {
        $notifications = Cache::get("buyer_notifications_{$buyerId}", []);
        
        foreach ($notifications as &$notification) {
            $notification['read'] = true;
        }
        
        Cache::put("buyer_notifications_{$buyerId}", $notifications, 86400);
        return true;
    }

    /**
     * Send real-time notification via WebSocket
     */
    private function sendRealtimeNotification($notification)
    {
        // In a real implementation, this would push to WebSocket server
        // For now, we'll store in a real-time cache for polling
        
        $realtimeKey = "realtime_notifications_{$notification['buyer_id']}";
        $realtimeNotifications = Cache::get($realtimeKey, []);
        
        array_unshift($realtimeNotifications, [
            'id' => $notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'timestamp' => now()->toISOString()
        ]);
        
        // Keep only last 10 real-time notifications
        $realtimeNotifications = array_slice($realtimeNotifications, 0, 10);
        
        Cache::put($realtimeKey, $realtimeNotifications, 300); // 5 minutes
        
        Log::info("Real-time notification queued", [
            'buyer_id' => $notification['buyer_id'],
            'notification_id' => $notification['id']
        ]);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($buyer, $notification)
    {
        // Check if buyer has email notifications enabled
        $preferences = $buyer->preferences ?? [];
        $emailSettings = $preferences['email_notifications'] ?? [];
        
        $settingKey = $this->getEmailSettingKey($notification['type']);
        if (!($emailSettings[$settingKey] ?? true)) {
            return; // Email notifications disabled for this type
        }

        try {
            // In a real implementation, you'd send actual emails
            // For now, we'll just log it
            Log::info("Email notification sent", [
                'buyer_email' => $buyer->email,
                'subject' => $notification['title'],
                'type' => $notification['type']
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send email notification", [
                'buyer_id' => $buyer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification($buyer, $notification)
    {
        // Check if buyer has push notifications enabled
        $preferences = $buyer->preferences ?? [];
        $pushSettings = $preferences['push_notifications'] ?? [];
        
        if (!($pushSettings['browser_notifications'] ?? true)) {
            return;
        }

        // In a real implementation, you'd use a push notification service
        Log::info("Push notification sent", [
            'buyer_id' => $buyer->id,
            'title' => $notification['title'],
            'type' => $notification['type']
        ]);
    }

    /**
     * Send SMS notification
     */
    private function sendSMSNotification($buyer, $notification)
    {
        // SMS notifications for critical alerts only
        if (!in_array($notification['type'], ['low_balance', 'payment_failed', 'account_suspended'])) {
            return;
        }

        // In a real implementation, you'd use Twilio or similar service
        Log::info("SMS notification sent", [
            'buyer_phone' => $buyer->phone,
            'message' => $notification['message'],
            'type' => $notification['type']
        ]);
    }

    /**
     * Get email setting key for notification type
     */
    private function getEmailSettingKey($type)
    {
        $mapping = [
            'new_lead' => 'new_leads',
            'payment_success' => 'payment_confirmations',
            'payment_failed' => 'payment_confirmations',
            'low_balance' => 'low_balance_warnings',
            'lead_returned' => 'new_leads',
            'contract_signed' => 'system_updates',
            'account_activated' => 'system_updates'
        ];

        return $mapping[$type] ?? 'system_updates';
    }

    /**
     * Send lead notification
     */
    public function sendLeadNotification($buyerId, $leadData)
    {
        $title = "New Lead Available";
        $message = sprintf(
            "%s Insurance lead from %s - %s, age %s. Price: $%.2f",
            ucfirst($leadData['vertical'] ?? 'Insurance'),
            $leadData['city'] ?? 'Unknown',
            $leadData['name'] ?? 'Unknown',
            $leadData['age'] ?? 'Unknown',
            $leadData['price'] ?? 0
        );

        return $this->sendNotification(
            $buyerId,
            'new_lead',
            $title,
            $message,
            $leadData,
            ['database', 'realtime', 'email', 'push']
        );
    }

    /**
     * Send payment notification
     */
    public function sendPaymentNotification($buyerId, $paymentData)
    {
        $title = $paymentData['status'] === 'completed' 
            ? "Payment Processed Successfully" 
            : "Payment Failed";
            
        $message = $paymentData['status'] === 'completed'
            ? sprintf("Your account has been credited $%.2f. New balance: $%.2f", 
                $paymentData['amount'], $paymentData['new_balance'])
            : sprintf("Payment of $%.2f failed. Reason: %s", 
                $paymentData['amount'], $paymentData['failure_reason'] ?? 'Unknown error');

        return $this->sendNotification(
            $buyerId,
            $paymentData['status'] === 'completed' ? 'payment_success' : 'payment_failed',
            $title,
            $message,
            $paymentData,
            ['database', 'realtime', 'email']
        );
    }

    /**
     * Send balance alert
     */
    public function sendBalanceAlert($buyerId, $currentBalance, $threshold)
    {
        $title = "Low Balance Alert";
        $message = sprintf(
            "Your account balance ($%.2f) is below your threshold ($%.2f). Consider adding funds to continue receiving leads.",
            $currentBalance,
            $threshold
        );

        return $this->sendNotification(
            $buyerId,
            'low_balance',
            $title,
            $message,
            ['balance' => $currentBalance, 'threshold' => $threshold],
            ['database', 'realtime', 'email', 'sms']
        );
    }

    /**
     * Send system notification
     */
    public function sendSystemNotification($buyerId, $title, $message, $data = [])
    {
        return $this->sendNotification(
            $buyerId,
            'system',
            $title,
            $message,
            $data,
            ['database', 'realtime', 'email']
        );
    }

    /**
     * Get real-time notifications for polling
     */
    public function getRealtimeNotifications($buyerId)
    {
        $realtimeKey = "realtime_notifications_{$buyerId}";
        $notifications = Cache::get($realtimeKey, []);
        
        // Clear after retrieving
        Cache::forget($realtimeKey);
        
        return $notifications;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats($buyerId)
    {
        $notifications = Cache::get("buyer_notifications_{$buyerId}", []);
        
        $stats = [
            'total' => count($notifications),
            'unread' => count(array_filter($notifications, fn($n) => !$n['read'])),
            'today' => count(array_filter($notifications, fn($n) => 
                \Carbon\Carbon::parse($n['created_at'])->isToday()
            )),
            'by_type' => []
        ];
        
        foreach ($notifications as $notification) {
            $type = $notification['type'];
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        }
        
        return $stats;
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences($buyerId, $preferences)
    {
        $buyer = Buyer::find($buyerId);
        if (!$buyer) {
            return false;
        }

        $currentPreferences = $buyer->preferences ?? [];
        $currentPreferences['email_notifications'] = $preferences['email'] ?? [];
        $currentPreferences['push_notifications'] = $preferences['push'] ?? [];
        $currentPreferences['sms_notifications'] = $preferences['sms'] ?? [];

        $buyer->update(['preferences' => $currentPreferences]);

        Log::info("Notification preferences updated", [
            'buyer_id' => $buyerId,
            'preferences' => $preferences
        ]);

        return true;
    }
}