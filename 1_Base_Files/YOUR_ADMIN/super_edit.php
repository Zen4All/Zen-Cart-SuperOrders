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
//  DESCRIPTION:   Generates a pop-up window to edit the selected order //
//  information, broken into sections: contact, product, history, and //
//  total.                //
//////////////////////////////////////////////////////////////////////////
// $Id: super_edit.php v 2010-10-24 $
*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'order.php');
  global $db;

  $target = $_REQUEST['target'];  // 'contact', 'product', 'history', or 'total'
  $oID = (int)$_REQUEST['oID'];
  $order = new order($oID);

  // recreate the $order->products array, adding in some extra fields
  $index = 0;
  $orders_products = $db->Execute("select orders_products_id, products_name, products_model,
                                          products_price, products_tax, products_quantity,
                                          final_price, onetime_charges,
                                          product_is_free, products_id
                                   from " . TABLE_ORDERS_PRODUCTS . "
                                   where orders_id = '" . $oID . "'");

  while (!$orders_products->EOF) {
    // convert quantity to proper decimals - account history
    if (QUANTITY_DECIMALS != 0) {
      $fix_qty = $orders_products->fields['products_quantity'];
      switch (true) {
        case (!strstr($fix_qty, '.')):
          $new_qty = $fix_qty;
        break;
        default:
          $new_qty = preg_replace('/[0]+$/', '', $orders_products->fields['products_quantity']);
        break;
      }
    } else {
      $new_qty = $orders_products->fields['products_quantity'];
    }

    $new_qty = round($new_qty, QUANTITY_DECIMALS);

    if ($new_qty == (int)$new_qty) {
      $new_qty = (int)$new_qty;
    }

    $order->products[$index] = array('qty' => $new_qty,
                  'name' => $orders_products->fields['products_name'],
                  'products_id' => $orders_products->fields['products_id'],
                  'model' => $orders_products->fields['products_model'],
                  'tax' => $orders_products->fields['products_tax'],
                  'price' => $orders_products->fields['products_price'],
                  'onetime_charges' => $orders_products->fields['onetime_charges'],
                  'final_price' => $orders_products->fields['final_price'],
                  'product_is_free' => $orders_products->fields['product_is_free'],
                  'orders_products_id' => $orders_products->fields['orders_products_id']);

    $subindex = 0;
    $attributes = $db->Execute("select products_options, products_options_values, options_values_price,
                                    price_prefix,
                  product_attribute_is_free
                  from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  where orders_id = '" . $oID . "'
                  and orders_products_id = '" . (int)$orders_products->fields['orders_products_id'] . "'");
    if ($attributes->RecordCount()>0) {
    while (!$attributes->EOF) {
      $order->products[$index]['attributes'][$subindex] = array('option' => $attributes->fields['products_options'],
                                'value' => $attributes->fields['products_options_values'],
                                'prefix' => $attributes->fields['price_prefix'],
                                'price' => $attributes->fields['options_values_price'],
                                'product_attribute_is_free' => $attributes->fields['product_attribute_is_free']);
        $subindex++;
        $attributes->MoveNext();
      }
    }

    $index++;
    $orders_products->MoveNext();
  }  // END while (!$orders_products->EOF) {


  if ($_POST['process'] == 1) {
    $update = array();
    switch ($target) {
      case 'contact':

        // customer address data
        if ($_POST['customers_name'] != $order->customer['name']) {
          $update['customers_name'] = zen_db_prepare_input($_POST['customers_name'], true);
        }
        if ($_POST['customers_company'] != $order->customer['company']) {
          $update['customers_company'] = zen_db_prepare_input($_POST['customers_company'], true);
        }
        if ($_POST['customers_street_address'] != $order->customer['street_address']) {
          $update['customers_street_address'] = zen_db_prepare_input($_POST['customers_street_address'], true);
        }
        if ($_POST['customers_suburb'] != $order->customer['suburb']) {
          $update['customers_suburb'] = zen_db_prepare_input($_POST['customers_suburb'], true);
        }
        if ($_POST['customers_city'] != $order->customer['city']) {
          $update['customers_city'] = zen_db_prepare_input($_POST['customers_city'], true);
        }
        if ($_POST['customers_postcode'] != $order->customer['postcode']) {
          $update['customers_postcode'] = zen_db_prepare_input($_POST['customers_postcode'], true);
        }
        if ($_POST['customers_state'] != $order->customer['state']) {
          $update['customers_state'] = zen_db_prepare_input($_POST['customers_state'], true);
        }
        if ($_POST['customers_country'] != $order->customer['country']) {
          $update['customers_country'] = zen_db_prepare_input($_POST['customers_country'], true);
        }

        // delivery address data
        if ($_POST['delivery_name'] != $order->delivery['name']) {
          $update['delivery_name'] = zen_db_prepare_input($_POST['delivery_name'], true);
        }
        if ($_POST['delivery_company'] != $order->delivery['company']) {
          $update['delivery_company'] = zen_db_prepare_input($_POST['delivery_company'], true);
        }
        if ($_POST['delivery_street_address'] != $order->delivery['street_address']) {
          $update['delivery_street_address'] = zen_db_prepare_input($_POST['delivery_street_address'], true);
        }
        if ($_POST['delivery_suburb'] != $order->delivery['suburb']) {
          $update['delivery_suburb'] = zen_db_prepare_input($_POST['delivery_suburb'], true);
        }
        if ($_POST['delivery_city'] != $order->delivery['city']) {
          $update['delivery_city'] = zen_db_prepare_input($_POST['delivery_city'], true);
        }
        if ($_POST['delivery_postcode'] != $order->delivery['postcode']) {
          $update['delivery_postcode'] = zen_db_prepare_input($_POST['delivery_postcode'], true);
        }
        if ($_POST['delivery_state'] != $order->delivery['state']) {
          $update['delivery_state'] = zen_db_prepare_input($_POST['delivery_state'], true);
        }
        if ($_POST['delivery_country'] != $order->delivery['country']) {
          $update['delivery_country'] = zen_db_prepare_input($_POST['delivery_country'], true);
        }

        // billing address data
        if ($_POST['billing_name'] != $order->billing['name']) {
          $update['billing_name'] = zen_db_prepare_input($_POST['billing_name'], true);
        }
        if ($_POST['billing_company'] != $order->billing['company']) {
          $update['billing_company'] = zen_db_prepare_input($_POST['billing_company'], true);
        }
        if ($_POST['billing_street_address'] != $order->billing['street_address']) {
          $update['billing_street_address'] = zen_db_prepare_input($_POST['billing_street_address'], true);
        }
        if ($_POST['billing_suburb'] != $order->billing['suburb']) {
          $update['billing_suburb'] = zen_db_prepare_input($_POST['billing_suburb'], true);
        }
        if ($_POST['billing_city'] != $order->billing['city']) {
          $update['billing_city'] = zen_db_prepare_input($_POST['billing_city'], true);
        }
        if ($_POST['billing_postcode'] != $order->billing['postcode']) {
          $update['billing_postcode'] = zen_db_prepare_input($_POST['billing_postcode'], true);
        }
        if ($_POST['billing_state'] != $order->billing['state']) {
          $update['billing_state'] = zen_db_prepare_input($_POST['billing_state'], true);
        }
        if ($_POST['billing_country'] != $order->billing['country']) {
          $update['billing_country'] = zen_db_prepare_input($_POST['billing_country'], true);
        }

        // personal contact data
        if ($_POST['customers_telephone'] != $order->customer['telephone']) {
          $update['customers_telephone'] = zen_db_prepare_input($_POST['customers_telephone'], true);
        }
        if ($_POST['customers_email_address'] != $order->customer['email_address']) {
          $update['customers_email_address'] = zen_db_prepare_input($_POST['customers_email_address'], true);
        }

        // targetted customer
        if ($_POST['change_customer'] == 'on' && $_POST['customers_id'] != $order->customer['id']) {
          //$update['customers_id'] = $_POST['customers_id'];
        }

        // confirm that there are changes to make to avoid a SQL error
        if (sizeof($update) >= 1) {
          zen_db_perform(TABLE_ORDERS, $update, 'update', "orders_id = '" . $oID . "'");
        }
      break;


      case 'product':
        require(DIR_WS_CLASSES . 'super_order.php');
        require(DIR_WS_CLASSES . 'currencies.php');
        $currencies = new currencies();

        if (isset($_POST['split_products']) && zen_not_null($_POST['split_products'])) {
          // Duplicate order details from "orders" table
          $old_order = $db->Execute("SELECT * FROM " . TABLE_ORDERS. " WHERE orders_id = '" . $oID . "' LIMIT 1");
          $new_order = array('customers_id' => $old_order->fields['customers_id'],
                             'customers_name' => $old_order->fields['customers_name'],
                             'customers_company' => $old_order->fields['customers_company'],
                             'customers_street_address' => $old_order->fields['customers_street_address'],
                             'customers_suburb' => $old_order->fields['customers_suburb'],
                             'customers_city' => $old_order->fields['customers_city'],
                             'customers_postcode' => $old_order->fields['customers_postcode'],
                             'customers_state' => $old_order->fields['customers_state'],
                             'customers_country' => $old_order->fields['customers_country'],
                             'customers_telephone' => $old_order->fields['customers_telephone'],
                             'customers_email_address' => $old_order->fields['customers_email_address'],
                             'customers_address_format_id' => $old_order->fields['customers_address_format_id'],
                             'delivery_name' => $old_order->fields['delivery_name'],
                             'delivery_company' => $old_order->fields['delivery_company'],
                             'delivery_street_address' => $old_order->fields['delivery_street_address'],
                             'delivery_suburb' => $old_order->fields['delivery_suburb'],
                             'delivery_city' => $old_order->fields['delivery_city'],
                             'delivery_postcode' => $old_order->fields['delivery_postcode'],
                             'delivery_state' => $old_order->fields['delivery_state'],
                             'delivery_country' => $old_order->fields['delivery_country'],
                             'delivery_address_format_id' => $old_order->fields['delivery_address_format_id'],
                             'billing_name' => $old_order->fields['billing_name'],
                             'billing_company' => $old_order->fields['billing_company'],
                             'billing_street_address' => $old_order->fields['billing_street_address'],
                             'billing_suburb' => $old_order->fields['billing_suburb'],
                             'billing_city' => $old_order->fields['billing_city'],
                             'billing_postcode' => $old_order->fields['billing_postcode'],
                             'billing_state' => $old_order->fields['billing_state'],
                             'billing_country' => $old_order->fields['billing_country'],
                             'billing_address_format_id' => $old_order->fields['billing_address_format_id'],
                             'payment_method' => $old_order->fields['payment_method'],
                             'payment_module_code' => $old_order->fields['payment_module_code'],
                             'shipping_method' => $old_order->fields['shipping_method'],
                             'shipping_module_code' => $old_order->fields['shipping_module_code'],
                             'coupon_code' => $old_order->fields['coupon_code'],
                             'cc_type' => $old_order->fields['cc_type'],
                             'cc_owner' => $old_order->fields['cc_owner'],
                             'cc_number' => $old_order->fields['cc_number'],
                             'cc_expires' => $old_order->fields['cc_expires'],
                             'cc_cvv' => $old_order->fields['cc_cvv'],
                             'last_modified' => 'now()',
                             'date_purchased' => $old_order->fields['date_purchased'],
                             'orders_status' => $old_order->fields['orders_status'],
                             'currency' => $old_order->fields['currency'],
                             'currency_value' => $old_order->fields['currency_value'],
                             'order_total' => $old_order->fields['order_total'],
                             'order_tax' => $old_order->fields['order_tax'],
                             'split_from_order' => $oID,
               'is_parent' => 0,
               );
          zen_db_perform(TABLE_ORDERS, $new_order);

          // get new order ID to use with other split actions
          $new_order_id = $db->Insert_ID(); // Change for ZenCart 1.5.3 and beyond.
      $messageStack->add_session(SUCCESS_ORDER_SPLIT . ' ' . $new_order_id, 'success');
      $db->Execute("UPDATE " . TABLE_ORDERS . " SET
                          split_from_order = '" . $new_order_id . "', is_parent= '1'
                          WHERE orders_id = '" . $oID . "'");

          // update "orders_status_history" table
          $old_order_status_history = $db->Execute("SELECT * FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_id = '" . $oID . "'");
          while (!$old_order_status_history->EOF) {
            $new_order_status_history = array('orders_id' => $new_order_id,
                                              'orders_status_id' => $old_order_status_history->fields['orders_status_id'],
                                              'date_added' => $old_order_status_history->fields['date_added'],
                                              'customer_notified' => $old_order_status_history->fields['customer_notified'],
                                              'comments' => $old_order_status_history->fields['comments']);
            zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $new_order_status_history);
            $old_order_status_history->MoveNext();
          }

          // update "orders_total" table
          $old_order_total = $db->Execute("SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . $oID . "'");
          while (!$old_order_total->EOF) {
        if($old_order_total->fields['class']=='ot_total' || $old_order_total->fields['class']=='ot_subtotal')
      {
            $new_order_total = array('orders_id' => $new_order_id,
                                     'title' => $old_order_total->fields['title'],
                                     'text' => $old_order_total->fields['text'],
                                     'value' => $old_order_total->fields['value'],
                                     'class' => $old_order_total->fields['class'],
                                     'sort_order' => $old_order_total->fields['sort_order']);
            zen_db_perform(TABLE_ORDERS_TOTAL, $new_order_total);
          }
            $old_order_total->MoveNext();
              }

         // Reassign affected products to new order
          $split_products = zen_db_prepare_input($_POST['split_products']);
          foreach($split_products as $orders_products_id) {
            $db->Execute("UPDATE " . TABLE_ORDERS_PRODUCTS . " SET
                          orders_id = '" . $new_order_id . "'
                          WHERE orders_products_id = '" . $orders_products_id . "'");

            $db->Execute("UPDATE " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " SET
                          orders_id = '" . $new_order_id . "'
                          WHERE orders_products_id = '" . $orders_products_id . "'");

            $db->Execute("UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " SET
                          orders_id = '" . $new_order_id . "'
                          WHERE orders_products_id = '" . $orders_products_id . "'");
          }

          // recalculate totals on both orders
          recalc_total($oID);
          recalc_total($new_order_id);

          // add history comments to both orders reflecting the split
          $notify_split = (isset($_POST['notify_split']) ? 1 : 0);

          // entry for original order
          $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . "
                       (orders_id, orders_status_id, date_added, customer_notified, comments)
                       VALUES ('" . $oID . "',
                       '" . $new_order['orders_status'] . "',
                       now(),
                       '" . $notify_split . "',
                       '" . COMMENTS_SPLIT_OLD . $new_order_id . "')");

          // entry for new order
          $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . "
                       (orders_id, orders_status_id, date_added, customer_notified, comments)
                       VALUES ('" . $new_order_id . "',
                       '" . $new_order['orders_status'] . "',
                       now(),
                       '" . $notify_split . "',
                       '" . COMMENTS_SPLIT_NEW . $oID . "')");

    // duplicate an existing Super Order payment data (if requested)
          //if (isset($_POST['copy_payments'])) {
      $old_new_order_total = $db->Execute("SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . $oID . "'");
          while (!$old_new_order_total->EOF) {
            if($old_new_order_total->fields['title']=='Total:')
      $old_order_total_value = $old_new_order_total->fields['value'];
            $old_new_order_total->MoveNext();
          }
    // die('<br>'.$old_order_total_value);
            $so = new super_order($oID);
      $reach_old_payment =0;
            if ($so->payment) {
              for ($i = 0; $i < sizeof($so->payment); $i++) {
          unset($old_payment, $new_payment, $old_new_payment);
                $old_payment = $so->payment[$i];
                $new_payment = array();
        $old_new_payment['orders_id'] = $oID;
                $old_new_payment['payment_number'] = $old_payment['number'];
                $old_new_payment['payment_name'] = $old_payment['name'];
                $old_new_payment['payment_type'] = $old_payment['type'];
                $old_new_payment['date_posted'] = $old_payment['posted'];
                $old_new_payment['last_modified'] = $old_payment['modified'];
        if($old_payment['amount'] > ($old_order_total_value-$reach_old_payment) )
        {
          $old_new_payment['payment_amount'] = $old_order_total_value-$reach_old_payment;
          $old_payment['amount'] = ($reach_old_payment + $old_payment['amount'])- $old_order_total_value;
          $reach_old_payment =  $old_order_total_value;
        }
        else if($reach_old_payment < $old_order_total_value)
        {
          $old_new_payment['payment_amount'] = $old_payment['amount'];
                  $reach_old_payment =  $reach_old_payment + $old_payment['amount'];
          $old_payment['amount'] = 0;
         }
        zen_db_perform(TABLE_SO_PAYMENTS, $old_new_payment);
        if(($reach_old_payment == $old_order_total_value) && $old_payment['amount']> 0)
        {
                   $new_payment['orders_id'] = $new_order_id;
                  $new_payment['payment_number'] = $old_payment['number'];
                  $new_payment['payment_name'] = $old_payment['name'];
                 $new_payment['payment_amount'] = $old_payment['amount'];
                 $new_payment['payment_type'] = $old_payment['type'];
                  $new_payment['date_posted'] = $old_payment['posted'];
                 $new_payment['last_modified'] = $old_payment['modified'];
        zen_db_perform(TABLE_SO_PAYMENTS, $new_payment);
        }
        $so->delete_payment($old_payment['index']);
              }
            }
            if ($so->purchase_order) {
              for ($i = 0; $i < sizeof($so->purchase_order); $i++) {
                unset($old_po, $new_po);
                $old_po = $so->purchase_order[$i];
                $new_po = array();
                $new_po['orders_id'] = $new_order_id;
                $new_po['po_number'] = $old_po['number'];
                $new_po['date_posted'] = $old_po['posted'];
                $new_po['last_modified'] = $old_po['modified'];
                zen_db_perform(TABLE_SO_PURCHASE_ORDERS, $new_po);
              }
            }
            if ($so->refund) {
              for ($i = 0; $i < sizeof($so->refund); $i++) {
                unset($old_refund, $new_refund);
                $old_refund = $so->refund[$i];
                $new_refund = array();
                $new_refund['orders_id'] = $new_order_id;
                $new_refund['payment_id'] = $old_refund['payment'];
                $new_refund['refund_number'] = $old_refund['number'];
                $new_refund['refund_name'] = $old_refund['name'];
                $new_refund['refund_amount'] = $old_refund['amount'];
                $new_refund['refund_type'] = $old_refund['type'];
                $new_refund['date_posted'] = $old_refund['posted'];
                $new_refund['last_modified'] = $old_refund['modified'];
                zen_db_perform(TABLE_SO_REFUNDS, $new_refund);
              }
            }
         // }  // END if (isset($_POST['copy_payments']))
          // notify customer (if selected)
          if ($notify_split) {
            email_latest_status($oID);
            $oID = $new_order_id;
            email_latest_status($oID);
          }
        }  // END if (isset($_POST['split_products']) && zen_not_null($_POST['split_products']))
      break;


      case 'history':
        $update_status_history = $db->Execute("SELECT * FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                               WHERE orders_id = '" . $oID . "'
                                               ORDER BY orders_status_history_id DESC");
        while (!$update_status_history->EOF) {
if ($update_status_history->fields['customer_notified'] == -1) {

  $this_history_id = $update_status_history->fields['orders_status_history_id'];
    $this_status = zen_db_prepare_input($_POST['status_' . $this_history_id]);
  $this_comments = zen_db_prepare_input(stripslashes($_POST['comments_' . $this_history_id]));
  $this_comments= $_POST['comments' . $this_history_id]=str_replace("\\r\\n","\n",$this_comments);
  $this_comments= $_POST['comments' . $this_history_id]=str_replace("\\","",$this_comments);
  $this_delete = zen_db_prepare_input($_POST['delete_' . $this_history_id]);
  $change_exists = false;

          if ($this_delete == 1) {
            zen_db_delete(TABLE_ORDERS_STATUS_HISTORY, "orders_status_history_id = '" . $this_history_id . "'");
          }

          if ($this_comments != $update_status_history->fields['comments']) {
            $update_history['comments'] = $this_comments;
            $change_exists = true;
          }

          if ($change_exists) {
            zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $update_history, 'update', "orders_status_history_id  = '" . $this_history_id . "'");
          }
}
          $update_status_history->MoveNext();
        }

        // Re-query the orders_status_history table and reset the
        // current status and modify date in the orders table
        $update_status_history = $db->Execute("SELECT * FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                               WHERE orders_id = '" . $oID . "'
                                               ORDER BY orders_status_history_id DESC limit 1");

  $tbl_orders_history['last_modified'] = $update_status_history->fields['date_added'];
  zen_db_perform(TABLE_ORDERS, $tbl_orders_history, 'update', "orders_id = '" . $oID . "'");
      break;


      case 'total':
        require(DIR_WS_CLASSES . 'currencies.php');
        $currencies = new currencies();

        $update_totals = zen_db_prepare_input($_POST['update_totals']);
        $running_total = 0;
        $sort_order = 0;

        foreach($update_totals as $total_index => $total_details) {
          extract($total_details, EXTR_PREFIX_ALL, "ot");

          if (trim($ot_title) && trim($ot_value)) {
            $sort_order++;

            // add values to running_total
            if($ot_class == "ot_subtotal") {
              $running_total += $ot_value;
            }

            elseif($ot_class == "ot_tax") {
              $running_total += $ot_value;
            }

            // modified per http://thecartblog.com/2009/12/21/zen-cart-edit-orders-and-my-discounting-mods
            elseif(is_discount_module($ot_class)) {
              $running_total -= $ot_value;
            }

            elseif($ot_class == "ot_total") {
              $ot_value = $running_total;
              $db->Execute("update " . TABLE_ORDERS . " set
                            order_total = '" . $ot_value . "'
                            where orders_id = '" . $oID . "'");
            }

            else {
              $running_total += $ot_value;
            }

            // format the text version of the amount modified per http://thecartblog.com/2009/12/21/zen-cart-edit-orders-and-my-discounting-mods
            if (is_discount_module($ot_class)) {
              $ot_text = "-" . $currencies->format($ot_value);
            }

            else {
              $ot_text = $currencies->format($ot_value);
            }

            if($ot_total_id > 0) {
              $query = "UPDATE " . TABLE_ORDERS_TOTAL . " SET
                        title = '" . $ot_title . "',
                        text = '" . $ot_text . "',
                        value = '" . $ot_value . "',
                        sort_order = '" . $sort_order . "'
                        WHERE orders_total_id = '" . $ot_total_id . "'";
              $db->Execute($query);
            }
            else {
              $query = "INSERT INTO " . TABLE_ORDERS_TOTAL . " SET
                        orders_id = '" . $oID . "',
                        title = '" . $ot_title . "',
                        text = '" . $ot_text . "',
                        value = '" . $ot_value . "',
                        class = '" . $ot_class . "',
                        sort_order = '" . $sort_order . "'";
              $db->Execute($query);
            }

          }

          // an empty line means the value should be deleted
          elseif($ot_total_id > 0) {
            zen_db_delete(TABLE_ORDERS_TOTAL, "orders_total_id = '" . $ot_total_id . "'");
          }

        }
      break;
    }  // END switch ($target)
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo REDIRECT; ?></title>
<script language="JavaScript" type="text/javascript">
  <!--
  function returnParent() {
    window.opener.location.reload(true);
    window.opener.focus();
    self.close();
  }
  //-->
</script>
</head>
<!-- header_eof //-->
<body onload="returnParent()">
</body>
</html>
<?php
  }  // END if ($_POST['process'] == 1)
  else {
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<script language="JavaScript" type="text/javascript">
  <!--
  function closePopup() {
    window.opener.focus();
    self.close();
  }
  //-->
</script>
</head>
<!-- header_eof //-->
<body onload="self.focus()">
<!-- body //-->
<table border="0" cellpadding="0" cellspacing="0" width="100%">


    <tr>
<!-- body_text //-->
<td align="center">
      <table border="0" cellpadding="2" cellspacing="0" width="100%">
<?php
  echo '    ' . zen_draw_form('edit', FILENAME_SUPER_EDIT, '', 'post', '', true) . NL;
  echo '      ' . zen_draw_hidden_field('target', $target) . NL;
  echo '      ' . zen_draw_hidden_field('process', 1) . NL;
  echo '      ' . zen_draw_hidden_field('oID', $oID) . NL;
?>
<?php
  switch ($target) {
    case 'contact':
      $customers_sql = $db->Execute("select customers_id, customers_email_address, customers_firstname, customers_lastname
                                     from " . TABLE_CUSTOMERS . "
                                     order by customers_lastname, customers_firstname, customers_email_address");
      while(!$customers_sql->EOF) {
        $customers[] = array('id' => $customers_sql->fields['customers_id'],
                             'text' => $customers_sql->fields['customers_lastname'] . ', ' . $customers_sql->fields['customers_firstname'] . ' (' . $customers_sql->fields['customers_email_address'] . ')');

        $customer_array[$customers_sql->fields['customers_id']] = $customers_sql->fields['customers_firstname'] . ' ' . $customers_sql->fields['customers_lastname'];
        $customers_sql->MoveNext();
      }
?>


<?php
    break;
    case 'product':
      require(DIR_WS_CLASSES . 'currencies.php');
      $currencies = new currencies();

      // next available order number
      $nextID = $db->Execute("SELECT (orders_id + 1) AS nextID FROM " . TABLE_ORDERS . " ORDER BY orders_id DESC LIMIT 1");
      $nextID = $nextID->fields['nextID'];
?>
<!-- Begin Products Listing Block -->
      <tr>
      <td class="pageHeading" align="center"><strong><?php echo HEADER_SPLIT_ORDER . $oID; ?></strong></td>
      </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td>
            <table border="0" cellpadding="2" cellspacing="0" width="100%">

                <tr class="dataTableHeadingRow">
<?php if (sizeof($order->products) > 1) { ?>
          <td class="dataTableHeadingContent smalltext" width="4%">&nbsp;</td>
        <?php } ?>
          <td class="dataTableHeadingContent smalltext" width="25%"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                  <td class="dataTableHeadingContent smalltext" width="25%"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
                  <td class="dataTableHeadingContent smalltext" align="right" width="6%"><?php echo TABLE_HEADING_TAX; ?></td>
                  <td class="dataTableHeadingContent smalltext" align="right" width="10%"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
                  <td class="dataTableHeadingContent smalltext" align="right" width="10%"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
                  <td class="dataTableHeadingContent smalltext" align="right" width="10%"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
                  <td class="dataTableHeadingContent smalltext" align="right" width="10%"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
                </tr>
<?php for ($i = 0; $i < sizeof($order->products); $i++) {
      $orders_products_id = $order->products[$i]['orders_products_id'];
      echo '        <tr class="dataTableRow">' . NL;
      if (sizeof($order->products) > 1) {
        echo '          <td class="dataTableContent smalltext" valign="top" align="center">' . zen_draw_checkbox_field('split_products[' . $i . ']', $orders_products_id) . NL;
      }
      echo '          <td class="dataTableContent smalltext" valign="top" align="left">' . $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') echo TEXT_INFO_ATTRIBUTE_FREE;
          echo '</i></small></nobr>';
        }
      }

      echo '          </td>' . NL .
           '          <td class="dataTableContent smalltext" valign="top">' . $order->products[$i]['model'] . '</td>' . NL .
           '          <td class="dataTableContent smalltext" align="right" valign="top">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . NL .
           '          <td class="dataTableContent smalltext" align="right" valign="top">' .
                          $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . NL .
           '          <td class="dataTableContent smalltext" align="right" valign="top">' .
                          $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . NL .
           '          <td class="dataTableContent smalltext" align="right" valign="top">' .
                          $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . NL .
           '          <td class="dataTableContent smalltext" align="right" valign="top">' .
                          $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . NL;
      echo '        </tr>' . NL;
    }
?>
                <tr>
                  <td class="smalltext" valign="top" align="center"><?php echo zen_draw_checkbox_field('notify_split', 1); ?></td>
                  <td class="smalltext" valign="top" colspan="6"><?php echo ENTRY_NOTIFY_CUSTOMER . '<br>';?>
                           (<?php echo TEXT_SPLIT_EXPLAIN . '<strong>' . $nextID . '</strong>';?>)</td>
                  <td class="smalltext" valign="top">&nbsp;&nbsp;</td>
               </tr>
                <tr>
                  <td class="smalltext" valign="top" align="center"><?php echo zen_draw_checkbox_field('notify_comments', on); ?></td>
                  <td class="smalltext" valign="top" colspan="6"><?php echo ENTRY_NOTIFY_COMMENTS . '<br>';?></td>
                  <td class="smalltext" valign="top">&nbsp;&nbsp;</td>
               </tr>

            </table>
            </td>
          </tr>
<!-- End Products Listings Block -->

<?php
    break;
    case 'history':
      $orders_statuses = array();
      $status_query = $db->Execute("select orders_status_id, orders_status_name
                                    from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$_SESSION['languages_id'] . "'");
      while (!$status_query->EOF) {
        $orders_statuses[] = array('id' => $status_query->fields['orders_status_id'],
                                   'text' => $status_query->fields['orders_status_name']);
        $status_query->MoveNext();
      }
?>
<!-- Begin Order Status History -->
          <tr>
            <td class="pageHeading" align="center"><?php echo HEADER_EDIT_ORDER . $oID; ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
      <tr>
            <td align="center">
            <table border="1" cellpadding="5" cellspacing="0" width="100%">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent smallText" width="19%"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
                  <td class="dataTableHeadingContent smallText" width="66%"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
                  <td class="dataTableHeadingContent smallText" align="center" width="5%"><strong><?php echo TABLE_HEADING_DELETE_COMMENTS; ?></strong></td>
                </tr>
<?php
    $orders_history = $db->Execute("select * from " . TABLE_ORDERS_STATUS_HISTORY . "
                                    where orders_id = '" . $oID . "'
                                    order by orders_status_history_id asc");
    if ($orders_history->RecordCount() > 0) {
      while (!$orders_history->EOF){
          if ($orders_history->fields['customer_notified'] == -1) {

        echo '        <tr>' . NL .
             '          <td class="smallText" valign="top">' . zen_datetime_short($orders_history->fields['date_added']) . '</td>' . NL;

        $status_id = 'status_' . $orders_history->fields['orders_status_history_id'];
        $status_default = $orders_history->fields['orders_status_id'];
        $orders_status = $orders_history->fields['orders_status_name'];
        $comments_id  = 'comments_' . $orders_history->fields['orders_status_history_id'];
        $comments_default = zen_db_output($orders_history->fields['comments']);
        $delete_id = 'delete_' . $orders_history->fields['orders_status_history_id'];
        echo '          <td>' . zen_draw_textarea_field($comments_id, 'soft', '60', '8', $comments_default) . '</td>' . NL;
        echo '          <td align="center" valign="top">' . zen_draw_checkbox_field($delete_id, 1) . '</td>' . NL;
        echo '        </tr>' . NL;
          }
        $orders_history->MoveNext();

      }
    } else {
        echo '          <tr>' . NL .
             '            <td class="smallText" colspan="4">' . TEXT_NO_ORDER_HISTORY . '</td>' . NL .
             '          </tr>' . NL;
    }
?>

            </table>
            </td>
          </tr>
<!-- End Order Status History -->


<?php break;

  }  // END switch ($target)
?>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '15'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="3" align="right">
      <input class="normal_button button" value="<?php echo BUTTON_CANCEL; ?>" onclick="closePopup()" type="button">
      <input class="submit_button button" value="<?php echo BUTTON_SUBMIT; ?>" onclick="document.edit.submit();this.disabled=true" type="submit">
      </td>
          </tr>

      </form>
      </table></td>
<!-- body_text_eof //-->
   </tr>

</table>

<!-- body_eof //-->
</body>
</html>
<?php
  }  // END else
?>
