<?php

namespace Tests\Client;

use DateTime;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Vivait\UKBankHolidays\Client\HttpClient;

class HttpClientTest extends TestCase
{

    private string $live_response;

    protected function setUp(): void
    {
        parent::setUp();
        $http_client = new HttpClient(new Client());
        $this->live_response = $http_client->getAllHolidays();
    }

    /**
     * @group live-calls
     */
    public function testClientReturnsAValidJsonArray(): void
    {
        $this->assertJson($this->live_response);
    }

    /**
     * @group live-calls
     */
    public function testHolidayContainsValidDate(): void
    {
        $all_holidays_array = json_decode($this->live_response, true, 512, JSON_THROW_ON_ERROR);
        $all_holidays_array['england-and-wales']['events'][0]['date'];
        $date = $all_holidays_array['england-and-wales']['events'][0]['date'];

        $this->assertEquals(DateTime::createFromFormat('Y-m-d', $date)->format('Y-m-d'), $date);
    }

}
