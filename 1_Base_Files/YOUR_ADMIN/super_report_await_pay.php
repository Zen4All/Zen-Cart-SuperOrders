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
 *  DESCRIPTION:   This report displays orders that have outstanding
 *  payments and refunds, or missing purchase order data.     Orders
 *  missing a purchase order are not included in the missing payment
 *  report. Report results come solely from the Super Orders payment
 *  system.
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
require 'includes/application_top.php';
require DIR_WS_CLASSES . 'super_order.php';
require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$report_type = (isset($_GET['report_type']) ? $_GET['report_type'] : false);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/css/super_stylesheet.css">
    <link rel="stylesheet" href="includes/css/srap_print.css" media="print">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <?php if (!$print) { ?>
      <script>
        function init() {
            cssjsmenu('navbar');
            if (document.getElementById) {
                var kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
        }
      </script>
    <?php } ?>
  </head>
  <body onLoad="init()">
      <?php (zen_db_prepare_input($_GET['print'] == 'yes')) ? $print = true : $print = false; ?>
    <!-- header //-->
    <?php
    if (!$print) {
      require DIR_WS_INCLUDES . 'header.php';
    }
    ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <?php
      if ($print) {
        if ($report_type == 'out_po') {
          $this_report = OUT_PO;
        } elseif ($report_type == 'out_payment') {
          $this_report = OUT_PAYMENTS;
        } elseif ($report_type == 'out_refund') {
          $this_report = OUT_REFUNDS;
        }
        ?>
        <!-- print_header //-->
        <h1><a href="<?php echo zen_href_link(FILENAME_SUPER_REPORT_AWAIT_PAY, 'report_type=' . $report_type); ?>"><?php echo HEADING_TITLE; ?></a></h1>
        <div class="pageHeading text-right"><?php echo date('l M d, Y', time()); ?></div>
        <div class="row">
          <div class="col-sm-12 pageHeading"><?php echo $this_report; ?><br></div>
        </div>
        <!-- print_header_eof //-->
      <?php } else { ?>
        <h1><?php echo HEADING_TITLE; ?></h1>
        <?php echo zen_draw_form('select_search', FILENAME_SUPER_REPORT_AWAIT_PAY, '', 'get', 'class="form-horizontal"'); ?>
        <div class="col-sm-12">
          <div class="form-group">
              <?php echo zen_draw_label(HEADING_REPORT_TYPE, 'rport_type', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <div class="radio">
                <label><?php echo zen_draw_radio_field('report_type', 'out_po') . OUT_PO; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('report_type', 'out_payment') . OUT_PAYMENTS . '<br />'; ?></label>
              </div>
              <div class="radio">
                <label><?php echo zen_draw_radio_field('report_type', 'out_refund') . OUT_REFUNDS . '<br />'; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 col-md-6">
              <div class="checkbox">
                <label><?php echo zen_draw_checkbox_field('within_limit', 1) . HEADING_WITHIN_LIMIT; ?></label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 col-md-6">
              <button class="btn btn-info" type="submit"><?php echo BUTTON_SEARCH; ?></button>
              <?php if ($report_type) { ?>
                &nbsp;<a href="<?php echo zen_href_link(FILENAME_SUPER_REPORT_AWAIT_PAY, $_SERVER['QUERY_STRING'] . "&print=yes"); ?>" role="button" class="btn btn-info" target="_blank"><?php echo BUTTON_PRINT; ?></a>
              <?php } ?>
            </div>
          </div>
        </div>
        <?php echo '</form>'; ?>
      <?php } ?>
      <?php if ($report_type) { ?>
        <table class="table table-striped">
          <thead>
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER_NUMBER; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATE; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TYPE; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></th>
              <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_BILLING_NAME; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_PHONE; ?></th>
              <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></th>
              <?php if ($report_type == 'out_payment' || $report_type == 'out_refund') { ?>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_AMOUNT_APPLIED; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_SO_BALANCE; ?></th>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
              <?php
              if ($_GET['within_limit'] == 1) {
                $date_limit = "";
              } else {
                $date_limit = " AND o.date_purchased <= DATE_ADD(CURDATE(), INTERVAL -1 MONTH) ";
              }

              $num_orders = 0;
              $total_applied = 0;
              $total_balance = 0;
              $order_grand_total = 0;

              if ($report_type == 'out_po') {
                $outstanding_query = $db->Execute("SELECT o.*, os.orders_status_name
                                                   FROM " . TABLE_ORDERS . " o
                                                   LEFT JOIN " . TABLE_ORDERS_STATUS . " os ON os.orders_status = o.orders_status_id
                                                     AND os.language_id = " . (int)$_SESSION['languages_id'] . "
                                                   LEFT JOIN " . TABLE_SO_PURCHASE_ORDERS . " po ON po.orders_id = o.orders_id
                                                   WHERE o.payment_module_code = 'purchaseorder'
                                                   AND o.date_cancelled IS NULL
                                                   AND o.balance_due > 0
                                                   AND po.orders_id IS NULL
                                                   " . $date_limit . "
                                                   ORDER BY o.orders_id ASC");
                foreach ($outstanding_query as $outstanding) {
                  $order_grand_total += $outstanding['order_total'];
                  $num_orders++;
                  ?>
                <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $outstanding['orders_id'] . '&action=edit'); ?>'" style="cursor: pointer">
                  <td class="dataTableContent"><?php echo $outstanding['orders_id']; ?></td>
                  <td class="dataTableContent"><?php echo $outstanding['customers_state']; ?></td>
                  <td class="dataTableContent"><?php echo $outstanding['payment_method']; ?></td>
                  <td class="dataTableContent"><?php echo $outstanding['orders_status_name']; ?></td>
                  <td class="dataTableContent text-center"><?php echo $outstanding['date_purchased']; ?></td>
                  <td class="dataTableContent"><?php echo $outstanding['billing_name']; ?></td>
                  <td class="dataTableContent"><?php echo $outstanding['customers_telephone']; ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($outstanding->fields['order_total']); ?></td>
                </tr>
              <?php } ?>
              <tr class="dataTableRowUnique">
                <td class="dataTableHeadingContent"><?php echo $num_orders . TEXT_ORDERS; ?></td>
                <td colspan="6">&nbsp;</td>
                <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($order_grand_total); ?></td>
              </tr>
              <?php
            } elseif ($report_type == 'out_payment') {
              $sub_order_total = 0;
              $sub_applied = 0;
              $sub_balance = 0;
              $sub_num_orders = 0;

              // ctp: dummy this up to create so classes for all orders in range. This will ensure all paypal
              // transactions are captured in the so_payments and so_refunds for the order range selected.
              $orders_query = "SELECT orders_id
                               FROM " . TABLE_ORDERS . " o
                               WHERE date_purchased BETWEEN '" . $sd . "'
                               AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)";
              $orders_list = $db->Execute($orders_query);
              foreach ($orders_list as $order_list) {
                $so = new super_order($order_list['orders_id']);  // instantiated once simply for the full_type() function
                $so = NULL;
              }

              // first display any outstanding payments on purchase orders
              // this is money owed to us for stuff already shipped
              $out_po_check_query = $db->Execute("SELECT o.*,
                                                   os.orders_status_name
                                            FROM " . TABLE_ORDERS . " o
                                            LEFT JOIN " . TABLE_ORDERS_STATUS . " os ON os.orders_status_id = o.orders_status
                                              AND os.language_id = " . $_SESSION['languages_id'] . "
                                            WHERE o.payment_module_code = 'purchaseorder'
                                            AND o.date_completed IS NULL
                                            AND o.date_cancelled IS NULL
                                            AND o.balance_due > 0
                                            " . $date_limit . "
                                            ORDER BY o.orders_id ASC");
              if ($out_po_check_query->RecordCount() > 0) {
                ?>
                <tr>
                  <td colspan="10" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . TABLE_SUBHEADING_PO_CHECKS . zen_draw_separator(); ?></strong></td>
                </tr>
                <?php
                foreach ($out_po_check_query as $$out_po_check) {
                  unset($so);
                  $so = new super_order($out_po_check['orders_id']);

                  if ($so->purchase_order) {
                    $sub_order_total += $so->order_total;
                    $sub_applied += $so->amount_applied;
                    $sub_balance += $so->balance_due;
                    $sub_num_orders++;
                    ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $out_po_check->fields['orders_id'] . '&action=edit'); ?>'" style="cursor: pointer;">
                      <td class="dataTableContent"><?php echo $out_po_check['orders_id']; ?></td>
                      <td class="dataTableContent"><?php echo $out_po_check['customers_state']; ?></td>
                      <td class="dataTableContent"><?php echo $out_po_check['payment_method']; ?></td>
                      <td class="dataTableContent"><?php echo $out_po_check['orders_status_name']; ?></td>
                      <td class="dataTableContent text-center"><?php echo $out_po_check['date_purchased']; ?></td>
                      <td class="dataTableContent"><?php echo $out_po_check['billing_name']; ?></td>
                      <td class="dataTableContent"><?php echo $out_po_check['customers_telephone']; ?></td>
                      <td class="dataTableContent text-right"><?php echo $currencies->format($so->order_total); ?></td>
                      <td class="dataTableContent text-right"><?php echo $currencies->format($so->amount_applied); ?></td>
                      <td class="dataTableContent text-right"><?php echo $currencies->format($so->balance_due); ?></td>
                    </tr>
                    <?php
                  }
                }
                ?>
                <tr class="dataTableRowUnique">
                  <td class="dataTableHeadingContent"><?php echo $sub_num_orders . TEXT_ORDERS; ?></td>
                  <td colspan="6">&nbsp;</td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_order_total); ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_applied); ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_balance); ?></td>
                </tr>
                <?php
                // add to grand totals
                $order_grand_total += $sub_order_total;
                $total_applied += $sub_applied;
                $total_balance += $sub_balance;
                $num_orders += $sub_num_orders;

                // zero out the sub-total variables
                $sub_order_total = 0;
                $sub_applied = 0;
                $sub_balance = 0;
                $sub_num_orders = 0;
              }  // END if ($out_po_check->RecordCount() > 0)
              // then display outstanding checks
              // these orders aren't shipped until we have payment
              $out_check_query = $db->Execute("SELECT o.*,
                                                os.orders_status_name
                                         FROM " . TABLE_ORDERS . " o
                                         LEFT JOIN " . TABLE_ORDERS_STATUS . " os ON os.orders_status_id = o.orders_status
                                           AND os.language_id = " . $_SESSION['languages_id'] . "
                                         WHERE o.payment_module_code != ''
                                         AND o.date_completed IS NULL
                                         AND o.date_cancelled IS NULL
                                         AND o.balance_due > 0
                                         " . $date_limit . "
                                         ORDER BY o.orders_id ASC");
              if ($out_check_query->RecordCount() > 0) {
                ?>
                <tr>
                  <td colspan="10" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . TABLE_SUBHEADING_CHECKS . zen_draw_separator(); ?></strong></td>
                </tr>
                <?php
                foreach ($out_check_query as $out_check) {
                  unset($so);
                  $so = new super_order($out_check['orders_id']);

                  $sub_order_total += $so->order_total;
                  $sub_applied += $so->amount_applied;
                  $sub_balance += $so->balance_due;
                  $sub_num_orders++;

                  // ctp: begin dummy this up to create so classes for all orders in range. This will ensure all paypal
                  // transactions are captured in the so_payments and so_refunds for the order range selected.
                  $orders_list_query = "SELECT o.orders_id
                                    FROM " . TABLE_ORDERS . " o
                                    WHERE date_purchased BETWEEN '" . $sd . "'
                                    AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)";
                  $orders_list = $db->Execute($orders_list_query);
                  foreach ($orders_list as $order_list) {
                    $so = new super_order($order_list['orders_id']);  // instantiated once simply for the full_type() function
                    $so = NULL;
                  }
                  // ctp: end dummy this up to create so classes for all orders in range. This will ensure all paypal
                  ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $out_check['orders_id'] . '&action=edit'); ?>'" style="cursor: pointer;">
                    <td class="dataTableContent"><?php echo $out_check['orders_id']; ?></td>
                    <td class="dataTableContent"><?php echo $out_check['customers_state']; ?></td>
                    <td class="dataTableContent"><?php echo $out_check['payment_method']; ?></td>
                    <td class="dataTableContent"><?php echo $out_check['orders_status_name']; ?></td>
                    <td class="dataTableContent text-center"><?php echo $out_check['date_purchased']; ?></td>
                    <td class="dataTableContent"><?php echo $out_check['billing_name']; ?></td>
                    <td class="dataTableContent"><?php echo $out_check['customers_telephone']; ?></td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($so->order_total); ?></td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($so->amount_applied); ?></td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($so->balance_due); ?></td>
                  </tr>
                <?php } ?>
                <tr class="dataTableRowUnique">
                  <td class="dataTableHeadingContent"><?php echo $sub_num_orders . TEXT_ORDERS; ?></td>
                  <td colspan="6">&nbsp;</td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_order_total); ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_applied); ?></td>
                  <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($sub_balance); ?></td>
                </tr>
                <?php
                // add to grand totals
                $order_grand_total += $sub_order_total;
                $total_applied += $sub_applied;
                $total_balance += $sub_balance;
                $num_orders += $sub_num_orders;
              }  // END if ($out_check->RecordCount() > 0)
              ?>
              <tr>
                <td colspan="10" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . TABLE_SUBHEADING_TOTAL_PAYMENTS . zen_draw_separator(); ?></strong></td>
              </tr>
              <?php
            }  // END elseif ($report_type == 'out_payment')
            elseif ($report_type == 'out_refund') {
              $out_refund = $db->Execute("SELECT o.*,
                                                 os.orders_status_name
                                          FROM " . TABLE_ORDERS . " o
                                          LEFT JOIN " . TABLE_ORDERS_STATUS . " os ON os.orders_status_id = o.orders_status
                                          WHERE balance_due < 0
                                          " . $date_limit . "
                                          ORDER BY o.orders_id ASC");

              foreach ($out_refund as $item) {
                $so = new super_order($item['orders_id']);

                $order_grand_total += $so->order_total;
                $total_applied += $so->amount_applied;
                $total_balance += $so->balance_due;
                $num_orders++;
                ?>
                <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $item['orders_id'] . '&action=edit'); ?>'" style="cursor: pointer;">
                  <td class="dataTableContent"><?php echo $item['orders_id']; ?></td>
                  <td class="dataTableContent"><?php echo $item['customers_state']; ?></td>
                  <td class="dataTableContent"><?php echo $item['payment_method']; ?></td>
                  <td class="dataTableContent"><?php echo $item['orders_status_name']; ?></td>
                  <td class="dataTableContent text-center"><?php echo $item['date_purchased']; ?></td>
                  <td class="dataTableContent"><?php echo $item['billing_name']; ?></td>
                  <td class="dataTableContent"><?php echo $item['customers_telephone']; ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($so->order_total); ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($so->amount_applied); ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($so->balance_due); ?></td>
                </tr>
                <?php
              }
            }
            ?>
          </tbody>
          <?php
          if ($report_type == 'out_payment' || $report_type == 'out_refund') {
            ?>
            <tfoot>
              <tr class="dataTableRowUnique">
                <td class="dataTableHeadingContent"><?php echo $num_orders . TEXT_ORDERS; ?></td>
                <td colspan="6">&nbsp;</td>
                <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($order_grand_total); ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($total_applied); ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo $currencies->format($total_balance); ?></td>
              </tr>
            </tfoot>
          <?php } ?>
        </table>
      <?php } ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php
    if (!$print) {
      require DIR_WS_INCLUDES . 'footer.php';
    }
    ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
