<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a user activity in the clinic system.
     *
     * @param string $action create|update|delete|attendance|dosage|payment|login|logout
     * @param string $module patients|visits|therapy|weight_loss|payments|settings|staff|roles|auth
     * @param string $description Detailed human-readable explanation in Arabic
     * @param int|null $patientId
     * @param int|null $recordId
     * @param int|null $userId
     * @return ActivityLog|null
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?int $patientId = null,
        ?int $recordId = null,
        ?int $userId = null
    ): ?ActivityLog {
        try {
            $effectiveUserId = $userId ?? Auth::id();

            return ActivityLog::create([
                'user_id' => $effectiveUserId,
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'patient_id' => $patientId,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently fail if logging encounters an issue so it never breaks critical business transactions
            report($e);
            return null;
        }
    }
}
