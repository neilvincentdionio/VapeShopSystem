# VapeShop Mobile + Web Integration Guide

This document summarizes what was implemented and how to run the mobile app with the local web/database backend.

## 1) What Was Implemented

### Mobile App (`VapeShopMobile`)
- Login/register UI and flows updated to use backend APIs.
- Product cards and product details now use real API data (price, stock, puffs, rating, flavors/variants).
- Add-to-cart requires selection first for flavor-based products (pods/disposable/e-liquid), including puff/flavor where applicable.
- Cart, checkout, and orders (`My Purchase`) use backend data.
- `My Purchase` moved to bottom navigation and styled with status tabs:
  - All, To Pay, To Ship, To Receive, Completed, To Review, Cancelled, Return/Refund, Failed Delivery
- Home search now filters on the same page (no page switch).
- Header buttons aligned and cleaned up.
- Settings cleaned to essential actions:
  - Edit Profile
  - Change Password
  - Logout

### Backend API (`C:\xampp\htdocs\VapeShopSystem\mobile_api`)
- Implemented/fixed endpoints:
  - `login.php`
  - `register.php`
  - `profile_update.php`
  - `password_update.php`
  - `products.php` (supports product list + single product detail + variants + rating)
  - `cart_add.php` (supports `variant_id` and flavor validation)
  - `cart_list.php` (variant/flavor-aware cart response)
  - `checkout.php`
  - `orders_list.php`
- Address sanitization and output safety handled in shared helper logic (`common.php`).

## 2) Local Environment Setup

## Prerequisites
- Windows with Android Studio and Android SDK installed
- XAMPP installed
- ADB available in terminal (`adb` command works)
- Project folders:
  - Mobile app: `C:\Users\ADMIN\AndroidStudioProjects\VapeShopMobile`
  - Web/API: `C:\xampp\htdocs\VapeShopSystem`

## Start Backend
1. Open XAMPP Control Panel.
2. Start:
   - Apache
   - MySQL
3. Ensure database exists and is populated:
   - `vapeshop_db`

## API Location
- API base folder:
  - `C:\xampp\htdocs\VapeShopSystem\mobile_api`
- Primary mobile endpoint pattern:
  - `http://<host>/VapeShopSystem/mobile_api/<endpoint>.php`

## 3) Android App Configuration

## Manifest requirements
`AndroidManifest.xml` should allow network access for local API calls:
- Internet permission
- Cleartext HTTP enabled

## Base URL fallbacks in app
The app tries multiple local addresses (USB reverse, emulator, LAN), including:
- `http://127.0.0.1:8080/VapeShopSystem/mobile_api/` (via adb reverse)
- `http://10.0.2.2/VapeShopSystem/mobile_api/` (Android Emulator)
- `http://10.0.3.2/VapeShopSystem/mobile_api/` (Genymotion)
- LAN IP fallbacks

## 4) Device Connectivity (Important)

For a physical Android device connected by USB:

```powershell
adb reverse tcp:8080 tcp:80
```

This allows the phone to reach your local Apache server via `127.0.0.1:8080`.

## 5) Standard Run Flow

1. Start XAMPP (Apache + MySQL).
2. Confirm DB/tables in `vapeshop_db`.
3. Run `adb reverse tcp:8080 tcp:80` if using a physical device.
4. Build and run app from Android Studio.
5. Validate flows:
   - Register/Login
   - Product browsing
   - Product detail (real data)
   - Add to cart with flavor selection when required
   - Cart and checkout
   - My Purchase tabs and order statuses

## 6) Key Endpoint Contracts (Quick View)

## `products.php`
- GET list: returns active products with fields like:
  - `id`, `name`, `category`, `puffs`, `spec`, `price`, `stock_qty`
  - `average_rating`, `review_count`
  - `has_flavors`, `variants[]`
- GET single detail:
  - `products.php?product_id=<id>`

## `cart_add.php`
- POST:
  - `email`
  - `product_name`
  - `quantity`
  - optional `variant_id`
- Validates flavor selection when product category/variants require it.

## `cart_list.php`
- POST:
  - `email`
- Returns cart lines including flavor/variant context when present.

## `orders_list.php`
- POST:
  - `email`
- Returns customer orders with status data used by mobile tab filters.

## 7) Troubleshooting

- `Cannot connect to server`
  - Check Apache is running.
  - Check endpoint path under `htdocs`.
  - Re-run `adb reverse tcp:8080 tcp:80`.
- Empty/invalid product info
  - Verify API response from `products.php`.
  - Ensure products are active in DB.
- Add-to-cart fails for flavored products
  - Confirm product has variants and selected `variant_id` is valid and active.
- Address shows unusable text
  - Ensure sanitized address output logic is active in shared API helpers.

## 8) Reusable Prompts

## Prompt for Android App Work
```text
Act as a senior Android Java developer.

Project: VapeShopMobile (single-activity, fragment-based app).
Goal: Improve UI/UX and make all product/cart/account flows use real API data.

Requirements:
1) Keep Home, Cart, Purchase, Settings bottom navigation intact.
2) Use backend endpoints under /VapeShopSystem/mobile_api/.
3) Product details must come from API (price, stock, puffs, rating, flavors).
4) For flavor-based categories (pods/disposable/e-liquid), require flavor (and puff if applicable) selection before add-to-cart.
5) Keep search in Home page only (no separate search page).
6) Clean settings: keep only practical options (Edit Profile, Change Password, Logout).
7) Avoid placeholder toasts for critical flows.
8) Ensure build passes (assembleDebug) and provide changed files list.

Output format:
- What changed
- Why
- Files modified
- How to test
```

## Prompt for Web/API Work
```text
Act as a senior PHP/CodeIgniter developer.

Project: VapeShopSystem (customer/admin/rider modules) + mobile_api.
Goal: Ensure mobile and web use consistent product/order/cart logic.

Requirements:
1) Expose mobile-safe JSON endpoints in /mobile_api for:
   login, register, products, product detail, cart add/list, checkout, orders list.
2) Products API must include:
   product id, name, category, puffs, price, stock, average rating, review count, has_flavors, variants.
3) Cart add must validate:
   - user exists
   - product active
   - stock available
   - if flavor-based with variants, require valid variant_id.
4) Orders list must return delivery status buckets compatible with tabs:
   all/to_pay/to_ship/to_receive/completed/to_review/cancelled/return_refund/failed_delivery.
5) Sanitize address output (avoid encrypted/garbled legacy values).
6) Keep SQL safe (prepared statements, no conflicting placeholders).
7) Return clear JSON messages and proper HTTP status codes.

Output format:
- Endpoint contract (request/response)
- DB assumptions/tables
- Changed files
- Test cases (Postman/curl)
```
//Run terminal para gumana sa app every time mag tangal ng usb 
& "C:\Users\ADMIN\AppData\Local\Android\Sdk\platform-tools\adb.exe" reverse tcp:8080 tcp:80
& "C:\Users\ADMIN\AppData\Local\Android\Sdk\platform-tools\adb.exe" reverse --list**


//for power shell
netsh advfirewall firewall add rule name="VapeShop Apache 80 In" dir=in action=allow protocol=TCP localport=80 profile=private
netsh advfirewall firewall add rule name="VapeShop Apache 80 Out" dir=out action=allow protocol=TCP localport=80 profile=private

// ipconfig