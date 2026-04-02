<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOldAuditLog implements ShouldQueue
{
    use Queueable, Dispatchable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = now()->subHours(3);

        Log::info('Checking audit logs older than 3 hours...');

        $query = \Illuminate\Support\Facades\DB::table('audit_logs')
            ->where('created_at', '<=', $date);

        $count = $query->count();

        if ($count === 0) {
            Log::info('No audit logs older than 3 hours found.');
            return;
        }

        Log::info("Found {$count} records. Deleting...");

        $query->delete();

        Log::info('Old audit logs deleted successfully.');
    }
}
