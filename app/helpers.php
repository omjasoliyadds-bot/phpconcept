<?php

use App\Jobs\ProcessAuditLog;

if (!function_exists('auditLog')) {
    function auditLog($action, $module = null, $description = null, $old = null, $new = null, $recordId = null, $userId = null)
    {
        $data = [
            'user_id' => $userId ?? auth()->id(),
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        ProcessAuditLog::dispatch($data);
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = (float) max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = (int) min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}