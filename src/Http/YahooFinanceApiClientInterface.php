<?php

namespace App\Http;

interface YahooFinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region) : array;
}