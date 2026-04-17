<?php

$conn = mysqli_connect(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    'railway',
    getenv('MYSQLPORT')
);

if (!$conn) {
    die("DB Connection Failed: " . mysqli_connect_error());
}
?>
