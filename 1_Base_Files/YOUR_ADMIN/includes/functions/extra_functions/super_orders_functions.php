<?php

/**
 *
 *  SUPER ORDERS v5.0
 *
 *  Based on Super Order 2.0
 *  By Frank Koehl - PM: BlindSide (original author)
 *
 *  Super Orders Updated by:
 *  ~ JT of GTICustom
 *  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)
 *  ~ Loose Chicken Software Development, david@loosechicken.com
 *  ~ Zen4All (https://zen4all.nl)
 *
 *  Powered by Zen-Cart (www.zen-cart.com)
 *  Portions Copyright (c) 2005 The Zen-Cart Team
 *
 *  Released under the GNU General Public License
 *  available at www.zen-cart.com/license/2_0.txt
 *  or see "license.txt" in the downloaded zip
 *
 *  DESCRIPTION: A collection of functions utilized throughout the
 *  Super Orders files. Handy for developers, normal users won't need
 *  to even look in here. See each funtion for a brief description.
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
/////////////////
// Function    : update_status
// Arguments   : oID, new_status, notified(optional), comments(optional)
// Return      : none
// Description : Adds a new status entry to an order
/////////////////
function update_status($oID, $new_status, $notified = 0, $comments = '')
{
  global $db;
  if ($notified == -1) {
    $cust_notified = -1;
  } elseif ($notified == 1) {
    $cust_notified = 1;
  } else {
    $cust_notified = 0;
  }
  $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments)
                VALUES (" . (int)$oID . ", '" . $new_status . "', now(), '" . $cust_notified . "', '" . zen_db_prepare_input($comments) . "')");

  $db->Execute("UPDATE " . TABLE_ORDERS . "
                SET orders_status = '" . $new_status . "',
                    last_modified = now()
                WHERE orders_id = " . (int)$oID);
}

/////////////////
// Function    : email_latest_status
// Arguments   : oID, orders_status_array
// Return      : NONE
// Description : Sends email to customer notifying of the latest status assigned to given order
/////////////////
function email_latest_status($oID)
{
  global $db;
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'order_status_email.php');

  $orders_status_array = array();
  $orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                                 FROM " . TABLE_ORDERS_STATUS . "
                                 WHERE language_id = " . (int)$_SESSION['languages_id']);
  while (!$orders_status->EOF) {
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }

  $customer_info = $db->Execute("SELECT customers_name, customers_email_address, date_purchased
                                 FROM " . TABLE_ORDERS . "
                                 WHERE orders_id = '" . $oID . "'");

  $status_info = $db->Execute("SELECT orders_status_id, comments
                               FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                               WHERE orders_id = '" . $oID . "'
                               ORDER BY date_added Desc limit 1");

  $status = $status_info->fields['orders_status_id'];
  if ($_POST['notify_comments'] == 'on' && zen_not_null($status_info->fields['comments']) && $status_info->fields['comments'] != '') {
    $notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . $status_info->fields['comments'] . "\n\n";
  }

  // send email to customer
  $message = STORE_NAME . " " . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n\n" .
      EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n\n" .
      EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($customer_info->fields['date_purchased']) . "\n\n" .
      strip_tags($notify_comments) .
      EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status]) .
      EMAIL_TEXT_STATUS_PLEASE_REPLY;

  $html_msg['EMAIL_CUSTOMERS_NAME'] = $customer_info->fields['customers_name'];
  $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID;
  $html_msg['EMAIL_TEXT_INVOICE_URL'] = '<a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . '">' . str_replace(':', '', EMAIL_TEXT_INVOICE_URL) . '</a>';
  $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($customer_info->fields['date_purchased']);
  $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
  $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n', '', EMAIL_TEXT_STATUS_UPDATED);
  $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n', '', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status]));
  $html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[$status];
  $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n', '', EMAIL_TEXT_STATUS_PLEASE_REPLY);

  $html_msg['EMAIL_PAYPAL_TRANSID'] = '';
  // End Zen Cart v1.5 Modified Core Code

  zen_mail($customer_info->fields['customers_name'], $customer_info->fields['customers_email_address'], EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');


  // PayPal Trans ID, if any
  $sql = "SELECT txn_id, parent_txn_id
          FROM " . TABLE_PAYPAL . "
          WHERE order_id = :orderID
          ORDER BY last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC ";
  $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
  $result = $db->Execute($sql);
  if ($result->RecordCount() > 0) {
    $message .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
    $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
  }
  // End Zen Cart v1.5 Modified Core Code
  // send extra emails
  if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
    zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra');
  }

  //_TODO accept an optional array of additional recipients
}

/////////////////
// Function    : zen_get_payment_type_name
// Arguments   : payment code, language_id
// Return      : payment type full name
// Description : Translate a payment type code into the full name (eg. "MC" -> "MasterCard")
//               This function mimics the full_type() function of the super_order class
/////////////////
function zen_get_payment_type_name($payment_type_code, $language_id = '')
{
  global $db;

  if (empty($language_id)) {
    $language_id = $_SESSION['languages_id'];
  }
  $payment_type = $db->Execute("SELECT ptd.payment_type_full
                                FROM " . TABLE_SO_PAYMENT_TYPES . " pt
                                LEFT JOIN " . TABLE_SO_PAYMENT_TYPES_DESCRIPTION . " ptd ON ptd.payment_type_id = pt.payment_type_id
                                  AND ptd.language_id = " . (int)$language_id . "
                                WHERE pt.payment_type_code LIKE '" . $payment_type_code . "'
                                LIMIT 1");

  return $payment_type->fields['payment_type_full'];
}

/////////////////
// Function    : zen_get_payment_types
// Arguments   : none
// Return      : array or payment types
// Description : Builds array of payment types, following the format for a Zen dropdown
/////////////////
function zen_get_payment_types()
{
  global $db;

  $payment_type_array = array();
  $payment_types = $db->Execute("SELECT pt.payment_type_code, ptd.payment_type_full
                                FROM " . TABLE_SO_PAYMENT_TYPES . " pt
                                LEFT JOIN " . TABLE_SO_PAYMENT_TYPES_DESCRIPTION . " ptd ON ptd.payment_type_id = pt.payment_type_id
                                  AND ptd.language_id = '" . $_SESSION['languages_id'] . "'
                                ORDER BY ptd.payment_type_full DESC");

  foreach ($payment_types as $payment_type) {
    $payment_type_array[] = array(
      'id' => $payment_type['payment_type_code'],
      'text' => $payment_type['payment_type_full']
    );
  }

  return $payment_type_array;
}

/////////////////
// Function    : so_close_status
// Arguments   : orders_id
// Return      : array or false
// Description : builds 2-value array: cancel/complete status and the timestamp
//               Used when checking order status without using super_order class
/////////////////
function so_close_status($oID)
{
  global $db;
  $status = $db->Execute("SELECT date_cancelled, date_completed
                          FROM " . TABLE_ORDERS . "
                          WHERE orders_id = " . (int)$oID . "
                          AND (date_cancelled IS NOT NULL OR date_completed IS NOT NULL)");
  if ($status->RecordCount() == 0) {
    return false;
  } else {
    $close_status = array();
    if (zen_not_null($status->fields['date_cancelled'])) {
      $close_status['type'] = 'cancelled';
      $close_status['date'] = $status->fields['date_cancelled'];
    } elseif (zen_not_null($status->fields['date_completed'])) {
      $close_status['type'] = 'completed';
      $close_status['date'] = $status->fields['date_completed'];
    } else {
      $close_status['type'] = false;
      $close_status['date'] = false;
    }

    return $close_status;
  }
}

/////////////////
// Function    : all_products_array
// Arguments   : none
// Return      : products array
// Description : builds an array of all products (all languages, enabled or disabled) for a dropdown for searches
/////////////////
function all_products_array($first_option = false, $show_price = false, $show_model = false, $show_id = false)
{
  global $db, $currencies;
  if (!isset($currencies)) {
    require(DIR_WS_CLASSES . 'currencies.php');
    $currencies = new currencies();
  }
  $products_array = array();
  if ($first_option) {
    $products_array[] = array(
      'id' => '',
      'text' => $first_option
    );
  }
  $products = $db->Execute("SELECT products_id, products_name
                            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                            ORDER BY products_name ASC");
  foreach ($products as $product) {
    $display_price = zen_get_products_base_price($product['products_id']);
    $products_array[] = array(
      'id' => $product['products_id'],
      'text' => $product['products_name'] . ($show_price ? ' (' . $currencies->format($display_price) . ')' : '') . ($show_model ? ' [' . $product['products_model'] . ']' : '') . ($show_id ? ' [' . $product['products_id'] . ']' : '')
    );
  }
  return $products_array;
}

/////////////////
// Function    : all_payments_array
// Arguments   : none
// Return      : payments array
// Description : builds an array of each payment method attached to an order
/////////////////
function all_payments_array($first_option = false, $show_code = false)
{
  global $db;
  $payments_array = array();
  if ($first_option) {
    $payments_array[] = array(
      'id' => '',
      'text' => $first_option
    );
  }

  $payments = $db->Execute("SELECT DISTINCT payment_method, payment_module_code
                            FROM " . TABLE_ORDERS);
  foreach ($payments as $payment) {
    $payments_array[] = array(
      'id' => $payment['payment_module_code'],
      'text' => $payment['payment_method'] . ($show_code ? ' [' . $payment['payment_module_code'] . ']' : '')
    );
  }
  return $payments_array;
}

/////////////////
// Function    : all_customers_array
// Arguments   : none
// Return      : customers array
// Description : builds an array of *all* customers for a dropdown menu.
//               WARNING: Should not be used for e-mails, as it ignores newsletter preferences!
/////////////////
function all_customers_array($first_option = false, $show_email = false, $show_id = false)
{
  global $db;
  $customers_array = array();
  if ($first_option) {
    $customers_array[] = array(
      'id' => '',
      'text' => $first_option
    );
  }
  $customers_sql = "SELECT DISTINCT customers_id, customers_email_address,
                    customers_firstname, customers_lastname
                    FROM " . TABLE_CUSTOMERS . "
                    ORDER BY customers_lastname, customers_firstname, customers_email_address";
  $customers = $db->Execute($customers_sql);
  foreach ($customers as $customer) {
    $customers_array[] = array(
      'id' => $customer['customers_id'],
      'text' => $customer['customers_lastname'] . ', ' . $customer['customers_firstname'] . ($show_email ? ' (' . $customer['customers_email_address'] . ')' : '') . ($show_id ? ' [' . $customer['customers_id'] . ']' : '')
    );
  }
  return $customers_array;
}

/////////////////
// Function    : zen_datetime_long
// Arguments   : a raw date
// Return      : formatted date & time
// Description : Outputs a fully expressed date string
/////////////////
function zen_datetime_long($raw_date = 'now')
{
  if (($raw_date == '0001-01-01 00:00:00') || ($raw_date == '')) {
    return false;
  } elseif ($raw_date == 'now') {
    $raw_date = date('Y-m-d H:i:s');
  }

  $year = (int)substr($raw_date, 0, 4);
  $month = (int)substr($raw_date, 5, 2);
  $day = (int)substr($raw_date, 8, 2);
  $hour = (int)substr($raw_date, 11, 2);
  $minute = (int)substr($raw_date, 14, 2);
  $second = (int)substr($raw_date, 17, 2);

  return strftime('%b %d, %Y %r', mktime($hour, $minute, $second, $month, $day, $year));
}

/////////////////
// Function    : zen_db_delete
// Arguments   : Zen DB table, SQL "where" parameters
// Return      : none
// Description : Deletes a row or rows from the specified $table based on the $parameters
/////////////////
function zen_db_delete($table, $parameters)
{
  global $db;

  $db->Execute("DELETE FROM " . $table . " WHERE " . $parameters);

  return;
}

/////////////////
// Function    : recalc_total
// Arguments   : target order
// Return      : none
// Description : Reprocesses totals stored in the orders_total table for the given order.
/////////////////
function recalc_total($target_oID)
{
  global $db;
  global $currencies;

  $ot_subtotal = 0;
  $ot_tax = 0;
  $ot_total = 0;

  $products = $db->Execute("SELECT *
                            FROM " . TABLE_ORDERS_PRODUCTS . "
                            WHERE orders_id = " . (int)$target_oID);

  // recalculate subtotal and tax from products in order
  foreach ($products as $product) {
    $this_tax = 0;

    $this_subtotal = ($product['final_price'] * $product['products_quantity']);
    $ot_subtotal += $this_subtotal;

    // not everyone charges tax, so we check to see if it exists first
    if ($product['products_tax'] > 0) {
      $this_tax = $this_subtotal * ($product['products_tax'] / 100);
      $ot_tax += $this_tax;
    }
  }

  // apply new subtotal and tax values to the record
  $db->Execute("UPDATE " . TABLE_ORDERS_TOTAL . "
                SET text = '" . $currencies->format($ot_subtotal) . "',
                    value = '" . $ot_subtotal . "'
                WHERE orders_id = " . (int)$target_oID . "
                AND class = 'ot_subtotal'");

  if ($ot_tax > 0) {
    $db->Execute("UPDATE " . TABLE_ORDERS_TOTAL . "
                  SET text = '" . $currencies->format($ot_tax) . "',
                      value = '" . $ot_tax . "'
                  WHERE orders_id = " . (int)$target_oID . "
                  AND class = 'ot_tax'");
  }

  // add up all the records for the order (except ot_total)
  $all_totals = $db->Execute("SELECT *
                              FROM " . TABLE_ORDERS_TOTAL . "
                              WHERE orders_id = " . (int)$target_oID . "
                              ORDER BY sort_order ASC");

  foreach ($all_totals as $total) {
    $orders_total_id = $total['orders_total_id'];

    if ($total['class'] != 'ot_total') {
      if (is_discount_module($total['class'])) {
        $ot_total -= $total['value'];
      } else {
        $ot_total += $total['value'];
      }
    }
  }

  // apply new total value
  $db->Execute("UPDATE " . TABLE_ORDERS_TOTAL . "
                SET text = '" . $currencies->format($ot_total) . "',
                    value = '" . $ot_total . "'
                WHERE orders_id = " . (int)$target_oID . "
                AND class = 'ot_total'");

  $db->Execute("UPDATE " . TABLE_ORDERS . "
                SET order_tax = '" . $ot_tax . "',
                    order_total = '" . $ot_total . "'
                WHERE orders_id = " . (int)$target_oID);

  //return $ot_total;
}

/////////////////
// Function    : current_countries_array
// Arguments   : first_option
// Return      : countries array
// Description : builds an array of all countries (used in at least one order) for a dropdown menu.
/////////////////
function current_countries_array($first_option = false)
{
  global $db;

  $countries_array = array();
  if ($first_option) {
    $countries_array[] = array(
      'id' => '',
      'text' => $first_option
    );
  }
  $countries_array[] = array(
    'id' => get_store_country_name(),
    'text' => get_store_country_name()
  );
  $countries_array[] = array(
    'id' => 'International',
    'text' => 'International'
  );


  $countries = $db->Execute("SELECT DISTINCT customers_country
                             FROM " . TABLE_ORDERS . "
                             WHERE customers_country <> '" . get_store_country_name() . "'
                             ORDER BY customers_country");
  foreach ($countries as $country) {
    $countries_array[] = array(
      'id' => $country['customers_country'],
      'text' => $country['customers_country']
    );
  }
  return $countries_array;
}

/////////////////
// Function    : get_store_country_name
// Arguments   : NONE
// Return      : store country's name
// Description : gets store country's name from id
/////////////////
function get_store_country_name()
{
  $lcsd_store_country_name = '';
  if (defined('LCSD_STORE_COUNTRY_NAME')) {
    $lcsd_store_country_name = LCSD_STORE_COUNTRY_NAME;
  } else {
    global $db;
    $lcsd_db_country = $db->Execute("SELECT countries_name
                                     FROM " . TABLE_COUNTRIES . "
                                     WHERE countries_id = " . STORE_COUNTRY);
    if (!$lcsd_db_country->EOF) {
      $lcsd_store_country_name = $lcsd_db_country->fields['countries_name'];
      define('LCSD_STORE_COUNTRY_NAME', $lcsd_store_country_name);
    }
  }
  return $lcsd_store_country_name;
}

function is_discount_module($ot_class)
{
  if ($ot_class == "ot_gv" ||
      $ot_class == "ot_coupon" ||
      $ot_class == "ot_group_pricing" ||
      $ot_class == "ot_quantity_discount" ||
      $ot_class == "ot_better_together" ||
      $ot_class == "ot_big_chooser" ||
      $ot_class == "ot_bigspender_discount" ||
      $ot_class == "ot_combination_discounts" ||
      $ot_class == "ot_frequency_discount" ||
      $ot_class == "ot_quantity_discount" ||
      $ot_class == "ot_newsletter_discount" ||
      $ot_class == "ot_military_discount" ||
      $ot_class == "ot_table_discounts" ||
      $ot_class == "ot_case_discounts" ||
      $ot_class == "ot_freegift_chooser" ||
      $ot_class == "ot_manufacturer_discount" ||
      $ot_class == "ot_bogo_discount" ||
      $ot_class == 'ot_sc') {
    return true;
  }
  return false;
}
