<?php
    include_once __DIR__ . '/../modules/debug.php';
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

        public function getAllCategories() {
            $query = "SELECT id, name, image FROM categories ORDER BY name ASC";
            $result = $this->db_con->query($query);

            if (!$result) {
                Debug::log("Failed to fetch categories: " . $this->db_con->error);
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }
               // Fetch a list of all events
        public function getAllEvents() {
            $query = "
                SELECT 
                    e.id AS id,
                    e.name AS title,
                    IFNULL(e.image, c.image) AS image, -- Use category image if event image is null
                    e.price,
                    CONCAT(e.date, ' ', e.start_hour) AS date_time,
                    e.category_id AS category_id
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

        public function createEvent($name, $date, $start_hour, $price, $category_id, $image = null) {
            // Validate category_id
            $categoryQuery = "SELECT id FROM categories WHERE id = ?";
            $stmt = $this->db_con->prepare($categoryQuery);
            if (!$stmt) {
                Debug::log("Failed to prepare category validation: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $stmt->store_result();
        
            if ($stmt->num_rows === 0) {
                Debug::log("Invalid category_id: " . $category_id);
                return false;
            }
            $stmt->close();
        
            // Insert the event
            $query = "INSERT INTO events (name, date, start_hour, price, category_id, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Failed to prepare event creation: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('sssdis', $name, $date, $start_hour, $price, $category_id, $image);
            $result = $stmt->execute();
        
            if ($result) {
                return $stmt->insert_id;  // Return the last inserted ID
            }
        
            $stmt->close();
            return false;
        }
        
        public function updateEvent($id, $name, $date, $start_hour, $price, $category_id, $image = null) {
            // Validate category_id
            $categoryQuery = "SELECT id FROM categories WHERE id = ?";
            $stmt = $this->db_con->prepare($categoryQuery);
            if (!$stmt) {
                Debug::log("Failed to prepare category validation: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $stmt->store_result();
        
            if ($stmt->num_rows === 0) {
                Debug::log("Invalid category_id: " . $category_id);
                return false;
            }
            $stmt->close();
        
            // Update the event
            $query = "UPDATE events SET name = ?, date = ?, start_hour = ?, price = ?, category_id = ?, image = ? WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Failed to prepare event update: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('sssdisi', $name, $date, $start_hour, $price, $category_id, $image, $id);
            $result = $stmt->execute();
            $stmt->close();
        
            return $result;
        }

        public function deleteEvent($id) {
            $query = "DELETE FROM events WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Failed to prepare event deletion: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        }

        // Create a new session
        public function createSession($userId, $token) {
            if (!is_int($userId) || $userId <= 0) {
                Debug::log("Invalid user ID: " . $userId);
                return false;
            }

            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
            $query = "INSERT INTO sessions (user_id, token, expires_at) 
                      VALUES (?, ?, ?)";
            
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('iss', $userId, $token, $expiresAt);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Failed to execute statement: " . $stmt->error);
                return false;
            }
        
            return true;
        }        

        public function deleteSessionsByUserId($userId) {
            // Debug::log('Deleting sessions for user with id: ' . $userId);
        
            $query = "DELETE FROM sessions WHERE user_id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $userId);
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

        public function getAllUsers() {
            $query = "SELECT id, name, email, rights FROM users";
            $result = $this->db_con->query($query);
        
            if (!$result) {
                Debug::log("Failed to fetch all users: " . $this->db_con->error);
                return [];
            }
        
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        

        public function createUser($name, $email, $password) {
            $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('sss', $name, $email, $password);
            $result = $stmt->execute();
            if (!$result) {
                Debug::log("Failed to execute statement: " . $stmt->error);
                return false;
            }
            return $this->db_con->insert_id;
        }        

        public function deleteUser($id) {
            // Debug::log('Deleting user with id: ' . $id);
        
            // Delete sessions first
            if (!$this->deleteSessionsByUserId($id)) {
                Debug::log("Failed to delete sessions for user with id: " . $id);
                return false;
            }
        
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Failed to execute statement: " . $stmt->error);
                return false;
            }
        
            return true;
        }
        
        
        public function updateUser($id, $name = null, $email = null, $password = null, $rights = null) {        
            // Fetch current values
            $query = "SELECT name, email, password, rights FROM users WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare statement for fetching user: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if (!$result || $result->num_rows === 0) {
                Debug::log("User not found with id: " . $id);
                return false;
            }
        
            $currentData = $result->fetch_assoc();
            $stmt->close();
        
            // Use ternary operator to set fields
            $updatedName = $name !== null ? $name : $currentData['name'];
            $updatedEmail = $email !== null ? $email : $currentData['email'];
            $updatedPassword = $password !== null ? $password : $currentData['password'];
            $updatedRights = $rights !== null ? $rights : $currentData['rights'];
        
            // Validate rights
            $validRights = ['user', 'admin', 'moderator'];
            if (!in_array($updatedRights, $validRights)) {
                Debug::log("Invalid rights provided: " . $updatedRights);
                return false;
            }
        
            // Construct and execute the UPDATE query
            $query = "UPDATE users SET name = ?, email = ?, password = ?, rights = ? WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Failed to prepare update statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('ssssi', $updatedName, $updatedEmail, $updatedPassword, $updatedRights, $id);
            
            // Debug::log($stmt);

            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Failed to execute update statement: " . $stmt->error);
                return false;
            }
        
            // Debug::log("User updated successfully with id: " . $id);
            return true;
        }        

        public function emailExists($email) {
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db_con->prepare($query);
            
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
        
            return $stmt->num_rows > 0; // Returns true if email exists, false otherwise
        }

        public function reserveSeats($eventId, $numberOfSeats, $price, $userId) {
            // Step 1: Get the hall capacity
            $hallQuery = "SELECT capacity FROM hall LIMIT 1";
            $hallResult = $this->db_con->query($hallQuery);
            if (!$hallResult || $hallResult->num_rows === 0) {
                Debug::log("Nu s-a putut obtine capacitatea de locuri a salii");
                return null;
            }
            $hallCapacity = (int) $hallResult->fetch_assoc()['capacity'];
        
            // Step 2: Get currently reserved seats for the event
            $reservedSeatsQuery = "SELECT seat_number FROM tickets WHERE event_id = ? ORDER BY seat_number ASC";
            $stmt = $this->db_con->prepare($reservedSeatsQuery);
            if (!$stmt) {
                Debug::log("Nu s-a putut executa comanda pentru obtinerea listei de locuri ocupate: " . $this->db_con->error);
                return null;
            }
            $stmt->bind_param('i', $eventId);
            $stmt->execute();
            $reservedSeatsResult = $stmt->get_result();
        
            $reservedSeats = [];
            while ($row = $reservedSeatsResult->fetch_assoc()) {
                $reservedSeats[] = (int) $row['seat_number'];
            }
            $stmt->close();
        
            // Step 3: Check if there are enough free seats
            $freeSeats = [];
            for ($i = 1; $i <= $hallCapacity; $i++) {
                if (!in_array($i, $reservedSeats)) {
                    $freeSeats[] = $i;
                }
            }
        
            if (count($freeSeats) < $numberOfSeats) {
                Debug::log("Nu sunt disponibile suficiente locuri pentru evenimentul cu ID: $eventId");
                return null;
            }
        
            // Step 4: Reserve the required seats
            $seatsToReserve = array_slice($freeSeats, 0, $numberOfSeats);
            $ticketQuery = "INSERT INTO tickets (user_id, event_id, seat_number, price) VALUES (?, ?, ?, ?)";
            $stmt = $this->db_con->prepare($ticketQuery);
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda pentru rezervarea locurilor: " . $this->db_con->error);
                return null;
            }
        
            foreach ($seatsToReserve as $seat) {
                $stmt->bind_param('iiid', $userId, $eventId, $seat, $price);
                if (!$stmt->execute()) {
                    Debug::log("Nu s-a putut executa rezervarea locului $seat: " . $stmt->error);
                    return null;
                }
            }
        
            $stmt->close();
        
            // Return the reserved seats
            return $seatsToReserve;
        }        

        public function getUserTickets($userId) {
            // Prepare the SQL query to join tickets and events tables
            $query = "
                SELECT 
                    e.name AS showName, 
                    CONCAT(e.date, ' ', e.start_hour) AS showDate, 
                    t.seat_number AS seatNumber,
                    t.price AS ticketPrice
                FROM 
                    tickets t
                INNER JOIN 
                    events e ON t.event_id = e.id
                WHERE 
                    t.user_id = ?
                ORDER BY 
                    e.date, e.start_hour, t.seat_number
            ";
        
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Failed to prepare statement: " . $this->db_con->error);
                return [];
            }
        
            // Bind the user ID parameter to the query
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        
            $result = $stmt->get_result();
        
            if (!$result) {
                Debug::log("Failed to execute query: " . $stmt->error);
                return [];
            }
        
            // Fetch all tickets as an array of objects
            $tickets = [];
            while ($row = $result->fetch_assoc()) {
                $tickets[] = (object) [
                    'showName' => $row['showName'],
                    'showDate' => $row['showDate'],
                    'seatNumber' => $row['seatNumber'],
                    'ticketPrice' => $row['ticketPrice']
                ];
            }
        
            $stmt->close();
        
            return $tickets;
        }        
    };
?>