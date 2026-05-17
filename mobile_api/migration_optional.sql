-- Optional migration for installations missing cart/order support tables.
-- Safe to run in phpMyAdmin because each statement uses IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS carts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_carts_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_cart_product_variant (cart_id, product_id, variant_id),
    KEY idx_cart_items_cart_id (cart_id),
    KEY idx_cart_items_product_id (product_id),
    CONSTRAINT fk_cart_items_cart_id FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product_id FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS order_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    method ENUM('cash','card','gcash','bank_transfer') NOT NULL DEFAULT 'cash',
    status ENUM('paid','partial','unpaid','pending') NOT NULL DEFAULT 'unpaid',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    amount_received DECIMAL(12,2) NULL,
    change_amount DECIMAL(12,2) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_order_payments_order_id (order_id),
    KEY idx_order_payments_status (status),
    CONSTRAINT fk_order_payments_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS order_shipments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    assigned_rider_id INT UNSIGNED NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'to_pay',
    assigned_at DATETIME NULL,
    picked_up_at DATETIME NULL,
    completed_at DATETIME NULL,
    tracking_number VARCHAR(100) NULL,
    shipping_address TEXT NULL,
    contact_number VARCHAR(20) NULL,
    shipped_at DATETIME NULL,
    delivered_at DATETIME NULL,
    delivery_proof_image VARCHAR(255) NULL,
    delivery_notes TEXT NULL,
    delivery_proof_submitted_at DATETIME NULL,
    notes TEXT NULL,
    delivery_latitude DECIMAL(10,7) NULL,
    delivery_longitude DECIMAL(10,7) NULL,
    delivery_address TEXT NULL,
    store_latitude DECIMAL(10,7) NULL,
    store_longitude DECIMAL(10,7) NULL,
    store_address TEXT NULL,
    delivered_latitude DECIMAL(10,7) NULL,
    delivered_longitude DECIMAL(10,7) NULL,
    rider_latitude DECIMAL(10,7) NULL,
    rider_longitude DECIMAL(10,7) NULL,
    final_rider_latitude DECIMAL(10,7) NULL,
    final_rider_longitude DECIMAL(10,7) NULL,
    last_location_updated_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_order_shipments_order_id (order_id),
    KEY idx_order_shipments_status (status),
    CONSTRAINT fk_order_shipments_order_id FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_shipments_rider FOREIGN KEY (assigned_rider_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
);
