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
//  DESCRIPTION:              //
//////////////////////////////////////////////////////////////////////////
// $Id: order_status_email.php v 2010-10-24 $
*/

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Order Update');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_COMMENTS_UPDATE', "\n" . '<br><strong><em>Comments:</em></strong><br>' . "\n");
define('EMAIL_TEXT_STATUS_UPDATED', "\n" . '<br>Your current order status is:  ');
define('EMAIL_TEXT_STATUS_LABEL', '<em>%s</em><br><br>' . "\n\n");
define('EMAIL_TEXT_STATUS_PLEASE_REPLY',  "\n" . '<br>Please reply to this email if you have any questions.<br><br>' . "\n\n");
