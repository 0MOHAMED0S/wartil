<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            // Validate and cap the 'per_page' parameter to prevent overloading the database (Max 50)
            $perPage = $request->query('per_page', 15);
            $perPage = is_numeric($perPage) ? (int) $perPage : 15;
            $perPage = max(1, min(50, $perPage));

            $notifications = $user->notifications()->paginate($perPage);

            // Transform the collection to safely handle missing data keys
            $notifications->getCollection()->transform(function ($notification) {
                $data = $notification->data;

                return [
                    'id'         => $notification->id,
                    'title'      => $data['title'] ?? 'إشعار جديد',
                    'message'    => $data['message'] ?? '',
                    'type'       => $data['type'] ?? 'general',
                    'payload'    => $data['payload'] ?? null,
                    'is_read'    => $notification->read_at !== null,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'date'       => $notification->created_at->format('Y-m-d'),
                    'time'       => $notification->created_at->format('h:i A'),
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'تم استرجاع الإشعارات بنجاح.',
                'data'    => [
                    'unread_count'  => $user->unreadNotifications()->count(),
                    'notifications' => $notifications
                ]
            ], 200);
        } catch (\Throwable $e) {
            
            // Log detailed error for debugging
            Log::error('Get Notifications Error', [
                'user_id' => optional(auth()->user())->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء جلب الإشعارات.'
            ], 500);
        }
    }

    // Delete a single notification (student or teacher)
    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            // Validate notification_id
            $notificationId = $request->input('notification_id');
            if (empty($notificationId) || !is_numeric($notificationId)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'معرّف الإشعار غير صالح.',
                ], 422);
            }

            $notification = $user->notifications()->where('id', $notificationId)->first();
            if (!$notification) {
                return response()->json([
                    'status'  => false,
                    'message' => 'الإشعار غير موجود أو لا يخص المستخدم.',
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف الإشعار بنجاح.',
                'data'    => [
                    'unread_count' => $user->unreadNotifications()->count(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Delete Notification Error', [
                'user_id' => optional(auth()->user())->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء حذف الإشعار.',
            ], 500);
        }
    }

    // Delete all notifications for the authenticated user (student or teacher)
    public function destroyAll(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            $user->notifications()->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف جميع الإشعارات بنجاح.',
                'data'    => [
                    'unread_count' => 0,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Delete All Notifications Error', [
                'user_id' => optional(auth()->user())->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء حذف جميع الإشعارات.',
            ], 500);
        }
    }
    

    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'المستخدم غير مسجل الدخول.',
                ], 401);
            }

            if ($request->has('notification_id')) {
                // Ensure the notification belongs to the authenticated user securely
                $notification = $user->notifications()->where('id', $request->notification_id)->first();
                if ($notification) {
                    $notification->markAsRead();
                }
            } else {
                // Mark all unread notifications as read
                $user->unreadNotifications->markAsRead();
            }

            return response()->json([
                'status'  => true,
                'message' => 'تم تحديث حالة الإشعارات بنجاح.',
                'data'    => [
                    'unread_count' => $user->unreadNotifications()->count()
                ]
            ], 200);
        } catch (\Throwable $e) {
            
            // Log detailed error for debugging
            Log::error('Mark Notifications Read Error', [
                'user_id' => optional(auth()->user())->id,
                'ip'      => $request->ip(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الإشعارات.'
            ], 500);
        }
    }
}
