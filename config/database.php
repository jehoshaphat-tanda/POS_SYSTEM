<?php // Open the PHP section for the database configuration file.
declare(strict_types=1); // Enable strict type checking for safer PHP execution.

$host = '127.0.0.1'; // Store the MySQL server address used by the XAMPP installation.
$dbName = 'pos_system'; // Store the name of the MySQL database used by the POS application.
$dbUser = 'root'; // Store the default XAMPP MySQL username.
$dbPassword = ''; // Store the default XAMPP MySQL password, which is empty unless changed.
$charset = 'utf8mb4'; // Use UTF-8 capable character encoding for names and text.

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}"; // Build the PDO connection string.
$options = [ // Start the PDO configuration array.
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Make PDO throw exceptions when a database error happens.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return database rows as associative arrays by default.
    PDO::ATTR_EMULATE_PREPARES => false, // Ask MySQL to use real prepared statements for safer queries.
]; // Finish the PDO configuration array.

try { // Start the protected database connection block.
    $pdo = new PDO($dsn, $dbUser, $dbPassword, $options); // Create the PDO object and connect to MySQL.
} catch (PDOException $exception) { // Catch a database connection error.
    http_response_code(500); // Tell the browser that a server-side error occurred.
    exit('Database connection failed. Import database/pos_system.sql and check config/database.php.'); // Stop the application with a useful setup message.
} // End the connection error handler.
?>
