<?php
    include_once __DIR__ . '/../modules/debug.php';
    class DB {
        private static $db_instance = null; // !!! conexiune unica
        private $db_con;

        private function __construct() {
            // Incarcam credentialele de configurare din credentials.json
            $config = json_decode(file_get_contents(__DIR__ . '/../credentials.json'), true)['db'];

            if (!$config) {
                die("Error: Nu s-a putut incarca fisierul de configurare a bazei de date.");
            }

            $db_host = $config['host'];
            $db_user = $config['user'];
            $db_pass = $config['password'];
            $db_name = $config['database'];

            // Conectam BD
            $this->db_con = new mysqli($db_host, $db_user, $db_pass, $db_name);

            // Verificam eventuale erori la conectare
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

        // Returnam instanta bazei de date
        public static function getInstance() {
            if (self::$db_instance === null) {
                self::$db_instance = new DB();
            }
            return self::$db_instance;
        }

        // returnam obiectul conexiunii
        public function getConnection() {
            return $this->db_con;
        }

        // initializam BD prin executarea codului SQL din db_populate.php
        public function initialize() {
            $commands = include 'db/db_populate.php';
    
            foreach ($commands as $command) {
                if (!$this->db_con->query($command)) {
                    file_put_contents('errors.log', "Eroare la executare: $command\n" . $this->db_con->error . PHP_EOL, FILE_APPEND);
                    die("Eroare la initilizarea bazei de date.");
                }
            }
        }

        // Functie de resetare a bazei de date
        public function reset() {
            // Preluam toate numele de tabele
            $result = $this->db_con->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                $this->db_con->query("DROP TABLE IF EXISTS $table");
            }

            // Reinitializam BD
            $this->initialize();
        }

        // Inchidem conexiunea
        public function close() {
            $this->db_con->close();
            self::$instance = null;
        }

        public function getAllCategories() {
            $query = "SELECT id, name, image FROM categories ORDER BY name ASC";
            $result = $this->db_con->query($query);

            if (!$result) {
                Debug::log("Nu s-a reusit preluarea categoriilor: " . $this->db_con->error);
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        // Returneaza lista cu toate eventurile
        public function getAllEvents() {
            $query = "
                SELECT 
                    e.id AS id,
                    e.name AS title,
                    IFNULL(e.image, c.image) AS image, -- folosim imaginea categoriei daca image e null
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
                file_put_contents('errors.log', "Eroare in preluarea eventurilor: " . $this->db_con->error . PHP_EOL, FILE_APPEND);
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function createEvent($name, $date, $start_hour, $price, $category_id, $image = null) {
            // Verificam daca nu exista deja un event cu acelasi nume
            $duplicateCheckQuery = "SELECT id FROM events WHERE name = ?";
            $stmt = $this->db_con->prepare($duplicateCheckQuery);
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati verificarea pentru eveniment cu acelasi nume: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->store_result();
        
            if ($stmt->num_rows > 0) {
                Debug::log("Exista deja un event cu numele: " . $name);
                $stmt->close();
                return false;  // Exista deja event cu numele ales deci => false
            }
            $stmt->close();
        
            // Validam category_id
            $categoryQuery = "SELECT id FROM categories WHERE id = ?";
            $stmt = $this->db_con->prepare($categoryQuery);
            if (!$stmt) {
                Debug::log("Nu s-a putut realiza pregatirea pentru valiarea categoriei: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $stmt->store_result();
        
            if ($stmt->num_rows === 0) {
                Debug::log("Invalid category_id: " . $category_id);
                $stmt->close();
                return false;
            }
            $stmt->close();
        
            // Inseram event-ul
            $query = "INSERT INTO events (name, date, start_hour, price, category_id, image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Eroare in pregatirea crearii eventului: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('sssdis', $name, $date, $start_hour, $price, $category_id, $image);
            $result = $stmt->execute();
        
            if ($result) {
                return $stmt->insert_id;
            }
        
            $stmt->close();
            return false;
        }        
        
        public function updateEvent($id, $name, $date, $start_hour, $price, $category_id, $image = null) {
            // Validam category_id
            $categoryQuery = "SELECT id FROM categories WHERE id = ?";
            $stmt = $this->db_con->prepare($categoryQuery);
            if (!$stmt) {
                Debug::log("Eroare in pregatirea validarii categoriei: " . $this->db_con->error);
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
        
            // Actualizam event-ul
            $query = "UPDATE events SET name = ?, date = ?, start_hour = ?, price = ?, category_id = ?, image = ? WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Eroare in pregatirea pentru actualizarea eventului: " . $this->db_con->error);
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
                Debug::log("Eroare in pregatirea pentru stergerea eventului: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        }

        // Cream o sesiune noua
        public function createSession($userId, $token) {
            if (!is_int($userId) || $userId <= 0) {
                Debug::log("User ID invalid: " . $userId);
                return false;
            }

            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
            $query = "INSERT INTO sessions (user_id, token, expires_at) 
                      VALUES (?, ?, ?)";
            
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('iss', $userId, $token, $expiresAt);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Nu s-a putut executa comanda: " . $stmt->error);
                return false;
            }
        
            return true;
        }        

        public function deleteSessionsByUserId($userId) {        
            $query = "DELETE FROM sessions WHERE user_id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $userId);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Nu s-a putut executa comanda: " . $stmt->error);
                return false;
            }
        
            return true;
        }
        

        // Verificam daca o sesiune exista - pornind de la token
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

        // Inchidem o sesiune
        public function endSession($token) {
            $query = "DELETE FROM sessions WHERE token = ?";
            $stmt = $this->db_con->prepare($query);
            $stmt->bind_param('s', $token);
            return $stmt->execute();
        }

        // Verificam credentialele utilizatorului si returnam datele sale
        public function login($email, $password) {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->db_con->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verificam parola <-> hashed password
            if ($user && password_verify($password, $user['password'])) {
                return $user; // Credentiale ok => returnam datele utilizatorlui
            }

            return null; // Credentiale gresite => returnam null
        }

        public function getAllUsers() {
            $query = "SELECT id, name, email, rights FROM users";
            $result = $this->db_con->query($query);
        
            if (!$result) {
                Debug::log("Nu s-au putut returna toti utilizatorii: " . $this->db_con->error);
                return [];
            }
        
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        

        public function createUser($name, $email, $password) {
            $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $this->db_con->prepare($query);
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
            $stmt->bind_param('sss', $name, $email, $password);
            $result = $stmt->execute();
            if (!$result) {
                Debug::log("Nu s-a putut executa comanda: " . $stmt->error);
                return false;
            }
            return $this->db_con->insert_id;
        }        

        public function deleteUser($id) {        
            // Intai stergem sesiunea
            if (!$this->deleteSessionsByUserId($id)) {
                Debug::log("Nu s-a putut sterge sesiunea userului cu ID: " . $id);
                return false;
            }
        
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Nu s-a putut executa comanda: " . $stmt->error);
                return false;
            }
        
            return true;
        }
        
        
        public function updateUser($id, $name = null, $email = null, $password = null, $rights = null) {        
            // Fetch current values
            $query = "SELECT name, email, password, rights FROM users WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if (!$result || $result->num_rows === 0) {
                Debug::log("Nu a fost gasit userul cu ID-ul: " . $id);
                return false;
            }
        
            $currentData = $result->fetch_assoc();
            $stmt->close();
        
            $updatedName = $name !== null ? $name : $currentData['name'];
            $updatedEmail = $email !== null ? $email : $currentData['email'];
            $updatedPassword = $password !== null ? $password : $currentData['password'];
            $updatedRights = $rights !== null ? $rights : $currentData['rights'];
        
            // Validam drepturile
            $validRights = ['user', 'admin'];
            if (!in_array($updatedRights, $validRights)) {
                Debug::log("Drepturi invalide: " . $updatedRights);
                return false;
            }
        
            // Construct and execute the UPDATE query
            $query = "UPDATE users SET name = ?, email = ?, password = ?, rights = ? WHERE id = ?";
            $stmt = $this->db_con->prepare($query);
        
            if (!$stmt) {
                Debug::log("Eroare in pregatirea comenzii: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('ssssi', $updatedName, $updatedEmail, $updatedPassword, $updatedRights, $id);
            
            $result = $stmt->execute();
        
            if (!$result) {
                Debug::log("Eroare in updatarea datelor utilizatorului: " . $stmt->error);
                return false;
            }
            
            return true;
        }        

        public function emailExists($email) {
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->db_con->prepare($query);
            
            if (!$stmt) {
                Debug::log("Nu s-a putut pregati comanda: " . $this->db_con->error);
                return false;
            }
        
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
        
            return $stmt->num_rows > 0; // Returneaza true daca exista emailul sai false daca nu
        }

        public function reserveSeats($eventId, $numberOfSeats, $price, $userId) {
            // 1: Verificam capacitatea salii
            $hallQuery = "SELECT capacity FROM hall LIMIT 1";
            $hallResult = $this->db_con->query($hallQuery);
            if (!$hallResult || $hallResult->num_rows === 0) {
                Debug::log("Nu s-a putut obtine capacitatea de locuri a salii");
                return null;
            }
            $hallCapacity = (int) $hallResult->fetch_assoc()['capacity'];
        
            // 2: Verificam cate locuri sunt deja rezervate pentru eveniment
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
        
            // 3: Verificam daca sunt suficiente locuri libere
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
        
            // 4: Rezervam locurile (daca sunt libere)
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
        
            // Returnam numarul de locuri rezervate
            return $seatsToReserve;
        }        

        public function getUserTickets($userId) {
            //  SQL query - join tickets & events
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
                Debug::log("Nu s-aputut pregati comanda: " . $this->db_con->error);
                return [];
            }
        
            // Legam id-ul userului la query
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        
            $result = $stmt->get_result();
        
            if (!$result) {
                Debug::log("Failed to execute query: " . $stmt->error);
                return [];
            }
        
            // Preluam toate biletele ca un array de obiecte
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

        // Metoda de inserare a datelor de analytics
        public function insertAnalyticsData($userId, $sessionId, $ipAddress, $country, $city, $deviceType, $browser, $operatingSystem, $pageUrl, $previousPage, $pageLoadTime, $serverResponseTime, $timeSpent, $pagesViewed) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $siteUrl = $protocol . $_SERVER['HTTP_HOST'];
        
            if (strpos($previousPage, $siteUrl) === 0) {
                $previousPage = NULL;
            }
        
            // Verificam daca exista deja o intrare pentru sesiune si ip
            $checkQuery = "SELECT id, pages_viewed, page_load_time, server_response_time, time_spent FROM analytics WHERE session_id = ? AND ip_address = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db_con->prepare($checkQuery);
            $stmt->bind_param("ss", $sessionId, $ipAddress);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result && $result->num_rows > 0) {
                $existingData = $result->fetch_assoc();
                $existingPagesViewed = $existingData['pages_viewed'];
        
                // Ajustam valorile folosind medii
                $updatedPageLoadTime = (($existingPagesViewed * $existingData['page_load_time']) + $pageLoadTime) / ($existingPagesViewed + 1);
                $updatedServerResponseTime = (($existingPagesViewed * $existingData['server_response_time']) + $serverResponseTime) / ($existingPagesViewed + 1);
                $updatedTimeSpent = (($existingPagesViewed * $existingData['time_spent']) + $timeSpent) / ($existingPagesViewed + 1);
                $updatedPagesViewed = $existingPagesViewed + 1;
        
                // Actulizam inregistrarile existente
                $updateQuery = "
                    UPDATE analytics 
                    SET page_load_time = ?, server_response_time = ?, time_spent = ?, pages_viewed = ? 
                    WHERE id = ?
                ";
                $updateStmt = $this->db_con->prepare($updateQuery);
                $updateStmt->bind_param("dddii", $updatedPageLoadTime, $updatedServerResponseTime, $updatedTimeSpent, $updatedPagesViewed, $existingData['id']);
                
                if (!$updateStmt->execute()) {
                    Debug::log("Eroare la actualizarea tabelei analytics: " . $updateStmt->error);
                    return false;
                }
        
                $updateStmt->close();
            } else {
                // Daca nu exista intrare cu aceeasi sesiune si ip, cream una noua
                $insertQuery = "
                    INSERT INTO analytics (user_id, session_id, ip_address, country, city, device_type, browser, operating_system, page_url, previous_page, page_load_time, server_response_time, time_spent, pages_viewed)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $insertStmt = $this->db_con->prepare($insertQuery);
                $insertStmt->bind_param("isssssssssdddd", $userId, $sessionId, $ipAddress, $country, $city, $deviceType, $browser, $operatingSystem, $pageUrl, $previousPage, $pageLoadTime, $serverResponseTime, $timeSpent, $pagesViewed);
        
                if (!$insertStmt->execute()) {
                    Debug::log("Eroare la inserarea in tabela analytics: " . $insertStmt->error);
                    return false;
                }
        
                $insertStmt->close();
            }
            $stmt->close();
            return true;
        }        
        

        public function getAnalyticsGroupBy($column) {
            $query = "SELECT $column AS name, COUNT(*) AS value FROM analytics GROUP BY $column ORDER BY value DESC";
            $result = $this->db_con->query($query);

            if (!$result || $result->num_rows === 0) {
                return [['name' => 'Fără date', 'value' => 1]];
            }
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getTotalAnalyticsCount() {
            $query = "SELECT COUNT(DISTINCT session_id) AS total FROM analytics";
            $result = $this->db_con->query($query);
            if ($result) {
                $data = $result->fetch_assoc();
                return $data['total'];
            }
            return 0;
        }

        public function getNumberOfUniqueIps() {
            $query = "SELECT COUNT(DISTINCT ip_address) AS ips FROM analytics";
            $result = $this->db_con->query($query);
            if($result) {
                $data = $result->fetch_assoc();
                return $data['ips'];
            }
            return 0;
        }

        public function getTotalPagesViewed() {
            $query = "
                SELECT SUM(latest_pages_viewed) AS total_pages
                FROM (
                    SELECT MAX(pages_viewed) AS latest_pages_viewed
                    FROM analytics
                    GROUP BY session_id
                ) AS session_views
            ";

            $result = $this->db_con->query($query);
            if ($result) {
                $data = $result->fetch_assoc();
                return $data['total_pages'];
            }
            return 0;
        }

        public function getAverageTimeSpent() {
            $query = "SELECT AVG(time_spent) AS avg_time_spent FROM analytics";
            $result = $this->db_con->query($query);
            if ($result) {
                $data = $result->fetch_assoc();
                return $data['avg_time_spent'];
            }
            return 0;
        }

        public function getAveragePageLoadTime() {
            $query = "SELECT AVG(page_load_time) AS avg_page_load_time FROM analytics";
            $result = $this->db_con->query($query);
            if ($result) {
                $data = $result->fetch_assoc();
                return $data['avg_page_load_time'];
            }
            return 0;
        }

        // Ultima pagina vazuta de user
        public function getLastPageViewed() {
            $query = "
                SELECT previous_page 
                FROM analytics 
                WHERE previous_page IS NOT NULL 
                GROUP BY previous_page 
                ORDER BY MAX(created_at) DESC
            ";
        
            $result = $this->db_con->query($query);
        
            if ($result && $result->num_rows > 0) {
                $pages = [];
                while ($row = $result->fetch_assoc()) {
                    $pages[] = $row['previous_page'];
                }
                return $pages;
            }
            return ['N/A'];
        }              
    };
?>