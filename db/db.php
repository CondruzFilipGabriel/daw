<?php
    class DB {
        private static $db_instance = null; // Singleton instance
        private $db_con;

        private function __construct() {
            // Load the database configuration from credentials.json
            $config = json_decode(file_get_contents(__DIR__ . '/../credentials.json'), true)['db'];

            if (!$config) {
                die("Error: Nu s-a putut incarca fisierul de configurare a bazei de date.");
            }

            $db_host = $config['host'];
            $db_user = $config['user'];
            $db_pass = $config['password'];
            $db_name = $config['database'];

            // Establish connection
            $this->db_con = new mysqli($db_host, $db_user, $db_pass, $db_name);

            // Check for connection errors
            if ($this->db_con->connect_error) {
                file_put_contents('errors.log', $this->db_con->connect_error . PHP_EOL, FILE_APPEND);
                die('Conectare esuata la baza de date');
            }

            // Initializam baza de date daca este goala
            $result = $this->db_con->query("SHOW TABLES");
            if ($result && $result->num_rows === 0) {
                $this->initialize();
            }
        }

        // Get the singleton instance of the Database
        public static function getInstance() {
            if (self::$db_instance === null) {
                self::$db_instance = new DB();
            }
            return self::$db_instance;
        }

        // Get the connection object
        public function getConnection() {
            return $this->db_con;
        }

        // Initialize the database by executing the SQL commands in db_populate.php
        public function initialize() {
            $commands = include 'db/db_populate.php';
    
            foreach ($commands as $command) {
                if (!$this->db_con->query($command)) {
                    file_put_contents('errors.log', "Error executing: $command\n" . $this->db_con->error . PHP_EOL, FILE_APPEND);
                    die("Error initializing the database.");
                }
            }
        }

        // Reset the database by dropping all tables and reinitializing
        public function reset() {
            // Fetch all table names
            $result = $this->db_con->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                $this->db_con->query("DROP TABLE IF EXISTS $table");
            }

            // Reinitialize the database
            $this->initialize();
        }

        // Close the connection (optional, for cleanup)
        public function close() {
            $this->db_con->close();
            self::$instance = null;
        }

               // Fetch a list of all events
        public function getAllEvents() {
            $query = "
                SELECT 
                    e.id,
                    e.name AS title,
                    IFNULL(e.image, c.image) AS image, -- Use category image if event image is null
                    e.price,
                    CONCAT(e.date, ' ', e.start_hour) AS date_time
                FROM 
                    events e
                INNER JOIN 
                    categories c ON e.category_id = c.id
            ";

            $result = $this->db_con->query($query);

            if (!$result) {
                file_put_contents('errors.log', "Error fetching events: " . $this->db_con->error . PHP_EOL, FILE_APPEND);
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Create a new session
        public function createSession($userId, $token, $ipAddress, $geolocation) {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
            $query = "INSERT INTO sessions (user_id, ip_address, token, expires_at, geolocation) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('issss', $userId, $ipAddress, $token, $expiresAt, $geolocation);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Failed to execute statement: " . $stmt->error);
                return false;
            }
        
            return true;
        }        

        // Verify if a session exists based on a token
        public function verifySession($token) {
            $query = "
                SELECT 
                    s.user_id, u.name, u.email, u.rights
                FROM 
                    sessions s
                INNER JOIN 
                    users u ON s.user_id = u.id
                WHERE 
                    s.token = ? AND s.expires_at > NOW()
            ";

            $stmt = $this->db_con->prepare($query);
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        // End a session
        public function endSession($token) {
            $query = "DELETE FROM sessions WHERE token = ?";
            $stmt = $this->db_con->prepare($query);
            $stmt->bind_param('s', $token);
            return $stmt->execute();
        }

    // Verify user credentials and return user data
        public function login($email, $password) {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->db_con->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify the raw password against the hashed password
            if ($user && password_verify($password, $user['password'])) {
                return $user; // Return user data if credentials are valid
            }

            return null; // Return null if credentials are invalid
        }
        
    };
?>