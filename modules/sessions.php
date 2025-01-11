<?php
    class Session {
        private $db;

        public function __construct() {
            $this->db = DB::getInstance();
            session_start([
                'cookie_lifetime' => 86400, // 1 day
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Only secure cookies over HTTPS
                'cookie_httponly' => true, // Prevent JavaScript access to session cookies
                'cookie_samesite' => 'Strict' // Prevent CSRF and cross-origin issues
            ]);            
        }

        // Create a new session
        public function new_session($userId) {
            if (is_int($userId) && $userId > 0) {
                $token = bin2hex(random_bytes(32)); // Generate a secure token
                $result = $this->db->createSession($userId, $token);

                if ($result) {
                    $_SESSION['user_token'] = $token;
                    return $token;
                } else {
                    Debug::log("Session creation failed for user ID: " . $userId);
                    return false;
                }
            } else {
                Debug::log("User creation failed");
                return false;
            }
        }

                // Login the user and create a session
        public function login($email, $password) {
            $user = $this->db->login($email, $password);

            if ($user) {
                // Create a session for the user
                $this->new_session($user['id']);
                return $user;
            }

            return null;
        }

        // Retrieve user data if logged in
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
                // Remove the session from the database
                $this->db->endSession($_SESSION['user_token']);
                unset($_SESSION['user_token']);
            }
            session_destroy(); // Destroy the PHP session
        }        
    }
?>
