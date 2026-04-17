<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // Add created_at to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_first_login");
    echo "Migration Successful: 'created_at' added to 'users' table.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Migration Note: 'created_at' already exists in 'users' table.\n";
    } else {
        echo "Migration Error: " . $e->getMessage() . "\n";
    }
}
?>
