<?php

namespace App\Events\Store;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class EventStore
{
    protected static $events = [];

    public static $persistOnCount = 5;

    public static $isRecording = true;

    public static function withoutRecording()
    {
        static::$isRecording = false;
    }

    public static function recording($value = true)
    {
        static::$isRecording = $value;
    }

    public static function persistOnCount(int $value)
    {
        static::$persistOnCount = $value;
    }

    public static function add(array $event)
    {
        if (! static::$isRecording) {
            return;
        }
        // since we are collecting these fields from multiple places and chunking for insert
        // we need to make sure that all fields are specified for batch inserts to work
        $defaults = [
            'event_id' => strtolower((string) Str::ulid()),
            'occurred_at' => Date::now(),
            'user_id' => Auth::id(),
            'before' => null,
            'after' => null,
            'meta' => null,
        ];

        static::$events[] = array_merge($defaults, $event);

        if (count(static::$events) >= static::$persistOnCount) {
            static::persistEvents();
        }
    }

    public static function getEvents()
    {
        return static::$events;
    }

    public static function flush()
    {
        return static::persistEvents();
    }

    public static function persistEvents()
    {
        $events = static::$events;

        if (empty($events)) {
            return;
        }

        static::$events = [];

        collect($events)
            ->chunk(50)
            ->each(function ($data) {
                DB::table('user_action_logs')->insert($data->toArray());
            });
    }
}
