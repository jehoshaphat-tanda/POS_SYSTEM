<?php // Open the PHP section for the sale receipt page.
declare(strict_types=1); // Enable strict type checking for the receipt page.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requireLogin(); // Require a logged-in user to view a receipt.
$pageTitle = 'Sale Receipt'; // Set the page title.
$saleId = (int)($_GET['id'] ?? 0); // Read the requested sale identifier.
$stmt = $pdo->prepare("SELECT s.*, COALESCE(s.cashier_name, u.name, 'Deleted account') AS cashier FROM sales s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?"); // Prepare the sale header query.
$stmt->execute([$saleId]); // Execute the sale header query.
$sale = $stmt->fetch(); // Read the sale header record.
if (!$sale) { // Check whether the sale exists.
    http_response_code(404); // Tell the browser that the requested receipt was not found.
    exit('Sale not found.'); // Stop the page with a clear message.
} // End the sale existence check.
$itemStmt = $pdo->prepare('SELECT product_name, unit_price, quantity, subtotal FROM sale_items WHERE sale_id = ? ORDER BY id ASC'); // Prepare the sale items query.
$itemStmt->execute([$saleId]); // Execute the sale items query.
$items = $itemStmt->fetchAll(); // Read every item belonging to the sale.
require __DIR__ . '/includes/header.php'; // Load the shared application header.
?>
<?php if (isset($_GET['success'])): ?><div class="alert success">Sale completed successfully.</div><?php endif; ?> <!-- Show the completion confirmation after a successful sale. -->
<div class="receipt panel"> <!-- Start the receipt panel. -->
<div class="receipt-head"><div><h2>POS SYSTEM</h2><span>Official Sale Receipt</span></div><strong><?= e($sale['invoice_no']) ?></strong></div> <!-- Show the receipt title and invoice number. -->
<div class="receipt-meta"><div><span>Date</span><strong><?= e(date('d M Y, H:i', strtotime($sale['sale_date']))) ?></strong></div><div><span>Cashier</span><strong><?= e($sale['cashier']) ?></strong></div></div> <!-- Show the sale date and cashier. -->
<div class="table-wrap"><table><thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th class="right">Subtotal</th></tr></thead><tbody> <!-- Start the receipt item table. -->
<?php foreach ($items as $item): ?> <!-- Loop through the sold items. -->
<tr><td><?= e($item['product_name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= number_format((float)$item['unit_price'], 0) ?> XAF</td><td class="right"><?= number_format((float)$item['subtotal'], 0) ?> XAF</td></tr> <!-- Display one receipt line. -->
<?php endforeach; ?> <!-- Finish the receipt item loop. -->
</tbody></table></div> <!-- Close the receipt item table. -->
<div class="receipt-total"><div><span>Total</span><strong><?= number_format((float)$sale['total_amount'], 0) ?> XAF</strong></div><div><span>Amount Paid</span><strong><?= number_format((float)$sale['amount_paid'], 0) ?> XAF</strong></div><div><span>Change</span><strong><?= number_format((float)$sale['change_amount'], 0) ?> XAF</strong></div></div> <!-- Show total, payment and change. -->
<div class="receipt-actions"><a class="btn" href="sales.php">Back to Sales</a><button class="btn primary" type="button" onclick="window.print()">Print Receipt</button></div> <!-- Provide navigation and printing actions. -->
</div> <!-- Close the receipt panel. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared application footer. ?>
