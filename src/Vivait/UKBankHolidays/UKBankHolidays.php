<?php

namespace Vivait\UKBankHolidays;

use DateTime;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use Vivait\UKBankHolidays\Client\HttpClient;
use Vivait\UKBankHolidays\Exception\TooFarInFutureException;
use Vivait\UKBankHolidays\Exception\TooManyNonWorkingDays;
use Vivait\UKBankHolidays\Region\Region;

class UKBankHolidays
{
    private HttpClient $client;
    private CacheInterface $cache;
    private Region $region;

    public const CACHE_KEY = 'vivait_uk_bank_holidays';

    public function __construct(HttpClient $client, Region $region, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->region = $region;
    }


    private function getAllHolidays(): array
    {
        $cached_holidays = $this->cache->get(self::CACHE_KEY, null);
        if($cached_holidays !== null) {
            return json_decode($cached_holidays, JSON_THROW_ON_ERROR, 512, JSON_THROW_ON_ERROR);
        }

        $all_region_holidays = json_decode(
            $this->client->getAllHolidays(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $holidays = [];
        foreach ($all_region_holidays as $region) {
            foreach ($region['events'] as $holiday_date) {
                $holidays[] = $region['division'] . '-' . DateTime::createFromFormat('Y-m-d', $holiday_date['date'])->format('Y-m-d');
            }
        }

        $this->cache->set(self::CACHE_KEY, json_encode($holidays, JSON_THROW_ON_ERROR, 512), 86400);

        return $holidays;
    }

    public function isBankHoliday(\DateTimeInterface $date): bool
    {
        $all_holidays = $this->getAllHolidays();
        return in_array($this->region->getRegion() . '-' . $date->format('Y-m-d'), $all_holidays, true);
    }


    public function isWeekend(\DateTimeInterface $date): bool
    {
        return ($date->format('N') >= 6);
    }

    /**
     * @param DateTimeInterface $date
     * @return DateTimeInterface
     * @throws \JsonException
     */
    public function nextWorkingDayFrom(\DateTimeInterface $date): DateTimeInterface
    {
        $nextWorkingDay = $this->cloneDateTime($date);

        $iterations = 0;

        do {
            if (++$iterations > 10) {
                throw TooManyNonWorkingDays::withDates($date, $nextWorkingDay);
            }

            $nextWorkingDay->add(new \DateInterval('P1D'));
        } while (
            $this->isBankHoliday($nextWorkingDay)
            || $this->isWeekend($nextWorkingDay)
        );

        return $nextWorkingDay;
    }

    /**
     * @param DateTimeInterface $date
     * @return DateTimeInterface
     * @throws TooFarInFutureException
     * @throws \JsonException
     */
    public function nextBankHoliayFrom(\DateTimeInterface $date): DateTimeInterface
    {
        $nextHoliday = $this->cloneDateTime($date);

        $all_holidays = $this->getAllHolidays();

        $iterations = 0;

        do {
            $nextHoliday->add(new \DateInterval('P1D'));
            if (++$iterations > 120) {
                throw TooFarInFutureException::withDates($date, $nextHoliday);
            }

        } while (
            !in_array($this->region->getRegion() . '-' . $nextHoliday->format('Y-m-d'), $all_holidays, true)
        );

        return $nextHoliday;
    }

    private function cloneDateTime(DateTimeInterface $date): DateTime
    {
        return DateTime::createFromFormat(
            DateTimeInterface::ATOM,
            $date->format(DateTimeInterface::ATOM)
        );
    }

}