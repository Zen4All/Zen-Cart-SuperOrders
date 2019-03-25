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
 *  DESCRIPTION:   Report that displays all income for the given date
 *  range.  Report results come solely from the Super Orders payment
 *  system.
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
require 'includes/application_top.php';

$target = (isset($_GET['target']) ? $_GET['target'] : false);
$is_for_display = ($_GET['print_format'] == 1 ? false : true);

if ($target) {
  require DIR_WS_CLASSES . 'currencies.php';
  $currencies = new currencies();
  require DIR_WS_CLASSES . 'super_order.php';

  $sd = zen_date_raw((!isset($_GET['start_date']) ? date("m-d-Y", (time())) : $_GET['start_date']));
  $ed = zen_date_raw((!isset($_GET['end_date']) ? date("m-d-Y", (time())) : $_GET['end_date']));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/css/super_stylesheet.css">
    <link rel="stylesheet" href="includes/css/srap_print.css" media="print" />
    <?php if ($is_for_display) { ?>
      <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
      <link rel="stylesheet" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
      <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
      <script src="includes/menu.js"></script>
      <script src="includes/general.js"></script>
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
  <?php if ($is_for_display) { ?>
    <body onload="init()">
      <div id="spiffycalendar" class="text"></div>
      <!-- header //-->
      <?php require DIR_WS_INCLUDES . 'header.php'; ?>
      <!-- header_eof //-->
      <!-- body //-->
      <script>
        var StartDate = new ctlSpiffyCalendarBox("StartDate", "search", "start_date", "btnDate1", "<?php echo (($_GET['start_date'] == '') ? '' : $_GET['start_date']); ?>", scBTNMODE_CUSTOMBLUE);
        var EndDate = new ctlSpiffyCalendarBox("EndDate", "search", "end_date", "btnDate2", "<?php echo (($_GET['end_date'] == '') ? '' : $_GET['end_date']); ?>", scBTNMODE_CUSTOMBLUE);
      </script>
    <?php } ?>

    <!-- body_text //-->
    <div class="container-fluid">
        <?php if (!$is_for_display) { ?>
        <div class="col-sm-12">
          <!-- Print Header -->
          <h1><a href="<?php echo zen_href_link(FILENAME_SUPER_REPORT_CASH, 'target=' . $target . '&start_date=' . $_GET['start_date'] . '&end_date=' . $_GET['end_date']); ?>"><?php echo HEADING_TITLE; ?></a></h1>
          <div class="pageHeading text-right"><?php echo $_GET['start_date'] . TEXT_TO . $_GET['end_date']; ?></div>
          <!-- END Print Header -->
        </div>
      <?php } else { ?>
        <!-- Display Header -->
        <h1><?php echo HEADING_TITLE; ?></h1>
        <?php echo zen_draw_form('search', FILENAME_SUPER_REPORT_CASH, '', 'get', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(HEADING_SELECT_TARGET, 'target', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <div class="radio">
              <label><?php echo zen_draw_radio_field('target', 'payments') . TEXT_PAYMENTS; ?></label>
            </div>
            <div class="radio">
              <label><?php echo zen_draw_radio_field('target', 'refunds') . TEXT_REFUNDS; ?></label>
            </div>
            <div class="radio">
              <label><?php echo zen_draw_radio_field('target', 'both') . TEXT_BOTH; ?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><?php echo HEADING_DATE_RANGE; ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(HEADING_START_DATE, 'start_date', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <script>
              StartDate.writeControl();
              StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
            </script>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(HEADING_END_DATE, 'end_date', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <script>
              EndDate.writeControl();
              EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
            </script>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-12 col-md-6">
            <div class="checkbox">
              <label><?php echo zen_draw_checkbox_field('print_format', 1) . HEADING_PRINT_FORMAT; ?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9 col-md-6">
            <button class="btn btn-info" type="submit"><?php echo BUTTON_SEARCH; ?></button>
          </div>
        </div>
        <?php echo '</form>'; ?>
        <?php if ($target && $is_for_display) { ?>
          <div class="col-sm-12">
            <table>
              <tr>
                <td class="main text-center"><?php echo HEADING_COLOR_KEY; ?></td>
                <td class="dataTableContent paymentRow text-center"><?php echo TEXT_PAYMENTS; ?></td>
                <td class="dataTableContent refundRow text-center"><?php echo TEXT_REFUNDS; ?></td>
              </tr>
            </table>
          </div>
        <?php } ?>
      <?php } ?>
      <!-- END Display Header -->
      <?php if ($target) { ?>
        <table class="table table-striped table-hover">
          <thead>
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER_ID; ?></th>
              <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_POSTED; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_NAME; ?></th>
              <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_TYPE; ?></th>
              <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATE; ?></th>
              <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_AMOUNT; ?></th>
            </tr>
          </thead>
          <tbody>
              <?php
              $grand_count = 0;
              $grand_total = 0;
              $num_of_types = 0;

              // ctp: begin dummy this up to create so classes for all orders in range. This will ensure all paypal
              // transactions are captured in the so_payments and so_refunds for the order range selected.
              $orders_list_query = "SELECT o.orders_id
                                    FROM " . TABLE_ORDERS . " o
                                    WHERE date_purchased BETWEEN '" . $sd . "'
                                    AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)";
              $order_list = $db->Execute($orders_list_query);
              foreach ($orders_list as $order_list) {
                $so = new super_order($order_list['orders_id']);  // instantiated once simply for the full_type() function
                $so = NULL;
              }
              // ctp: end dummy this up to create so classes for all orders in range. This will ensure all paypal

              if ($target == 'payments' || $target == 'both') {
                $payment_query = "SELECT *
                                  FROM " . TABLE_SO_PAYMENTS . " p
                                  LEFT JOIN " . TABLE_ORDERS . " o ON o.orders_id = p.orders_id
                                  WHERE date_posted BETWEEN '" . $sd . "'
                                  AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)
                                  ORDER BY payment_type ASC";
                $payments = $db->Execute($payment_query);

                if (zen_not_null($payments->fields['orders_id'])) {
                  $so = new super_order($payments->fields['orders_id']);  // instantiated once simply for the full_type() function
                  $current_type = strtoupper($payments->fields['payment_type']);
                  $num_of_types++;
                  $sub_total = 0;
                  $sub_count = 0;
                  ?>
                <tr>
                  <td colspan="7" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . $so->full_type($current_type) . zen_draw_separator(); ?></strong></td>
                </tr>
                <?php
                //_TODO make this into a do/while loop so that the final sub_total values can be displayed
                foreach ($payments as $payment) {
                  if ($current_type != strtoupper($payment['payment_type'])) {
                    // print subtotal line & count for type
                    ?>
                    <tr class="dataTableRowUnique">
                      <td class="dataTableContent" colspan="3"><strong><?php echo sprintf(TABLE_SUB_COUNT, $so->full_type($current_type)) . $sub_count; ?></strong></td>
                      <td class="dataTableContent text-right" colspan="4"><strong><?php echo sprintf(TABLE_SUB_TOTAL, $so->full_type($current_type)) . $currencies->format($sub_total); ?></strong></td>
                    </tr>
                    <?php
                    // reset type values for the next one
                    $current_type = strtoupper($payment['payment_type']);
                    $num_of_types++;
                    $sub_total = 0;
                    $sub_count = 0;
                    ?>
                    <tr>
                      <td colspan="7" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . $so->full_type($current_type) . zen_draw_separator(); ?></strong></td>
                    </tr>
                  <?php } ?>
                  <tr class="paymentRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $payment['orders_id'] . '&action=edit'); ?>'">
                    <td class="dataTableContent"><?php echo $payment['orders_id']; ?></td>
                    <td class="dataTableContent text-center"><?php echo zen_datetime_short($payment['date_posted']); ?></td>
                    <td class="dataTableContent"><?php echo $payment['payment_number']; ?></td>
                    <td class="dataTableContent"><?php echo $payment['payment_name']; ?></td>
                    <td class="dataTableContent text-center"><?php echo zen_get_payment_type_name($payment['payment_type']); ?></td>
                    <td class="dataTableContent"><?php echo $payment['billing_state']; ?></td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($payment['payment_amount']); ?></td>
                  </tr>
                  <?php
                  $sub_count++;
                  $grand_count++;

                  $sub_total += $payment['payment_amount'];
                  $grand_total += $payment['payment_amount'];
                }
                ?>
                <tr class="dataTableRowUnique">
                  <td class="dataTableContent" colspan="3"><strong><?php echo sprintf(TABLE_SUB_COUNT, $so->full_type($current_type)) . $sub_count; ?></strong></td>
                  <td class="dataTableContent text-right" colspan="4"><strong><?php echo sprintf(TABLE_SUB_TOTAL, $so->full_type($current_type)) . $currencies->format($sub_total); ?></strong></td>
                </tr>
              <?php } else { ?>
                <tr>
                  <td class="dataTableContent text-center" colspan="7"><strong><?php echo TEXT_NO_PAYMENT_DATA; ?></strong></td>
                </tr>
                <?php
              }
            }

            if ($target == 'refunds' || $target == 'both') {
              $refund_query = "SELECT *
                               FROM " . TABLE_SO_REFUNDS . "
                               WHERE date_posted BETWEEN '" . $sd . "'
                               AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)";

              $refund = $db->Execute($refund_query);

              if (zen_not_null($refund->fields['orders_id'])) {
                $refund_count = 0;
                $refund_total = 0;
                ?>
                <tr>
                  <td colspan="7" class="dataTableContent text-center"><strong><?php echo zen_draw_separator() . TEXT_REFUNDS . zen_draw_separator(); ?></strong></td>
                </tr>
                <?php foreach ($refund as $item) { ?>
                  <tr class="refundRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $item['orders_id'] . '&action=edit'); ?>'">
                    <td class="dataTableContent"><?php echo $$item['orders_id']; ?></td>
                    <td class="dataTableContent text-center"><?php echo zen_datetime_short($$item['date_posted']); ?></td>
                    <td class="dataTableContent"><?php echo $$item['refund_number']; ?></td>
                    <td class="dataTableContent"><?php echo $$item['refund_name']; ?></td>
                    <td class="dataTableContent text-center"><?php echo $$item['refund_type']; ?></td>
                    <td class="dataTableContent">&nbsp;</td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($$item['refund_amount']); ?></td>
                  </tr>
                  <?php
                  $refund_count++;
                  $refund_total += $$item['refund_amount'];
                }
              } else {
                ?>
                <tr>
                  <td class="dataTableContent text-center" colspan="7"><strong><?php echo TEXT_NO_REFUND_DATA; ?></strong></td>
                </tr>
                <?php
              }
              $total_income = $grand_total - $refund_total;
              ?>
              <tr>
                <td colspan="5">
                <td class="ot-tax-Text text-right"><strong><?php echo (int)$grand_count . ' ' . TABLE_FOOTER_CASH_TOTAL; ?></strong></td>
                <td class="ot-tax-Amount text-right"><?php echo $currencies->format($grand_total); ?></td>
              </tr>
              <tr>
                <td colspan="5">
                <td class="ot-tax-Text text-right"><strong><?php echo (int)$refund_count . ' ' . TABLE_FOOTER_REFUND_TOTAL; ?></strong></td>
                <td class="ot-tax-Amount text-right"><?php echo '-' . $currencies->format($refund_total); ?></td>
              </tr>
              <tr>
                <td colspan="5">
                <td class="ot-total-Text text-right"><?php echo TABLE_FOOTER_TOTAL_INCOME; ?></td>
                <td class="ot-total-Amount text-right"><?php echo $currencies->format($total_income); ?></td>
              </tr>
            <?php } else { ?>
              <tr class="dataTableRowUnique">
                <td class="dataTableContent" colspan="3"><strong><?php echo TABLE_FOOTER_NUM_PAYMENTS . $grand_count; ?></strong></td>
                <td class="dataTableContent text-right" colspan="4"><strong><?php echo TABLE_FOOTER_TOTAL_INCOME . $currencies->format($grand_total); ?></strong></td>
              </tr>
            <?php } ?>
          </tbody>
          <?php if ($num_of_types > 1) { ?>
            <tfoot>
              <tr>
                <td class="dataTableContent" colspan="7" align="left"><?php echo $num_of_types . TABLE_FOOTER_NUM_TYPES; ?></td>
              </tr>
            </tfoot>
          <?php } ?>
        </table>
      <?php } ?>
      <!-- body_text_eof //-->

      <!-- body_eof //-->
      <?php if (!$is_for_display) { ?>
        <div class="col-sm-12">
          <a href="javascript:history.back()" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>&nbsp;<button type="button" onClick="window.print()" class="btn btn-info"><?php echo BUTTON_PRINT; ?></button>
        </div>
      <?php } ?>
      <!-- footer //-->
      <?php
      if ($is_for_display) {
        require DIR_WS_INCLUDES . 'footer.php';
      }
      ?>
    </div>
    <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>