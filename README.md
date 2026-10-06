# POS SYSTEM

This project is a simple Point of Sale system built with PHP and MySQL for the internship task.

## Main account
The database creates one permanent principal administrator:

- Username: `FORSOH`
- Password: `FORSOH123`
- Role: Principal Administrator

The principal administrator can create and delete normal **Admin** and **Cashier** accounts from `users.php`.

The principal administrator account is protected and cannot be deleted from the application.

## Features
- Principal Admin / Admin / Cashier login.
- FORSOH can create Admin and Cashier accounts.
- FORSOH can delete Admin and Cashier accounts while historical sales remain preserved with the recorded cashier name.
- Admin can add, edit and delete products.
- Cashier can view stock but cannot edit products.
- Search products during a sale.
- Add products to a cart.
- Change item quantities without exceeding stock.
- Automatic total calculation.
- Amount paid and customer change calculation.
- Transaction-safe sale completion.
- Automatic stock reduction after a completed sale.
- Prevent sales when stock is insufficient or out of stock.
- Sales history with invoice, date, products, quantities, cashier and totals.
- Printable sale receipt.
- Dashboard with today's sales, sale count, current stock and out-of-stock count.

## XAMPP installation
1. Extract the folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/pos_system.sql`.
5. Open `http://localhost/POS_SYSTEM/`.
6. Login with the principal account above.
7. Use **User Accounts** to create Cashier and Admin accounts.

## Database configuration
The default connection is in `config/database.php`:
- Host: `127.0.0.1`
- Database: `pos_system`
- User: `root`
- Password: empty

Change these values when your MySQL settings are different.

## Important note about comments
The source files are intentionally written with explanatory comments throughout the code so that each code line/block is understandable during an internship demonstration or defense.
