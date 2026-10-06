<?php // Open the PHP section for sales history.
declare(strict_types=1); // Enable strict type checking for the sales history page.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requireLogin(); // Require a logged-in user to view sales history.
$pageTitle = 'Sales History'; // Set the page title.
$search = trim((string)($_GET['search'] ?? '')); // Read the optional search term.
if ($search !== '') { // Use a filtered query when a search term exists.
    $stmt = $pdo->prepare("SELECT s.id, s.invoice_no, s.sale_date, s.total_amount, COALESCE(s.cashier_name, u.name, 'Deleted account') AS cashier, GROUP_CONCAT(CONCAT(si.product_name, ' x', si.quantity) ORDER BY si.id SEPARATOR ', ') AS items FROM sales s LEFT JOIN users u ON u.id = s.user_id INNER JOIN sale_items si ON si.sale_id = s.id WHERE s.invoice_no LIKE ? OR COALESCE(s.cashier_name, u.name, 'Deleted account') LIKE ? OR si.product_name LIKE ? GROUP BY s.id ORDER BY s.id DESC"); // Prepare a sales history query that can search invoice, cashier, or product.
    $term = '%' . $search . '%'; // Wrap the search term for SQL partial matching.
    $stmt->execute([$term, $term, $term]); // Execute the sales search query.
    $sales = $stmt->fetchAll(); // Read the matching sales.
} else { // Handle the case where no search is supplied.
    $sales = $pdo->query("SELECT s.id, s.invoice_no, s.sale_date, s.total_amount, COALESCE(s.cashier_name, u.name, 'Deleted account') AS cashier, GROUP_CONCAT(CONCAT(si.product_name, ' x', si.quantity) ORDER BY si.id SEPARATOR ', ') AS items FROM sales s LEFT JOIN users u ON u.id = s.user_id INNER JOIN sale_items si ON si.sale_id = s.id GROUP BY s.id ORDER BY s.id DESC")->fetchAll(); // Read all sales with a compact list of items.
} // Finish the sales history query selection.
require __DIR__ . '/includes/header.php'; // Load the shared application header.
?>
<div class="panel"> <!-- Start the sales history panel. -->
<div class="panel-head"><div><h2>Previous Sales</h2><span class="muted">Date, products, quantities, cashier and total amount.</span></div></div> <!-- Show the sales history heading. -->
<form class="search-form" method="get"> <!-- Start the sales search form. -->
<input name="search" value="<?= e($search) ?>" placeholder="Search invoice, product or cashier..."> <!-- Collect a sales history search term. -->
<button class="btn" type="submit">Search</button> <!-- Submit the sales history search. -->
<?php if ($search !== ''): ?><a class="btn" href="sales.php">Clear</a><?php endif; ?> <!-- Provide a clear-search link when filtering is active. -->
</form> <!-- Close the sales search form. -->
<div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Date</th><th>Products / Qty</th><th>Cashier</th><th class="right">Total</th><th></th></tr></thead><tbody> <!-- Start the sales history table. -->
<?php foreach ($sales as $sale): ?> <!-- Loop through each sale record. -->
<tr><td><a href="sale_view.php?id=<?= (int)$sale['id'] ?>"><?= e($sale['invoice_no']) ?></a></td><td><?= e(date('d M Y, H:i', strtotime($sale['sale_date']))) ?></td><td><?= e((string)$sale['items']) ?></td><td><?= e($sale['cashier']) ?></td><td class="right"><strong><?= number_format((float)$sale['total_amount'], 0) ?> XAF</strong></td><td><a href="sale_view.php?id=<?= (int)$sale['id'] ?>">View</a></td></tr> <!-- Show one completed sale. -->
<?php endforeach; ?> <!-- Finish the sales loop. -->
<?php if (!$sales): ?><tr><td colspan="6" class="empty">No sales found.</td></tr><?php endif; ?> <!-- Show a message when there are no matching sales. -->
</tbody></table></div> <!-- Close the sales history table. -->
</div> <!-- Close the sales history panel. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared application footer. ?>
