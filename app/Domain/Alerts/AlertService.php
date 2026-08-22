<?php

namespace App\Domain\Alerts;

use App\Models\Alert;
use App\Models\User;
use App\Notifications\ServerAlertNotification;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    public function trigger(string $type, string $severity, string $fingerprint, array $context = []): Alert
    {
        $alert = Alert::updateOrCreate(['fingerprint' => $fingerprint], [
            'type' => $type, 'severity' => $severity, 'context' => $context,
            'triggered_at' => now(), 'resolved_at' => null,
        ]);

        if (! $alert->notified_at || $alert->notified_at->addMinutes((int) config('monitoring.alert_cooldown_minutes', 30))->isPast()) {
            Notification::route('mail', config('monitoring.admin_email'))->notify(new ServerAlertNotification($type, $severity, $context));
            $alert->update(['notified_at' => now()]);
        }

        return $alert;
    }

    public function resolve(string $fingerprint): void
    {
        Alert::where('fingerprint', $fingerprint)->whereNull('resolved_at')->update(['resolved_at' => now()]);
    }
}
