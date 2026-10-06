<?php // Open the PHP section for the login page.
declare(strict_types=1); // Enable strict type checking for this login script.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.

if (isLoggedIn()) { // Check whether the visitor is already logged in.
    header('Location: index.php'); // Send already-authenticated users to the dashboard.
    exit; // Stop running the login page after redirecting.
} // End the already-logged-in check.

$error = ''; // Prepare an empty error message.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Process the login form only when it is submitted.
    $username = trim($_POST['username'] ?? ''); // Read and trim the username field.
    $password = (string)($_POST['password'] ?? ''); // Read the password field as a string.
    $stmt = $pdo->prepare('SELECT id, name, username, password, role, active FROM users WHERE username = ? LIMIT 1'); // Prepare a secure query to find the account.
    $stmt->execute([$username]); // Execute the query using the supplied username.
    $user = $stmt->fetch(); // Read the matching account if one exists.

    if ($user && (int)$user['active'] === 1 && password_verify($password, $user['password'])) { // Accept the login only when the account is active and the password is correct.
        $_SESSION['user'] = [ // Start the session record for the logged-in account.
            'id' => (int)$user['id'], // Store the account identifier in the session.
            'name' => $user['name'], // Store the display name in the session.
            'username' => $user['username'], // Store the username in the session.
            'role' => $user['role'], // Store the account role in the session.
        ]; // Finish the session user array.
        header('Location: index.php'); // Send the successful login to the dashboard.
        exit; // Stop running the login page after successful authentication.
    } // End the successful login condition.
    $error = 'Invalid username or password, or the account is inactive.'; // Display a generic error for failed login attempts.
} // End form submission processing.
?>
<!doctype html> <!-- Tell the browser that this document uses the HTML5 standard. -->
<html lang="en"> <!-- Start the login document. -->
<head> <!-- Start the login document head. -->
<meta charset="utf-8"> <!-- Use UTF-8 character encoding. -->
<meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Make the login screen responsive. -->
<title>POS SYSTEM - Login</title> <!-- Set the browser tab title. -->
<link rel="stylesheet" href="assets/css/style.css"> <!-- Load the shared POS styling. -->
</head> <!-- Close the document head. -->
<body class="login-page"> <!-- Start the login page body. -->
<div class="login-card"> <!-- Create the centered login card. -->
<div class="login-logo">P</div> <!-- Display the POS logo. -->
<h1>POS SYSTEM</h1> <!-- Display the application name. -->
<p class="muted">Sign in to manage sales and stock.</p> <!-- Explain the purpose of the login screen. -->
<?php if ($error !== ''): ?> <!-- Check whether a login error should be shown. -->
<div class="alert error"><?= e($error) ?></div> <!-- Show the escaped login error. -->
<?php endif; ?> <!-- End the error display condition. -->
<form method="post" autocomplete="off"> <!-- Start the secure server-processed login form. -->
<label>Username<input type="text" name="username" required autofocus></label> <!-- Ask the user for the account username. -->
<label>Password<input type="password" name="password" required></label> <!-- Ask the user for the account password. -->
<button class="btn primary full" type="submit">Login</button> <!-- Submit the login form. -->
</form> <!-- Close the login form. -->
</div> <!-- Close the login card. -->
</body> <!-- Close the login page body. -->
</html> <!-- Close the login document. -->
