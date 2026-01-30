<?php

namespace App\DataTransferObjects;

use App\Models\Church;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents a resolved activity for a specific date.
 *
 * This is the output format when computing activities - it contains all
 * the resolved values after applying day details, rules, and overrides.
 *
 * @implements Arrayable<string, mixed>
 */
readonly class ComputedActivity implements Arrayable
{
    /**
     * Source types for tracking where the activity came from.
     */
    public const SOURCE_SCHEDULE = 'schedule';

    public const SOURCE_LITURGICAL = 'liturgical';

    public const SOURCE_ONE_TIME = 'one_time';

    public function __construct(
        public int $churchId,
        public string $type,
        public string $startTime,
        public ?string $endTime,
        public string $language,
        public ?string $activityTypeDescription,
        public Carbon $date,
        public string $source,
        public ?int $sourceId = null,
        public ?string $holidayName = null,
        public array $tags = [],
        public ?Church $church = null,
    ) {}

    /**
     * Create a ComputedActivity from an ActivitySchedule.
     *
     * @param  \App\Models\ActivitySchedule  $schedule
     * @param  array{start_time: string, end_time: string|null, language: string, activity_type_description: string|null, is_skipped: bool}  $resolvedValues
     */
    public static function fromSchedule(
        $schedule,
        Carbon $date,
        array $resolvedValues,
        array $tags = [],
    ): self {
        return new self(
            churchId: $schedule->church_id,
            type: $schedule->type,
            startTime: $resolvedValues['start_time'],
            endTime: $resolvedValues['end_time'],
            language: $resolvedValues['language'],
            activityTypeDescription: $resolvedValues['activity_type_description'],
            date: $date,
            source: self::SOURCE_SCHEDULE,
            sourceId: $schedule->id,
            holidayName: null,
            tags: $tags,
            church: $schedule->relationLoaded('church') ? $schedule->church : null,
        );
    }

    /**
     * Create a ComputedActivity from a LiturgicalScheduleTime.
     *
     * @param  \App\Models\LiturgicalScheduleTime  $time
     * @param  \App\Models\LiturgicalSchedule  $schedule
     */
    public static function fromLiturgicalTime(
        $time,
        $schedule,
        Carbon $date,
        array $tags = [],
    ): self {
        return new self(
            churchId: $schedule->church_id,
            type: $time->type,
            startTime: $time->start_time,
            endTime: $time->end_time,
            language: $time->language,
            activityTypeDescription: $time->activity_type_description,
            date: $date,
            source: self::SOURCE_LITURGICAL,
            sourceId: $time->id,
            holidayName: $schedule->holiday?->name,
            tags: $tags,
            church: $schedule->relationLoaded('church') ? $schedule->church : null,
        );
    }

    /**
     * Create a ComputedActivity from a OneTimeActivity.
     *
     * @param  \App\Models\OneTimeActivity  $activity
     */
    public static function fromOneTimeActivity(
        $activity,
        array $tags = [],
    ): self {
        return new self(
            churchId: $activity->church_id,
            type: $activity->type,
            startTime: $activity->start_time,
            endTime: $activity->end_time,
            language: $activity->language,
            activityTypeDescription: $activity->activity_type_description,
            date: $activity->date,
            source: self::SOURCE_ONE_TIME,
            sourceId: $activity->id,
            holidayName: null,
            tags: $tags,
            church: $activity->relationLoaded('church') ? $activity->church : null,
        );
    }

    /**
     * Create a new instance with a modified start time.
     */
    public function withStartTime(string $startTime): self
    {
        return new self(
            churchId: $this->churchId,
            type: $this->type,
            startTime: $startTime,
            endTime: $this->endTime,
            language: $this->language,
            activityTypeDescription: $this->activityTypeDescription,
            date: $this->date,
            source: $this->source,
            sourceId: $this->sourceId,
            holidayName: $this->holidayName,
            tags: $this->tags,
            church: $this->church,
        );
    }

    /**
     * Create a new instance with a church model attached.
     */
    public function withChurch(Church $church): self
    {
        return new self(
            churchId: $this->churchId,
            type: $this->type,
            startTime: $this->startTime,
            endTime: $this->endTime,
            language: $this->language,
            activityTypeDescription: $this->activityTypeDescription,
            date: $this->date,
            source: $this->source,
            sourceId: $this->sourceId,
            holidayName: $this->holidayName,
            tags: $this->tags,
            church: $church,
        );
    }

    /**
     * Get a unique key for deduplication.
     */
    public function getUniqueKey(): string
    {
        return sprintf(
            '%d-%s-%s-%s',
            $this->churchId,
            $this->type,
            $this->startTime,
            $this->date->toDateString()
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'church_id' => $this->churchId,
            'type' => $this->type,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'language' => $this->language,
            'activity_type_description' => $this->activityTypeDescription,
            'date' => $this->date->toDateString(),
            'source' => $this->source,
            'source_id' => $this->sourceId,
            'holiday_name' => $this->holidayName,
            'tags' => $this->tags,
        ];
    }
}
