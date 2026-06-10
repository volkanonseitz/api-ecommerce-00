<?php

namespace App\Http\Controllers;

use App\Services\NotifyLogsService;
use App\Http\Resources\NotifyLogResource;
use App\Models\NotifyLogs;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class NotifyLogsController extends Controller
{
    public function __construct(private NotifyLogsService $notifyService) {}

    /**
     * GET /notify-logs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $limit = $request->limit ?? 10;
        $logs = $this->notifyService->getNotifyLogsQuery($request, $user)->paginate($limit);
        return NotifyLogResource::collection($logs);
    }

    /**
     * GET /notify-logs/{id}
     */
    public function show(Request $request, $id)
    {
        $log = $this->notifyService->findOrFail($id);
        return new NotifyLogResource($log);
    }

    /**
     * DELETE /notify-logs/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $log = NotifyLogs::findOrFail($id);
        $this->notifyService->deleteNotifyLog($log);
        return response()->json(['message' => 'Notify log deleted']);
    }

    /**
     * POST /notify-logs/read
     */
    public function readNotifyLogs(Request $request)
    {
        $request->validate(['id' => 'required|exists:notify_logs,id']);
        $log = NotifyLogs::findOrFail($request->id);
        $updated = $this->notifyService->markAsRead($log);
        return new NotifyLogResource($updated);
    }

    /**
     * POST /notify-logs/read-all
     */
    public function readAllNotifyLogs(Request $request)
    {
        $request->validate([
            'receiver' => 'required|exists:users,id',
            'notify_type' => 'nullable|string',
            'set_all_read' => 'required|boolean'
        ]);
        $logs = $this->notifyService->markAllAsRead($request->notify_type, $request->receiver);
        return NotifyLogResource::collection($logs);
    }
}