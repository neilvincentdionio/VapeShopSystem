# VapeShop Mobile API

Base URL (XAMPP local):

`http://localhost/VapeShopSystem/mobile_api`

## Response Format (All Endpoints)

All endpoints return strict JSON:

```json
{
  "success": true,
  "message": "human readable message",
  "data": {}
}
```

`data` is included only when relevant.

## Endpoints

### 1) `POST /login.php`

Required fields:
- `email`
- `password`

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/login.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"customer@example.com\",\"password\":\"Password123\"}"
```

Success sample:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "full_name": "Juan Dela Cruz",
    "email": "customer@example.com"
  }
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Invalid email or password."
}
```

---

### 2) `POST /register.php`

Required fields:
- `full_name`
- `email`
- `password`
- `phone`
- `street`
- `city`
- `barangay`
- `postal_code`
- `province`
- `country`

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/register.php" \
  -H "Content-Type: application/json" \
  -d "{\"full_name\":\"Juan Dela Cruz\",\"email\":\"juan@example.com\",\"password\":\"Password123\",\"phone\":\"09171234567\",\"street\":\"Blk 1 Lot 2\",\"city\":\"Quezon City\",\"barangay\":\"Bagumbayan\",\"postal_code\":\"1110\",\"province\":\"Metro Manila\",\"country\":\"Philippines\"}"
```

Success sample:

```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "full_name": "Juan Dela Cruz",
    "email": "juan@example.com"
  }
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Email is already registered."
}
```

---

### 3) `POST /profile_update.php`

Required fields:
- `current_email`
- `full_name`
- `email`

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/profile_update.php" \
  -H "Content-Type: application/json" \
  -d "{\"current_email\":\"juan@example.com\",\"full_name\":\"Juan Updated\",\"email\":\"juan.updated@example.com\"}"
```

Success sample:

```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "data": {
    "full_name": "Juan Updated",
    "email": "juan.updated@example.com"
  }
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Email is already in use by another account."
}
```

---

### 4) `POST /password_update.php`

Required fields:
- `email`
- `current_password`
- `new_password`

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/password_update.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"juan@example.com\",\"current_password\":\"Password123\",\"new_password\":\"NewPass1234\"}"
```

Success sample:

```json
{
  "success": true,
  "message": "Password updated successfully."
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Current password is incorrect."
}
```

---

### 5) `POST /cart_add.php`

Required fields:
- `email`
- `product_name`
- `quantity`

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/cart_add.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"juan@example.com\",\"product_name\":\"Sample Vape Product\",\"quantity\":2}"
```

Success sample:

```json
{
  "success": true,
  "message": "Product added to cart.",
  "data": {
    "cart_id": 5,
    "product_id": 12,
    "product_name": "Sample Vape Product",
    "quantity": 2,
    "unit_price": 350
  }
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Product not found."
}
```

---

### 6) `POST /checkout.php`

Required fields:
- `email`
- `total_amount`

Checkout source of items: existing items in the user cart (`carts` and `cart_items`).

cURL:

```bash
curl -X POST "http://localhost/VapeShopSystem/mobile_api/checkout.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"juan@example.com\",\"total_amount\":700.00}"
```

Success sample:

```json
{
  "success": true,
  "message": "Checkout successful.",
  "data": {
    "order_id": 101,
    "reference_number": "ORD-20260515233500-A1B2",
    "total_amount": 700,
    "item_count": 2,
    "status": "pending"
  }
}
```

Failed sample:

```json
{
  "success": false,
  "message": "Total amount mismatch.",
  "data": {
    "expected_total": 700,
    "provided_total": 500
  }
}
```

## Postman Quick Setup

1. Create a collection: `VapeShop Mobile API`.
2. For each endpoint above:
   - Method: `POST`
   - URL: `http://localhost/VapeShopSystem/mobile_api/<endpoint>.php`
   - Headers: `Content-Type: application/json`
   - Body type: `raw` + `JSON`
3. Paste the sample JSON body from this file.

## Optional SQL Migration

If your database is missing cart/order support tables, import:

`mobile_api/migration_optional.sql`

in phpMyAdmin (`vapeshop_db`).
