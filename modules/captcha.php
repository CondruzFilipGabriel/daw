<?php
    require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer autoload file

    use ReCaptcha\ReCaptcha;

    class Captcha {
        private $siteKey;
        private $secretKey;

        public function __construct() {            
            // Load credentials
            $credentials = json_decode(file_get_contents(__DIR__ . '/../credentials.json'), true);

            if (!$credentials || !isset($credentials['recaptcha_site_key'], $credentials['recaptcha_secret_key'])) {
                die("Error: Missing reCAPTCHA keys.");
            }

            $this->siteKey = $credentials['recaptcha_site_key'];
            $this->secretKey = $credentials['recaptcha_secret_key'];

            // Include the necessary scripts for reCAPTCHA
            $this->includeScripts();
            
            // Handle CAPTCHA validation if it's a POST request
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
            }
        }

        public static function getSiteKey() {
            $credentials = json_decode(file_get_contents(__DIR__ . '/../credentials.json'), true);
            return $credentials['recaptcha_site_key'] ?? '';
        }

        private function includeScripts() {
            $siteKey = $this->siteKey;
            echo <<<HTML
            <script>const recaptchaSiteKey = "$siteKey";</script>
            <script src="https://www.google.com/recaptcha/api.js?render=$siteKey" async defer></script>
            <script src="/ProiectDaw/js/captcha.js" defer></script>
            HTML;
        }

        private function handlePostRequest() {
            $token = $_POST['g-recaptcha-response'] ?? null;
            $action = $_POST['g-recaptcha-action'] ?? null;

            if (!$this->validate($token, $action)) {
                header("Location: you-are-a-robot.php");
            } 
            // else {
            //     header("Location: index.php");
            // }

            exit();
        }

        public function validate($token, $action, $scoreThreshold = 0.5) {
            if (!$token || !$action) {
                return false;
            }

            $recaptcha = new ReCaptcha($this->secretKey);
        
            $response = $recaptcha->setExpectedAction($action)
                                  ->setScoreThreshold($scoreThreshold)
                                  ->verify($token, $_SERVER['REMOTE_ADDR']);

            return $response->isSuccess();
        }
    }
?>
