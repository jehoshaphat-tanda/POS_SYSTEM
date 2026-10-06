<?php // Open the PHP section for product and stock management.
declare(strict_types=1); // Enable strict type checking for this page.
require_once __DIR__ . '/config/database.php'; // Load the MySQL connection.
require_once __DIR__ . '/includes/auth.php'; // Load authentication and output helpers.
requireLogin(); // Allow both admins and cashiers to see available stock.
$user = currentUser(); // Store the current account for permission decisions.
$isAdmin = in_array($user['role'] ?? '', ['principal_admin', 'admin'], true); // Determine whether the current user can change products.
$pageTitle = 'Products & Stock'; // Set the page title.
$message = ''; // Prepare a success message container.
$error = ''; // Prepare an error message container.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) { // Process product changes only for administrators.
    $action = $_POST['action'] ?? ''; // Read the requested product action.
    $id = (int)($_POST['id'] ?? 0); // Read the product identifier when editing or deleting.
    $name = trim((string)($_POST['name'] ?? '')); // Read and trim the product name.
    $price = (float)($_POST['price'] ?? 0); // Read the product price as a number.
    $quantity = (int)($_POST['quantity'] ?? 0); // Read the product quantity as an integer.
    if (($action === 'add' || $action === 'edit') && ($name === '' || $price < 0 || $quantity < 0)) { // Validate add and edit input values.
        $error = 'Enter a valid product name, price and quantity.'; // Tell the administrator how to correct invalid input.
    } elseif ($action === 'add') { // Handle a new product request.
        $stmt = $pdo->prepare('INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)'); // Prepare a secure insert query.
        $stmt->execute([$name, $price, $quantity]); // Insert the new product.
        $message = 'Product added successfully.'; // Confirm the successful product creation.
    } elseif ($action === 'edit' && $id > 0) { // Handle an existing product update.
        $stmt = $pdo->prepare('UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?'); // Prepare a secure update query.
        $stmt->execute([$name, $price, $quantity, $id]); // Update the product with the supplied values.
        $message = 'Product updated successfully.'; // Confirm the successful update.
    } elseif ($action === 'delete' && $id > 0) { // Handle deletion of a product.
        try { // Start the protected delete operation.
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?'); // Prepare the product delete query.
            $stmt->execute([$id]); // Delete the selected product.
            $message = 'Product deleted successfully.'; // Confirm the deletion.
        } catch (PDOException $exception) { // Catch a database constraint error when the product was used in a sale.
            $error = 'This product cannot be deleted because it is referenced by an existing sale.'; // Explain why the deletion failed.
        } // Finish the delete error handler.
    } // Finish the product action chain.
} // End product form processing.

$editProduct = null; // Prepare the optional product being edited.
if (isset($_GET['edit']) && $isAdmin) { // Load the selected product only for administrators.
    $stmt = $pdo->prepare('SELECT id, name, price, quantity FROM products WHERE id = ?'); // Prepare the edit-product query.
    $stmt->execute([(int)$_GET['edit']]); // Execute the edit-product query.
    $editProduct = $stmt->fetch() ?: null; // Store the product or null when it does not exist.
} // End edit product loading.

$search = trim((string)($_GET['search'] ?? '')); // Read the optional stock search term.
if ($search !== '') { // Check whether the user entered a search term.
    $stmt = $pdo->prepare('SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC'); // Prepare a safe product search query.
    $stmt->execute(['%' . $search . '%']); // Search products by partial name.
    $products = $stmt->fetchAll(); // Read all matching products.
} else { // Handle the case where no search term was supplied.
    $products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll(); // Read all products from newest to oldest.
} // End product list selection.

