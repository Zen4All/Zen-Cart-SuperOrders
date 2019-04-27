<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 Fri Nov 9 08:33:17 2018 -0500 Modified in v1.5.6 $
 */
/**
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com	//
 *
 *  Powered by Zen-Cart (www.zen-cart.com)
 *  Portions Copyright (c) 2005 The Zen-Cart Team
 *
 *  DESCRIPTION:  Language file definitions for super_orders.php
 */

define('HEADING_TITLE', 'Orders');
define('HEADING_TITLE_DETAILS', 'Order Details');
define('HEADING_TITLE_SEARCH', 'Order ID:');
define('HEADING_TITLE_STATUS', 'Status:');
define('HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS', 'Product Name or ID:XX or Model');
define('HEADING_TITLE_SEARCH_ALL','Search: ');
define('HEADING_TITLE_SEARCH_PRODUCTS','Product search: ');
define('TEXT_RESET_FILTER', 'Remove search filter');
define('TEXT_INFO_SEARCH_DETAIL_FILTER_ORDERS_PRODUCTS', 'Search Filter: ');
define('TABLE_HEADING_PAYMENT_METHOD', 'Payment<br />Shipping');
define('TABLE_HEADING_ORDERS_ID','ID');

define('TEXT_BILLING_SHIPPING_MISMATCH','Billing and Shipping does not match ');

define('TABLE_HEADING_COMMENTS', 'Comments');
define('TABLE_HEADING_CUSTOMERS', 'Customers');
define('TABLE_HEADING_ORDER_TOTAL', 'Order Total');
define('TABLE_HEADING_DATE_PURCHASED', 'Date Purchased');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_TYPE', 'Order Type');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_QUANTITY', 'Qty.');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_TAX', 'Tax');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (excl)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (incl)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (excl)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (incl)');
define('TABLE_HEADING_UPDATED_BY', 'Updated By');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');

define('ENTRY_CUSTOMER', 'Customer:');
define('ENTRY_CUSTOMER_ADDRESS', 'Customer Address:<br><i class="fa fa-2x fa-user"></i>');
define('ENTRY_SOLD_TO', 'SOLD TO:');
define('ENTRY_DELIVERY_TO', 'Delivery To:');
define('ENTRY_SHIP_TO', 'SHIP TO:');
define('ENTRY_SHIPPING_ADDRESS', 'Shipping Address:<br><i class="fa fa-2x fa-truck"></i>');
define('ENTRY_BILLING_ADDRESS', 'Billing Address:<br><i class="fa fa-2x fa-credit-card"></i>');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_CREDIT_CARD_TYPE', 'Credit Card Type:');
define('ENTRY_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('ENTRY_CREDIT_CARD_CVV', 'Credit Card CVV Number:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Credit Card Expires:');
define('ENTRY_SUB_TOTAL', 'Sub-Total:');
define('ENTRY_TAX', 'Tax:');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_DATE_PURCHASED', 'Date Purchased:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_DATE_LAST_UPDATED', 'Date Last Updated:');
define('ENTRY_NOTIFY_CUSTOMER', 'Notify Customer:');
define('ENTRY_NOTIFY_COMMENTS', 'Append Comments:');
define('ENTRY_PRINTABLE', 'Print Invoice');

define('TEXT_INFO_HEADING_DELETE_ORDER', 'Delete Order');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this order?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Restock product quantity');
define('TEXT_DATE_ORDER_CREATED', 'Date Created:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_PAYMENT_METHOD', 'Payment Method:');
define('TEXT_PAID', 'Paid');
define('TEXT_UNPAID', 'Un-paid');

define('TEXT_ALL_ORDERS', 'All Orders');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Order Update');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Error: Order does not exist.');
define('SUCCESS_ORDER_UPDATED', 'Success: Order has been successfully updated.');
define('WARNING_ORDER_NOT_UPDATED', 'Warning: Nothing to change. The order was not updated.');

define('ENTRY_ORDER_ID','Invoice No. ');
define('TEXT_INFO_ATTRIBUTE_FREE', '&nbsp;-&nbsp;<span class="alert">FREE</span>');

