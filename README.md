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

The E-commerce Vapeshop is a web-based sales and inventory management system developed to support the online retail of vape products. The system is designed to streamline business operations by integrating product management, transaction processing, and compliance monitoring into a single centralized platform.

The system provides centralized product and inventory management, allowing administrators to efficiently monitor stock levels, update product information, and manage item availability. It supports customer purchase and sales transaction processing, with automatic stock level updates after each successful purchase to ensure inventory accuracy.

To meet regulatory requirements, the system includes age-verification transaction logging for compliance purposes. Additional features such as receipt generation and printing improve transaction documentation, while smart low-stock and fast-moving product alerts assist in inventory planning and decision-making. The system also monitors product expiry, particularly for vape liquids, to maintain product quality and safety.

Furthermore, the platform generates daily and monthly enterprise sales reports to support business analysis and performance tracking. Role-based system access ensures that only authorized users can manage sensitive system functions, improving overall security and operational control.The E-commerce Vapeshop is a web-based sales and inventory management system developed to support the online retail of vape products. The system is designed to streamline business operations by integrating product management, transaction processing, and compliance monitoring into a single centralized platform.

The system provides centralized product and inventory management, allowing administrators to efficiently monitor stock levels, update product information, and manage item availability. It supports customer purchase and sales transaction processing, with automatic stock level updates after each successful purchase to ensure inventory accuracy.

To meet regulatory requirements, the system includes age-verification transaction logging for compliance purposes. Additional features such as receipt generation and printing improve transaction documentation, while smart low-stock and fast-moving product alerts assist in inventory planning and decision-making. The system also monitors product expiry, particularly for vape liquids, to maintain product quality and safety.

Furthermore, the platform generates daily and monthly enterprise sales reports to support business analysis and performance tracking. Role-based system access ensures that only authorized users can manage sensitive system functions, improving overall security and operational control.
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