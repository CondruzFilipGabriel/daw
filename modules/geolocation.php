<?php
    class Geolocation
    {
        private const API_URL = 'http://ip-api.com/json/';

        /**
         * API pentru obtinerea localizarii in functie de IP
         * Se primeste parametru un string cu IP-ul
         * se returneaza un array cu detalii, intre care tara si orasul
         * returneaza null daca API-ul nu functioneaza
         */

        public static function getLocation(string $ip): ?array
        {
            // Apelam API-ul furnizand IP-ul
            $url = self::API_URL . urlencode($ip);
            $response = @file_get_contents($url);

            if ($response === false) {
                return null; // API-ul nu a returnat un rezultat valid
            }

            $data = json_decode($response, true);

            // Verificam validitatea raspunsului primit
            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['regionName'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                ];
            }

            return null; // Daca nu e valid
        }
    }
?>