define('TEXT_DOWNLOAD_TITLE', 'Order Download Status');
define('TEXT_DOWNLOAD_STATUS', 'Status');
define('TEXT_DOWNLOAD_FILENAME', 'Filename');
define('TEXT_DOWNLOAD_MAX_DAYS', 'Days');
define('TEXT_DOWNLOAD_MAX_COUNT', 'Count');

define('TEXT_DOWNLOAD_AVAILABLE', 'Available');
define('TEXT_DOWNLOAD_EXPIRED', 'Expired');
define('TEXT_DOWNLOAD_MISSING', 'Not on Server');

define('IMAGE_ICON_STATUS_CURRENT', 'Status - Available');
define('IMAGE_ICON_STATUS_EXPIRED', 'Status - Expired');
define('IMAGE_ICON_STATUS_MISSING', 'Status - Missing');

define('SUCCESS_ORDER_UPDATED_DOWNLOAD_ON', 'Download was successfully enabled');
define('SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF', 'Download was successfully disabled');
define('TEXT_MORE', '... more');

define('TEXT_INFO_IP_ADDRESS', 'IP Address: ');
define('TEXT_DELETE_CVV_FROM_DATABASE','Delete CVV from database');
define('TEXT_DELETE_CVV_REPLACEMENT','Deleted');
define('TEXT_MASK_CC_NUMBER','Mask this number');

define('TEXT_INFO_EXPIRED_DATE', 'Expired Date:<br />');
define('TEXT_INFO_EXPIRED_COUNT', 'Expired Count:<br />');

define('TABLE_HEADING_CUSTOMER_COMMENTS', 'Customer<br />Comments');
define('TEXT_COMMENTS_YES', 'Customer Comments - YES');
define('TEXT_COMMENTS_NO', 'Customer Comments - NO');

define('TEXT_CUSTOMER_LOOKUP', '<i class="fa fa-search"></i> Lookup Customer');

define('TEXT_INVALID_ORDER_STATUS', '<span class="alert">(Invalid Order Status)</span>');

define('BUTTON_TO_LIST', 'Order List');
define('SELECT_ORDER_LIST', 'Jump to Order:');

define('TEXT_MAP_CUSTOMER_ADDRESS', 'Map Customer Address');
define('TEXT_MAP_SHIPPING_ADDRESS', 'Map Shipping Address');
define('TEXT_MAP_BILLING_ADDRESS', 'Map Billing Address');

// BEGIN SUPER ORDERS  Changes 1 of 1
define('HEADING_TITLE_ORDERS_LISTING', 'Orders Listing');
define('HEADING_TITLE_ORDER_DETAILS', 'Order # ');

define('HEADING_TITLE_STATUS', 'Status:');
define('HEADING_REOPEN_ORDER', 'Re-Open Order');

define('TABLE_HEADING_STATUS_HISTORY', 'Order Status History &amp; Comments');
define('TABLE_HEADING_ADD_COMMENTS', 'Add Comments');
define('TABLE_HEADING_FINAL_STATUS', 'Close Order');

define('TABLE_HEADING_PAYMENT_METHOD', 'Payment Method');

define('PAYMENT_TABLE_NUMBER', 'Number');
define('PAYMENT_TABLE_NAME', 'Payor Name');
define('PAYMENT_TABLE_AMOUNT', 'Amount');
define('PAYMENT_TABLE_TYPE', 'Type');
define('PAYMENT_TABLE_POSTED', 'Date Posted');
define('PAYMENT_TABLE_MODIFIED', 'Last Modified');
define('PAYMENT_TABLE_ACTION', 'Action');
define('ALT_TEXT_ADD', 'Add');
define('ALT_TEXT_UPDATE', 'Update');
define('ALT_TEXT_DELETE', 'Delete');
define('ENTRY_PAYMENT_DETAILS', 'Payment Details');
define('ENTRY_CUSTOMER_ADDRESS', 'Customer Address:');
define('TEXT_ICON_LEGEND', 'Action Icon Legend:');
define('TEXT_BILLING_SHIPPING_MISMATCH', 'Billing and Shipping do not match');

