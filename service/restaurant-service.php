<?php
    require_once(__DIR__ . "/../lib/dal.php");
    require_once(__DIR__ . "/../model/restaurant-model.php");

    class restaurantService {
        private static $instance;
        private $dal;

        public function __construct() {
            self::$instance = $this; 
            $this->dal = dataLayer::getInstance();
		}

		// Initialize instance if not already intitialized. Then returns that instance.
        public static function getInstance() {
            return !self::$instance ? new restaurantService() : self::$instance;
		}

		public function getAllRestaurants() {
			return $this->dal->getAllRestaurants();
        }
        
        public function getRestaurant($id) {
            return $this->dal->getRestaurant($id);
        }

        public function updateRestaurant($restaurant) {
            return $this->dal->updateRestaurant($restaurant);
        }

        public function deleteRestaurant($id) {
            return $this->dal->deleteRestaurant($id);
        }
	}
?>