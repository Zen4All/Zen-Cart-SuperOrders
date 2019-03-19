
-- EDIT EXISTING TABLES
ALTER TABLE orders 
	DROP date_completed,
	DROP date_cancelled,
	DROP balance_due;

ALTER TABLE orders
	DROP split_from_order,
	DROP is_parent;

-- DROP TABLES
DROP TABLE IF EXISTS so_payments;
DROP TABLE IF EXISTS so_payment_types;
DROP TABLE IF EXISTS so_purchase_orders;
DROP TABLE IF EXISTS so_refunds;
DROP TABLE IF EXISTS payment_purchase_order;
DROP TABLE IF EXISTS payment_check;
DROP TABLE IF EXISTS payment_check_balance;

-- Store Phone and Fax numbers
-- comment out if you want to keep these values
DELETE FROM configuration WHERE configuration_key = 'STORE_FAX';
DELETE FROM configuration WHERE configuration_key = 'STORE_PHONE';

-- Purchase Order payment module configs
DELETE FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_PURCHASE_ORDER_STATUS';
DELETE FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_PURCHASE_ORDER_PAYTO';
DELETE FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_PURCHASE_ORDER_SORT_ORDER';
DELETE FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_PURCHASE_ORDER_ZONE';
DELETE FROM configuration WHERE configuration_key = 'MODULE_PAYMENT_PURCHASE_ORDER_ORDER_STATUS_ID';

-- Super Orders configuration group
SELECT @t4:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title= 'Super Orders';
DELETE FROM configuration WHERE configuration_group_id = @t4;
DELETE FROM configuration_group WHERE configuration_group_id = @t4;


DELETE FROM admin_pages WHERE page_key ='reportsOrdersAwaitingPayment';
DELETE FROM admin_pages WHERE page_key ='reportsCashReport';
DELETE FROM admin_pages WHERE page_key ='configSuperOrders';
DELETE FROM admin_pages WHERE page_key ='customersBatchStatusUpdate';
DELETE FROM admin_pages WHERE page_key ='customersBatchFormPrint';
DELETE FROM admin_pages WHERE page_key ='localizationManagePaymentTypes';