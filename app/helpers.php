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
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = (float) max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = (int) min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}
