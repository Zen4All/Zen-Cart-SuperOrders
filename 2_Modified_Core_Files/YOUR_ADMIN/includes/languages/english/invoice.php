<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//////////////////////////////////////////////////////////////////////////
//  Based on Super Order 2.0                                        	//
//  By Frank Koehl - PM: BlindSide (original author)                	//
//                                                                  	//
//  Super Orders Updated by:						//
//  ~ JT of GTICustom							//
//  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)	//
//  ~ Loose Chicken Software Development, david@loosechicken.com	//
//////////////////////////////////////////////////////////////////////////
//  DESCRIPTION: Language file definitions for super_invoice.php	//
//////////////////////////////////////////////////////////////////////////
//  $Id: invoice.php 5961 2007-03-03 17:17:39Z ajeh $
//

// Don't forget to configure the new Phone and Fax numbers in the Admin!
// Configuration > My Store > Store Phone/Store Fax

define('HEADER_INVOICE', 'Invoice - Order #');
// AJB 2012-05-31 - start (1)
define('HEADER_INVOICES', 'Invoices');
// AJB 2012-05-31 - end (1)
define('HEADER_TAX_ID', 'Tax ID #');
define('HEADER_PHONE', 'Phone:');
define('HEADER_FAX', 'Fax:');
define('HEADER_CUSTOMER_NOTES', 'Order Notes:');
define('HEADER_PO_NUMBER', 'P.O. Number:');
define('HEADER_PO_INVOICE_DATE', 'Invoice Date:');
define('HEADER_PO_TERMS', 'Terms:');
define('HEADER_PO_TERMS_LENGTH', '30 Days');
define('TABLE_HEADING_COMMENTS', 'Comments');
define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');
define('TABLE_HEADING_STATUS', 'Status');

define('TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_TAX', 'Tax');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (excl)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (incl)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (excl)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (incl)');
define('TABLE_HEADING_PRICE_NO_TAX', 'Unit Price');
define('TABLE_HEADING_TOTAL_NO_TAX', 'Total');

define('ENTRY_CUSTOMER', 'CUSTOMER:');

define('ENTRY_SOLD_TO', 'SOLD TO:');
define('ENTRY_BILL_TO', 'BILL TO:');
define('ENTRY_SHIP_TO', 'SHIP TO:');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_PO_INFO', 'P.O. DETAILS');
define('ENTRY_NO_TAX', 'Tax Exempt');
define('ENTRY_SUB_TOTAL', 'Sub-Total:');
define('ENTRY_TAX', 'Tax:');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_DATE_PURCHASED', 'Date Ordered:');

define('ENTRY_ORDER_ID','Order #');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_AMOUNT_APPLIED_CUST', 'Amount Applied:');
define('ENTRY_BALANCE_DUE_CUST', 'Balance Due:');
define('ENTRY_AMOUNT_APPLIED_SHOP', 'Amount Applied: (Default Store Currency)');
define('ENTRY_BALANCE_DUE_SHOP', 'Balance Due: (Default Store Currency)');

define('TEXT_INFO_ATTRIBUTE_FREE', '&nbsp;-&nbsp;FREE');