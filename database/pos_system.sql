CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; -- Create the POS database when it does not already exist.
USE pos_system; -- Select the POS database for the following commands.
DROP TABLE IF EXISTS sale_items; -- Remove old sale item data when the setup script is imported again.
DROP TABLE IF EXISTS sales; -- Remove old sales data when the setup script is imported again.
DROP TABLE IF EXISTS products; -- Remove old product data when the setup script is imported again.
DROP TABLE IF EXISTS users; -- Remove old account data when the setup script is imported again.
CREATE TABLE users ( -- Create the table that stores principal, admin and cashier accounts.
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Create a unique numeric ID for each account.
name VARCHAR(100) NOT NULL, -- Store the user's full display name.
username VARCHAR(50) NOT NULL UNIQUE, -- Store the unique login username.
password VARCHAR(255) NOT NULL, -- Store the securely hashed login password.
role ENUM('principal_admin','admin','cashier') NOT NULL DEFAULT 'cashier', -- Store the account permission level.
active TINYINT(1) NOT NULL DEFAULT 1, -- Store whether the account is allowed to log in.
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Store when the account was created.
) ENGINE=InnoDB; -- Use InnoDB so foreign keys and transactions are supported.
CREATE TABLE products ( -- Create the product and stock table.
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Create a unique ID for every product.
name VARCHAR(150) NOT NULL, -- Store the product name.
price DECIMAL(12,2) NOT NULL DEFAULT 0, -- Store the product selling price in XAF.
quantity INT NOT NULL DEFAULT 0, -- Store the available stock quantity.
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Store the time the product was created.
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Store the latest product update time.
INDEX idx_products_name (name) -- Add an index so product name searches are faster.
) ENGINE=InnoDB; -- Use InnoDB for consistent product storage.
CREATE TABLE sales ( -- Create the completed sales table.
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Create a unique ID for each sale.
invoice_no VARCHAR(60) NOT NULL UNIQUE, -- Store a unique human-readable invoice number.
user_id INT UNSIGNED NULL, -- Store the account ID that completed the sale and allow historical deletion.
cashier_name VARCHAR(100) NOT NULL, -- Store the cashier name at the time of the sale so history remains readable.
total_amount DECIMAL(12,2) NOT NULL, -- Store the verified sale total.
amount_paid DECIMAL(12,2) NOT NULL, -- Store the money received from the customer.
change_amount DECIMAL(12,2) NOT NULL, -- Store the change returned to the customer.
sale_date DATETIME NOT NULL, -- Store the date and time of the sale.
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Store the database creation timestamp.
CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL -- Preserve sales history when an Admin or Cashier account is deleted.
) ENGINE=InnoDB; -- Use InnoDB to support transactional sales.
CREATE TABLE sale_items ( -- Create the table containing individual products in each sale.
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- Create a unique ID for every sale item row.
sale_id BIGINT UNSIGNED NOT NULL, -- Link the item to its sale header.
product_id INT UNSIGNED NOT NULL, -- Link the item to the product that was sold.
product_name VARCHAR(150) NOT NULL, -- Store the product name at the time of the sale.
unit_price DECIMAL(12,2) NOT NULL, -- Store the unit price used for this sale line.
quantity INT NOT NULL, -- Store the quantity sold.
subtotal DECIMAL(12,2) NOT NULL, -- Store the line subtotal.
CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE, -- Delete item rows automatically when their sale header is deleted.
CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id) -- Keep each item linked to an existing product.
) ENGINE=InnoDB; -- Use InnoDB for sale item integrity.
INSERT INTO users (name, username, password, role, active) VALUES ('FORSOH', 'FORSOH', '$2y$12$IM0u4Oif4H3H6Grn/SHCjelbmat4I.Vasxi6VwrgLbqRnB3tOw5Lq', 'principal_admin', 1); -- Create the permanent principal administrator FORSOH with the requested password hash.
INSERT INTO products (name, price, quantity) VALUES ('Coca-Cola', 500, 20), ('Pepsi', 500, 15), ('Indomie', 350, 30), ('Onga', 250, 25), ('Cheese Balls', 300, 18); -- Add simple demonstration stock matching the internship brief style.
