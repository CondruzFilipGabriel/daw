<?php
    class Geolocation
    {
        private const API_URL = 'http://ip-api.com/json/';

        /**
         * Fetches geolocation details for the given IP.
         *
         * @param string $ip The IP address to fetch the location for.
         * @return array|null Returns location data or null on failure.
         */
        public static function getLocation(string $ip): ?array
        {
            // Make an API call to ip-api
            $url = self::API_URL . urlencode($ip);
            $response = @file_get_contents($url);

            if ($response === false) {
                return null; // API call failed
            }

            $data = json_decode($response, true);

            // Ensure the API response is valid and successful
            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['regionName'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                ];
            }

            return null; // Invalid API response
        }
    }
?>