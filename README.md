# E-Commerce Inventory and Sales Management System for Vape Shop

## Group Members
- Neil Vincent Dionio
- Jhondell Rañises
- Christian Bermudez
- Mike Cidric Santillan
- Jed Isaac Valenzuela
- Mark Owen Lu

# System Discription
- System Description – E-commerce Vapeshop

The E-commerce Vapeshop is a comprehensive, web-based sales and inventory management system carefully developed to effectively support the growing online retail of various vape products. The system is intelligently designed to significantly streamline daily business operations by seamlessly integrating advanced product management, secure transaction processing, and rigorous compliance monitoring into one powerful centralized platform.

The system delivers robust centralized product and inventory management capabilities, enabling administrators to precisely monitor real-time stock levels, promptly update detailed product information, and efficiently manage overall item availability. It fully supports smooth customer purchase and sales transaction processing, featuring automatic stock level updates immediately after each successful purchase to guarantee complete inventory accuracy and reliability.

To strictly meet stringent regulatory requirements, the system incorporates mandatory age-verification transaction logging for essential compliance purposes. Additional practical features such as automated receipt generation and convenient printing greatly improve transaction documentation processes, while intelligent low-stock and fast-moving product alerts effectively assist in proactive inventory planning and informed decision-making. The system also diligently monitors product expiry dates, particularly for sensitive vape liquids, to consistently maintain superior product quality and user safety.

Furthermore, the platform reliably generates detailed daily and monthly enterprise sales reports to strongly support in-depth business analysis and accurate performance tracking. Role-based system access controls rigorously ensure that only properly authorized users can access and manage sensitive system functions, thereby enhancing overall security and operational control.

# Technologies Used

* Designing UI
- HTML, CSS, JavaScript

* Database
- mySQL, PHPMyAdmin

* Applications
- VSCode, Android Studio, XAMPP

* Back-end
- PHP, CodeIgniter Framework

## Installation Steps

### Prerequisites
- XAMPP (or similar local server environment with Apache, MySQL, PHP)
- Composer
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone or Extract the Project**
   ```bash
   cd c:\xampp\htdocs
   # Extract the VapeShopSystem folder if not already present
   ```

2. **Install PHP Dependencies**
   ```bash
   cd VapeShopSystem
   composer install
   ```

3. **Configure Environment**
   - Copy `.env.example` to `.env` (if it exists)
   - Update database credentials in `.env` or `app/Config/Database.php`
   ```php
   'hostname' => 'localhost',
   'username' => 'root',
   'password' => '',
   'database' => 'vape_shop',
   ```

4. **Create Database**
   - Open PHPMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `vape_shop`

5. **Run Migrations**
   ```bash
   php spark migrate
   ```

6. **Seed Database (Optional)**
   ```bash
   php spark db:seed UserSeeder
   ```

7. **Start XAMPP Server**
   - Start Apache and MySQL from XAMPP Control Panel

8. **Access the Application**
   - Open your browser and navigate to: `http://localhost/VapeShopSystem/public`

### Troubleshooting
- Ensure XAMPP is running before accessing the application
- Check database connection settings if migration fails
- Clear browser cache if you encounter display issues