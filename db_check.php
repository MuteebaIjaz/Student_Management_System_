<?php
require_once __DIR__ . '/includes/db.php'; // Use db.php which has $pdo
function printSchema($pdo, $table) {
    echo "--- Table: $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
    } catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
    echo "\n";
}
printSchema($pdo, 'users');
printSchema($pdo, 'students');
printSchema($pdo, 'attendance');
echo "MySQL Version: " . $pdo->query("SELECT VERSION()")->fetchColumn() . "\n";
?>
