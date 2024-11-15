<?php

namespace App\Events\Actions;

use Illuminate\Support\Arr;
use App\Events\Store\EventStore;
use Illuminate\Database\Eloquent\Model;

class RecordModelEvent
{
    protected $model;

    protected $event;

    public function __construct(Model $model, string $event)
    {
        $this->model = $model;

        $this->event = $event;
    }

    public function handle(?array $changes = [])
    {
        $before = $this->asJson($changes['before'] ?? $this->getBeforeAttributes());

        $after = $this->asJson($changes['after'] ?? $this->getAfterAttributes());

        // skip empty/ touch timestamps
        if ($this->event === 'updated' && empty($after)) {
            return;
        }

        $event = [
            'name' => $this->event,
            'entity_id' => $this->model->getKey(),
            'entity_type' => $this->model->getMorphClass(),
            'team_id' => session()->get('current_team_id'),
            'before' => $before,
            'after' => $after,
            'meta' => $this->asJson($changes['meta'] ?? $this->getMetaData()),
        ];

        EventStore::add($event);
    }

    protected function getMetaData()
    {
        if (method_exists($this->model, 'getRecordableEventMetaData')) {
            return $this->model->getRecordableEventMetaData($this->event);
        }
    }

    public function __invoke(...$args)
    {
        return $this->handle(...$args);
    }

    protected function getRecordableFields(array $data = [])
    {
        $keys =
            $this->model->getRecordableAttributes() ?:
            array_keys(Arr::except($this->model->getAttributes(), $this->model->getHidden()));

        return Arr::only($data, (array) $keys);
    }

    protected function getBeforeAttributes()
    {
        if (in_array($this->event, ['deleted', 'softDeleted'], true)) {
            return $this->getRecordableFields($this->model->getAttributes());
        }

        if ($this->event === 'updated') {
            return $this->getModelAttributesBeforeUpdate();
        }
    }

    protected function getAfterAttributes()
    {
        if ($this->event === 'updated') {
            return $this->getRecordableFields($this->model->getChanges());
        }

        if ($this->event === 'created') {
            return $this->getRecordableFields($this->model->getAttributes());
        }

        return [];
    }

    public function asJson($value)
    {
        return blank($value) ? null : json_encode($value);
    }

    protected function getModelAttributesBeforeUpdate()
    {
        $original = $this->model->attributesBeforeRecording ?: [];

        $changes = $this->getRecordableFields($this->model->getChanges());

        $result = [];

        foreach (Arr::dot($changes) as $key => $value) {
            $originalValue = Arr::get($original, $key);

            if ($originalValue !== $value) {
                Arr::set($result, $key, $originalValue);
            }
        }

        return $result;
    }
}
