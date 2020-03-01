<?php
    require_once(__DIR__ . "/../config/credentials.php");
    require_once(__DIR__ . "/../model/event-model.php"); 
    require_once(__DIR__ . "/../model/programmeItem-model.php");
    require_once(__DIR__ . "/../model/image-model.php");

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
            $this->conn->set_charset('utf8');
            $stmt = $this->conn->prepare($query);
            if (isset($params) && count($variables) > 0) {
                try {
                    $stmt->bind_param($params, ...$variables);
                } catch (Exception $e) {
                    throw new Exception("Connection failed (or params are fucked?); $e");
                }
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

            return count($this->executeSelectQuery($query, 's', $email)) == 1;
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
                SELECT E.id , E.artist, E.price, E.ticketsLeft, E.description, E.more, 
                P.id AS programmeId, P.startsAt, P.endsAt, P.location,
                I.id AS imageId, I.url, I.description AS imageDescription
                FROM event AS E
                JOIN programme AS P
                ON E.programmeId = P.id
                JOIN image AS I
                ON E.imageId = I.id
                WHERE E.eventTypeId = ?
            ";
     
            $results = $this->executeSelectQuery($query, 'i', intval($eventType));

            $events = [];
            foreach ($results as $row) {
                $programmeItem = new ProgrammeItem(
                    $row["programmeId"],
                    strtotime($row["startsAt"]),
                    strtotime($row["endsAt"]),
                    $row["location"],
                    $eventType
                );

                $image = new Image(
                    $row["imageId"],
                    $row["url"],
                    $row["imageDescription"]
                );

                $event = new Event(
                    $row["id"],
                    $row["artist"],
                    $row["price"],
                    $row["ticketsLeft"],
                    $programmeItem,
                    $image,
                    $eventType,
                    $row["description"],
                    $row["more"]
                );

                array_push($events, $event);
            }
            return $events;
        }

        public function getEventById($eventId) {
            $query = "
                SELECT E.artist, E.price, E.ticketsLeft, E.eventTypeId, E.description, E.more,
                P.id AS programmeId, P.startsAt, P.endsAt, P.location,
                I.id as imageId, I.url, I.description AS imageDescription
                FROM event AS E
                JOIN programme AS P
                ON E.programmeId = P.id
                JOIN image AS I
                ON E.imageId = I.id
                WHERE E.id = ?
            ";

            $row = $this->executeSelectQuery($query, 'i', intval($eventId))[0];

            $programmeItem = new ProgrammeItem(
                $row["programmeId"],
                strtotime($row["startsAt"]),
                strtotime($row["endsAt"]),
                $row["location"],
                $row["eventTypeId"]
            );

            $image = new Image(
                $row["imageId"],
                $row["url"],
                $row["imageDescription"]
            );

            $event = new Event(
                $eventId,
                $row["artist"],
                $row["price"],
                $row["ticketsLeft"],
                $programmeItem,
                $image,
                $row["eventTypeId"],
                $row["description"],
                $row["more"]
            );

            return $event;
        }

        public function userIsAdmin($email) {
            $query = "
                SELECT isAdmin
                FROM user
                WHERE email = ?
            ";

            return $this->executeSelectQuery($query, 's', $email)[0]["isAdmin"];
        }

        public function sortEvents($eventType) {
            $query = "
            SELECT
            E.id,
            E.artist,
            E.price,
            E.ticketsLeft,
            E.description,
            E.more,
            P.id AS programmeId,
            P.startsAt,
            P.endsAt,
            P.location,
            I.id AS imageId,
            I.url,
            I.description AS imageDescription
            FROM event AS
             E
            JOIN
            programme AS P
            ON
            E.programmeId = P.id
            JOIN
            image AS I
            ON
            E.imageId = I.id
            WHERE
            E.eventTypeId = ?
            ORDER BY
            price ASC
            ";

            $results = $this->executeSelectQuery($query, 'i', intval($eventType));
            $events = [];

            foreach ($results as $row) {
                $programmeItem = new ProgrammeItem(
                    $row["programmeId"],
                    strtotime($row["startsAt"]),
                    strtotime($row["endsAt"]),
                    $row["location"],
                    $eventType
                );

                $image = new Image(
                    $row["imageId"],
                    $row["url"],
                    $row["imageDescription"]
                );

                $event = new Event(
                    $row["id"],
                    $row["artist"],
                    $row["price"],
                    $row["ticketsLeft"],
                    $programmeItem,
                    $image,
                    $eventType,
                    $row["description"],
                    $row["more"]
                );

                array_push($events, $event);
            }
            return $events;
        }

        public function updateEvent($event) {
            $query = "
                UPDATE event
                SET something = ?
                WHERE id = ?

                SELECT E.artist, E.price, E.ticketsLeft, E.eventTypeId, E.description, E.more,
                P.id AS programmeId, P.startsAt, P.endsAt, P.location,
                I.id as imageId, I.url, I.description AS imageDescription
                FROM event AS E
                JOIN programme AS P
                ON E.programmeId = P.id
                JOIN image AS I
                ON E.imageId = I.id
                WHERE E.id = ?

                i dont know how to write queries help
            ";

            $row = $this->executeSelectQuery($query, 'i', intval($eventId))[0];
        }
    }
?>