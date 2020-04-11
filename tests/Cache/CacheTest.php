<?php

namespace Tests\Cache;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Vivait\UKBankHolidays\Client\HttpClient;
use Vivait\UKBankHolidays\Region\Region;
use Vivait\UKBankHolidays\UKBankHolidays;

class CacheTest extends TestCase
{
    private UKBankHolidays $uk_bank_holiday;

    /** @var HttpClient|MockObject */
    private MockObject $mock_http_client;

    /**
     * CacheTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mock_http_client =
            $this->getMockBuilder(HttpClient::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->mock_http_client
            ->expects($this->once())
            ->method('getAllHolidays')
            ->willReturn(
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '../Payloads' . DIRECTORY_SEPARATOR . 'successfulResponseFrom20200411.json')
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new FilesystemCache('tests', '3600', __DIR__ . DIRECTORY_SEPARATOR . '../../tmp/cache');
        $cache->deleteItem(UKBankHolidays::CACHE_KEY);

        $this->uk_bank_holiday = new UKBankHolidays(
            $this->mock_http_client,
            new Region('england-and-wales'),
            $cache
        );
    }

    /**
     * @throws \Exception
     */
    public function testMultipleBankHolidays(): void
    {
        $this->assertTrue($this->uk_bank_holiday->isBankHoliday(new DateTime('2020-04-10')));
        $this->assertTrue($this->uk_bank_holiday->isBankHoliday(new DateTime('2020-04-10')));
    }
}
