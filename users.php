<?php // Open the PHP section for principal administrator account management.
declare(strict_types=1); // Enable strict type checking for the user management page.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requirePrincipalAdmin(); // Allow only the principal administrator to create or delete accounts.
$pageTitle = 'User Accounts'; // Set the page title.
$message = ''; // Prepare a success message container.
$error = ''; // Prepare an error message container.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Process account creation or deletion requests.
    $action = $_POST['action'] ?? ''; // Read the requested account action.
    if ($action === 'create') { // Handle creation of a new regular account.
        $name = trim((string)($_POST['name'] ?? '')); // Read the new account's display name.
        $username = trim((string)($_POST['username'] ?? '')); // Read the new account's username.
        $password = (string)($_POST['password'] ?? ''); // Read the new account's plain-text password before hashing.
        $role = (string)($_POST['role'] ?? 'cashier'); // Read the requested role and default to cashier.
        if ($name === '' || $username === '' || $password === '') { // Validate the required account fields.
            $error = 'Name, username and password are required.'; // Explain the missing fields.
        } elseif (!in_array($role, ['admin', 'cashier'], true)) { // Restrict new accounts to regular admin or cashier roles.
            $error = 'Only Admin and Cashier accounts can be created here.'; // Explain the allowed account roles.
        } elseif (strlen($password) < 6) { // Require a basic minimum password length.
            $error = 'Password must contain at least 6 characters.'; // Tell the principal administrator how to correct the password.
        } else { // Continue when account data is valid.
            try { // Start the protected account creation operation.
                $stmt = $pdo->prepare('INSERT INTO users (name, username, password, role, active) VALUES (?, ?, ?, ?, 1)'); // Prepare a secure account insert query.
                $stmt->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role]); // Hash the password and create the new account.
                $message = ucfirst($role) . ' account created successfully.'; // Confirm creation of the requested account type.
            } catch (PDOException $exception) { // Catch duplicate username and other database errors.
                $error = $exception->getCode() === '23000' ? 'That username is already in use.' : 'The account could not be created.'; // Convert the database error into a user-friendly message.
            } // Finish the account creation error handler.
        } // Finish account validation and creation.
    } elseif ($action === 'delete') { // Handle deletion of an existing regular account.
        $id = (int)($_POST['id'] ?? 0); // Read the target account identifier.
        $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ?'); // Prepare a query to inspect the account before deletion.
        $stmt->execute([$id]); // Execute the account inspection query.
        $target = $stmt->fetch(); // Read the target account if it exists.
        if (!$target) { // Check whether the requested account exists.
            $error = 'Account not found.'; // Tell the principal administrator that the target does not exist.
        } elseif ((int)$target['id'] === (int)$_SESSION['user']['id']) { // Prevent the principal administrator from deleting their current account.
            $error = 'The principal administrator account cannot delete itself.'; // Explain the protection.
        } elseif ($target['role'] === 'principal_admin') { // Prevent deletion of any principal administrator account.
            $error = 'The principal administrator account is protected.'; // Explain the protection.
        } else { // Continue with deletion when the target is a normal account.
            try { // Start the protected account deletion operation.
                $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = ?'); // Prepare the account delete query.
                $deleteStmt->execute([$id]); // Delete the selected admin or cashier account.
                $message = 'Account deleted successfully.'; // Confirm the account deletion.
            } catch (PDOException $exception) { // Catch any database error that prevents account deletion.
                $error = 'The account could not be deleted because of a database constraint.'; // Show a simple database error message.
            } // Finish the deletion error handler.
        } // Finish the deletion permission checks.
    } // Finish account action processing.
} // End account form processing.

$accounts = $pdo->query("SELECT id, name, username, role, active, created_at FROM users ORDER BY CASE WHEN role = 'principal_admin' THEN 0 ELSE 1 END, id ASC")->fetchAll(); // Load all accounts with the principal account first.
require __DIR__ . '/includes/header.php'; // Load the shared application header.
?>
<?php if ($message !== ''): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?> <!-- Show a successful account operation message. -->
<?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?> <!-- Show an account operation error message. -->
<div class="two-col users-layout"> <!-- Start the account management two-column layout. -->
<div class="panel form-panel"> <!-- Start the create-account form panel. -->
<div class="panel-head"><div><h2>Create Account</h2><span class="muted">Only FORSOH can create regular accounts.</span></div></div> <!-- Show the create-account heading. -->
<form method="post"> <!-- Start the account creation form. -->
<input type="hidden" name="action" value="create"> <!-- Tell the server that this form creates an account. -->
<label>Full name<input type="text" name="name" required placeholder="e.g. John Doe"></label> <!-- Collect the new account's display name. -->
<label>Username<input type="text" name="username" required placeholder="e.g. cashier01"></label> <!-- Collect the unique login username. -->
<label>Password<input type="password" name="password" minlength="6" required placeholder="Minimum 6 characters"></label> <!-- Collect the new account password. -->
<label>Role<select name="role"><option value="cashier">Cashier</option><option value="admin">Admin</option></select></label> <!-- Let FORSOH decide whether the account is an Admin or Cashier. -->
<button class="btn primary full" type="submit">Create Account</button> <!-- Submit the new account creation request. -->
</form> <!-- Close the account creation form. -->
</div> <!-- Close the create-account panel. -->
<div class="panel"> <!-- Start the existing account list panel. -->
<div class="panel-head"><div><h2>Existing Accounts</h2><span class="muted">The principal administrator is protected.</span></div><span class="badge"><?= count($accounts) ?> account(s)</span></div> <!-- Show the account list heading. -->
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody> <!-- Start the account table. -->
<?php foreach ($accounts as $account): ?> <!-- Loop through every account. -->
<tr> <!-- Start one account row. -->
<td><strong><?= e($account['name']) ?></strong></td> <!-- Show the account display name. -->
<td><?= e($account['username']) ?></td> <!-- Show the account username. -->
<td><?= e(ucwords(str_replace('_', ' ', $account['role']))) ?></td> <!-- Show the human-readable account role. -->
<td><?= (int)$account['active'] === 1 ? '<span class="status good">Active</span>' : '<span class="status danger">Inactive</span>' ?></td> <!-- Show whether the account can currently log in. -->
<td><?= e(date('d M Y', strtotime($account['created_at']))) ?></td> <!-- Show when the account was created. -->
<td><?php if ($account['role'] !== 'principal_admin'): ?><form method="post" onsubmit="return confirmDelete('account')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$account['id'] ?>"><button class="btn small danger-btn" type="submit">Delete</button></form><?php else: ?><span class="protected-label">Protected</span><?php endif; ?></td> <!-- Show a delete button for regular accounts and a protected label for FORSOH. -->
</tr> <!-- Close one account row. -->
<?php endforeach; ?> <!-- Finish the account loop. -->
</tbody></table></div> <!-- Close the account table. -->
</div> <!-- Close the existing account panel. -->
</div> <!-- Close the account management layout. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared application footer. ?>
