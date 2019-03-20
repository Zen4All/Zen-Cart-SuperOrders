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
 *  DESCRIPTION:   Updates order statuses en masse. Order search can
 *  be customized based on available filters (date range, current
 *  status, customer, and product)
 *
 * $Id: super_batch_forms.php v 2010-10-24 $
 */
require('includes/application_top.php');
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$orders_statuses = array();
$orders_status_array = array();
$orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                               FROM " . TABLE_ORDERS_STATUS . "
                               WHERE language_id = " . (int)$_SESSION['languages_id']);

foreach ($orders_status as $status) {
  $orders_statuses[] = array(
    'id' => $status['orders_status_id'],
    'text' => $status['orders_status_name'] . ' [' . $status['orders_status_id'] . ']'
  );
  $orders_status_array[$status['orders_status_id']] = $status['orders_status_name'];
}

$products = all_products_array(DROPDOWN_ALL_PRODUCTS, true, false, true);
$payments = all_payments_array(DROPDOWN_ALL_PAYMENTS, true);
$customers = all_customers_array(DROPDOWN_ALL_CUSTOMERS, true, false);

$countries = current_countries_array(DROPDOWN_ALL_COUNTRIES);

$ot_sign = array();
$ot_sign[] = array(
  'id' => '1',
  'text' => ' > ' . DROPDOWN_GREATER_THAN
);
$ot_sign[] = array(
  'id' => '2',
  'text' => ' < ' . DROPDOWN_LESS_THAN
);
//TODO fix the order total seach so that 'equals to' searches work
//$ot_sign[] = array('id' => '3',
//                   'text' => ' = ' . DROPDOWN_EQUAL_TO);

