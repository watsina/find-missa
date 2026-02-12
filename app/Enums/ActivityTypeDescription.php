<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityTypeDescription: string
{
    use EnumToArray;

    case Mass = 'mass';
    case Confession = 'confession';
    case Adoration = 'adoration';
    case Benediction = 'benediction';
    case Exposition = 'exposition';
    case TraditionalLatinMass = 'traditional-latin-mass';
    case AnticipationMass = 'anticipation-mass';
    case MassOfTheLordsSupper = 'mass-of-the-lords-supper';
    case GoodFridayService = 'good-friday-service';
    case EasterVigilMass = 'easter-vigil-mass';
    case EasterMass = 'easter-mass';
    case EasterMondayMass = 'easter-monday-mass';
    case ChristmasVigilMass = 'christmas-vigil-mass';
    case ChristmasMass = 'christmas-mass';

    public static function getValidDescriptionsForType(string $type): array
    {
        return match ($type) {
            ActivityType::Mass->value => [
                self::Mass->value,
                self::TraditionalLatinMass->value,
                self::AnticipationMass->value,
                self::MassOfTheLordsSupper->value,
                self::GoodFridayService->value,
                self::EasterVigilMass->value,
                self::EasterMass->value,
                self::EasterMondayMass->value,
                self::ChristmasVigilMass->value,
                self::ChristmasMass->value,
            ],
            ActivityType::Confession->value => [self::Confession->value],
            ActivityType::Adoration->value => [self::Adoration->value, self::Exposition->value, self::Benediction->value],
            default => [],
        };
    }
}
