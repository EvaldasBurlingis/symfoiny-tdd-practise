<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient implements FinanceApiClientInterface
{
    private const URL = 'https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-profile';
    private const X_RAPID_API_HOST = 'apidojo-yahoo-finance-v1.p.rapidapi.com';
    private $rapidApiKey;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $rapidApiKey;
    }

    public function fetchStockProfile(string $symbol, string $region): JsonResponse
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query' => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'x-rapidapi-key'  => $this->rapidApiKey,
                'x-rapidapi-host' => self::X_RAPID_API_HOST,
                'Accept'          => 'application/json'
            ]
        ]);

        if($response->getStatusCode() !== 200) {
            return new JsonResponse('Finance API Client Error', 400);
        }

        $stockProfile = json_decode($response->getContent())->price;

        $stockProfileAsArray = [
            'symbol'        => $stockProfile->symbol,
            'shortName'     => $stockProfile->shortName,
            'region'        => $region,
            'exchangeName'  => $stockProfile->exchangeName,
            'currency'      => $stockProfile->currency,
            'price'         => $stockProfile->regularMarketPrice->raw,
            'previousClose' => $stockProfile->regularMarketPreviousClose->raw,
            'priceChange'   => $stockProfile->regularMarketPrice->raw - $stockProfile->regularMarketPreviousClose->raw
        ];

        return new JsonResponse($stockProfileAsArray, 200);
    }
}