<?php
    // => Client IP
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    // => tipul de dispozitiv
    function getDeviceType() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (preg_match('/mobile|android|iphone|ipad/', $userAgent)) {
            return 'Mobil';
        } else {
            return 'Desktop';
        }
    }

    // => browser
    function getBrowser() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        return 'Altele';
    }

    // => sistemul de operare
    function getOS() {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($userAgent, 'windows') !== false) return 'Windows';
        if (strpos($userAgent, 'mac') !== false) return 'MacOS';
        if (strpos($userAgent, 'linux') !== false) return 'Linux';
        if (strpos($userAgent, 'android') !== false) return 'Android';
        if (strpos($userAgent, 'iphone') !== false) return 'iOS';
        return 'Altele';
    }

    // incepe masuratoarea page load time
    $pageLoadStart = microtime(true);

    register_shutdown_function(function() use ($pageLoadStart, $db) {
        $pageLoadTime = microtime(true) - $pageLoadStart;
        $serverResponseTime = $_SERVER['REQUEST_TIME_FLOAT'] ? (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) : 0;
        
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
        $sessionId = session_id();
        $ipAddress = getClientIP();
        $previousPage = $_SERVER['HTTP_REFERER'] ?? NULL;
        $pageUrl = $_SERVER['REQUEST_URI'];
        $deviceType = getDeviceType();
        $browser = getBrowser();
        $operatingSystem = getOS();

        // Geolocation
        require_once __DIR__ . '/geolocation.php';
        $location = Geolocation::getLocation($ipAddress);
        $country = $location['country'] ?? NULL;
        $city = $location['city'] ?? NULL;

        // calculam timpul petrecut pe pagina anterioara
        $timeSpent = isset($_SESSION['last_page_time']) ? microtime(true) - $_SESSION['last_page_time'] : 0;
        $_SESSION['last_page_time'] = microtime(true);

        // crestem numarul de pagini vizualizate din sesiune
        $_SESSION['pages_viewed'] = ($_SESSION['pages_viewed'] ?? 0) + 1;

        // inseram datele de analytics in BD
        $success = $db->insertAnalyticsData(
            $userId,
            $sessionId,
            $ipAddress,
            $country,
            $city,
            $deviceType,
            $browser,
            $operatingSystem,
            $pageUrl,
            $previousPage,
            $pageLoadTime,
            $serverResponseTime,
            $timeSpent,
            $_SESSION['pages_viewed']
        );

        if (!$success) {
            Debug::log("Failed to insert analytics data.");
        }
    });
?>