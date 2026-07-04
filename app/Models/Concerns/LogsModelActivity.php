<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Automatic activity logging for core domain models. Records create/update/
 * delete of fillable attributes (only the ones that actually changed) at the
 * `debug` level, so these entries only surface when LOG_LEVEL is set to
 * `debug`. Sensitive attributes are always excluded.
 */
trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password', 'remember_token'])
            ->dontSubmitEmptyLogs();
    }
}
