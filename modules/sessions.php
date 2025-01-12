<?php
    class Session {
        private $db;

        public function __construct() {
            $this->db = DB::getInstance();
            session_start([
                'cookie_lifetime' => 86400, // 1 zi
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Doar cookie-uri securizate pe HTTPS
                'cookie_httponly' => true, // Nu permite JavaScript sa acceseze cookie-urile de sesiune
                'cookie_samesite' => 'Strict' // Prevenirea atacurilor CSRF si cross-origin
            ]);
        }

        // Cream o noua sesiune
        public function new_session($userId) {
            if (is_int($userId) && $userId > 0) {
                $token = bin2hex(random_bytes(32)); // Generam un token securizat
                $result = $this->db->createSession($userId, $token);

                if ($result) {
                    $_SESSION['user_token'] = $token;
                    return $token;
                } else {
                    Debug::log("Eroare la crearea sesiunii pentru userul cu ID: " . $userId);
                    return false;
                }
            } else {
                Debug::log("Eroare la crearea userului!");
                return false;
            }
        }

        // Conectam utilizatorul si initiem o sesiune noua
        public function login($email, $password) {
            $user = $this->db->login($email, $password);

            if ($user) {
                // Cream o sesiune pentru utilizator
                $this->new_session($user['id']);
                return $user;
            }

            return null;
        }

        // Returnam datele userului daca e logat
        public function retrieve_user_data() {
            if (!isset($_SESSION['user_token'])) {
                return null;
            }

            $token = $_SESSION['user_token'];
            return $this->db->verifySession($token);
        }

        // Sign out
        public function sign_out() {
            if (isset($_SESSION['user_token'])) {
                // Stergem sesiunea din baza de date
                $this->db->endSession($_SESSION['user_token']);
                unset($_SESSION['user_token']);
            }
            session_destroy(); // Distrugem sesiunea PHP
        }        
    }
?>
