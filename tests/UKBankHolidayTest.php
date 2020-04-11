<?php

namespace Tests;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;
use Vivait\UKBankHolidays\Client\HttpClient;
use Vivait\UKBankHolidays\Exception\TooFarInFutureException;
use Vivait\UKBankHolidays\Region\Region;
use Vivait\UKBankHolidays\UKBankHolidays;

class UKBankHolidayTest extends TestCase
{
    private UKBankHolidays $uk_bank_holiday;

    public function weekendProvider(): array
    {
        return [
            ['2020-04-10', false],
            ['2020-04-11', true],
            ['2020-04-12', true],
            ['2020-04-13', false],
        ];
    }

    public function bankHolidayProvider()
    {
        return [
            ['2020-04-09', false],
            ['2020-04-10', true],
            ['2020-04-11', false],
            ['2020-04-12', false],
            ['2020-04-13', true],
            ['2020-04-14', false],
        ];
    }

    public function nextBankHolidayFromDate()
    {
        return [
            ['2020-04-09', '2020-04-10'],
            ['2020-04-20', '2020-05-08'],
            ['2020-12-29', '2021-01-01'],
        ];
    }

    public function nextWorkingDayFromDate()
    {
        return [
            ['2020-04-09', '2020-04-14'],
            ['2020-04-20', '2020-04-21'],
            ['2020-12-24', '2020-12-29'],
            ['2020-12-29', '2020-12-30'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var HttpClient|MockObject $mock_http_client */
        $mock_http_client = $this->getMockBuilder(HttpClient::class)->disableOriginalConstructor()->getMock();
        $mock_http_client->method('getAllHolidays')->willReturn(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Payloads' . DIRECTORY_SEPARATOR . 'successfulResponseFrom20200411.json')
        );

        $this->uk_bank_holiday = new UKBankHolidays(
            $mock_http_client,
            new Region('england-and-wales'),
            new NullCache()
        );
    }

    /**
     * @dataProvider weekendProvider
     * @param string $date
     * @param bool $isWeekend
     * @throws \Exception
     */
    public function testWeekends(string $date, bool $isWeekend): void
    {
        $this->assertSame($isWeekend, $this->uk_bank_holiday->isWeekend(new DateTime($date)));
    }

    /**
     * @dataProvider bankHolidayProvider
     * @param string $date
     * @param bool $isBankHoliday
     * @throws \Exception
     */
    public function testBankHolidays(string $date, bool $isBankHoliday): void
    {
        $this->assertSame($isBankHoliday, $this->uk_bank_holiday->isBankHoliday(new DateTime($date)));
    }

    public function testTooFarFutureBankHoliday(): void
    {
        $this->expectException(TooFarInFutureException::class);
        $this->uk_bank_holiday->nextBankHoliayFrom(new DateTime('2025-01-01'));
    }

    /**
     * @param string $searchFrom
     * @param string $nextBankHoliday
     * @throws TooFarInFutureException
     * @throws \JsonException
     * @dataProvider nextBankHolidayFromDate
     */
    public function testNextBankHoliday(string $searchFrom, string $nextBankHoliday): void
    {
        $this->assertEquals(
            $nextBankHoliday,
            $this->uk_bank_holiday->nextBankHoliayFrom(new DateTime($searchFrom))->format('Y-m-d')
        );
    }

    /**
     * @param $searchFrom
     * @param $nextWorkingDay
     * @dataProvider nextWorkingDayFromDate
     * @throws \JsonException
     */
    public function testNextWorkingDay(string $searchFrom, string $nextWorkingDay): void
    {
        $this->assertEquals(
            $nextWorkingDay,
            $this->uk_bank_holiday->nextWorkingDayFrom(new DateTime($searchFrom))->format('Y-m-d')
        );
    }

    public function testScotishHolidayIsNotIncluded(): void
    {
        $this->assertFalse($this->uk_bank_holiday->isBankHoliday(new DateTime('2020-11-30')));
    }


}
