<?php
/*
//////////////////////////////////////////////////////////////////////////
//  SUPER ORDERS v3.0                                                 //
//                                                                    //
//  Based on Super Order 2.0                                          //
//  By Frank Koehl - PM: BlindSide (original author)                  //
//                                                                    //
//  Super Orders Updated by:            //
//  ~ JT of GTICustom             //
//  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)  //
//  ~ Loose Chicken Software Development, david@loosechicken.com  //
//                                                          //
//  Powered by Zen-Cart (www.zen-cart.com)                  //
//  Portions Copyright (c) 2005 The Zen-Cart Team           //
//                                                          //
//  Released under the GNU General Public License           //
//  available at www.zen-cart.com/license/2_0.txt           //
//  or see "license.txt" in the downloaded zip              //
//////////////////////////////////////////////////////////////////////////
//  DESCRIPTION:   Contains all the general defines necessary for the //
//  Super Orders system to operate properly.        //
//                  //
//  You should not have to edit anything in this file.      //
//////////////////////////////////////////////////////////////////////////
// $Id: super_batch_forms.php v 2010-10-24 $
*/

// Core files
define('FILENAME_SUPER_EDIT', 'super_edit');
define('FILENAME_SUPER_ORDERS', 'orders');
define('FILENAME_SUPER_DATA_SHEET', 'super_data_sheet');
define('FILENAME_SUPER_INVOICE', 'invoice');
define('FILENAME_SUPER_PACKINGSLIP', 'packingslip');
define('FILENAME_SUPER_SHIPPING_LABEL', 'super_shipping_label');
define('FILENAME_SUPER_PAYMENTS', 'super_payments');
define('FILENAME_SUPER_PAYMENT_TYPES', 'super_payment_types');

// Reports
define('FILENAME_SUPER_REPORT_AWAIT_PAY', 'super_report_await_pay');
define('FILENAME_SUPER_REPORT_CASH', 'super_report_cash');

// Batch Systems
define('FILENAME_SUPER_BATCH_STATUS', 'super_batch_status');
define('FILENAME_SUPER_BATCH_FORMS', 'super_batch_forms');
define('FILENAME_SUPER_BATCH_PAGES', 'super_batch_pages');


// Table names
define('TABLE_SO_PURCHASE_ORDERS', DB_PREFIX . 'so_purchase_orders');
define('TABLE_SO_PAYMENTS', DB_PREFIX . 'so_payments');
define('TABLE_SO_PAYMENT_TYPES', DB_PREFIX . 'so_payment_types');
define('TABLE_SO_PAYMENT_TYPES_DESCRIPTION', DB_PREFIX . 'so_payment_types_description');
define('TABLE_SO_REFUNDS', DB_PREFIX . 'so_refunds');

// Other labels
define('HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS', 'Search by Product Name or <strong>ID:XX</strong> or Model ');
define('TEXT_INFO_SEARCH_DETAIL_FILTER_ORDERS_PRODUCTS', 'Search Filter: ');

// Admin Menu Boxes
define('BOX_CONFIGURATION_SUPER_ORDERS', 'Super Orders');
define('BOX_CUSTOMERS_SUPER_BATCH_STATUS', 'Batch Status Update');
define('BOX_CUSTOMERS_SUPER_BATCH_FORMS', 'Batch Form Print');
define('BOX_CUSTOMERS_SUPER_BATCH_PAGES', 'Super Orders Batch Pages');
define('BOX_CUSTOMERS_SUPER_SHIPPING_LABEL', 'Super Orders Shipping Label');
define('BOX_CUSTOMERS_SUPER_EDIT_POPUP', 'Super Orders Edit Pop-Up');
define('BOX_CUSTOMERS_SUPER_DATA_SHEET', 'Super Orders Data Sheet');
//define('BOX_CUSTOMERS_SUPER_PAYMENTS', 'Super Payments'); //This is leftover from older versions of SO. It looks like it was planned future development
define('BOX_REPORTS_SUPER_REPORT_AWAIT_PAY', 'Orders Awaiting Payment');
define('BOX_REPORTS_SUPER_REPORT_CASH', 'Cash Report');
define('BOX_REPORTS_SUPER_PAYMENT_TYPES', 'Payment Types');
define('BOX_LOCALIZATION_MANAGE_PAYMENT_TYPES','Manage Payment Types');
