<?php

namespace Vivait\UKBankHolidays\Exception;

use DateTimeInterface;

class TooFarInFutureException extends UKBankHolidaysException
{
    public static function withDates(DateTimeInterface $startingDate, DateTimeInterface $searchedUpTo): TooFarInFutureException
    {
        return new static(
            sprintf(
                'Couldn\'t find any bank holidays, searched from: "%s" to "%s"',
                $startingDate->format('Y-m-d'),
                $searchedUpTo->format('Y-m-d')
            )
        );
    }
}