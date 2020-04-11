<?php

namespace Vivait\UKBankHolidays\Exception;

use DateTimeInterface;

class CantConnectToServiceException extends UKBankHolidaysException
{
    public static function because(string $url, string $error): CantConnectToServiceException
    {
        return new static(
            sprintf(
                'Couldn\'t connect to the external service: "%s" because "%s"',
                $url,
                $error
            )
        );
    }
}