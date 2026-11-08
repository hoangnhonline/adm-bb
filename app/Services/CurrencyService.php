<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    const RATE_KEY    = 'fx:rate:VND:INR';
    const COUNT_KEY   = 'fx:count:VND:INR';
    const REFRESH_KEY = 'fx:refreshing:VND:INR';

    public function vndToInr($amountVnd, $round = 0)
    {
        $rate = $this->getRateVndToInr();
        return round(((float)$amountVnd) * $rate, (int)$round);
    }

    public function getRateVndToInr()
    {
        $limit       = (int) env('FX_USAGE_LIMIT', 20);
        $ttlMinutes  = (int) env('FX_RATE_TTL_MINUTES', 1440);
        $cooldownSec = (int) env('FX_REFRESH_COOLDOWN_SECONDS', 60);

        if (!Cache::has(self::COUNT_KEY)) {
            Cache::put(self::COUNT_KEY, 0, now()->addMinutes($ttlMinutes));
        }

        $count = Cache::increment(self::COUNT_KEY);
        $rate  = Cache::get(self::RATE_KEY);

        // lần đầu chưa có rate
        if (!$rate) {
            $rate = $this->refreshRate($ttlMinutes);
            Cache::put(self::COUNT_KEY, 1, now()->addMinutes($ttlMinutes));
            return $rate;
        }

        // quá 20 lần → refresh (có cooldown)
        if ($count > $limit) {
            if (!Cache::has(self::REFRESH_KEY)) {
                Cache::put(self::REFRESH_KEY, 1, now()->addSeconds($cooldownSec));
                try {
                    $rate = $this->refreshRate($ttlMinutes);
                    Cache::put(self::COUNT_KEY, 0, now()->addMinutes($ttlMinutes));
                } catch (\Throwable $e) {
                    Log::warning('FX refresh failed', ['err' => $e->getMessage()]);
                }
            }
        }

        return (float) $rate;
    }

    private function refreshRate($ttlMinutes)
    {
        $rate = $this->fetchRateFromApi();
        Cache::put(self::RATE_KEY, $rate, now()->addMinutes($ttlMinutes));
        return (float) $rate;
    }

    /**
     * FETCH FX RATE VIA CURL (NO Http::, WORKS ON ALL LARAVEL)
     */
    private function fetchRateFromApi()
    {
        $url = env('FX_RATE_URL', 'https://open.er-api.com/v6/latest/VND');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \RuntimeException('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $json = json_decode($response, true);

        if (!isset($json['rates']['INR'])) {
            throw new \RuntimeException('INR rate not found');
        }

        return (float) $json['rates']['INR'];
    }
}
