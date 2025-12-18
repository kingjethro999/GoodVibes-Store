## 3ED.I SOCIETY (3ED.I Society Store)

E‑commerce web app for **3ED.I Society / 3ED.I SOCIETY** built with **PHP + MySQL**.  
Users can browse products, place orders, pay via **Cash on Delivery** or **Bank Transfer** (with receipt upload), and track orders.  
Admins can manage products, users, and orders, and approve payments from uploaded receipts.

---

## 1. Project Structure (Key Files)

**Storefront**
- `index.php` – Landing page + featured products.
- `about.php` – Brand story / about page.
- `products.php` – All products grid.
- `product_details.php` – Single product page + “Buy Now”.
- `checkout.php` – Checkout form, shipping details, payment method selection, optional receipt upload.
- `orders.php` – “My Orders” page for logged-in users.
- `order_details.php` – Detailed view of a single order, bank info, and receipt upload (for pending orders).

**Auth / Users**
- `register.php` – User registration (creates `user` accounts; super admin can also create `super_admin`).
- `login.php` – Main login; redirects admins to dashboard.
- `user-login.php` – Alternate/simple user login flow.
- `logout.php` – Session destroy + redirect home.
- `profile.php` – Placeholder for future profile features.

**Admin**
- `admin/dashboard.php` – Admin overview (stats, recent orders) + “Add New Product” form.
- `admin/products.php` – Product management (list, edit, delete).
- `admin/add_product.php` – Handles product creation and file uploads for product images.
- `admin/process.php` – Orders list with filters (All / Pending / Completed / Cancelled).
- `admin/order_details.php` – Detailed admin view for a specific order with receipt preview and approve/cancel controls.
- `admin/login.php`, `admin/signup.php`, `admin/index.php` – Admin entry/auth pages.

**Shared / Layout**
- `db_connect.php` – Central database connection (used by most pages).
- `assets/config.php` – Simple DB config (legacy; `index.php` currently includes this).
- `theme/header.php` / `theme/footer.php` – Shared admin layout (top bar, sidebar, scripts).
- `assets/admin.css` – Admin styling.

**Uploads**
- `uploads/` – Product images (e.g. `hoodie.jpg`, `tshirt.jpg`).
- `uploads/receipts/` – Bank transfer receipt uploads (auto-created if missing).

**Database**
- `db.sql` – Database schema and initial seed data for:
  - `users`
  - `products`
  - `orders`
  - `feedback`
  - `reciepts` (for payment receipts).

---

## 2. Requirements

- **Server**: PHP 8+ (tested on typical XAMPP/LAMPP stack).
- **Database**: MySQL or MariaDB.
- **Extensions**: `mysqli`, `openssl` (for `password_hash`).
- **Web Server**: Apache (or equivalent), document root pointing to the `Vibes` folder.

---

## 3. Installation

### 3.1. Place Project in Web Root

Clone or copy the project into your web root, e.g.:

```bash
/opt/lampp/htdocs/Vibes
```

On Windows with XAMPP:

```text
C:\xampp\htdocs\Vibes
```

### 3.2. Create Database & Import Schema

1. Create the database (name must match `db_connect.php`):

```sql
CREATE DATABASE edisocie_good_vibes
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

2. Import `db.sql` into this database:
   - Using phpMyAdmin: select DB → Import → choose `db.sql`.
   - Or from CLI:

```bash
mysql -u YOUR_USER -p edisocie_good_vibes < db.sql
```

> `db.sql` also seeds a **super admin**:
> - Email: `admin@goodvibes.com`  
> - Password: `admin123` (stored with `MD5` in the seed for bootstrapping).

### 3.3. Configure Database Connection

Edit `db_connect.php`:

```php
$servername = "localhost";
$username   = "YOUR_DB_USERNAME";
$password   = "YOUR_DB_PASSWORD";
$dbname     = "edisocie_good_vibes";
```

Make sure:
- Credentials match your MySQL user.
- No stray spaces in `$username` or `$password`.

> Optional: you can refactor `index.php` to use `db_connect.php` instead of `assets/config.php` so all pages share one connection file.

---

## 4. Running the App

1. Start **Apache** and **MySQL** in XAMPP/LAMPP.
2. Visit in your browser:
   - Storefront home: `http://localhost/Vibes/index.php`
   - Products: `http://localhost/Vibes/products.php`
   - User login: `http://localhost/Vibes/login.php` (or `user-login.php`)
   - Admin dashboard: `http://localhost/Vibes/admin/dashboard.php` (after admin login)

