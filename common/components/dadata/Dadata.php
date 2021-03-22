<?php

namespace common\components\dadata;

use Dadata\DadataClient;

/**
 * Class Dadata
 * @package common\components\dadata
 */
class Dadata
{
    const TOKEN = "e57b654f5042b08618226a75e979456c5fd9957a";
    const SECRET = "c530aad8f08d32878a51019c06d22c29c4431476";

    /**
     * @param $address
     * @return array|null
     */
    public static function getCoordinatesByAddress($address)
    {
        $dadata = new DadataClient(self::TOKEN, self::SECRET);
        $result = $dadata->clean("address", $address);
        if (isset($result['geo_lat'], $result['geo_lon'])) {
            return [
                'geo_lon' => $result['geo_lon'],
                'geo_lat' => $result['geo_lat'],
            ];
        }
        return null;
    }
}
