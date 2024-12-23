<?php
    class DB {
        private static $db_instance = null; // Singleton instance
        private $db_con;

        private function __construct() {
            // Load the database configuration from db_config.json
            $config = json_decode(file_get_contents('DB/db_config.json'), true);

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
    };
?>