if ($_GET['action'] == 'batch_status') {
  $selected_oids = zen_db_prepare_input($_POST['batch_order_numbers']);
  if (!is_array($selected_oids)) {
    $messageStack->add_session(ERROR_NO_ORDERS, 'warning');
  }

  $status = zen_db_prepare_input($_POST['assign_status'], true);
  $comments = zen_db_prepare_input($_POST['comments'], true);
  $notify = zen_db_prepare_input($_POST['notify']);
  $notify_comments = zen_db_prepare_input($_POST['notify_comments']);
  foreach ($selected_oids as $oID => $print_order) {
    batch_status($oID, $status, $comments, $notify, $notify_comments);
  }
  //zen_redirect(zen_href_link(FILENAME_SUPER_BATCH_STATUS, '', 'NONSSL'));
  zen_redirect($_REQUEST['redirect'], '', 'NONSSL');
} else {
  ?>
  <!doctype html>
  <html <?php echo HTML_PARAMS; ?>>
    <head>
      <meta charset="<?php echo CHARSET; ?>">
      <title><?php echo TITLE; ?></title>
      <link rel="stylesheet" href="includes/stylesheet.css">
      <link rel="stylesheet" href="includes/css/super_stylesheet.css">
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

        function checkByParent(aId) {
            var collection = document.getElementById(aId).getElementsByTagName('input');
            for (var x = 0; x < collection.length; x++) {
                if (collection[x].type.toUpperCase() == 'CHECKBOX') {
                    if (collection[x].checked == true) {
                        collection[x].checked = false;
                    } else {
                        collection[x].checked = true;
                    }
                }
            }
        }
      </script>
    </head>
    <body onLoad="init()">
      <div id="spiffycalendar" class="text"></div>
      <!-- header //-->
      <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
      <!-- header_eof //-->
      <script>
        var StartDate = new ctlSpiffyCalendarBox("StartDate", "order_search", "start_date", "btnDate1", "<?php echo (($_GET['start_date'] == '') ? '' : $_GET['start_date']); ?>", scBTNMODE_CUSTOMBLUE);
        var EndDate = new ctlSpiffyCalendarBox("EndDate", "order_search", "end_date", "btnDate2", "<?php echo (($_GET['end_date'] == '') ? '' : $_GET['end_date']); ?>", scBTNMODE_CUSTOMBLUE);
      </script>
      <div class="container-fluid">
        <h1><?php echo HEADING_TITLE; ?></h1>
        <div class="col-sm-12">
          <a href="<?php echo zen_href_link(FILENAME_SUPER_BATCH_FORMS, ''); ?>" class="btn btn-info btn-sm" role="button"><?php echo BOX_CUSTOMERS_SUPER_BATCH_FORMS; ?></a>
          &nbsp;&nbsp;
          <a href="<?php echo zen_href_link(FILENAME_ORDERS, ''); ?>" class="btn btn-info btn-sm" role="button"><?php echo BOX_CONFIGURATION_SUPER_ORDERS; ?></a>
        </div>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', 1, 5); ?>
        </div>
        <div class="col-sm-12">
          <p><strong><?php echo HEADING_SEARCH_FILTER; ?></strong></p>
        </div>
        <?php echo zen_draw_form('order_search', FILENAME_SUPER_BATCH_STATUS, '', 'get', 'class="form-horizontal"', true); ?>
        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_START_DATE, 'start_date', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <script>
                  StartDate.writeControl();
                  StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
                </script>
              </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_END_DATE, 'end_date', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <script>
                  EndDate.writeControl();
                  EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
                </script>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_STATUS, 'status', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), $_GET['status'], 'class="form-control"'); ?></div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_PRODUCTS, 'products', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_pull_down_menu('products', $products, $_GET['products'], 'class="form-control"'); ?></div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_CUSTOMERS, 'customers', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_pull_down_menu('customers', $customers, $_GET['customers'], 'class="form-control"'); ?></div>
            </div>
            <?php
            /* BEGIN addition added seach by country */
            ?>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_COUNTRY, 'countries', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_pull_down_menu('countries', $countries, $_GET['countries'], 'class="form-control"'); ?></div>
            </div>
            <?php /* END addition */ ?>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_PAYMENT_METHOD, 'payments', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_pull_down_menu('payments', $payments, $_GET['payments'], 'class="form-control"'); ?></div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_ORDER_TOTAL, 'ot_sign', 'class="control-label col-md-3"'); ?>
              <div class="col-md-5"><?php echo zen_draw_pull_down_menu('ot_sign', $ot_sign, $_GET['ot_sign'], 'class="form-control"'); ?></div>
              <div class="col-md-4"><?php echo zen_draw_input_field('order_total', '', 'size="8" class="form-control"'); ?></div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_TEXT, 'search', 'class="control-label col-md-3"'); ?>
              <div class="col-md-9"><?php echo zen_draw_input_field('search', $_GET['search'], 'class="form-control"'); ?></div>
            </div>
            <?php
            /* BEGIN addition added seach by OrderID Range */
            // if you want to start above order 1, uncomment this block
            /*
              if (!isset($_GET['oid_range_first']) ||  (!zen_not_null($_GET['oid_range_first']))) {
              $_GET['oid_range_first'] = 12000;
              }
             */
            ?>
            <div class="form-group">
                <?php echo zen_draw_label(HEADING_SEARCH_ORDERID_RANGE, 'oid_range_first', 'class="control-label col-md-3"'); ?>
              <div class="col-md-4"><?php echo zen_draw_input_field('oid_range_first', $_GET['oid_range_first'], 'size="8" class="form-control"'); ?></div>
              <div class="col-md-1"><b>to</b></div>
              <div class="col-md-4"><?php echo zen_draw_input_field('oid_range_last', $_GET['oid_range_last'], 'size="8" class="form-control"'); ?></div>
            </div>
            <?php /* END addition */ ?>
          </div>
        </div>
        <div class="row">
            <?php echo zen_draw_separator('pixel_trans.gif', 1, 5); ?>
        </div>
        <div class="row text-right">
          <button class="btn btn-primary" type="submit"><?php echo BUTTON_SEARCH; ?></button>
        </div>
        <?php echo '</form>'; ?>
        <div class="row">
            <?php echo zen_draw_separator(); ?>
        </div>
        <!-- end search -->
        <?php
