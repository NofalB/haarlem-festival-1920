<?php
    require_once("../config/credentials.php");

    class dataLayer {
        private static $instance;
        private $conn;

        private function __construct() {
            $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DB, DB_PORT);
        }

        // Initialize instance if not already intitialized. Then returns that instance.
        public static function getInstance() {
            return !self::$instance ? new dataLayer() : self::$instance;
        }

        public function doesUserExist($email) {
            $query = $this->conn->prepare("SELECT email FROM user WHERE email = ?");
            $query->bind_param('s', $email);
            $query->execute();
            $result = $query->get_result();

            if (!$result) {
                $error = $this->conn->error;
                throw new Exception("Database error: '$error'");
            } else {
                return $result->num_rows > 0;
            }
        }

        public function registerUser($user) {
            $user->password = password_hash($user->password, PASSWORD_DEFAULT);
            $query = $this->conn->prepare("INSERT INTO user (fullName, email, password, isAdmin) VALUES (?, ?, ?, 0)");
            $query->bind_param('sss', $user->fullname, $user->email, $user->password);
            $query->execute();

            return $query->affected_rows == 1;
        }

        public function getHashedPass($email) {
            $query = $this->conn->prepare("SELECT password FROM user WHERE email = ?");
            $query->bind_param('s', $email);
            $query->execute();
            $result = $query->get_result();

            if (!$result) {
                $error = $this->conn->error;
                throw new Exception("Database error: '$error'");
            } else {
                if ($result->num_rows > 0) {
                    return $result->fetch_row()[0];
                } else {
                    return false;
                }
            }
        }

        public function getFullName($email) {
            $query = $this->conn->prepare("SELECT fullName FROM user WHERE email = ?");
            $query->bind_param('s', $email);
            $query->execute();
            $result = $query->get_result(); 

            if (!$result) {
                $error = $this->conn->error;
                throw new Exception("Database error: '$error'");
            } else {
                if ($result->num_rows > 0) {
                    return $result->fetch_row()[0];
                } else {
                    return false;
                }
            }
        }

        public function getAllEvents() { // UNFINISHED
            $query = $this->conn->prepare("SELECT artist, price, event.eventTypeId, location, startsAt, endsAt FROM event JOIN programme ON event.programmeId = programme.id");
            $query->execute();
            $result = $query->get_result();

            if (!$result) {
                $error = $this->conn->error;
                throw new Exception("Database error: '$error'");
            } else {
                if ($result->num_rows > 0) {
                    return $result->fetch_row()[0];
                } else {
                    return false;
                }
            }
        }
    }
?>