require __DIR__ . '/includes/header.php'; // Load the shared page header.
?>
<?php if ($message !== ''): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?> <!-- Show a successful product message when one exists. -->
<?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?> <!-- Show a product error message when one exists. -->
<div class="two-col products-layout"> <!-- Start the product management layout. -->
<?php if ($isAdmin): ?> <!-- Show product editing controls to administrators. -->
<div class="panel form-panel"> <!-- Start the add/edit product form panel. -->
<div class="panel-head"><h2><?= $editProduct ? 'Edit Product' : 'Add Product' ?></h2><?php if ($editProduct): ?><a href="products.php">Cancel</a><?php endif; ?></div> <!-- Show the product form heading and optional cancel link. -->
<form method="post"> <!-- Start the product creation or editing form. -->
<input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'add' ?>"> <!-- Store the requested product action in a hidden form field. -->
<?php if ($editProduct): ?><input type="hidden" name="id" value="<?= (int)$editProduct['id'] ?>"> <!-- Store the product ID during editing. --><?php endif; ?> <!-- Finish the optional product ID field. -->
<label>Product name<input name="name" required value="<?= e($editProduct['name'] ?? '') ?>" placeholder="e.g. Coca-Cola"></label> <!-- Collect the product name. -->
<label>Price (XAF)<input type="number" name="price" min="0" step="1" required value="<?= e((string)($editProduct['price'] ?? '')) ?>"></label> <!-- Collect the product price in XAF. -->
<label>Quantity in stock<input type="number" name="quantity" min="0" step="1" required value="<?= e((string)($editProduct['quantity'] ?? 0)) ?>"></label> <!-- Collect the number of available units. -->
<button class="btn primary full" type="submit"><?= $editProduct ? 'Save Changes' : 'Add Product' ?></button> <!-- Submit the product form. -->
</form> <!-- Close the product form. -->
</div> <!-- Close the product form panel. -->
<?php endif; ?> <!-- End administrator product controls. -->
<div class="panel"> <!-- Start the stock table panel. -->
<div class="panel-head"><div><h2>Available Stock</h2><span class="muted"><?= $isAdmin ? 'Admin can add, edit and delete products.' : 'Cashier view is read-only.' ?></span></div><span class="badge"><?= count($products) ?> product(s)</span></div> <!-- Show the stock panel heading. -->
<form class="search-form" method="get"> <!-- Start the stock search form. -->
<input name="search" value="<?= e($search) ?>" placeholder="Search product..."> <!-- Collect a product search term. -->
<button class="btn" type="submit">Search</button> <!-- Submit the product search. -->
<?php if ($search !== ''): ?><a class="btn" href="products.php">Clear</a><?php endif; ?> <!-- Provide a clear-search link when a search is active. -->
</form> <!-- Close the stock search form. -->
<div class="table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th><?php if ($isAdmin): ?><th></th><?php endif; ?></tr></thead><tbody> <!-- Start the product table. -->
<?php foreach ($products as $product): ?> <!-- Loop through each product. -->
<tr> <!-- Start one product row. -->
<td><strong><?= e($product['name']) ?></strong></td> <!-- Show the product name. -->
<td><?= number_format((float)$product['price'], 0) ?> XAF</td> <!-- Show the product selling price. -->
<td><strong><?= (int)$product['quantity'] ?></strong></td> <!-- Show the current stock quantity. -->
<td><?= (int)$product['quantity'] <= 0 ? '<span class="status danger">Out of stock</span>' : ((int)$product['quantity'] <= 5 ? '<span class="status warning">Low stock</span>' : '<span class="status good">Available</span>') ?></td> <!-- Show a simple stock status badge. -->
<?php if ($isAdmin): ?><td class="actions"><a class="btn small" href="products.php?edit=<?= (int)$product['id'] ?>">Edit</a><form method="post" class="inline-form" onsubmit="return confirmDelete('product')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="btn small danger-btn" type="submit">Delete</button></form></td><?php endif; ?> <!-- Show administrator actions when permitted. -->
</tr> <!-- Close one product row. -->
<?php endforeach; ?> <!-- Finish the product loop. -->
<?php if (!$products): ?><tr><td colspan="<?= $isAdmin ? '5' : '4' ?>" class="empty">No products found.</td></tr><?php endif; ?> <!-- Show an empty message when there are no matching products. -->
</tbody></table></div> <!-- Close the product table. -->
</div> <!-- Close the stock table panel. -->
</div> <!-- Close the product management layout. -->
<?php require __DIR__ . '/includes/footer.php'; // Load the shared application footer.
?>
