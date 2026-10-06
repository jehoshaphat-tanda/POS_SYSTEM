<?php // Open the PHP section for the dashboard page.
declare(strict_types=1); // Enable strict type checking for the dashboard.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requireLogin(); // Require a valid account before showing the dashboard.
$pageTitle = 'Dashboard'; // Set the page title used by the shared header.
$today = date('Y-m-d'); // Store today's date for dashboard calculations.
$todayStmt = $pdo->prepare('SELECT COUNT(*) AS count_sales, COALESCE(SUM(total_amount), 0) AS total_sales FROM sales WHERE DATE(sale_date) = ?'); // Prepare today's sales summary query.
$todayStmt->execute([$today]); // Execute today's sales summary query.
$todayStats = $todayStmt->fetch(); // Read today's sales count and total amount.
$stock = $pdo->query('SELECT COUNT(*) AS products, COALESCE(SUM(quantity), 0) AS units, COALESCE(SUM(quantity <= 0), 0) AS out_of_stock FROM products')->fetch(); // Read the current product and stock totals.
$recentSales = $pdo->query("SELECT s.id, s.invoice_no, s.sale_date, s.total_amount, COALESCE(s.cashier_name, u.name, 'Deleted account') AS cashier FROM sales s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.id DESC LIMIT 6")->fetchAll(); // Read the six most recent sales.
$lowStock = $pdo->query('SELECT id, name, price, quantity FROM products WHERE quantity <= 5 ORDER BY quantity ASC, name ASC LIMIT 8')->fetchAll(); // Read products with five or fewer units for the low-stock panel.
require __DIR__ . '/includes/header.php'; // Load the shared application header.
?>
<div class="stats-grid"> <!-- Start the dashboard statistic cards. -->
<div class="stat-card"><span>Today's Sales</span><strong><?= number_format((float)$todayStats['total_sales'], 0) ?> XAF</strong><small><?= (int)$todayStats['count_sales'] ?> transaction(s)</small></div> <!-- Show the total money collected today. -->
<div class="stat-card"><span>Sales Today</span><strong><?= (int)$todayStats['count_sales'] ?></strong><small>Completed transactions</small></div> <!-- Show the number of completed sales today. -->
<div class="stat-card"><span>Current Stock</span><strong><?= (int)$stock['units'] ?></strong><small><?= (int)$stock['products'] ?> product type(s)</small></div> <!-- Show the total number of units currently in stock. -->
<div class="stat-card"><span>Out of Stock</span><strong><?= (int)$stock['out_of_stock'] ?></strong><small>Products needing restock</small></div> <!-- Show the number of products with zero stock. -->
</div> <!-- Close the dashboard statistic grid. -->
<div class="two-col"> <!-- Start the two-column dashboard section. -->
<div class="panel"> <!-- Start the recent sales panel. -->
<div class="panel-head"><div><h2>Recent Sales</h2><span class="muted">Latest completed transactions</span></div><a href="sales.php">View all</a></div> <!-- Show the recent sales panel heading. -->
<?php if (!$recentSales): ?> <!-- Check whether any sales have been recorded. -->
<div class="empty">No sales recorded yet.</div> <!-- Tell the user that no transactions exist. -->
<?php else: ?> <!-- Render the sales table when transactions exist. -->
<div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Date</th><th>Cashier</th><th class="right">Total</th></tr></thead><tbody> <!-- Start the recent sales table. -->
<?php foreach ($recentSales as $sale): ?> <!-- Loop through each recent sale. -->
<tr><td><a href="sale_view.php?id=<?= (int)$sale['id'] ?>"><?= e($sale['invoice_no']) ?></a></td><td><?= e(date('d M Y, H:i', strtotime($sale['sale_date']))) ?></td><td><?= e($sale['cashier']) ?></td><td class="right"><strong><?= number_format((float)$sale['total_amount'], 0) ?> XAF</strong></td></tr> <!-- Display one recent sale row. -->
<?php endforeach; ?> <!-- Finish the recent sales loop. -->
</tbody></table></div> <!-- Close the recent sales table. -->
<?php endif; ?> <!-- Finish the recent sales conditional rendering. -->
</div> <!-- Close the recent sales panel. -->
<div class="panel"> <!-- Start the low-stock panel. -->
<div class="panel-head"><div><h2>Low Stock</h2><span class="muted">Five units or less</span></div><a href="products.php">Manage</a></div> <!-- Show the low-stock panel heading. -->
<?php if (!$lowStock): ?> <!-- Check whether any products are low on stock. -->
<div class="empty">Everything is well stocked.</div> <!-- Tell the user that no product is low on stock. -->
<?php else: ?> <!-- Render the low-stock list when needed. -->
<div class="stock-list"> <!-- Start the low-stock item list. -->
<?php foreach ($lowStock as $product): ?> <!-- Loop through the low-stock products. -->
<div class="stock-item"><div><strong><?= e($product['name']) ?></strong><span><?= number_format((float)$product['price'], 0) ?> XAF</span></div><b class="stock-number <?= (int)$product['quantity'] === 0 ? 'danger-text' : '' ?>"><?= (int)$product['quantity'] ?></b></div> <!-- Show one low-stock product. -->
<?php endforeach; ?> <!-- Finish the low-stock loop. -->
</div> <!-- Close the low-stock list. -->
<?php endif; ?> <!-- Finish the low-stock conditional rendering. -->
</div> <!-- Close the low-stock panel. -->
</div> <!-- Close the dashboard two-column section. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared application footer and scripts.
?>
