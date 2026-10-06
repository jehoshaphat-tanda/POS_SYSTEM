<?php // Open the PHP section for the server-side sale processor.
declare(strict_types=1); // Enable strict type checking for the transaction processor.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requireLogin(); // Require a logged-in user before accepting a sale.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Reject direct GET requests to the sale processor.
    header('Location: sale.php'); // Send a direct visitor back to the sale screen.
    exit; // Stop the processor.
} // End the request method guard.

$cart = json_decode((string)($_POST['cart'] ?? '[]'), true); // Decode the submitted cart JSON into a PHP array.
$amountPaid = (float)($_POST['amount_paid'] ?? 0); // Read the customer payment amount.

if (!is_array($cart) || $cart === []) { // Check that the submitted cart is valid and not empty.
    header('Location: sale.php?error=' . urlencode('Cart is empty. Add at least one product.')); // Return to the sale page with an explanatory message.
    exit; // Stop processing the invalid cart.
} // End the empty-cart guard.

try { // Start the atomic sale transaction.
    $pdo->beginTransaction(); // Begin a database transaction so sale and stock updates succeed together.
    $total = 0.0; // Initialize the total sale amount.
    $validatedItems = []; // Prepare an array of server-verified sale items.
    foreach ($cart as $item) { // Examine every cart item sent by the browser.
        $productId = (int)($item['id'] ?? 0); // Read the product ID as an integer.
        $quantity = (int)($item['quantity'] ?? 0); // Read the requested quantity as an integer.
        if ($productId <= 0 || $quantity <= 0) { // Reject invalid product IDs or quantities.
            throw new RuntimeException('Invalid cart item.'); // Stop the transaction with a validation error.
        } // End item validation.
        $stmt = $pdo->prepare('SELECT id, name, price, quantity FROM products WHERE id = ? FOR UPDATE'); // Lock the product row while the sale is being checked.
        $stmt->execute([$productId]); // Execute the locked product query.
        $product = $stmt->fetch(); // Read the current product data from MySQL.
        if (!$product) { // Check whether the requested product still exists.
            throw new RuntimeException('A selected product no longer exists.'); // Stop the transaction when the product cannot be found.
        } // End product existence validation.
        if ((int)$product['quantity'] < $quantity) { // Check the actual database stock against the requested quantity.
            throw new RuntimeException($product['name'] . ' has only ' . $product['quantity'] . ' item(s) left in stock.'); // Stop the sale when stock is insufficient.
        } // End stock validation.
        $lineTotal = (float)$product['price'] * $quantity; // Calculate the verified subtotal using the database price.
        $total += $lineTotal; // Add the verified subtotal to the full sale total.
        $validatedItems[] = [ // Store a verified copy of the sale item.
            'id' => (int)$product['id'], // Store the verified product ID.
            'name' => $product['name'], // Store the verified product name.
            'price' => (float)$product['price'], // Store the verified database price.
            'quantity' => $quantity, // Store the requested quantity after stock validation.
            'line_total' => $lineTotal, // Store the verified subtotal.
        ]; // Finish the verified sale item array.
    } // Finish the cart validation loop.
    if ($amountPaid < $total) { // Check that the customer's payment covers the entire sale.
        throw new RuntimeException('Amount paid is less than the total amount.'); // Stop the sale when insufficient payment was entered.
    } // End payment validation.
    $invoiceNo = 'INV-' . date('YmdHis') . '-' . random_int(100, 999); // Generate a human-readable unique invoice number.
    $changeAmount = $amountPaid - $total; // Calculate the customer's change.
    $saleStmt = $pdo->prepare('INSERT INTO sales (invoice_no, user_id, cashier_name, total_amount, amount_paid, change_amount, sale_date) VALUES (?, ?, ?, ?, ?, ?, NOW())'); // Prepare the sale header insert query.
    $saleStmt->execute([$invoiceNo, $_SESSION['user']['id'], $_SESSION['user']['name'], $total, $amountPaid, $changeAmount]); // Save the verified sale header.
    $saleId = (int)$pdo->lastInsertId(); // Read the generated sale ID.
    $itemStmt = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, product_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)'); // Prepare the sale item insert query.
    $stockStmt = $pdo->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?'); // Prepare the stock reduction query.
    foreach ($validatedItems as $item) { // Process every verified item in the sale.
        $itemStmt->execute([$saleId, $item['id'], $item['name'], $item['price'], $item['quantity'], $item['line_total']]); // Save the sale item details.
        $stockStmt->execute([$item['quantity'], $item['id']]); // Reduce the live product stock by the sold quantity.
    } // Finish the sale item processing loop.
    $pdo->commit(); // Permanently save the sale and stock changes together.
    header('Location: sale_view.php?id=' . $saleId . '&success=1'); // Send the user to the completed-sale receipt.
    exit; // Stop the sale processor after success.
} catch (Throwable $exception) { // Catch validation and database errors during the transaction.
    if ($pdo->inTransaction()) { // Check whether the transaction is still open.
        $pdo->rollBack(); // Undo every partial database change.
    } // Finish the rollback guard.
    header('Location: sale.php?error=' . urlencode($exception->getMessage())); // Return to the sale page with the error reason.
    exit; // Stop the processor after the failed transaction.
} // End the transaction error handler.
?>