define('TEXT_INFO_EXPIRED_DATE', 'Expired Date:<br />');
define('TEXT_INFO_EXPIRED_COUNT', 'Expired Count:<br />');

define('TEXT_INFO_SHIPPING_METHOD', 'Shipping Method:');

define('TEXT_DISPLAY_ONLY', '(Display Only)');
define('TEXT_CURRENT_STATUS', 'Current Status: ');

define('SUCCESS_MARK_COMPLETED', 'Success: Order #%s is completed!');
define('WARNING_MARK_CANCELLED', 'Warning: Order #%s has been cancelled');
define('WARNING_ORDER_REOPEN', 'Warning: Order #%s has been re-opened');

define('TEXT_NEW_WINDOW', ' (New Window)');
define('IMAGE_SHIPPING_LABEL', 'Shipping Label');
define('IMAGE_ORDER_DETAILS', 'Display Order Details');
define('ICON_ORDER_DETAILS', 'Display Order Details');
define('ICON_ORDER_PRINT', 'Print Data Sheet' . TEXT_NEW_WINDOW);
define('ICON_ORDER_INVOICE', 'Display Invoice' . TEXT_NEW_WINDOW);
define('ICON_ORDER_PACKINGSLIP', 'Display Packing Slip' . TEXT_NEW_WINDOW);
define('ICON_ORDER_SHIPPING_LABEL', 'Display Shipping Label' . TEXT_NEW_WINDOW);
define('ICON_ORDER_DELETE', 'Delete Order');
define('ICON_EDIT_CONTACT', 'Edit Contact Data');
define('ICON_EDIT_PRODUCT', 'Split Order');
define('ICON_EDIT_HISTORY', 'Edit Hidden (Admin) Comments');
define('ICON_CLOSE_STATUS', 'Close Status');
define('ICON_MARK_COMPLETED', 'Mark Order Completed');
define('ICON_MARK_CANCELLED', 'Mark Order Cancelled');
define('ICON_ORDER_EDIT', 'Edit this Order');

define('SUPER_IMAGE_ORDER_PRINT', 'Print Data Sheet' . TEXT_NEW_WINDOW);
define('SUPER_IMAGE_ORDERS_INVOICE', 'Display Invoice' . TEXT_NEW_WINDOW);
define('SUPER_IMAGE_ORDERS_PACKINGSLIP', 'Display Packing Slip' . TEXT_NEW_WINDOW);
define('SUPER_IMAGE_SHIPPING_LABEL', 'Display Shipping Label' . TEXT_NEW_WINDOW);

define('MINI_ICON_ORDERS', 'Show Customer\'s Orders');
define('MINI_ICON_INFO', 'Show Customer\'s Profile');


define('ENTRY_ORIGINAL_PAYMENT_AMOUNT', 'Split Order - Grand Total Paid:&nbsp;&nbsp;&nbsp;&nbsp;');
define('ENTRY_AMOUNT_APPLIED_CUST', 'Amount Applied:');
define('ENTRY_BALANCE_DUE_CUST', 'Balance Due:');
define('ENTRY_AMOUNT_APPLIED_SHOP', 'Amount Applied: (Default Store Currency)');
define('ENTRY_BALANCE_DUE_SHOP', 'Balance Due: (Default Store Currency)');

define('HEADING_COLOR_KEY', 'Color Key:');
define('TEXT_PURCHASE_ORDERS', 'Purchase Order');
define('TEXT_PAYMENTS', 'Payment');
define('TEXT_REFUNDS', 'Refund');
define('BUTTON_SPLIT', 'Split Packing Slip');

define('TEXT_NO_PAYMENT_DATA', 'No Order Payment Data Available');
define('TEXT_PAYMENT_DATA', 'Order Payment Data');

define('TEXT_MAILTO', 'mailto');
define('TEXT_STORE_EMAIL', 'web');
define('TEXT_WHOIS_LOOKUP', 'whois');
// END SUPER ORDERS  Changes 1 of 1
