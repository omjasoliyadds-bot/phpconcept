<?php
use App\Models\AuditLog;
function auditLog($action, $module = null, $description = null, $old = null, $new = null, $recordId = null, $userId = null)
{
    AuditLog::create([
        'user_id' => $userId ?? auth()->id(),
        'module' => $module,
        'action' => $action,
        'record_id' => $recordId,
        'description' => $description,
        'old_values' => $old,
        'new_values' => $new,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}