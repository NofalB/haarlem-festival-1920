<?php
    require_once("../config/credentials.php");
    require_once("../model/event-model.php"); 
    require_once("../model/programmeItem-model.php");

    class dataLayer {
        private static $instance;
        private $conn;

        public function __construct() {
            $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DB, DB_PORT);
        }

        // Initialize instance if not already intitialized. Then returns that instance.
        // if this self instance then return new data layer otherwise ::(references constants or statics) do this
        public static function getInstance() {
            return !self::$instance ? new dataLayer() : self::$instance;
        }

        private function executeQuery($query, $params, ...$variables) {
            $stmt = $this->conn->prepare($query);
            if (isset($params) && count($variables) > 0) {
                $stmt->bind_param($params, ...$variables);
            }
            $stmt->execute();

            $error = $this->conn->error;
            if ($error) {
                throw new Exception("Database error: '$error'");
            }

            return $stmt;
        }

        private function executeSelectQuery($query, $params, ...$variables) {
            return $this->executeQuery($query, $params, ...$variables)->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        private function executeEditQuery($query, $params, ...$variables) {
            return $this->executeQuery($query, $params, ...$variables)->affected_rows;
        }

        public function doesUserExist($email) {
            $query = "
                SELECT email
                FROM user
                WHERE email = ?
            ";
            return $this->executeSelectQuery($query, 's', $email)[0]["email"] == $email;
        }

        public function registerUser($user) {
            $query = "
                INSERT INTO user (fullName, email, password, isAdmin)
                VALUES (?, ?, ?, 0)
            ";

            return $this->executeEditQuery($query, 'sss', $user->fullname, $user->email, $user->password) == 1;
        }

        public function getHashedPass($email) {
            $query = "
                SELECT password
                FROM user
                WHERE email = ?
            ";

            return $this->executeSelectQuery($query, 's', $email)[0]["password"];
        }

        public function getFullName($email) {
            $query = "
                SELECT fullName
                FROM user
                WHERE email = ?
            ";

            return $this->executeSelectQuery($query, 's', $email)[0]["fullName"];
        }

        public function getEvents($eventType) {
            $query = "
                SELECT E.id, E.artist, E.price, E.ticketsLeft, E.programmeId, E.imageId, E.description, E.more, P.id, P.startsAt, P.endsAt, P.location
                FROM event AS E
                JOIN programme AS P
                ON E.programmeId = P.id
                WHERE E.eventTypeId = ?
            ";
   
            $events = [];
            $results = $this->executeSelectQuery($query, 'i', intval($eventType));

            foreach ($results as $row) {
                $programmeItem = new ProgrammeItem(
                    $row["id"],
                    $row["startsAt"],
                    $row["endsAt"],
                    $row["location"]
                );

                $event = new Event(
                    $row["E.id"],
                    $row["E.artist"],
                    $row["E.price"],
                    $row["E.ticketsLeft"],
                    $programmeItem,
                    $eventType,
                    $row["E.imageId"],
                    $row["E.description"],
                    $row["E.more"]
                );

                array_push($events, $event);
            }
            return $events;
        }

        public function getEventPage($eventType) {
            $query = "
                someone write this cursed query please lol
            ";
            
            return $this->executeSelectQuery($query, 'i', intval($eventType));
        }
    }
?>