<?php

namespace App\Services;

use App\Models\NotifyLogs;
use App\DTO\NotifyLogData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class NotifyLogsService
{
    /**
     * Ambil query notifikasi untuk user yang login
     */
    public function getNotifyLogsQuery(Request $request, Authenticatable $user)
    {
        $query = NotifyLogs::with('senderUser')->where('receiver', $user->id);
        if ($request->filled('notify_type')) {
            $query->where('notify_type', $request->notify_type);
        }
        return $query;
    }

    public function findOrFail(int $id): NotifyLogs
    {
        return NotifyLogs::findOrFail($id);
    }

    public function markAsRead(NotifyLogs $log): NotifyLogs
    {
        $log->is_read = true;
        $log->save();
        return $log;
    }

    public function markAllAsRead(?string $notifyType, int $receiverId): \Illuminate\Support\Collection
    {
        $query = NotifyLogs::where('receiver', $receiverId);
        if ($notifyType) {
            $query->where('notify_type', $notifyType);
        }
        $logs = $query->get();
        foreach ($logs as $log) {
            $log->is_read = true;
            $log->save();
        }
        return $logs;
    }

    public function deleteNotifyLog(NotifyLogs $log): void
    {
        $log->delete();
    }
}