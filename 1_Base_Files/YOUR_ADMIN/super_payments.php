<?php
/**
 *
 *  SUPER ORDERS v3.0
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
 *  DESCRIPTION:   This file generates a pop-up window that is used to
 *  enter and edit payment information for a given order.
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
require('includes/application_top.php');
require_once(DIR_WS_CLASSES . 'super_order.php');
global $db;

$oID = $_GET['oID'];
$payment_mode = $_GET['payment_mode'];
$action = (isset($_GET['action']) ? $_GET['action'] : '');

$so = new super_order($oID);

// the following "if" clause actually inputs data into the DB
if ($_GET['process'] == '1') {
  switch ($action) {

    // add a new payment entry
    case 'add':
      $update_status = (isset($_GET['update_status']) ? $_GET['update_status'] : false);
      $notify_customer = (isset($_GET['notify_customer']) ? $_GET['notify_customer'] : false);

      //update_status($oID, $new_status, $notified = 0, $comments = '')

      switch ($payment_mode) {
        case 'payment':
          // input new data
          $new_index = $so->add_payment($_GET['payment_number'], $_GET['payment_name'], $_GET['payment_amount'], $_GET['payment_type'], $_GET['purchase_order_id']);

          // update order status
          if ($update_status) {
            if ($_GET['purchase_order_id']) {
              update_status($oID, AUTO_STATUS_PO_PAYMENT, $notify_customer, sprintf(AUTO_COMMENTS_PO_PAYMENT, $_GET['payment_number']));
            } else {
              update_status($oID, AUTO_STATUS_PAYMENT, $notify_customer, sprintf(AUTO_COMMENTS_PAYMENT, $_GET['payment_number']));
            }
          }

          // notify the customer
          if ($notify_customer) {
            $_POST['notify_comments'] = 'on';
            email_latest_status($oID);
          }

          // redirect to confirmation screen
          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $new_index . '&action=confirm', 'NONSSL'));
          break;

        case 'purchase_order':
          $new_index = $so->add_purchase_order($_GET['po_number']);

          if ($update_status) {
            update_status($oID, AUTO_STATUS_PO, $notify_customer, sprintf(AUTO_COMMENTS_PO, $_GET['po_number']));
          }

          // notify the customer
          if ($notify_customer) {
            $_POST['notify_comments'] = 'on';
            email_latest_status($oID);
          }

          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $new_index . '&action=confirm', 'NONSSL'));
          break;

        case 'refund':
          $new_index = $so->add_refund($_GET['payment_id'], $_GET['refund_number'], $_GET['refund_name'], $_GET['refund_amount'], $_GET['refund_type']);

          if ($update_status) {
            update_status($oID, AUTO_STATUS_REFUND, $notify_customer, sprintf(AUTO_COMMENTS_REFUND, $_GET['refund_number']));
          }

          // notify the customer
          if ($notify_customer) {
            $_POST['notify_comments'] = 'on';
            email_latest_status($oID);
          }

          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $new_index . '&action=confirm', 'NONSSL'));
          break;
      }  // END switch ($payment_mode)

      break;  // END case 'add'
    // update an existing payment entry
    case 'my_update';
      switch ($payment_mode) {
        case 'payment':
          $so->update_payment($_GET['payment_id'], $_GET['purchase_order_id'], $_GET['payment_number'], $_GET['payment_name'], $_GET['payment_amount'], $_GET['payment_type']);

          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $_GET['payment_id'] . '&action=confirm', 'NONSSL'));
          break;

        case 'purchase_order':
          $so->update_purchase_order($_GET['purchase_order_id'], $_GET['po_number']);

          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $_GET['purchase_order_id'] . '&action=confirm', 'NONSSL'));
          break;

        case 'refund':
          $so->update_refund($_GET['refund_id'], $_GET['payment_id'], $_GET['refund_number'], $_GET['refund_name'], $_GET['refund_amount'], $_GET['refund_type'], false, false);

          zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $_GET['refund_id'] . '&action=confirm', 'NONSSL'));
          break;
      }  // END switch ($payment_mode)
      break;  // END case 'update'
    // removes requested payment data from the database (not recoverable!)
    case 'delete':
      $affected_rows = 0;
      switch ($payment_mode) {
        case 'payment':
          $so->delete_payment($_GET['payment_id']);
          $affected_rows++;

          // handle the refunds, if any
          if ($_GET['refund_action']) {
            for ($a = 0; $a < sizeof($so->refund); $a++) {
              if ($so->refund[$a]['payment'] == $_GET['payment_id']) {
                switch ($_GET['refund_action']) {
                  case 'keep':
                    $so->update_refund($so->refund[$a]['index'], 0);
                    $affected_rows++;
                    break;

                  case 'move':
                    $so->update_refund($so->refund[$a]['index'], $_GET['new_payment_id']);
                    $affected_rows++;
                    break;

                  case 'drop':
                    $so->delete_refund($so->refund[$a]['index']);
                    $affected_rows++;
                    break;
                }
              }
            }
          }  // END if ($_GET['refund_action'])

          break;  // END case 'payment'


        case 'purchase_order':
          $so->delete_purchase_order($_GET['purchase_order_id']);
          $affected_rows++;

          // handle the payments, if any
          if ($_GET['payment_action']) {
            for ($a = 0; $a < sizeof($so->po_payment); $a++) {
              if ($so->po_payment[$a]['assigned_po'] == $_GET['purchase_order_id']) {
                switch ($_GET['payment_action']) {
                  case 'keep':
                    $so->update_payment($so->po_payment[$a]['index'], 0);
                    $affected_rows++;
                    break;

                  case 'move':
                    $so->update_payment($so->po_payment[$a]['index'], $_GET['new_po_id']);
                    $affected_rows++;
                    break;

                  case 'drop':
                    $so->delete_payment($so->po_payment[$a]['index']);
                    $affected_rows++;
                    break;
                }
              }
            }
          }  // END if ($_GET['payment_action'])
          break;  // END case 'purchase_order'


        case 'refund':
          $so->delete_refund($_GET['refund_id']);
          $affected_rows++;
          break;  // END case 'refund'
      }  // END switch ($payment_mode)

      zen_redirect(zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&affected_rows=' . $affected_rows . '&action=delete_confirm', 'NONSSL'));
      break;
  }
} else {
  //_TODO code to customize the TITLE goes here
  ?>
  <!doctype html>
  <html <?php echo HTML_PARAMS; ?>>
    <head>
      <meta charset="<?php echo CHARSET; ?>">
      <title><?php echo TITLE; ?></title>
      <link rel="stylesheet" href="includes/super_stylesheet.css">
      <link rel="stylesheet" href="includes/stylesheet.css">
      <script src="includes/general.js"></script>
      <script>
        function returnParent() {
            window.opener.location.reload(true);
            window.opener.focus();
            self.close();
        }
      </script>
    </head>
    <body onload="self.focus()">
      <div class="container">
          <?php
          switch ($action) {
            case 'add':
              echo zen_draw_form('add', FILENAME_SUPER_PAYMENTS, '', 'get', 'class="form-horizontal"', true) . PHP_EOL;
              echo zen_draw_hidden_field('action', $action) . PHP_EOL;
              echo zen_draw_hidden_field('process', 1) . PHP_EOL;
              echo zen_draw_hidden_field('payment_mode', $payment_mode) . PHP_EOL;
              echo zen_draw_hidden_field('oID', $so->oID) . PHP_EOL;

              switch ($payment_mode) {
                case 'payment':
                  $po_array = $so->build_po_array(TEXT_NONE);
                  ?>
                <h1><?php echo HEADER_ENTER_PAYMENT; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_NUMBER, 'payment_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_number', '', 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_NAME, 'payment_name', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_name', '', 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_AMOUNT, 'payment_amount', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_amount', '', 'class="form-control" size="8"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_TYPE, 'payment_type', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('payment_type', $so->payment_key, '', 'class="form-control"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_ATTACHED_PO, 'purchase_order_id', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('purchase_order_id', $po_array, '', 'class="form-control"'); ?></div>
                </div>
                <?php
                break;

              case 'purchase_order':
                ?>
                <h1><?php echo HEADER_ENTER_PO; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PO_NUMBER, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('po_number', '', 'class="form-control" size="25"'); ?></div>
                </div>
                <?php
                break;

              case 'refund':
                $payment_array = $so->build_payment_array(TEXT_NONE);
                ?>
                <h1><?php echo HEADER_ENTER_REFUND; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_ATTACHED_PAYMENT, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('payment_id', $payment_array, '', 'class="form-control"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_NUMBER, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('refund_number', '', 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_NAME, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('refund_name', '', 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_AMOUNT, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_input_field('refund_amount', '', 'class="form-control" size="8"'); ?>
                    <span class="help-block"><?php echo TEXT_NO_MINUS; ?></span>
                  </div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_TYPE, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('refund_type', $so->payment_key, '', 'class="form-control"'); ?></div>
                </div>
                <?php
                break;
            }
            ?>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9 col-md-6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('update_status', 1, false) . CHECKBOX_UPDATE_STATUS; ?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9 col-md-6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('notify_customer', 1, false) . CHECKBOX_NOTIFY_CUSTOMER; ?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12 text-center">
                <button type="submit" onclick="document.add.submit(); this.disabled = true;" class="btn btn-primary btn-sm"><?php echo BUTTON_SUBMIT; ?></button>&nbsp;<button type="button" onclick="returnParent()" class="btn btn-default btn-sm"><?php echo BUTTON_CANCEL; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
            <?php
            break;
          case 'my_update':
            $index = $_GET['index'];
            echo zen_draw_form('update', FILENAME_SUPER_PAYMENTS, '', 'get', 'class="form-horizontal"', true) . PHP_EOL;
            echo zen_draw_hidden_field('action', $action) . PHP_EOL;
            echo zen_draw_hidden_field('process', 1) . PHP_EOL;
            echo zen_draw_hidden_field('payment_mode', $payment_mode) . PHP_EOL;
            echo zen_draw_hidden_field('oID', $so->oID) . PHP_EOL;

            switch ($payment_mode) {
              case 'payment':
                echo zen_draw_hidden_field('payment_id', $index) . PHP_EOL;
                $payment = $db->Execute("SELECT *
                                         FROM " . TABLE_SO_PAYMENTS . "
                                         WHERE payment_id = " . (int)$index);
                $po_array = $so->build_po_array(TEXT_NONE);
                ?>
                <h1><?php echo HEADER_UPDATE_PAYMENT . ' ' . $payment->fields['payment_number']; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_PAYMENT_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_NUMBER, 'payment_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_number', $payment->fields['payment_number'], 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_NAME, 'payment_name', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_name', $payment->fields['payment_name'], 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_AMOUNT, 'payment_amount', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('payment_amount', $payment->fields['payment_amount'], 'class="form-control" size="8"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PAYMENT_TYPE, 'payment_type', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('payment_type', $so->payment_key, $payment->fields['payment_type'], 'class="form-control"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_ATTACHED_PO, 'purchase_order_id', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('purchase_order_id', $po_array, $payment->fields['purchase_order_id'], 'class="form-control"'); ?></div>
                </div>
                <?php
                break;

              case 'purchase_order':
                echo zen_draw_hidden_field('purchase_order_id', $index) . PHP_EOL;
                for ($a = 0; $a < sizeof($so->purchase_order); $a++) {
                  if ($so->purchase_order[$a]['index'] == $index) {
                    $x = $a;
                    break 1;
                  }
                }
                ?>
                <h1><?php echo HEADER_UPDATE_PO . ' ' . $so->purchase_order[$x]['number']; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br />' . HEADER_PO_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_PO_NUMBER, 'po_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('po_number', $so->purchase_order[$x]['number'], 'class="form-control" size="25"'); ?></div>
                </div>
                <?php
                break;
              case 'refund':
                echo zen_draw_hidden_field('refund_id', $index) . PHP_EOL;
                for ($a = 0; $a < sizeof($so->refund); $a++) {
                  if ($so->refund[$a]['index'] == $index) {
                    $x = $a;
                    break 1;
                  }
                }
                $payment_array = $so->build_payment_array(TEXT_NONE);
                ?>
                <h1><?php echo HEADER_UPDATE_REFUND . ' ' . $so->refund[$x]['number']; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_REFUND_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_ATTACHED_PAYMENT, 'payment_id', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('payment_id', $payment_array, $so->refund[$x]['payment'], 'class="form-control"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_NUMBER, 'refund_number', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('refund_number', $so->refund[$x]['number'], 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_NAME, 'refund_name', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('refund_name', $so->refund[$x]['name'], 'class="form-control" size="25"'); ?></div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_AMOUNT, 'refund_amount', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6">
                      <?php echo zen_draw_input_field('refund_amount', $so->refund[$x]['amount'], 'class="form-control" size="8"'); ?>
                    <span class="help-block"><?php echo TEXT_NO_MINUS; ?></span>
                  </div>
                </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_REFUND_TYPE, 'refund_type', 'class="control-label col-sm-3"'); ?>
                  <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('refund_type', $so->payment_key, $so->refund[$x]['type'], 'class="form-control"'); ?></div>
                </div>
                <?php
                break;
            }  // END switch ($payment_mode)
            ?>
            <div class="form-group">
              <div class="col-sm-12 text-center">
                <button type="submit" onclick="document.update.submit();this.disabled = true;" class="btn btn-primary btn-sm"><?php echo BUTTON_SUBMIT; ?></button>&nbsp;
                <button type="button" onclick="returnParent()" class="btn btn-default btn-sm"><?php echo BUTTON_CANCEL; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
            <?php
            break;  // END case 'update'
          case 'delete':
            $index = $_GET['index'];
            echo zen_draw_form('delete', FILENAME_SUPER_PAYMENTS, '', 'get', '', true) . PHP_EOL;
            echo zen_draw_hidden_field('action', $action) . PHP_EOL;
            echo zen_draw_hidden_field('process', 1) . PHP_EOL;
            echo zen_draw_hidden_field('payment_mode', $payment_mode) . PHP_EOL;
            echo zen_draw_hidden_field('oID', $so->oID) . PHP_EOL;
            switch ($payment_mode) {
              case 'payment':
                echo zen_draw_hidden_field('payment_id', $index) . PHP_EOL;
                // check for attached refunds
                $refund_exists = false;
                $refund_count = 0;
                for ($a = 0; $a < sizeof($so->refund); $a++) {
                  if ($so->refund[$a]['payment'] == $index) {
                    $refund_exists = true;
                    $refund_count++;
                  }
                }
                ?>
                <h1><?php echo HEADER_DELETE_PAYMENT; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_PAYMENT_UID . $index; ?></strong></div>
                </div>
                <?php
                if ($refund_exists) {
                  $payment_array = $so->build_payment_array();
                  // zen_draw_radio_field($name, $value = '', $checked = false, $compare = '')
                  ?>
                  <div class="form-group">
                    <div class="col-sm-12"><?php echo sprintf(TEXT_REFUND_ACTION, $refund_count); ?></div>
                  </div>
                  <div class="form-group">
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('refund_action', 'keep', false) . REFUND_ACTION_KEEP; ?></label>
                    </div>
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('refund_action', 'move', false) . REFUND_ACTION_MOVE . zen_draw_pull_down_menu('new_payment_id', $payment_array, '', 'class="form-control"'); ?></label>
                    </div>
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('refund_action', 'drop', false) . REFUND_ACTION_DROP; ?></label>
                    </div>
                  </div>
                  <?php
                }
                ?>
                <div class="form-group">
                  <div class="alert alert-danger"><?php echo WARN_DELETE_PAYMENT; ?></div>
                </div>
                <?php
                break;
              case 'purchase_order':
                echo zen_draw_hidden_field('purchase_order_id', $index) . PHP_EOL;
                // check for attached payments, if any
                $payment_exists = false;
                $payment_count = 0;
                for ($a = 0; $a < sizeof($so->po_payment); $a++) {
                  if ($so->po_payment[$a]['assigned_po'] == $index) {
                    $payment_exists = true;
                    $payment_count++;
                  }
                }
                ?>
                <h1><?php echo HEADER_DELETE_PO; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_PO_UID . $index; ?></strong></div>
                </div>
                <?php
                if ($payment_exists) {
                  $po_array = $so->build_po_array();
                  ?>
                  <div class="form-group">
                    <div class="col-sm-12"><?php echo sprintf(TEXT_PAYMENT_ACTION, $payment_count); ?></div>
                  </div>
                  <div class="form-group">
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('payment_action', 'keep', false) . PAYMENT_ACTION_KEEP; ?></label>
                    </div>
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('payment_action', 'move', false) . PAYMENT_ACTION_MOVE . zen_draw_pull_down_menu('new_po_id', $po_array, '', 'class="form-control"'); ?></label>
                    </div>
                    <div class="radio">
                      <label><?php echo zen_draw_radio_field('payment_action', 'drop', false) . PAYMENT_ACTION_DROP; ?></label>
                    </div>
                  </div>
                  <?php
                }
                ?>
                <div class="form-group">
                  <div class="alert alert-danger"><?php echo WARN_DELETE_PO; ?></div>
                </div>
                <?php
                break;
              case 'refund':
                echo zen_draw_hidden_field('refund_id', $index) . PHP_EOL;
                ?>
                <h1><?php echo HEADER_DELETE_REFUND; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br />' . HEADER_REFUND_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="alert alert-danger"><?php echo WARN_DELETE_REFUND; ?></div>
                </div>
                <?php
                break;
            }  // END switch ($payment_mode)
            ?>
            <div class="form-group">
              <div class="col-sm-12 text-center">
                <button type="submit" onclick="document.delete.submit();this.disabled = true;" class="btn btn-primary btn-sm"><?php echo BUTTON_SUBMIT; ?></button>&nbsp;<button type="button" onclick="returnParent();" class="btn btn-default btn-sm"><?php echo BUTTON_CANCEL; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
            <?php
            break;  // END case 'delete':
          case 'delete_confirm':
            $affected_rows = $_GET['affected_rows'];
            ?>
            <h1><?php echo HEADER_DELETE_CONFIRM; ?></h1>
            <div class="form-group">
              <div class="col-sm-12"><?php echo sprintf(TEXT_DELETE_CONFIRM, $affected_rows); ?></div>
            </div>
            <div class="form-group">
              <div class="col-sm-12 text-center">
                <button type="button" onclick="returnParent();"><?php echo BUTTON_DELETE_CONFIRM; ?></button>
              </div>
            </div>
            <?php
            break;  // END case 'delete_confirm'
          case 'confirm':
            $index = $_GET['index'];
            switch ($payment_mode) {
              case 'payment':
                $payment_info = $db->Execute("SELECT p.*,
                                                     po.po_number
                                              FROM " . TABLE_SO_PAYMENTS . " p
                                              LEFT JOIN " . TABLE_SO_PURCHASE_ORDERS . " po ON po.purchase_order_id = p.purchase_order_id
                                              WHERE p.payment_id = " . (int)$index . "
                                              LIMIT 1");
                ?>
                <h1><?php echo HEADER_CONFIRM_PAYMENT; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_PAYMENT_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_PAYMENT_NUMBER; ?>&nbsp;<strong><?php echo $payment_info->fields['payment_number']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_PAYMENT_NAME; ?>&nbsp;<strong><?php echo $payment_info->fields['payment_name']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_PAYMENT_AMOUNT; ?>&nbsp;<strong><?php echo $payment_info->fields['payment_amount']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_PAYMENT_TYPE; ?>&nbsp;<strong><?php echo $so->full_type($payment_info->fields['payment_type']); ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_ATTACHED_PO; ?>&nbsp;<strong><?php echo ($payment_info->fields['purchase_order_id'] == 0 ? TEXT_NONE : $payment_info->fields['po_number']); ?></strong></div>
                </div>
                <?php
                break;
              case 'purchase_order':
                $po = $db->Execute("SELECT po_number
                                    FROM " . TABLE_SO_PURCHASE_ORDERS . "
                                    WHERE purchase_order_id = " . (int)$index . "
                                    LIMIT 1");
                ?>
                <h1><?php echo HEADER_CONFIRM_PO; ?></h1>

                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_PO_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_PO_NUMBER; ?>&nbsp;<strong><?php echo $po->fields['po_number']; ?></strong></div>
                </div>
                <?php
                break;
              case 'refund':
                $refund = $db->Execute("SELECT r.*,
                                               p.payment_number
                                        FROM " . TABLE_SO_REFUNDS . " r
                                        LEFT JOIN " . TABLE_SO_PAYMENTS . " p ON p.payment_id = r.payment_id
                                        WHERE refund_id = " . (int)$index . "
                                        LIMIT 1");
                ?>
                <h1><?php echo HEADER_CONFIRM_REFUND; ?></h1>
                <div class="form-group">
                  <div class="col-sm-12"><strong><?php echo HEADER_ORDER_ID . $so->oID . '<br>' . HEADER_REFUND_UID . $index; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_ATTACHED_PAYMENT; ?>&nbsp;<strong><?php echo $refund->fields['payment_number']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_REFUND_NUMBER; ?>&nbsp;<strong><?php echo $refund->fields['refund_number']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_REFUND_NAME; ?>&nbsp;<strong><?php echo $refund->fields['refund_name']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_REFUND_AMOUNT; ?>&nbsp;<strong><?php echo $refund->fields['refund_amount']; ?></strong></div>
                </div>
                <div class="form-group">
                  <div class="col-sm-12"><?php echo TEXT_REFUND_TYPE; ?>&nbsp;<strong><?php echo $so->full_type($refund->fields['refund_type']); ?></strong></div>
                </div>
                <?php
                break;
            }  // END switch ($payment_mode)
            ?>
            <div class="form-group">
              <div class="col-sm-12">
                <button type="button" onclick="this.disabled = true; returnParent();" class="btn btn-primary btn-sm"><?php echo BUTTON_SAVE_CLOSE; ?></button>&nbsp;<button type="button" onclick="this.disabled=true; window.location.href='<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&index=' . $index . '&action=my_update'); ?>'" class="btn btn-primary btn-sm"><?php echo BUTTON_MODIFY; ?></button>&nbsp;<button type="button" onclick="this.disabled=true; window.location.href='<?php echo zen_href_link(FILENAME_SUPER_PAYMENTS, 'oID=' . $so->oID . '&payment_mode=' . $payment_mode . '&action=add'); ?>'" class="btn btn-primary btn-sm"><?php echo BUTTON_ADD_NEW; ?></button>
              </div>
            </div>
            <?php
            break;
        }
      }
      ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>