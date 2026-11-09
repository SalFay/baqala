-- Make the customer ledger match the orders.
--   * Deleted orders: their ledger entries are removed (soft-deleted), instead of
--     the old one-sided "Order No.X Deleted" credit that left the walk-in
--     customer at -543,897.
--   * Edited orders: only the entries of the latest version are kept; the
--     original sale and older edits were posted again in full on every edit.
-- Rows are soft-deleted (deleted_at), never removed. Safe to re-run.

START TRANSACTION;

-- Every order-related ledger row, with the order id it belongs to.
CREATE TEMPORARY TABLE order_ledger AS
SELECT a.id,
       CAST(REGEXP_SUBSTR(a.comments, '[0-9]+') AS UNSIGNED) AS order_id,
       a.comments LIKE 'Updated Order No.%' AS is_update,
       COALESCE(a.credit, 0) AS credit
FROM accounts a
WHERE a.deleted_at IS NULL
  AND a.party_type = 'App\\Models\\Customer'
  AND (a.comments REGEXP '^Order No\\.[0-9]+$'
    OR a.comments REGEXP '^Updated Order No\\.[0-9]+$'
    OR a.comments REGEXP '^Order No\\.[0-9]+ Deleted$');

-- 1. Deleted orders: drop all their entries.
UPDATE accounts a
JOIN order_ledger l ON l.id = a.id
JOIN orders o ON o.id = l.order_id AND o.deleted_at IS NOT NULL
SET a.deleted_at = NOW();

-- 2. Edited orders: keep the latest edit's entries (its total row, which has no
--    credit and may be 0, and the payment row after it), drop the original
--    sale and older edits.
CREATE TEMPORARY TABLE latest_edit AS
SELECT l.order_id, MAX(l.id) AS keep_from
FROM order_ledger l
JOIN orders o ON o.id = l.order_id AND o.deleted_at IS NULL
WHERE l.is_update = 1 AND l.credit = 0
GROUP BY l.order_id;

UPDATE accounts a
JOIN order_ledger l ON l.id = a.id
JOIN latest_edit e ON e.order_id = l.order_id
SET a.deleted_at = NOW()
WHERE l.id < e.keep_from;

DROP TEMPORARY TABLE latest_edit;
DROP TEMPORARY TABLE order_ledger;

COMMIT;
