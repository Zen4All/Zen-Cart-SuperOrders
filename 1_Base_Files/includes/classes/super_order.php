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
 *
 *  Powered by Zen-Cart (www.zen-cart.com)
 *  Portions Copyright (c) 2005 The Zen-Cart Team
 *
 *  Released under the GNU General Public License
 *  available at www.zen-cart.com/license/2_0.txt
 *  or see "license.txt" in the downloaded zip
 *
 * DESCRIPTION: Class file that manages inserting, modifying, removing,
 * and displaying payment data.
 *
 * $Id: super_order.php 2019 Zen4All
 */
class super_order {

  var $payment, $purchase_order, $po_payment, $refund, $payment_key, $payment_key_array;
  var $oID, $cID, $order_total, $amount_applied, $balance_due, $status, $status_date;

  // instantiates the class and gathers existing data
  function __construct($orders_id)
  {
    $this->payment = array();
    $this->purchase_order = array();
    $this->po_payment = array();
    $this->refund = array();
    $this->payment_key = array();
    $this->payment_key_array = array();

    $this->oID = (int)$orders_id;   // now you have the order_id whenever you need it
    $this->start();
  }

  function start()
  {
    global $db;

    // scrape some useful info from the record in the orders table
    $order_query = $db->Execute("SELECT *
                                 FROM " . TABLE_ORDERS . "
                                 WHERE orders_id = " . (int)$this->oID);
    $this->cID = $order_query->fields['customers_id'];
    $this->order_total = $order_query->fields['order_total'];

    if (zen_not_null($order_query->fields['date_cancelled'])) {
      $this->status_date = $order_query->fields['date_cancelled'];
      $this->status = "cancelled";
    } elseif (zen_not_null($order_query->fields['date_completed'])) {
      $this->status_date = $order_query->fields['date_completed'];
      $this->status = "completed";
    } else {
      $this->status_date = false;
      $this->status = false;
    }

    // ctp: get any records from paypal table that aren't already recorded:
    $this->verify_so_paypal_records();
    $this->verify_ccpay_records();

    // build an array to translate the payment_type codes stored in so_payments
    $payment_keys = $db->Execute("SELECT *
                                  FROM " . TABLE_SO_PAYMENT_TYPES . "
                                  WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                  ORDER BY payment_type_full ASC");
    if ($payment_keys->RecordCount() > 0) {
      foreach ($payment_keys as $payment_key) {
        // this array is used by the full_type() function
        $this->payment_key_array[$payment_key['payment_type_code']] = $payment_key['payment_type_full'];

        // and this one can be used to build dropdown menus
        $this->payment_key[] = array(
          'id' => $payment_key['payment_type_code'],
          'text' => $payment_key['payment_type_full']
        );
      }
    }

    // get all payments not tied to a purchase order
    $payments = $db->Execute("SELECT payment_id, payment_number, payment_name, payment_amount, payment_type, date_posted, last_modified
                              FROM " . TABLE_SO_PAYMENTS . "
                              WHERE orders_id = " . (int)$this->oID . "
                              AND purchase_order_id = 0
                              ORDER BY date_posted ASC");

    if ($payments->RecordCount() > 0) {
      foreach ($payments as $payment) {
        $this->payment[] = array(
          'index' => $payment['payment_id'],
          'number' => $payment['payment_number'],
          'name' => $payment['payment_name'],
          'amount' => $payment['payment_amount'],
          'type' => $payment['payment_type'],
          'posted' => $payment['date_posted'],
          'modified' => $payment['last_modified']
        );
      }
    } else {
      unset($this->payment);
      $this->payment = false;
    }

    // get all the purchase orders for this order
    $purchase_orders = $db->Execute("SELECT purchase_order_id, po_number, date_posted, last_modified
                                     FROM " . TABLE_SO_PURCHASE_ORDERS . "
                                     WHERE orders_id = " . (int)$this->oID . "
                                     ORDER BY date_posted ASC");

    if ($purchase_orders->RecordCount() > 0) {
      foreach ($purchase_orders as $purchase_order) {
        $this->purchase_order[] = array(
          'index' => $purchase_order['purchase_order_id'],
          'number' => $purchase_order['po_number'],
          'posted' => $purchase_order['date_posted'],
          'modified' => $purchase_order['last_modified']
        );
      }
    } else {
      unset($this->purchase_order);
      $this->purchase_order = false;
    }

    // get any payments that are tied to a purchase order
    if ($this->purchase_order) {    // need a po before you can have po payments
      for ($i = 0; $i < sizeof($this->purchase_order); $i++) {
        $this_po_id = $this->purchase_order[$i]['index'];

        $po_payments = $db->Execute("SELECT *
                                     FROM " . TABLE_SO_PAYMENTS . "
                                     WHERE purchase_order_id = '" . $this_po_id . "'
                                     ORDER BY date_posted ASC");

        if ($po_payments->RecordCount() > 0) {
          foreach ($po_payments as $po_payment) {
            $this->po_payment[] = array(
              'index' => $po_payment['payment_id'],
              'assigned_po' => $this_po_id,
              'number' => $po_payment['payment_number'],
              'name' => $po_payment['payment_name'],
              'amount' => $po_payment['payment_amount'],
              'type' => $po_payment['payment_type'],
              'posted' => $po_payment['date_posted'],
              'modified' => $po_payment['last_modified']
            );
          }
        }
      }

      if (sizeof($this->po_payment) < 1) {
        unset($this->po_payment);
        $this->po_payment = false;
      }
    }

    // get any refunds
    if ($this->payment || $this->po_payment) {   // gotta have payments in order to refund them
      $refunds = $db->Execute("SELECT *
                               FROM " . TABLE_SO_REFUNDS . "
                               WHERE orders_id = " . (int)$this->oID . "
                               ORDER BY date_posted ASC");

      if ($refunds->RecordCount() > 0) {
        foreach ($refunds as $refund) {
          $this->refund[] = array(
            'index' => $refund['refund_id'],
            'payment' => $refund['payment_id'],
            'number' => $refund['refund_number'],
            'name' => $refund['refund_name'],
            'amount' => $refund['refund_amount'],
            'type' => $refund['refund_type'],
            'posted' => $refund['date_posted'],
            'modified' => $refund['last_modified']
          );
        }
      } else {
        unset($this->refund);
        $this->refund = false;
      }
    }

    // calculate and store the order total, amount applied, & balance due for the order
    // add individual payments if they exists
    if ($this->payment) {
      for ($i = 0; $i < sizeof($this->payment); $i++) {
        $this->amount_applied += $this->payment[$i]['amount'];
      }
    }

    // next add the po payments if they exist
    if ($this->po_payment) {
      for ($i = 0; $i < sizeof($this->po_payment); $i++) {
        $this->amount_applied += $this->po_payment[$i]['amount'];
      }
    }

    // now subtract out any refunds if they exist
    if ($this->refund) {
      for ($i = 0; $i < sizeof($this->refund); $i++) {
        $this->amount_applied -= $this->refund[$i]['amount'];
      }
    }

    // subtract from the order total to get the balance due
    $this->balance_due = $this->order_total - $this->amount_applied;

    // compare this balance to the one stored in the orders table, update if necessary
    if ($this->balance_due != $order_query->fields['balance_due']) {
      $this->new_balance();
    }
  }

  /**
   * input the current value of $this->balance_due into balance_due
   * field in the orders table
   */
  function new_balance()
  {
    $a['balance_due'] = $this->balance_due;
    zen_db_perform(TABLE_ORDERS, $a, 'update', "orders_id = " . (int)$this->oID);
  }

  /**
   * timestamp the date_completed field in orders table
   * will also NULL out date_cancelled field if set (you can't have both at once!)
   */
  function mark_completed()
  {
    global $db;
    if ($this->status == false || $this->status == 'cancelled') {
      $db->Execute("UPDATE " . TABLE_ORDERS . "
                    SET date_completed = now()
                    WHERE orders_id = " . (int)$this->oID);

      if ($this->status == 'cancelled') {
        $db->Execute("UPDATE " . TABLE_ORDERS . "
                      SET date_cancelled = NULL
                      WHERE orders_id = " . (int)$this->oID);
      }
      if (STATUS_ORDER_COMPLETED != 0) {
        update_status($this->oID, STATUS_ORDER_COMPLETED);
      }
      $this->status = 'completed';
      $this->status_date = zen_datetime_short(date('Y-m-d H:i:s'));
    }
  }

  /**
   * timestamp the date_cancelled field in orders table
   * will also NULL out date_completed field if set (you can't have both at once!)
   */
  function mark_cancelled()
  {
    global $db;
    if ($this->status == false || $this->status == 'completed') {
      $db->Execute("UPDATE " . TABLE_ORDERS . "
                    SET date_cancelled = now()
                    WHERE orders_id = " . (int)$this->oID);

      if ($this->status == "completed") {
        $db->Execute("UPDATE " . TABLE_ORDERS . "
                      SET date_completed = NULL
                      WHERE orders_id = " . (int)$this->oID);
      }
      if (STATUS_ORDER_CANCELLED != 0) {
        update_status($this->oID, STATUS_ORDER_CANCELLED);
      }
      $this->status = 'cancelled';
      $this->status_date = zen_datetime_short(date('Y-m-d H:i:s'));
    }
  }

  /**
   * removes the cancelled/completed timestamp
   */
  function reopen()
  {
    global $db;
    $db->Execute("UPDATE " . TABLE_ORDERS . "
                  SET date_completed = NULL,
                      date_cancelled = NULL
                  WHERE orders_id = " . (int)$this->oID . "
                  LIMIT 1");

    if (STATUS_ORDER_REOPEN != 0) {
      update_status($this->oID, STATUS_ORDER_REOPEN);
    }
    $this->status = false;
    $this->status_date = false;
  }

  /**
   * Begin - Recreate credit card information stored in orders table as a line item in SO payment system
   */
  function cc_line_item()
  {
    global $db;
    // first we look for credit card payments
    $cc_data = $db->Execute("SELECT cc_type, cc_owner, cc_number, cc_expires, cc_cvv, date_purchased, order_total
                             FROM " . TABLE_ORDERS . "
                             WHERE orders_id = " . (int)$this->oID . "
                             LIMIT 1");
    if ($cc_data->RecordCount()) {
      // convert CC type to match shorthand type in SO payemnt system
      // collect payment types from the DB
      $payment_data = $db->Execute("SELECT *
                                    FROM " . TABLE_SO_PAYMENT_TYPES . "
                                    WHERE language_id = " . (int)$_SESSION['languages_id']);
      $cc_type_key = array();
      foreach ($payment_data as $item) {
        $cc_type_key[$item['payment_type_full']] = $item['payment_type_code'];
      }

      // convert CC name to match shorthand type in SO payment system
      // the name used at checkout must match name entered into Admin > Localization > Payment Types!
      $payment_type = $cc_type_key[$cc_data->fields['cc_type']];
      $new_cc_payment = array(
        'orders_id' => (int)$this->oID,
        'payment_number' => $cc_data->fields['cc_number'],
        'payment_name' => $cc_data->fields['cc_owner'],
        'payment_amount' => $cc_data->fields['order_total'],
        'payment_type' => $payment_type,
        'date_posted' => 'now()',
        'last_modified' => 'now()'
      );

      zen_db_perform(TABLE_SO_PAYMENTS, $new_cc_payment);
    }
  }

  /**
   * End - Recreate credit card information stored in orders table as a line item in SO payment system
   * builds an array of all PO's attached to an order, suitable for a dropdown menu
   */
  function build_po_array($include_blank = false)
  {
    global $db;
    $po_array = array();

    // include a user-defined "empty" entry
    if ($include_blank) {
      $po_array[] = array(
        'id' => false,
        'text' => $include_blank
      );
    }

    $po_query = $db->Execute("SELECT purchase_order_id, po_number
                              FROM " . TABLE_SO_PURCHASE_ORDERS . "
                              WHERE orders_id = " . (int)$this->oID);

    if ($po_query->RecordCount() > 0) {
      foreach ($po_query as $po) {
        $po_array[] = array(
          'id' => $po['purchase_order_id'],
          'text' => $po['po_number']
        );
      }
    }

    return $po_array;
  }

  /**
   * builds an array of all payments attached to an order, suitable for a dropdown menu
   */
  function build_payment_array($include_blank = false)
  {
    global $db;
    $payment_array = array();

    // include a user-defined "empty" entry if requested
    if ($include_blank) {
      $payment_array[] = array(
        'id' => false,
        'text' => $include_blank
      );
    }

    $payment_query = $db->Execute("SELECT payment_id, payment_number
                                   FROM " . TABLE_SO_PAYMENTS . "
                                   WHERE orders_id = " . (int)$this->oID);

    foreach ($payment_query as $payment) {
      $payment_array[] = array(
        'id' => $payment['payment_id'],
        'text' => $payment['payment_number']
      );
    }

    return $payment_array;
  }

  /**
   * Displays a button that will open a popup window to enter a new payment entry
   * This code assumes you have the popupWindow() function defined in your header!
   * Valid $payment_mode entries are: 'payment', 'purchase_order', 'refund'
   */
  function button_add($payment_mode)
  {
    $button = '&nbsp;<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $this->oID . '&payment_mode=' . $payment_mode . '&action=add') . '\', \'scrollbars=yes,resizable=yes,width=600,height=500,screenX=150,screenY=100,top=100,left=150\')" class="btn btn-primary btn-sm" role="button">' . ALT_TEXT_ADD . ' ' . str_replace('_', ' ', $payment_mode) . '</a>';
    return $button;
  }

  /**
   * Displays a button that will open a popup window to update an existing payment entry
   * This code assumes you have the popupWindow() function defined in your header!
   * Valid $payment_mode entries are: 'payment', 'purchase_order', 'refund'
   */
  function button_update($payment_mode, $index)
  {
    $button = '&nbsp;<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $this->oID . '&payment_mode=' . $payment_mode . '&index=' . $index . '&action=my_update', 'NONSSL') . '\', \'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150\')" class="btn btn-primary btn-sm" role="button">' . ALT_TEXT_UPDATE . ' ' . str_replace('_', ' ', $payment_mode) . '</a>';
    return $button;
  }

  /**
   * Displays a button that will open a popup window to confirm deleting a payment entry
   * This code assumes you have the popupWindow() function defined in your header!
   * Valid $payment_mode entries are: 'payment', 'purchase_order', 'refund'
   */
  function button_delete($payment_mode, $index)
  {
    $button = '&nbsp;<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $this->oID . '&payment_mode=' . $payment_mode . '&index=' . $index . '&action=delete', 'NONSSL') . '\', \'scrollbars=yes,resizable=yes,width=400,height=300,screenX=150,screenY=100,top=100,left=150\')" class="btn btn-warning btn-sm" role="button">' . ALT_TEXT_DELETE . ' ' . str_replace('_', ' ', $payment_mode) . '</a>';
    return $button;
  }

  function add_payment($payment_number, $payment_name, $payment_amount, $payment_type, $purchase_order_id = false)
  {
    global $db;

    $new_payment = array(
      'orders_id' => $this->oID,
      'payment_number' => zen_db_prepare_input($payment_number),
      'payment_name' => zen_db_prepare_input($payment_name),
      'payment_amount' => zen_db_prepare_input($payment_amount),
      'payment_type' => zen_db_prepare_input($payment_type),
      'date_posted' => 'now()',
      'last_modified' => 'now()'
    );

    // link the payment to its P.O. if applicable
    if ($purchase_order_id) {
      $new_payment['purchase_order_id'] = (int)$purchase_order_id;
    }

    zen_db_perform(TABLE_SO_PAYMENTS, $new_payment);

    $new_index = $db->Insert_ID(); // Change for ZenCart 1.5.3 and beyond.
    return $new_index;
  }

  function update_payment($payment_id, $purchase_order_id = false, $payment_number = false, $payment_name = false, $payment_amount = false, $payment_type = false, $orders_id = false)
  {
    $update_payment = array();
    $update_payment['last_modified'] = 'now()';

    if ($orders_id && $orders_id != '') {
      $update_payment['orders_id'] = (int)$orders_id;
    }
    if ($payment_number && $payment_number != '') {
      $update_payment['payment_number'] = zen_db_prepare_input($payment_number);
    }
    if ($payment_name && $payment_name != '') {
      $update_payment['payment_name'] = zen_db_prepare_input($payment_name);
    }
    if ($payment_amount && $payment_amount != '') {
      $update_payment['payment_amount'] = zen_db_prepare_input($payment_amount);
    }
    if ($payment_type && $payment_type != '') {
      $update_payment['payment_type'] = zen_db_prepare_input($payment_type);
    }
    if (is_numeric($purchase_order_id)) {
      $update_payment['purchase_order_id'] = (int)$purchase_order_id;
    }

    zen_db_perform(TABLE_SO_PAYMENTS, $update_payment, 'update', "payment_id = " . (int)$payment_id);
  }

  function add_purchase_order($po_number)
  {
    global $db;

    $add_po = array(
      'po_number' => zen_db_prepare_input($po_number),
      'orders_id' => $this->oID,
      'date_posted' => 'now()',
      'last_modified' => 'now()'
    );

    zen_db_perform(TABLE_SO_PURCHASE_ORDERS, $add_po);

    $new_index = $db->insert_ID();
    return $new_index;
  }

  function update_purchase_order($purchase_order_id, $po_number = false, $orders_id = false)
  {
    $update_po = array();
    $update_po['last_modified'] = 'now()';

    if ($orders_id && $orders_id != '') {
      $update_po['orders_id'] = zen_db_prepare_input($orders_id);
    }
    if ($po_number && $po_number != '') {
      $update_po['po_number'] = zen_db_prepare_input($po_number);
    }

    zen_db_perform(TABLE_SO_PURCHASE_ORDERS, $update_po, 'update', "purchase_order_id = " . (int)$purchase_order_id);
  }

  function add_refund($payment_id, $refund_number, $refund_name, $refund_amount, $refund_type)
  {
    global $db;
    $new_refund = array(
      'payment_id' => (int)$payment_id,
      'orders_id' => $this->oID,
      'refund_number' => zen_db_prepare_input($refund_number),
      'refund_name' => zen_db_prepare_input($refund_name),
      'refund_amount' => zen_db_prepare_input($refund_amount),
      'refund_type' => zen_db_prepare_input($refund_type),
      'date_posted' => 'now()',
      'last_modified' => 'now()'
    );

    zen_db_perform(TABLE_SO_REFUNDS, $new_refund);

    $new_index = $db->insert_ID(); // Change for ZenCart 1.5.3 and beyond.
    return $new_index;
  }

  function update_refund($refund_id, $payment_id = false, $refund_number = false, $refund_name = false, $refund_amount = false, $refund_type = false, $orders_id = false)
  {
    $update_refund = array();
    $update_refund['last_modified'] = 'now()';

    if (is_numeric($payment_id)) {
      $update_refund['payment_id'] = (int)$payment_id;
    }
    if ($refund_number && $refund_number != '') {
      $update_refund['refund_number'] = zen_db_prepare_input($refund_number);
    }
    if ($refund_name && $refund_name != '') {
      $update_refund['refund_name'] = zen_db_prepare_input($refund_name);
    }
    if ($refund_amount && $refund_amount != '') {
      $update_refund['refund_amount'] = zen_db_prepare_input($refund_amount);
    }
    if ($refund_type && $refund_type != '') {
      $update_refund['refund_type'] = zen_db_prepare_input($refund_type);
    }
    if ($orders_id && $orders_id != '') {
      $update_refund['orders_id'] = (int)$orders_id;
    }

    zen_db_perform(TABLE_SO_REFUNDS, $update_refund, 'update', "refund_id = " . (int)$refund_id);
  }

  function delete_refund($refund_id, $payment_id = false, $all = false)
  {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_SO_REFUNDS . " WHERE refund_id = " . (int)$refund_id);
  }

  function delete_payment($payment_id)
  {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_SO_PAYMENTS . " WHERE payment_id = " . (int)$payment_id);
  }

  function delete_purchase_order($purchase_order_id)
  {
    global $db;
    $db->Execute("DELETE FROM " . TABLE_SO_PURCHASE_ORDERS . " WHERE purchase_order_id = " . (int)$purchase_order_id);
  }

  function delete_all_data()
  {
    global $db;
    // remove payment data
    $db->Execute("DELETE FROM " . TABLE_SO_PAYMENTS . " WHERE orders_id = " . (int)$this->oID);
    // remove purchase order data
    $db->Execute("DELETE FROM " . TABLE_SO_PURCHASE_ORDERS . " WHERE orders_id = " . (int)$this->oID);
    // remove refund data
    $db->Execute("DELETE FROM " . TABLE_SO_REFUNDS . " WHERE orders_id = " . (int)$this->oID);
  }

  /**
   *  translates payment type codes into full text
   */
  function full_type($code)
  {
    if (array_key_exists($code, $this->payment_key_array)) {
      $full_text = $this->payment_key_array[$code];
    } else {
      $full_text = $code;
    }
    return $full_text;
  }

  function find_po_payments($purchase_order_id)
  {
    $po_payment_array = array();

    for ($x = 0; $x < sizeof($this->po_payment); $x++) {
      if ($this->po_payment[$x]['assigned_po'] == $purchase_order_id) {

        $po_payment_array[] = array(
          'index' => $this->po_payment[$x]['index'],
          'assigned_po' => $purchase_order_id,
          'number' => $this->po_payment[$x]['number'],
          'name' => $this->po_payment[$x]['name'],
          'amount' => $this->po_payment[$x]['amount'],
          'type' => $this->po_payment[$x]['type'],
          'posted' => $this->po_payment[$x]['posted'],
          'modified' => $this->po_payment[$x]['modified']
        );
      }
    }

    return $po_payment_array;
  }

  function find_refunds($payment_id)
  {
    $refund_array = array();

    for ($x = 0; $x < sizeof($this->refund); $x++) {
      if ($this->refund[$x]['payment'] == $payment_id) {
        $refund_array[] = array(
          'index' => $this->refund[$x]['index'],
          'payment' => $payment_id,
          'number' => $this->refund[$x]['number'],
          'name' => $this->refund[$x]['name'],
          'amount' => $this->refund[$x]['amount'],
          'type' => $this->refund[$x]['type'],
          'posted' => $this->refund[$x]['posted'],
          'modified' => $this->refund[$x]['modified']
        );
      }
    }
    return $refund_array;
  }

  /**
   *  ctp 3/15/2011 - Ensure all paypal transactions for this order are captured in the SO payment system - only the payments are captured, the refunds code has been commented out and has NOT been tested. Use refunds code AT YOUR OWN RISK!!
   *
   */
  function verify_so_paypal_records()
  {

    $auto_payment = array();
//    $auto_refund = array();
    $so_data = array();
    global $db;
    // get all PayPal status records for this order
    $pp_data = $db->Execute("SELECT *
                             FROM " . TABLE_PAYPAL . "
                             WHERE order_id = " . (int)$this->oID);

// for each PayPal record, find a matching SO payment record. If can't find, create one
    foreach ($pp_data as $item) {
//        if ($item['mc_gross'] < 0) {  //refund
//            if ($item['payment_status'] == 'Refunded' || $item['payment_status'] == 'Reversed') {
//               fill in local array with info from query
//                $auto_refund['payment_id'] = 0; // $item['paypal_ipn_id'];  // must be payment_id of a payment or 0
//                $auto_refund['orders_id'] = $this->oID;
//                $auto_refund['refund_number'] = $item['paypal_ipn_id'] . ":" . $item['txn_id'];
//                $auto_refund['refund_name'] = $item['first_name'] . ' ' . $item['last_name'];
//                $auto_refund['refund_amount'] = -1.0 * $item['mc_gross'];
//                $auto_refund['refund_type'] = "PayPal:" . $item['payment_status'];
//                $auto_refund['date_posted'] = $item['payment_date'];
// check to see if this record already exists in SO_refunds
//                $so_data = $db->Execute("select * from " . TABLE_SO_REFUNDS . " where refund_number = '" . $item['paypal_ipn_id'] . ":" . $item['txn_id'] . "'");
//TODO: make this more robust - retrieve all SO_REFUNDS tied to this order and look for lines that might have changed status with a diff ipn_id
// if not yet recorded, enter into the table
//                if ($so_data->EOF) {    // really should check if there are > 1 already
//                    zen_db_perform(TABLE_SO_REFUNDS, $auto_refund);
//                }
//            } // payment_status check
//        } else { // payment, because mc_gross > 0
      if ($item['payment_status'] == 'Completed' || $item['payment_status'] == 'Canceled_Reversal') {
        $auto_payment['orders_id'] = $this->oID;
        $auto_payment['payment_number'] = $auto_payment['payment_number'] = $item['paypal_ipn_id'] . "-" . $item['txn_id'];
        $auto_payment['payment_name'] = $item['first_name'] . ' ' . $item['last_name'];
        $auto_payment['payment_amount'] = $item['mc_gross'];
        $auto_payment['payment_type'] = $item['module_mode'];
        $auto_payment['date_posted'] = $item['payment_date'];
        $auto_payment['last_modified'] = $item['payment_date'];
        //$auto_payment['purchase_order_id'] = 0;    // $item['txn_id'];
        // check to see if this record already exists in SO_Payments
        $so_data = $db->Execute("SELECT *
                                 FROM " . TABLE_SO_PAYMENTS . "
                                 WHERE orders_id = " . (int)$this->oID);
        // if not yet recorded, enter into the table
        if ($so_data->EOF) {
          zen_db_perform(TABLE_SO_PAYMENTS, $auto_payment);
        }
      }
//       } // if refund or payment
    }
  }

  /**
   *  C Jones 06/25/2012 - Ensure all credit card transactions for this order are captured in the SO payment system - This is mostly to ensure that payment records for existing orders are auto generated for new Super Order installs
   *
   */
  function verify_ccpay_records()
  {
    $auto_payment = array();
    global $db;
    // get order record for this order
    $ccpay_data = $db->Execute("SELECT *
                                FROM " . TABLE_ORDERS . "
                                WHERE orders_id = " . (int)$this->oID);

// for each Credit Card paid order, find a matching SO payment record. If can't find, create one
    foreach ($ccpay_data as $item) {
      if ($item['payment_module_code'] == 'authorizenet_aim' || $item['payment_method'] == 'Credit Card') {
        $auto_payment['orders_id'] = $this->oID;
        $auto_payment['payment_number'] = $auto_payment['payment_number'] = $item['orders_id'] . "-" . $item['cc_number'];
        $auto_payment['payment_name'] = $item['customers_name'];
        $auto_payment['payment_amount'] = $item['order_total'];
        $auto_payment['payment_type'] = $item['cc_type'];
        $auto_payment['date_posted'] = $item['date_purchased'];
        $auto_payment['last_modified'] = $item['last_modified'];

        // check to see if this record already exists in SO_Payments
        $so_data = $db->Execute("SELECT *
                                 FROM " . TABLE_SO_PAYMENTS . "
                                 WHERE orders_id = " . (int)$this->oID);
        // if not yet recorded, enter into the table
        if ($so_data->EOF) {
          zen_db_perform(TABLE_SO_PAYMENTS, $auto_payment);
        }
      }
    }
  }

}