---

## 5. Authentication & Roles

### 5.1. Roles

- `super_admin` – Full control; can manage users and optionally create more super admins.
- `admin` – Admin-style access (depending on how you assign the role).
- `user` – Normal customer account.

### 5.2. Login & Registration

- `login.php`
  - Authenticates via `users` table.
  - On success:
    - `super_admin` / `admin` → redirects to `admin/dashboard.php`.
    - `user` → redirects to `index.php`.
- `register.php`
  - Standard users register as `user`.
  - If a `super_admin` is already logged in, they can also create a `super_admin` user via this form.
- `logout.php`
  - Clears the session and redirects to `index.php`.

### 5.3. Access Control

Admin area (`admin/*.php`) checks:

```php
if (!isset($_SESSION['user_id']) ||
    ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
  header('Location: ../login.php');
  exit;
}
```

User-facing order pages (`checkout.php`, `orders.php`, `order_details.php`) require a logged-in user:

```php
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
```

---

## 6. Products & Orders Flow

### 6.1. Products

- Stored in the `products` table:
  - `product_name`, `category`, `description`, `price`, `image`, `created_at`.
- Displayed at:
  - `index.php` – “Featured Products” (latest few).
  - `products.php` – Full product catalogue.
  - `product_details.php?id=...` – Detailed page with “Buy Now” button.

Admin product management:
- `admin/products.php` – list, edit, delete.
- `admin/dashboard.php` – contains a “Add New Product” form posting to `admin/add_product.php`.
- `admin/add_product.php` – handles:
  - Image upload into `uploads/`.
  - Basic validation for file type.

### 6.2. Checkout & Order Creation

1. User clicks **“Buy Now”** on `product_details.php`.
2. Redirected to `checkout.php?id=PRODUCT_ID`.
3. On `checkout.php`, user:
   - Sets **quantity**.
   - Fills **shipping information**:
     - `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`.
   - Optionally sets a **second contact person** (alt name/email/phone).
   - Chooses **payment method**:
     - `cash_on_delivery`
     - `bank_transfer`
4. Shipping logic:
   - Subtotal = `price * quantity`
   - If subtotal ≥ **₦10,000** → `shipping = 0` (free).
   - Else `shipping = 500`.
   - `total_price = subtotal + shipping`.
5. When the form is submitted:
   - An entry is inserted into `orders` with:
     - `user_id`, `product_id`, `quantity`, `total_price`.
     - All shipping fields.
     - `status = 'pending'`.
   - If `payment_method = 'bank_transfer'` **and** a file is uploaded,
     a record is also created in `reciepts`.

---

## 7. Bank Transfer & Receipt Upload Flow

### 7.1. Bank Details (Display)

Bank details are shown in:

- **Checkout (`checkout.php`)** when user selects **Bank Transfer**:
  - Bank: **GTBank**
  - Account Number: **0140150361**
  - Account Name: **INYANG DAVID NSIKAK**
  - Receipt upload input is shown for transfer proof.

- **Order details (`order_details.php`, user side)** for each order:
  - Repeats the same bank details.
  - Shows the **total amount** the user needs to pay.

> To change bank details, search for `GTBank` or `0140150361` and update the strings in `checkout.php` and `order_details.php`.

### 7.2. Receipts Storage

- Table: `reciepts`
  - `order_id`
  - `receipt_image` (string path under `uploads/receipts/`)
  - `created_at`
- Directory: `uploads/receipts/`
  - Auto-created if it does not exist.

Allowed receipt types:
- `jpg`, `jpeg`, `png`, `pdf`

### 7.3. Upload Points

**A. During checkout (`checkout.php`)**
- If **Bank Transfer** is selected:
  - User can upload a receipt file.
  - On success, `reciepts` row is created for that new order.

**B. After order is placed (`order_details.php`)**
- For each order where:
  - It belongs to the logged-in user.
  - `status = 'pending'`.
  - No `reciepts` row / no `receipt_image` yet.
- The page shows:
  - Bank account info.
  - A form: **“Upload Payment Receipt”**.
