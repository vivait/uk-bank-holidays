<?php


namespace Vivait\UKBankHolidays\Region;

use Exception;

class Region

{
    public const ENGLAND_AND_WALES = 'england-and-wales';
    public const SCOTLAND = 'scotland';
    public const NORTHERN_IRELAND = 'northern-ireland';

    public const ALL_REGIONS = [
        self::ENGLAND_AND_WALES,
        self::NORTHERN_IRELAND,
        self::SCOTLAND,
    ];

    private string $region;

    public function __construct(string $region)
    {
        if (!self::isValidRegion($region)) {
            throw new Exception('Invalid region');
        }
        $this->region = $region;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    private static function isValidRegion(string $region): bool
    {
        return in_array($region, self::ALL_REGIONS);
    }
}