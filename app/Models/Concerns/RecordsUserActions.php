<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;
use App\Models\UserActionLog;
use App\Events\Actions\RecordModelEvent;

trait RecordsUserActions
{
    public $attributesBeforeRecording = [];

    public static function bootRecordsUserActions()
    {
        static::updating(function ($model) {
            $model->attributesBeforeRecording = $model->getOriginal();
        });

        foreach (static::getRecordableEvents() as $event) {
            static::$event(fn ($model) => $model->recordEvent($event));
        }
    }

    public function recordEvent(string $event, $changes = null)
    {
        return (new RecordModelEvent($this, $event))->__invoke($changes);
    }

    public function recordedEvents()
    {
        return $this->morphMany(UserActionLog::class, 'entity');
    }

    public function getRecordableEventMetaData($event): array
    {
        return [];
    }

    public static function getRecordableEvents()
    {
        return ['created', 'updated', 'deleted'];
    }

    public function getRecordableAttributes()
    {
        return array_keys(Arr::except($this->getattributes(), ['updated_at', 'created_at']));
    }
}