- When user uploads:
  - If a row in `reciepts` already exists for the order → it is **updated**.
  - Otherwise, a new row is inserted.
  - User sees success or error message on the same page.

**C. “Upload Receipt” Button in “My Orders”**

- In `orders.php`, orders are selected with:

```sql
SELECT o.*, p.product_name, p.image, p.price as unit_price, r.receipt_image
FROM orders o
LEFT JOIN products p ON o.product_id = p.id
LEFT JOIN reciepts r ON o.id = r.order_id
...
```

- For each order:
  - Always shows **“View Details”** (links to `order_details.php?id=...`).
  - If `status = 'pending'` **and** `receipt_image` is empty:
    - Also shows **“Upload Receipt”** button linking to the same `order_details.php` page.

This means even **old orders** created under the old structure (without receipt upload at checkout) can now have a receipt attached later.

---

## 8. Admin: Viewing & Approving Orders / Receipts

### 8.1. Orders List (`admin/process.php`)

- Shows list of all orders with:
  - Customer name and email.
  - Product name + thumbnail.
  - Quantity, total, date, and status.
- Filter options:
  - All / Pending / Completed / Cancelled.
- Each row has a **“View”** button → `admin/order_details.php?id=...`.

### 8.2. Order Details & Receipt Review (`admin/order_details.php`)

On this page, admin sees:

- Order meta (ID, date, status).
- Customer & shipping information (including alt contact).
- Product info (name, description, image, quantity, price).
- Order summary (subtotal, shipping, total).
- Receipt:
  - If present: thumbnail preview + link to open full receipt.
  - If missing: shows a warning that no receipt has been uploaded yet.

Actions:

- **Approve Receipt & Complete Order**
  - If a receipt is uploaded and the order is `pending`, admin can click:
    - “Approve Receipt & Complete Order”.
  - This sets `status = 'completed'`.

- **Update Status**
  - Dropdown to manually set `pending`, `completed`, or `cancelled`.

- **Cancel Order**
  - One-click cancel with confirmation; sets `status = 'cancelled'`.

This gives admins a complete view and simple workflow to confirm transfer receipts and update order states.

---

## 9. Security Notes

- **Passwords**
  - New accounts use `password_hash` / `password_verify`.
  - The seeded super admin from `db.sql` uses MD5 only as an initial bootstrap.  
    It is recommended to:
    - Log in as that admin.
    - Create a new super admin via the UI (so it’s hashed properly).
    - Or update the password directly in the database using `password_hash` from PHP.

- **File Uploads**
  - Only specific extensions are allowed for receipts.
  - Filenames are sanitized and timestamped before saving.
  - Ensure proper file permissions on `uploads/` and `uploads/receipts/`.
  - Consider adding max file size limits in `php.ini` and additional validation.

- **Sessions & Access**
  - All sensitive pages check for `$_SESSION['user_id']`, and admin pages also check `$_SESSION['role']`.
  - Make sure `session.cookie_secure` and `session.cookie_httponly` are configured appropriately in production.

---

## 10. Customization Tips

- **Change Bank Details**  
  Update the strings in:
  - `checkout.php`
  - `order_details.php`

- **Change Free Shipping Threshold**  
  Look for `10000` in `checkout.php` and adjust the logic and text.

- **Styling / Theme**  
  - Admin styling is mainly in `assets/admin.css` and `theme/header.php`.
  - Storefront pages use inline `<style>` blocks; you can extract these into one or more shared CSS files for cleaner maintenance.

---

## 11. Troubleshooting

- **Database Connection Failed**
  - Check credentials in `db_connect.php`.
  - Confirm MySQL is running and the database `edisocie_good_vibes` exists.

- **Blank Pages / PHP Errors**
  - Enable error reporting in your dev environment:

    ```php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ```

  - Place that temporarily at the top of the script you’re debugging (do not leave in production).

- **Uploads Not Working**
  - Verify `uploads/` and `uploads/receipts/` have write permissions.
  - Check your PHP `upload_max_filesize` and `post_max_size` settings.

---

## 12. Roadmap Ideas

- User profile page (`profile.php`) to edit name, email, and view statistics.
- Order cancellation / update from user side (before admin confirmation).
-.Admin notifications or emails when a new receipt is uploaded.
- Dashboard widgets for recent receipts awaiting approval.


