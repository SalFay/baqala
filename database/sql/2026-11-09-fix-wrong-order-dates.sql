-- Fix orders saved with a wrong date (POS date box changed by mistake).
-- Sets the date back to the day the order was actually created, in every table
-- that copied it. Orders placed after midnight and dated the previous business
-- day are NOT touched. Safe to re-run: the second run changes nothing.
--
-- created_at holds Pakistan wall-clock time stored as UTC, so read it in UTC.
SET time_zone = '+00:00';

START TRANSACTION;

CREATE TEMPORARY TABLE wrong_dates AS
SELECT id AS order_id, date AS wrong_date, DATE(created_at) AS real_date
FROM orders
WHERE deleted_at IS NULL
  AND NOT (DATEDIFF(date, DATE(created_at)) = 0
           OR (DATEDIFF(date, DATE(created_at)) = -1 AND HOUR(created_at) < 6));

SELECT COUNT(*) AS orders_to_fix FROM wrong_dates;

UPDATE order_items oi JOIN wrong_dates w ON w.order_id = oi.order_id
SET oi.date = w.real_date
WHERE oi.order_type = 'App\\Models\\Order' AND oi.date = w.wrong_date;

UPDATE inventory_log il JOIN wrong_dates w ON w.order_id = il.order_id
SET il.date = w.real_date
WHERE il.order_type = 'App\\Models\\Order' AND il.date = w.wrong_date;

UPDATE inventory i JOIN wrong_dates w ON w.order_id = i.order_id
SET i.date = w.real_date
WHERE i.stock_id = 0 AND i.date = w.wrong_date;

UPDATE orders o JOIN wrong_dates w ON w.order_id = o.id
SET o.date = w.real_date;

DROP TEMPORARY TABLE wrong_dates;

COMMIT;
