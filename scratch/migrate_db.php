<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add unique index to marks table
    $pdo->exec("ALTER TABLE marks ADD UNIQUE INDEX IF NOT EXISTS unique_mark (student_id, subject_id, exam_type, date)");
    echo "Database migrated: Marks unique index added successfully.";
} catch (Exception $e) {
    echo "Database migration error: " . $e->getMessage();
}
?>
Remove-Item -Recurse -Force .git