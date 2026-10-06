<?php // Open the PHP section for logout processing.
require_once __DIR__ . '/includes/auth.php'; // Load session helpers so the current session can be removed.
$_SESSION = []; // Clear every value stored in the current session.
if (ini_get('session.use_cookies')) { // Check whether PHP sessions are using cookies.
    $params = session_get_cookie_params(); // Read the current session cookie settings.
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']); // Expire the session cookie in the browser.
} // Finish the session cookie cleanup block.
session_destroy(); // Destroy the current server-side session.
header('Location: login.php'); // Return the user to the login page.
exit; // Stop the logout script.
?>
