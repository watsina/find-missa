<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Enums\HolidayType;
use Illuminate\Database\Seeder;
use App\Services\HolidayService;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = $this->getUniversalCatholicHolidays();

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['slug' => $holiday['slug']],
                $holiday
            );
        }

        $this->refreshInstances();
    }

    /**
     * Clear and regenerate holiday instances for the current and next year.
     *
     * Holiday instances are a computed cache of dates derived from holiday
     * definitions. When definitions change (e.g. easter_offset_days), stale
     * instances must be regenerated to reflect the updated values.
     */
    protected function refreshInstances(): void
    {
        $holidayService = app(HolidayService::class);
        $currentYear = (int) now()->year;
        $years = [$currentYear, $currentYear + 1];

        foreach ($years as $year) {
            $holidayService->clearInstancesForYear($year);
            $instances = $holidayService->computeInstancesForYear($year);

            $this->command?->info("Regenerated {$instances->count()} holiday instances for {$year}.");
        }
    }

    /**
     * Get all universal Catholic holidays to be seeded.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getUniversalCatholicHolidays(): array
    {
        return [
            ...$this->getFixedHolidays(),
            ...$this->getEasterRelativeHolidays(),
        ];
    }

    /**
     * Get fixed date holidays (same date every year).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFixedHolidays(): array
    {
        return [
            // Solemnities - Holy Days of Obligation
            [
                'name' => 'Christmas',
                'slug' => 'christmas',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 25,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Solemnity of Mary, Mother of God',
                'slug' => 'mary-mother-of-god',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 1,
                'fixed_day' => 1,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Assumption of the Blessed Virgin Mary',
                'slug' => 'assumption',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 8,
                'fixed_day' => 15,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'All Saints Day',
                'slug' => 'all-saints',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 11,
                'fixed_day' => 1,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Immaculate Conception',
                'slug' => 'immaculate-conception',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 8,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],

            // Other Fixed Solemnities & Major Feasts
            [
                'name' => 'Epiphany',
                'slug' => 'epiphany',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 1,
                'fixed_day' => 6,
                'is_holy_day_of_obligation' => false, // Obligation varies by country
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Presentation of the Lord (Candlemas)',
                'slug' => 'presentation-of-the-lord',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 2,
                'fixed_day' => 2,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Saint Joseph, Spouse of the Blessed Virgin Mary',
                'slug' => 'saint-joseph',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 3,
                'fixed_day' => 19,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Annunciation of the Lord',
                'slug' => 'annunciation',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 3,
                'fixed_day' => 25,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Nativity of Saint John the Baptist',
                'slug' => 'nativity-of-john-the-baptist',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 6,
                'fixed_day' => 24,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Saints Peter and Paul',
                'slug' => 'saints-peter-and-paul',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 6,
                'fixed_day' => 29,
                'is_holy_day_of_obligation' => false, // Obligation varies by country
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Transfiguration of the Lord',
                'slug' => 'transfiguration',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 8,
                'fixed_day' => 6,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Nativity of the Blessed Virgin Mary',
                'slug' => 'nativity-of-mary',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 9,
                'fixed_day' => 8,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Exaltation of the Holy Cross',
                'slug' => 'exaltation-of-the-holy-cross',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 9,
                'fixed_day' => 14,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'All Souls Day',
                'slug' => 'all-souls',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 11,
                'fixed_day' => 2,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'purple',
                'team_id' => null,
            ],

            // Advent & Christmas Season
            [
                'name' => 'Christmas Eve',
                'slug' => 'christmas-eve',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 24,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'purple',
                'team_id' => null,
            ],
            [
                'name' => 'Saint Stephen, First Martyr',
                'slug' => 'saint-stephen',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 26,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Saint John, Apostle and Evangelist',
                'slug' => 'saint-john-apostle',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 27,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Holy Innocents',
                'slug' => 'holy-innocents',
                'type' => HolidayType::Fixed->value,
                'fixed_month' => 12,
                'fixed_day' => 28,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => false,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Holy Family',
                'slug' => 'holy-family',
                'type' => HolidayType::Computed->value,
                'fixed_month' => null,
                'fixed_day' => null,
                'computation_rule' => ['special' => 'sunday_in_octave_of_christmas'],
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
        ];
    }

    /**
     * Get Easter-relative holidays (moveable feasts).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getEasterRelativeHolidays(): array
    {
        return [
            // Lent
            [
                'name' => 'Ash Wednesday',
                'slug' => 'ash-wednesday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -46,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'purple',
                'team_id' => null,
            ],
            [
                'name' => 'First Sunday of Lent',
                'slug' => 'first-sunday-of-lent',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -42,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'purple',
                'team_id' => null,
            ],

            // Holy Week
            [
                'name' => 'Palm Sunday',
                'slug' => 'palm-sunday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -7,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Holy Thursday',
                'slug' => 'holy-thursday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -3,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'suppresses_regular_schedule' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Good Friday',
                'slug' => 'good-friday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -2,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'suppresses_regular_schedule' => true,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Holy Saturday',
                'slug' => 'holy-saturday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => -1,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'suppresses_regular_schedule' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],

            // Easter
            [
                'name' => 'Easter Sunday',
                'slug' => 'easter-sunday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 0,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Easter Monday',
                'slug' => 'easter-monday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 1,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Divine Mercy Sunday',
                'slug' => 'divine-mercy-sunday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 7,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],

            // Post-Easter Solemnities
            [
                'name' => 'Ascension of the Lord',
                'slug' => 'ascension',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 39,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Pentecost Sunday',
                'slug' => 'pentecost',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 49,
                'is_holy_day_of_obligation' => true,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'red',
                'team_id' => null,
            ],
            [
                'name' => 'Trinity Sunday',
                'slug' => 'trinity-sunday',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 56,
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Corpus Christi',
                'slug' => 'corpus-christi',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 60, // Thursday after Trinity Sunday (or Sunday in some countries)
                'is_holy_day_of_obligation' => false, // Varies by country
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
            [
                'name' => 'Sacred Heart of Jesus',
                'slug' => 'sacred-heart',
                'type' => HolidayType::EasterRelative->value,
                'easter_offset_days' => 68, // Friday after Corpus Christi (19 days after Pentecost)
                'is_holy_day_of_obligation' => false,
                'is_universal' => true,
                'is_moveable' => true,
                'liturgical_color' => 'white',
                'team_id' => null,
            ],
        ];
    }
}
