document.addEventListener('DOMContentLoaded', () => { // Run the interface setup after the page HTML has loaded.
    document.querySelectorAll('.alert').forEach((alert) => { // Find every success or error alert currently visible.
        window.setTimeout(() => alert.classList.add('hide-alert'), 4500); // Slowly hide each alert after a few seconds.
    }); // Finish the alert setup loop.
    const productSearch = document.getElementById('productSearch'); // Find the product search box when the page contains one.
    const productTiles = Array.from(document.querySelectorAll('.product-tile')); // Collect all clickable product tiles.
    const cartInput = document.getElementById('cartInput'); // Find the hidden input that stores the JSON cart.
    const paidInput = document.getElementById('paidInput'); // Find the hidden input that stores the amount paid.
    const amountPaid = document.getElementById('amountPaid'); // Find the visible amount-paid field.
    let cart = []; // Create the in-memory shopping cart.
    const formatMoney = (value) => `${Number(value).toLocaleString('en-US')} XAF`; // Create a helper that formats XAF values.
    const refreshCart = () => { // Define a function that redraws the cart summary and hidden data.
        const body = document.getElementById('cartBody'); // Find the table body used to render cart items.
        const empty = document.getElementById('cartEmpty'); // Find the empty-cart message.
        const tableWrap = document.getElementById('cartTableWrap'); // Find the cart table wrapper.
        const totalElement = document.getElementById('cartTotal'); // Find the visible total element.
        const changeElement = document.getElementById('changeAmount'); // Find the visible change element.
        if (!body || !empty || !tableWrap || !totalElement || !changeElement || !cartInput || !amountPaid || !paidInput) return; // Stop safely when the page is not the sales screen.
        body.innerHTML = ''; // Clear the previous cart table rows before rebuilding them.
        let total = 0; // Reset the cart total before recalculating it.
        cart.forEach((item, index) => { // Loop through the current cart items.
            const subtotal = Number(item.price) * Number(item.quantity); // Calculate the current item's subtotal.
            total += subtotal; // Add the item subtotal to the cart total.
            const row = document.createElement('tr'); // Create a new table row for the cart item.
            row.innerHTML = `<td><strong>${escapeHtml(item.name)}</strong><small>${item.stock} available</small></td><td><input class="qty-input" type="number" min="1" max="${item.stock}" value="${item.quantity}" data-index="${index}"></td><td>${formatMoney(item.price)}</td><td>${formatMoney(subtotal)}</td><td><button class="btn small danger-btn remove-item" type="button" data-index="${index}">Remove</button></td>`; // Build the cart row using escaped product text and validated stock limits.
            body.appendChild(row); // Add the new row to the cart table.
        }); // Finish the cart item render loop.
        empty.classList.toggle('hidden', cart.length !== 0); // Hide the empty-cart message when at least one item exists.
        tableWrap.classList.toggle('hidden', cart.length === 0); // Hide the table when the cart is empty.
        totalElement.textContent = formatMoney(total); // Display the recalculated total.
        const paid = Number(amountPaid.value || 0); // Read the current amount paid.
        changeElement.textContent = formatMoney(Math.max(0, paid - total)); // Display non-negative change while the user types.
        cartInput.value = JSON.stringify(cart.map((item) => ({ id: item.id, quantity: item.quantity }))); // Store only server-relevant cart fields in the hidden input.
        paidInput.value = String(paid); // Store the current amount paid in the hidden input.
    }; // Finish the cart refresh function.
    const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character])); // Escape product names before placing them into HTML.
    const addToCart = (tile) => { // Define the action used when a product tile is clicked.
        const id = Number(tile.dataset.id); // Read the selected product ID.
        const name = tile.dataset.name; // Read the selected product name.
        const price = Number(tile.dataset.price); // Read the selected product price.
        const stock = Number(tile.dataset.stock); // Read the selected product stock.
        const existing = cart.find((item) => item.id === id); // Check whether the product is already in the cart.
        if (existing) { // Handle a product that is already in the cart.
            if (existing.quantity < stock) existing.quantity += 1; // Increase the quantity only when more stock is available.
        } else { // Handle a product that is not yet in the cart.
            cart.push({ id, name, price, stock, quantity: 1 }); // Add the product with a starting quantity of one.
        } // Finish the duplicate-product handling.
        refreshCart(); // Redraw the cart after the product is added.
    }; // Finish the add-to-cart function.
    productTiles.forEach((tile) => tile.addEventListener('click', () => addToCart(tile))); // Make every product tile add its product to the cart.
    if (productSearch) productSearch.addEventListener('input', () => { // React to typing in the product search box.
        const term = productSearch.value.toLowerCase().trim(); // Normalize the typed search term.
        productTiles.forEach((tile) => { // Check every product tile against the search term.
            tile.hidden = !tile.dataset.name.toLowerCase().includes(term); // Hide product tiles whose names do not match.
        }); // Finish the tile filtering loop.
    }); // Finish the product search listener.
    document.addEventListener('click', (event) => { // Listen for cart action clicks created dynamically.
        const removeButton = event.target.closest('.remove-item'); // Check whether the click came from a remove button.
        if (removeButton) { // Handle the remove action when a button was clicked.
            cart.splice(Number(removeButton.dataset.index), 1); // Remove the selected cart item by its array index.
            refreshCart(); // Redraw the cart after removal.
        } // Finish the remove action.
    }); // Finish the dynamic click listener.
    document.addEventListener('change', (event) => { // Listen for cart quantity changes created dynamically.
        const quantityInput = event.target.closest('.qty-input'); // Check whether the changed field is a cart quantity input.
        if (quantityInput) { // Handle a changed quantity field.
            const index = Number(quantityInput.dataset.index); // Read the cart item index.
            const quantity = Math.max(1, Math.min(Number(quantityInput.value || 1), cart[index].stock)); // Keep the new quantity between one and available stock.
            cart[index].quantity = quantity; // Save the validated quantity in the cart.
            refreshCart(); // Redraw the cart with the new quantity.
        } // Finish the quantity change action.
    }); // Finish the dynamic change listener.
    if (amountPaid) amountPaid.addEventListener('input', refreshCart); // Recalculate customer change whenever the payment input changes.
    const saleForm = document.getElementById('saleForm'); // Find the final sale submission form.
    if (saleForm) saleForm.addEventListener('submit', (event) => { // Validate the cart before the browser sends the sale request.
        if (cart.length === 0) { event.preventDefault(); window.alert('Add at least one product to the cart.'); return; } // Stop submission when the cart is empty.
        const total = cart.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0); // Calculate the visible cart total for client-side validation.
        if (Number(amountPaid.value || 0) < total) { event.preventDefault(); window.alert('Amount paid is less than the total amount.'); } // Stop submission when the visible payment is too small.
    }); // Finish sale form validation.
    refreshCart(); // Draw the initial empty cart state when the page loads.
}); // Finish the shared DOM-ready setup.

function clearCart() { // Create a global helper used by the Clear Cart button.
    window.location.reload(); // Reload the sale screen to reset the client-side cart.
} // Finish the clear-cart helper.

function confirmDelete(type) { // Create a reusable confirmation helper for destructive actions.
    return window.confirm(`Are you sure you want to delete this ${type}?`); // Ask the user to confirm the deletion.
} // Finish the delete confirmation helper.
