<?php // Open the PHP section for the new sale page.
declare(strict_types=1); // Enable strict type checking for this sales page.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication helpers.
requireLogin(); // Require a logged-in user to make a sale.
$pageTitle = 'New Sale'; // Set the page title.
$error = trim((string)($_GET['error'] ?? '')); // Read any error message returned from sale processing.
$products = $pdo->query('SELECT id, name, price, quantity FROM products WHERE quantity > 0 ORDER BY name ASC')->fetchAll(); // Load only products that can currently be sold.
require __DIR__ . '/includes/header.php'; // Load the shared application header.
?>
<?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?> <!-- Show a returned sale error when one exists. -->
<div class="sale-layout"> <!-- Start the two-column sale workspace. -->
<div class="panel"> <!-- Start the product picker panel. -->
<div class="panel-head"><div><h2>Products</h2><span class="muted">Select a product and add it to the cart.</span></div></div> <!-- Show the product picker heading. -->
<input id="productSearch" class="search-input" type="search" placeholder="Search product..."> <!-- Provide a client-side product search box. -->
<div id="productGrid" class="product-grid"> <!-- Start the product button grid. -->
<?php foreach ($products as $product): ?> <!-- Loop through every in-stock product. -->
<button type="button" class="product-tile" data-id="<?= (int)$product['id'] ?>" data-name="<?= e($product['name']) ?>" data-price="<?= e((string)$product['price']) ?>" data-stock="<?= (int)$product['quantity'] ?>"> <!-- Create a clickable product tile with the necessary sale data. -->
<strong><?= e($product['name']) ?></strong> <!-- Show the product name. -->
<span><?= number_format((float)$product['price'], 0) ?> XAF</span> <!-- Show the product unit price. -->
<small><?= (int)$product['quantity'] ?> in stock</small> <!-- Show the product's available stock. -->
</button> <!-- Close the product tile button. -->
<?php endforeach; ?> <!-- Finish the product grid loop. -->
<?php if (!$products): ?><div class="empty">No products are currently in stock.</div><?php endif; ?> <!-- Tell the cashier when no product can be sold. -->
</div> <!-- Close the product grid. -->
</div> <!-- Close the product picker panel. -->
<div class="panel cart-panel"> <!-- Start the shopping cart panel. -->
<div class="panel-head"><div><h2>Cart</h2><span class="muted">Review quantities before completing the sale.</span></div><button type="button" class="btn small" onclick="clearCart()">Clear</button></div> <!-- Show cart heading and clear action. -->
<div id="cartEmpty" class="empty">Your cart is empty.</div> <!-- Show the empty-cart state. -->
<div id="cartTableWrap" class="table-wrap hidden"><table><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr></thead><tbody id="cartBody"></tbody></table></div> <!-- Create the cart table that JavaScript fills dynamically. -->
<div class="sale-summary"> <!-- Start the payment summary. -->
<div><span>Total</span><strong id="cartTotal">0 XAF</strong></div> <!-- Show the calculated sale total. -->
<label>Amount paid<input id="amountPaid" type="number" min="0" step="1" placeholder="Enter amount paid"></label> <!-- Collect the amount received from the customer. -->
<div><span>Change</span><strong id="changeAmount">0 XAF</strong></div> <!-- Show the automatically calculated customer change. -->
</div> <!-- Close the payment summary. -->
<form id="saleForm" method="post" action="save_sale.php"> <!-- Start the final sale submission form. -->
<input id="cartInput" type="hidden" name="cart" value="[]"> <!-- Store the JavaScript cart as JSON for the server. -->
<input id="paidInput" type="hidden" name="amount_paid" value="0"> <!-- Store the received amount for the server. -->
<button class="btn primary full" type="submit">Complete Sale</button> <!-- Submit the transaction to the secure server-side sale processor. -->
</form> <!-- Close the final sale submission form. -->
</div> <!-- Close the cart panel. -->
</div> <!-- Close the sale workspace. -->
<script> // Start the page-specific JavaScript block for the cart.
window.POS_PRODUCTS = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; // Expose the current stock list to the shared POS JavaScript.
</script> <!-- Close the page-specific JavaScript block. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared footer and JavaScript. ?>
