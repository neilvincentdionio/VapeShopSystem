-- Backfill cost (unit_price) from selling price when cost is missing or zero.
-- Rule: unit_price = selling_price - 50 (minimum 0).
-- Run in phpMyAdmin on vapeshop_db, then fix existing order_items if needed.

UPDATE products
SET unit_price = ROUND(GREATEST(0, COALESCE(NULLIF(selling_price, 0), price) - 50), 2),
    selling_price = COALESCE(NULLIF(selling_price, 0), price),
    price = COALESCE(NULLIF(selling_price, 0), price)
WHERE COALESCE(unit_price, 0) <= 0
  AND COALESCE(NULLIF(selling_price, 0), price) > 0;

UPDATE order_items oi
INNER JOIN orders o ON o.id = oi.order_id
SET oi.unit_price = ROUND(GREATEST(0, COALESCE(NULLIF(oi.selling_price, 0), 0) - 50), 2),
    oi.subtotal = ROUND(COALESCE(NULLIF(oi.selling_price, 0), 0) * oi.quantity, 2),
    oi.profit = ROUND(
        ROUND(COALESCE(NULLIF(oi.selling_price, 0), 0) * oi.quantity, 2)
        - ROUND(GREATEST(0, COALESCE(NULLIF(oi.selling_price, 0), 0) - 50) * oi.quantity, 2),
        2
    ),
    oi.updated_at = NOW()
WHERE COALESCE(oi.unit_price, 0) <= 0
  AND COALESCE(NULLIF(oi.selling_price, 0), 0) > 0;

UPDATE orders o
SET o.total_profit = (
        SELECT COALESCE(SUM(oi.profit), 0)
        FROM order_items oi
        WHERE oi.order_id = o.id
    ),
    o.updated_at = NOW()
WHERE EXISTS (
    SELECT 1 FROM order_items oi
    WHERE oi.order_id = o.id AND COALESCE(oi.unit_price, 0) > 0
);