// we only need to check one variable since all are passed with the form
        if (isset($_GET['start_date'])) {
          // create query based on filter crieria
          $orders_query_raw = "SELECT o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.date_purchased, o.order_total, s.orders_status_name
                       FROM " . TABLE_ORDERS . " o
                       LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON o.orders_status = s.orders_status_id";

          if (isset($_GET['products']) && zen_not_null($_GET['products'])) {
            $orders_query_raw .= " LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON o.orders_id = op.orders_id";
          }
          $orders_query_raw .= " WHERE s.language_id = " . (int)$_SESSION['languages_id'];
          $search = '';
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            $keywords = zen_db_prepare_input($_GET['search'], true);
            $search = " AND (o.customers_city like '%" . $keywords . "%' OR o.customers_postcode like '%" . $keywords . "%' OR o.date_purchased like '%" . $keywords . "%' OR o.billing_name like '%" . $keywords . "%' OR o.billing_company like '%" . $keywords . "%' OR o.billing_street_address like '%" . $keywords . "%' OR o.delivery_city like '%" . $keywords . "%' OR o.delivery_postcode like '%" . $keywords . "%' OR o.delivery_name like '%" . $keywords . "%' OR o.delivery_company like '%" . $keywords . "%' OR o.delivery_street_address like '%" . $keywords . "%' OR o.billing_city like '%" . $keywords . "%' OR o.billing_postcode like '%" . $keywords . "%' OR o.customers_email_address like '%" . $keywords . "%' OR o.customers_name like '%" . $keywords . "%' OR o.customers_company like '%" . $keywords . "%' OR o.customers_street_address  like '%" . $keywords . "%' OR o.customers_telephone like '%" . $keywords . "%')";
            $orders_query_raw .= $search;
          }
          $sd = zen_date_raw(isset($_GET['start_date']) ? $_GET['start_date'] : '');
          $ed = zen_date_raw(isset($_GET['end_date']) ? $_GET['end_date'] : '');
          if ($sd != '' && $ed != '') {
            $orders_query_raw .= " AND o.date_purchased BETWEEN '" . $sd . "' AND DATE_ADD('" . $ed . "', INTERVAL 1 DAY)";
          }
          if (isset($_GET['status']) && zen_not_null($_GET['status'])) {
            $orders_query_raw .= " AND o.orders_status = '" . $_GET['status'] . "'";
          }
          if (isset($_GET['products']) && zen_not_null($_GET['products'])) {
            $orders_query_raw .= " AND op.products_id = '" . $_GET['products'] . "'";
          }
          if (isset($_GET['customers']) && zen_not_null($_GET['customers'])) {
            $orders_query_raw .= " AND o.customers_id = '" . $_GET['customers'] . "'";
          }
          if (isset($_GET['payments']) && zen_not_null($_GET['payments'])) {
            $orders_query_raw .= " AND o.payment_module_code = '" . $_GET['payments'] . "'";
          }
          if (isset($_GET['order_total']) && zen_not_null($_GET['order_total'])) {
            if ($_GET['ot_sign'] == 3) {
              $sign_operator = '=';
            } elseif ($_GET['ot_sign'] == 2) {
              $sign_operator = '<=';
            } else {
              $sign_operator = '>=';
            }
            $orders_query_raw .= " AND o.order_total " . $sign_operator . " '" . (int)$_GET['order_total'] . "'";
          }

          /* BEGIN addition added seach by OrderID Range */
          if (isset($_GET['oid_range_first']) && zen_not_null($_GET['oid_range_first']) &&
              isset($_GET['oid_range_last']) && zen_not_null($_GET['oid_range_last'])) {
            $orders_query_raw .= " AND o.orders_id BETWEEN " . (int)$_GET['oid_range_first'] . " AND " . (int)$_GET['oid_range_last'];
          } else if (isset($_GET['oid_range_first']) && zen_not_null($_GET['oid_range_first'])) {
            $orders_query_raw .= " AND o.orders_id >= " . (int)$_GET['oid_range_first'] . " ";
          } else if (isset($_GET['oid_range_last']) && zen_not_null($_GET['oid_range_last'])) {
            $orders_query_raw .= " AND o.orders_id <= " . (int)$_GET['oid_range_last'] . " ";
          }

          /* added seach by country */
          if (isset($_GET['countries']) && zen_not_null($_GET['countries'])) {
            if ($_GET['countries'] == 'International') {
              $orders_query_raw .= " AND o.customers_country <> '" . get_store_country_name() . "' ";
            } else {
              $orders_query_raw .= " AND o.customers_country = '" . $_GET['countries'] . "' ";
            }
          }
          /* END addition */

          $orders_query_raw .= " ORDER BY o.orders_id DESC";

          $orders = $db->Execute($orders_query_raw);
          if ($orders->RecordCount() > 0) {
            ?>
            <?php echo zen_draw_form('batch_status', FILENAME_SUPER_BATCH_STATUS, 'action=batch_status', 'post', 'class="form-horizontal"'); ?>
            <?php echo zen_draw_hidden_field('redirect', $_SERVER['REQUEST_URI']); ?>
            <div class="row">
              <div class="form-group">
                <div class="col-sm-12">
                  <p><strong><?php echo HEADING_UPDATE_ORDERS; ?></strong></p>
                </div>
              </div>
              <div class="form-group" id="so_assign">
                  <?php echo zen_draw_label(HEADING_SELECT_STATUS, 'assign_status', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('assign_status', $orders_statuses, $_GET['assign_status'], 'class="form-control"'); ?>
                </div>
              </div>
              <div class="form-group" id="so_comments">
                  <?php echo zen_draw_label(HEADING_ADD_COMMENTS, 'comments', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9 col-md-6"><?php echo zen_draw_textarea_field('comments', 'soft', '50', '4', '', 'class="form-control"'); ?></div>
              </div>
              <div class="form-group" id="so_notify">
                  <?php echo zen_draw_label(HEADING_NOTIFICATION, 'notify', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9 col-md-6">
                  <div class="radio">
                    <label><?php echo zen_draw_radio_field('notify', '1', true) . '-' . TEXT_EMAIL; ?></label>
                  </div>
                  <div class="radio">
                    <label><?php echo zen_draw_radio_field('notify', '0', FALSE) . '-' . TEXT_NOEMAIL; ?></label>
                  </div>
                  <div class="radio">
                    <label><?php echo zen_draw_radio_field('notify', '-1', FALSE) . '-' . TEXT_HIDE; ?></label>
                  </div>
                  <div class="checkbox">
                    <label><?php echo zen_draw_checkbox_field('notify_comments', 'on', true) . ENTRY_NOTIFY_COMMENTS; ?></label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9 col-md-6 text-right">
                  <button class="btn btn-primary" type="submit"><?php echo BUTTON_UPDATE_STATUS; ?></button>

                </div>
              </div>
            </div>
            <div class="row">
                <?php echo zen_draw_separator(); ?>
            </div>
            <div class="row">
              <table class="table">
                <tr>
                  <td>
                    <?php echo TEXT_TOTAL_ORDERS; ?><strong><?php echo $orders->RecordCount(); ?></strong>&nbsp;&nbsp;<button class="btn btn-default btn-sm" type="button" onclick="checkByParent('ordersList');"><?php echo BUTTON_CHECK_ALL; ?></button>
                  </td>
                  <td class="text-right">
                    <span class="fa-stack">
                      <i class="fa fa-circle fa-stack-2x" style="color:blue"></i>
                      <span class="fa-stack-1x"><span style="color:#fff;">d</span></span>
                    </span>
                    <?php echo ICON_ORDER_DETAILS; ?>
                  </td>
                </tr>
              </table>
            </div>
            <div class="row" id="ordersList">
              <table class="table table-striped table-hover">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_ORDERS_ID; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></th>
                    <th class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_ORDER_STATUS; ?></th>
                  </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) { ?>
                    <tr class="dataTableRow">
                      <td class="dataTableContent">
                        <div class="checkbox">
                          <label><?php echo zen_draw_checkbox_field('batch_order_numbers[' . $order['orders_id'] . ']', 'yes', false) . $order['orders_id']; ?></label>
                        </div>
                      </td>
                      <td class="dataTableContent text-right"><?php echo '[' . $order['customers_id'] . ']'; ?></td>
                      <td class="dataTableContent"><?php echo $order['customers_name']; ?></td>
                      <td class="dataTableContent text-right"><?php echo $currencies->format($order['order_total']); ?></td>
                      <td class="dataTableContent text-center"><?php echo zen_datetime_short($order['date_purchased']); ?></td>
                      <td class="dataTableContent"><?php echo $order['payment_method']; ?></td>
                      <td class="dataTableContent"><?php echo $order['orders_status_name']; ?></td>
                      <td class="dataTableContent text-right">
                        <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $order['orders_id'] . '&action=edit', 'NONSSL'); ?>">
                          <span class="fa-stack">
                            <i class="fa fa-circle fa-stack-2x" style="color:blue"></i>
                            <span class="fa-stack-1x"><span style="color:#fff;">d</span></span>
                          </span>
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
          <?php echo '</form>'; ?>
          <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', 1, 10); ?>          </div>
        <?php } else { ?>
          <div class="row"><?php echo TEXT_ENTER_SEARCH; ?></div>
        <?php } ?>
      </div>

      <!-- body_eof //-->
      <!-- footer //-->
      <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
      <!-- footer_eof //-->
    </body>
  </html>
  <?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
}

function batch_status($oID, $status, $comments, $notify, $notify_comments)
{

  global $db, $messageStack;
  require(DIR_WS_LANGUAGES . 'english/orders.php');

  $order_updated = false;
  $check_status = $db->Execute("SELECT customers_name, customers_email_address, orders_status, date_purchased
                                FROM " . TABLE_ORDERS . "
                                WHERE orders_id = " . (int)$oID);

  if (($check_status->fields['orders_status'] != $status) || zen_not_null($comments)) {
    update_status($oID, $status, $notify, $comments);
    if ($notify == '1') {
      email_latest_status($oID);
    }
    $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
  } else {
    $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
  }
}