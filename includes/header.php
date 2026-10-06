<?php // Open the PHP section for the shared application header.
require_once __DIR__ . '/auth.php'; // Load authentication helpers for protected pages.
requireLogin(); // Make sure the page is accessible only to logged-in users.
$user = currentUser(); // Store the logged-in user's data for display.
$pageTitle = $pageTitle ?? 'Dashboard'; // Use the page title supplied by the page or fall back to Dashboard.
$currentPage = basename($_SERVER['PHP_SELF']); // Find the current page name for active navigation styling.
?>
<!doctype html> <!-- Tell the browser that this document uses the HTML5 standard. -->
<html lang="en"> <!-- Start the HTML document and declare English as the document language. -->
<head> <!-- Start the document head section. -->
<meta charset="utf-8"> <!-- Use UTF-8 so text such as XAF and names displays correctly. -->
<meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Make the interface responsive on phones and computers. -->
<title><?= e($pageTitle) ?> - POS SYSTEM</title> <!-- Show the current page title in the browser tab. -->
<link rel="stylesheet" href="assets/css/style.css"> <!-- Load the main POS SYSTEM stylesheet. -->
</head> <!-- Close the document head section. -->
<body> <!-- Start the visible page body. -->
<div class="app-shell"> <!-- Create the main application layout container. -->
<aside class="sidebar"> <!-- Start the left navigation sidebar. -->
<div class="brand"> <!-- Start the POS SYSTEM brand area. -->
<div class="brand-mark">P</div> <!-- Display the simple POS logo mark. -->
<div class="brand-text"><strong>POS SYSTEM</strong><span>Shop Manager</span></div> <!-- Display the product name and subtitle. -->
</div> <!-- Close the brand area. -->
<nav class="nav-menu"> <!-- Start the application navigation menu. -->
<a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Dashboard</a> <!-- Link to the dashboard and mark it active when selected. -->
<a class="nav-link <?= $currentPage === 'sale.php' ? 'active' : '' ?>" href="sale.php">New Sale</a> <!-- Link to the sales screen. -->
<a class="nav-link <?= in_array($currentPage, ['products.php'], true) ? 'active' : '' ?>" href="products.php">Products</a> <!-- Link to product and stock management. -->
<a class="nav-link <?= in_array($currentPage, ['sales.php', 'sale_view.php'], true) ? 'active' : '' ?>" href="sales.php">Sales History</a> <!-- Link to past transactions and receipts. -->
<?php if (($user['role'] ?? '') === 'principal_admin'): ?> <!-- Show account management only to the principal administrator. -->
<a class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>" href="users.php">User Accounts</a> <!-- Link to the principal administrator's account management page. -->
<?php endif; ?> <!-- End the principal administrator navigation condition. -->
</nav> <!-- Close the navigation menu. -->
<div class="sidebar-bottom"> <!-- Start the bottom section of the sidebar. -->
<div class="user-card"> <!-- Start the logged-in user summary card. -->
<div class="avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div> <!-- Show the first letter of the logged-in user's name as an avatar. -->
<div><strong><?= e($user['name']) ?></strong><span><?= e(ucwords(str_replace('_', ' ', $user['role']))) ?></span></div> <!-- Display the logged-in user's name and role. -->
</div> <!-- Close the user summary card. -->
<a class="logout-link" href="logout.php">Log out</a> <!-- Provide a logout link for the current session. -->
</div> <!-- Close the sidebar bottom section. -->
</aside> <!-- Close the sidebar. -->
<main class="main-content"> <!-- Start the main content area. -->
<header class="topbar"> <!-- Start the top header of the current page. -->
<div><h1><?= e($pageTitle) ?></h1><p><?= e(date('l, d M Y')) ?></p></div> <!-- Show the current page title and current date. -->
<a class="btn primary" href="sale.php">+ New Sale</a> <!-- Provide a quick action button for a new sale. -->
</header> <!-- Close the top header. -->
<section class="content-area"> <!-- Start the page-specific content area. -->
