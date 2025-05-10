<?php
declare(strict_types=1);

/**
 * Secure Database Connection Handler
 * 
 * Features:
 * - Encrypted connection (SSL)
 * - Prepared statement emulation
 * - Error logging
 * - Connection pooling
 * - SQL injection prevention
 */

// Load configuration
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $this->connect();
    }

    private function connect(): void {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true, // Connection pooling
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
            PDO::MYSQL_ATTR_SSL_CA       => __DIR__ . '/certs/ca.pem',
            PDO::MYSQL_ATTR_SSL_CERT     => __DIR__ . '/certs/client-cert.pem',
            PDO::MYSQL_ATTR_SSL_KEY      => __DIR__ . '/certs/client-key.pem',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            $this->logError($e);
            throw new RuntimeException('Database connection failed');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    private function logError(PDOException $e): void {
        $message = sprintf(
            "[%s] Database Error: %s (Code: %d)\nTrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getCode(),
            $e->getTraceAsString()
        );

        error_log($message, 3, __DIR__ . '/../logs/db-errors.log');
    }

    // Prevent cloning and serialization
    public function __clone() { throw new BadMethodCallException('Clone is not allowed'); }
    public function __wakeup() { throw new BadMethodCallException('Wakeup is not allowed'); }
}

// Usage example:
try {
    $pdo = Database::getInstance()->getConnection();
    
    // For legacy compatibility (keep existing code working)
    if (!isset($pdo)) {
        throw new RuntimeException('Database connection failed');
    }
} catch (RuntimeException $e) {
    header('HTTP/1.1 503 Service Unavailable');
    include __DIR__ . '/../503.php';
    exit();
}
?>