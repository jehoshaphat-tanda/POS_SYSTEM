<?php // Open the PHP section for authentication helpers.
declare(strict_types=1); // Enable strict type checking in this helper file.

if (session_status() !== PHP_SESSION_ACTIVE) { // Check whether a PHP session is already running.
    session_start(); // Start the session so login information can be stored.
} // End the session check.

function e(string $value): string { // Create a helper for safe HTML output.
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); // Escape special HTML characters to prevent HTML injection.
} // End the output escaping helper.

function isLoggedIn(): bool { // Create a helper that tells the application whether a user is logged in.
    return isset($_SESSION['user']); // Return true when the user session exists.
} // End the login state helper.

function currentUser(): ?array { // Create a helper that returns the current logged-in user.
    return $_SESSION['user'] ?? null; // Return the stored user array or null when nobody is logged in.
} // End the current user helper.

function requireLogin(): void { // Create a helper that protects pages from unauthenticated users.
    if (!isLoggedIn()) { // Check whether the visitor is not logged in.
        header('Location: login.php'); // Send the visitor to the login page.
        exit; // Stop the current script after the redirect.
    } // End the login requirement check.
} // End the login protection helper.

function requireAdmin(): void { // Create a helper that allows regular admins and the principal admin.
    requireLogin(); // First make sure the visitor is logged in.
    $role = $_SESSION['user']['role'] ?? ''; // Read the logged-in user's role safely.
    if (!in_array($role, ['principal_admin', 'admin'], true)) { // Reject cashiers and unknown roles on admin-only pages.
        http_response_code(403); // Return the HTTP forbidden status code.
        exit('Access denied. Admin access is required.'); // Stop the request with a clear message.
    } // End the administrator role check.
} // End the regular administrator protection helper.

function requirePrincipalAdmin(): void { // Create a helper for actions reserved for FORSOH.
    requireLogin(); // First make sure the visitor is logged in.
    $role = $_SESSION['user']['role'] ?? ''; // Read the current user's role.
    if ($role !== 'principal_admin') { // Permit only the principal administrator role.
        http_response_code(403); // Return the HTTP forbidden status code for unauthorized users.
        exit('Access denied. Principal administrator access is required.'); // Stop the request with a clear message.
    } // End the principal administrator role check.
} // End the principal administrator protection helper.
?>
