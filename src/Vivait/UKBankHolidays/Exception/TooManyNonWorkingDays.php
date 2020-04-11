<?php

namespace Vivait\UKBankHolidays\Exception;

use DateTimeInterface;

class TooManyNonWorkingDays extends UKBankHolidaysException
{
    public static function withDates(DateTimeInterface $startingDate, DateTimeInterface $searchedUpTo): TooManyNonWorkingDays
    {
        return new static(
            sprintf(
                'Couldn\'t find any working days between: "%s" and "%s"',
                $startingDate->format('Y-m-d'),
                $searchedUpTo->format('Y-m-d')
            )
        );
    }
}