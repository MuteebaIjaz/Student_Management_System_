<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Database Singleton Factory using PDO
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            if (!extension_loaded('pdo_mysql')) {
                $availableDrivers = implode(', ', PDO::getAvailableDrivers());
                die("Database Connection Failed: pdo_mysql extension not loaded. Available PDO drivers: " . $availableDrivers);
            }

            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $availableDrivers = implode(', ', PDO::getAvailableDrivers());
            die("Database Connection Failed: " . $e->getMessage() . ". Available PDO drivers: " . $availableDrivers);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
}

// Global variable for easy access
$pdo = Database::getInstance();
?>
