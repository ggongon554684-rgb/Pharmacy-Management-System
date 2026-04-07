<?php

namespace App\Observers;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    public Function created(Model $model): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'old_values' => null,
            'new_values' => $model->getAttributes(),
        ]);

    }

    public function updated(Model $model): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'old_values' => $model->getOriginal(),
            'new_values' => $model->getAttributes(),
        ]);
    }

    public function deleted(Model $model): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'auditable_id' => $model->id,
            'auditable_type' => get_class($model),
            'old_values' => $model->getOriginal(),
            'new_values' => null,
        ]);
    }
}
