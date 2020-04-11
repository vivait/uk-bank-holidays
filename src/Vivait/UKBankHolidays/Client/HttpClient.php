<?php


namespace Vivait\UKBankHolidays\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Vivait\UKBankHolidays\Exception\CantConnectToServiceException;

class HttpClient
{
    private ClientInterface $http_client;

    public const UK_GOV_HOLIDAY_URL = 'https://www.gov.uk/bank-holidays.json';

    public function __construct(ClientInterface $http_client)
    {
        $this->http_client = $http_client;
    }

    /**
     * @return string JSON encoded array of all holidays in the UK
     * @throws CantConnectToServiceException
     */
    public function getAllHolidays(): string
    {
        try {
            $response = $this->http_client->get(self::UK_GOV_HOLIDAY_URL)->getBody();

            if($response === null) {
                throw CantConnectToServiceException::because(self::UK_GOV_HOLIDAY_URL, 'Empty response');
            }

            return $response->getContents();
        } catch (RequestException $e) {
            throw CantConnectToServiceException::because(self::UK_GOV_HOLIDAY_URL, $e->getMessage());
        }
    }
}