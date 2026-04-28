<?php

namespace App\Traits;

use App\Services\AuditEngine;
use Illuminate\Support\Arr;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     * This listens to Eloquent's lifecycle events silently in the background.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditEngine::log('RECORD', 'DATA.CREATED', self::cleanLogData($model, $model->toArray()), [], $model);
        });

        static::updated(function ($model) {
            $newValues = $model->getDirty();
            $oldValues = Arr::only($model->getOriginal(), array_keys($newValues));

            // Only log if something actually changed
            if (!empty($newValues)) {
                AuditEngine::log('RECORD', 'DATA.UPDATED', self::cleanLogData($model, $newValues), self::cleanLogData($model, $oldValues), $model);
            }
        });

        static::deleted(function ($model) {
            AuditEngine::log('RECORD', 'DATA.DELETED', [], self::cleanLogData($model, $model->toArray()), $model);
        });
    }

    /**
     * Security measure: We strip out passwords, tokens, and other hidden fields
     * so they don't get saved in plain text inside the JSON audit logs.
     */
    private static function cleanLogData($model, array $data)
    {
        return Arr::except($data, $model->getHidden());
    }
}
