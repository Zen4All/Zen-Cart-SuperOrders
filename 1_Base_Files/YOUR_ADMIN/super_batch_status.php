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
//  DESCRIPTION:   Updates order statuses en masse. Order search can  //
//  be customized based on available filters (date range, current //
//  status, customer, and product)          //
//////////////////////////////////////////////////////////////////////////
// $Id: super_batch_forms.php v 2010-10-24 $
*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                 from " . TABLE_ORDERS_STATUS . "
                                 where language_id = '" . (int)$_SESSION['languages_id'] . "'");

  while (!$orders_status->EOF) {
    $orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
                               'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }

  $products = all_products_array(DROPDOWN_ALL_PRODUCTS, true, false, true);
  $payments = all_payments_array(DROPDOWN_ALL_PAYMENTS, true);
  $customers = all_customers_array(DROPDOWN_ALL_CUSTOMERS, true, false);

    /* BEGIN addition (Loose Chicken Software Development, david@loosechicken.com 07-13-2010) */
    /* added seach by country */
    $countries = current_countries_array(DROPDOWN_ALL_COUNTRIES);
    /* END addition (Loose Chicken Software Development) */

  $ot_sign = array();
  $ot_sign[] = array('id' => '1',
                     'text' => ' > ' . DROPDOWN_GREATER_THAN);
  $ot_sign[] = array('id' => '2',
                     'text' => ' < ' . DROPDOWN_LESS_THAN);
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
    foreach($selected_oids as $oID => $print_order) {
      batch_status($oID, $status, $comments, $notify, $notify_comments);
    }
      //zen_redirect(zen_href_link(FILENAME_SUPER_BATCH_STATUS, '', 'NONSSL'));
    zen_redirect($_REQUEST['redirect'], '', 'NONSSL')   ;
  }

  else {
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/super_stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
<!--
  function init() {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }

  function checkByParent(aId) {
    var collection = document.getElementById(aId).getElementsByTagName('INPUT');
    for (var x=0; x<collection.length; x++) {
      if (collection[x].type.toUpperCase()=='CHECKBOX') {
        if (collection[x].checked == true)

        {
          collection[x].checked = false;
        }
        else { collection[x].checked = true; }
      }
    }
  }
// -->
</script>
</head>
<body onLoad="init()">
<div id="spiffycalendar" class="text"></div>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<script language="javascript">
var StartDate = new ctlSpiffyCalendarBox("StartDate", "order_search", "start_date", "btnDate1", "<?php echo (($_GET['start_date'] == '') ? '' : $_GET['start_date']); ?>", scBTNMODE_CUSTOMBLUE);
var EndDate = new ctlSpiffyCalendarBox("EndDate", "order_search", "end_date", "btnDate2", "<?php echo (($_GET['end_date'] == '') ? '' : $_GET['end_date']); ?>", scBTNMODE_CUSTOMBLUE);
</script>
<table border="0" cellpadding="2" cellspacing="2" width="100%">
  <tr>
<!-- begin search -->
    <td valign="top" width="100%"><table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td colspan="2"><table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td colspan="2" class="pageHeading"><?php echo
              HEADING_TITLE . '&nbsp;&nbsp;' .
              '<INPUT class="normal_button button" TYPE="BUTTON" VALUE="' . BOX_CUSTOMERS_SUPER_BATCH_FORMS . '" ONCLICK="window.location.href=\'' . zen_href_link(FILENAME_SUPER_BATCH_FORMS, '') . '\'">' .
              '&nbsp;&nbsp;' .
              '<INPUT class="normal_button button" TYPE="BUTTON" VALUE="' . BOX_CONFIGURATION_SUPER_ORDERS . '" ONCLICK="window.location.href=\'' . zen_href_link(FILENAME_ORDERS, '') . '\'">';
            ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', 1, 10); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="3"><strong><?php echo HEADING_SEARCH_FILTER; ?></strong></td>
          </tr>
          <?php echo zen_draw_form('order_search', FILENAME_SUPER_BATCH_STATUS, '', 'get', '', true); ?>

          <tr>
            <td valign="top"><table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td class="smallText" align="left"><?php echo HEADING_START_DATE; ?><br />
    <script language="javascript">
                  StartDate.writeControl(); StartDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
                </td>
              </tr>
              <tr>
                <td class="smallText" align="left"><?php echo HEADING_END_DATE; ?><br />
    <script language="javascript">
                  EndDate.writeControl(); EndDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
                </td>
              </tr>
            </table></td>
            <td valign="top"><table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_STATUS; ?></td>
                <td class="smallText"><?php echo zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), $_GET['status'], ''); ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_PRODUCTS; ?></td>
                <td class="smallText"><?php echo zen_draw_pull_down_menu('products', $products, $_GET['products'], ''); ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_CUSTOMERS; ?></td>
                <td class="smallText"><?php echo zen_draw_pull_down_menu('customers', $customers, $_GET['customers'], ''); ?></td>
              </tr>

<?php
    /* BEGIN addition added seach by country */
?>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_COUNTRY; ?></td>
                <td class="smallText"><?php echo zen_draw_pull_down_menu('countries', $countries, $_GET['countries'], ''); ?></td>
              </tr>
<?php /* END addition */ ?>
            </table></td>
            <td valign="top"><table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_PAYMENT_METHOD; ?></td>
                <td class="smallText" colspan="3"><?php echo zen_draw_pull_down_menu('payments', $payments, $_GET['payments'], ''); ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_ORDER_TOTAL; ?></td>
                <td class="smallText"><?php echo zen_draw_pull_down_menu('ot_sign', $ot_sign, $_GET['ot_sign'], ''); ?></td>
                <td class="smallText"><?php echo zen_draw_input_field('order_total', '', 'size="8"'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_TEXT; ?></td>
                <td class="smallText" colspan="2"><?php echo zen_draw_input_field('search', $_GET['search']); ?></td>
              </tr>
<?php
    /* BEGIN addition added seach by OrderID Range */
    // If you want to start above order 1, uncomment this block
    /*
    if (!isset($_GET['oid_range_first']) ||  (!zen_not_null($_GET['oid_range_first']))) {
       $_GET['oid_range_first'] = 12000;
    }
    */
?>
              <tr>
                <td class="smallText"><?php echo HEADING_SEARCH_ORDERID_RANGE; ?></td>
                <td class="smallText" colspan="3"><?php echo zen_draw_input_field('oid_range_first', $_GET['oid_range_first'], 'size="8"'); ?>
                &nbsp;&nbsp;<b>to</b>&nbsp;&nbsp; <?php echo zen_draw_input_field('oid_range_last', $_GET['oid_range_last'],'size="8"'); ?></td>
              </tr>
<?php /* END addition */ ?>
            </table></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', 1, 5); ?></td>
          </tr>
          <tr>
            <td class="smallText" colspan="3" align="right" valign="bottom">
      <input class="submit_button button" type="submit" value="<?php echo BUTTON_SEARCH; ?>"></td>
          </tr></form>
        </table></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo zen_draw_separator(); ?></td>
      </tr>
<!-- end search -->
<?php
// we only need to check one variable since all are passed with the form
if (isset($_GET['start_date']) ) {
  // create query based on filter crieria
  $orders_query_raw = "SELECT o.orders_id, o.customers_id, o.customers_name,
                              o.payment_method, o.date_purchased, o.order_total, s.orders_status_name
                       FROM " . TABLE_ORDERS . " o
                       LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON o.orders_status = s.orders_status_id";

  if (isset($_GET['products']) && zen_not_null($_GET['products'])) {
    $orders_query_raw .= " LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON o.orders_id = op.orders_id";
  }

  $orders_query_raw .= " WHERE s.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_prepare_input($_GET['search'], true);
    $search = " and (o.customers_city like '%" . $keywords . "%' or o.customers_postcode like '%" . $keywords . "%' or o.date_purchased like '%" . $keywords . "%' or o.billing_name like '%" . $keywords . "%' or o.billing_company like '%" . $keywords . "%' or o.billing_street_address like '%" . $keywords . "%' or o.delivery_city like '%" . $keywords . "%' or o.delivery_postcode like '%" . $keywords . "%' or o.delivery_name like '%" . $keywords . "%' or o.delivery_company like '%" . $keywords . "%' or o.delivery_street_address like '%" . $keywords . "%' or o.billing_city like '%" . $keywords . "%' or o.billing_postcode like '%" . $keywords . "%' or o.customers_email_address like '%" . $keywords . "%' or o.customers_name like '%" . $keywords . "%' or o.customers_company like '%" . $keywords . "%' or o.customers_street_address  like '%" . $keywords . "%' or o.customers_telephone like '%" . $keywords . "%')";

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
    if ($_GET['ot_sign'] == 3) { $sign_operator = '='; }
    elseif ($_GET['ot_sign'] == 2) { $sign_operator = '<='; }
      else { $sign_operator = '>='; }
      $orders_query_raw .= " AND o.order_total " . $sign_operator . " '" . (int)$_GET['order_total'] . "'";
  }

    /* BEGIN addition added seach by OrderID Range */
    if (isset($_GET['oid_range_first']) && zen_not_null($_GET['oid_range_first']) &&
            isset($_GET['oid_range_last']) && zen_not_null($_GET['oid_range_last'])){
        $orders_query_raw .= " AND o.orders_id BETWEEN " . (int)$_GET['oid_range_first'] . " AND " . (int)$_GET['oid_range_last'];
    } else if (isset($_GET['oid_range_first']) && zen_not_null($_GET['oid_range_first'])) {
        $orders_query_raw .= " AND o.orders_id >= " . (int)$_GET['oid_range_first'] . " ";
    } else if (isset($_GET['oid_range_last']) && zen_not_null($_GET['oid_range_last'])){
        $orders_query_raw .= " AND o.orders_id <= " . (int)$_GET['oid_range_last'] . " ";
    }

    /* added seach by country */
    if (isset($_GET['countries']) && zen_not_null($_GET['countries'])){
        if($_GET['countries'] == 'International'){
            $orders_query_raw .= " AND o.customers_country <> '" . get_store_country_name() . "' ";
        }
        else{
            $orders_query_raw .= " AND o.customers_country = '" . $_GET['countries'] . "' ";
        }
    }
    /* END addition */

  $orders_query_raw .= " ORDER BY o.orders_id DESC";

  $orders = $db->Execute($orders_query_raw);
  if ($orders->RecordCount() > 0) {
?>
      <tr>
        <td><table border="0" cellpadding="0" cellspacing="0" width="100%">
          <?php echo zen_draw_form('batch_status', FILENAME_SUPER_BATCH_STATUS, 'action=batch_status', 'post', ''); ?>
      <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']?>">
          <tr>
            <td colspan="2" align="left">
      <table border="0" cellpadding="0" cellspacing="2">
              <tr>
                <td class="main" colspan="2"><strong><?php echo HEADING_UPDATE_ORDERS; ?></strong></td>
              </tr>
              <tr id="so_assign">
                <td class="smallText"><?php echo HEADING_SELECT_STATUS; ?></td>
                <td class="smallText" colspan="2"><?php echo zen_draw_pull_down_menu('assign_status', $orders_statuses, $_GET['assign_status'], ''); ?></td>
              </tr>
                <tr id="so_comments">
                <td class="smallText" valign="top"><?php echo HEADING_ADD_COMMENTS; ?></td>
                <td class="smallText" colspan="2"><?php echo zen_draw_textarea_field('comments', 'soft', '50', '4'); ?></td>
              </tr>
                <tr id="so_notify">
                <td class="smallText" valign="top"><?php echo HEADING_NOTIFICATION; ?></td>
                <td class="smallText" colspan="2"><?php
        echo zen_draw_radio_field('notify', '1', true) . '-' . TEXT_EMAIL . ' ' . zen_draw_radio_field('notify', '0', FALSE) . '-' . TEXT_NOEMAIL . ' ' . zen_draw_radio_field('notify', '-1', FALSE) . '-' . TEXT_HIDE . '<br />';
                echo zen_draw_checkbox_field('notify_comments', 'on', true); echo '&nbsp;' . ENTRY_NOTIFY_COMMENTS; ?><br /><br />
                  &nbsp;<input class="submit_button button" type="submit" value="<?php echo BUTTON_UPDATE_STATUS; ?>">
                </td>
              </tr>
            </table>
      </td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', 1, 5); ?></td>
          </tr>
          <tr>
            <td class="main" valign="bottom"><?php
      echo TEXT_TOTAL_ORDERS . '<strong>' . $orders->RecordCount() . '</strong>' . '&nbsp;&nbsp;';
            echo '<INPUT class="normal_button button" TYPE="BUTTON" VALUE="' . BUTTON_CHECK_ALL . '" ONCLICK="checkByParent(\'ordersList\');">';

            ?></td>
            <td class="main" align="right" valign="bottom"><strong><?php echo zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS) . '&nbsp;' . ICON_ORDER_DETAILS; ?></strong></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td> <div id="ordersList">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td><table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" colspan="2" align="left">&nbsp;&nbsp;<?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></td>
                    <td class="dataTableHeadingContent" align="left" colspan="2"><?php echo TABLE_HEADING_ORDER_STATUS; ?></td>
                  </tr>
<?php
    while (!$orders->EOF) {
?>
                  <tr class="dataTableRow" onMouseOver="rowOverEffect(this);this.style.cursor='default'" onMouseOut="rowOutEffect(this)">
                    <td class="dataTableContent" align="left"><?php
        echo zen_draw_checkbox_field('batch_order_numbers[' . $orders->fields['orders_id'] . ']', 'yes', FALSE);
                      echo $orders->fields['orders_id'];
                    ?></td>
                    <td class="dataTableContent" align="right"><?php echo '[' . $orders->fields['customers_id'] . ']'; ?></td>
                    <td class="dataTableContent" align="left"><?php echo $orders->fields['customers_name']; ?></td>
                    <td class="dataTableContent" align="right"><?php echo $currencies->format($orders->fields['order_total']); ?></td>
                    <td class="dataTableContent" align="center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                    <td class="dataTableContent" align="left"><?php echo $orders->fields['payment_method']; ?></td>
                    <td class="dataTableContent" align="left"><?php echo $orders->fields['orders_status_name']; ?></td>
                    <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_SUPER_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS) . '</a>&nbsp'; ?></td>
                  </tr>
<?php
      $orders->MoveNext();
    }
  }  // END if ($orders->RecordCount() > 0)
?>
                </form>
                </table></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', 1, 10); ?></td>
              </tr>
            </table>
            </div>
      </td>
          </tr>
        </table></td>
      </tr>
<?php } else { ?>
      <tr>
        <td colspan="2"><?php echo TEXT_ENTER_SEARCH; ?></td>
      </tr>
<?php } ?>
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php  require(DIR_WS_INCLUDES . 'application_bottom.php');

  }

function batch_status($oID, $status, $comments, $notify, $notify_comments) {

  global $db, $messageStack;
  require(DIR_WS_LANGUAGES . 'english/orders.php');

  $order_updated = false;
  $check_status = $db->Execute("select customers_name, customers_email_address, orders_status, date_purchased
                                from " . TABLE_ORDERS . "
                                where orders_id = '" . (int)$oID . "'");

  if ( ($check_status->fields['orders_status'] != $status) || zen_not_null($comments)) {
    update_status($oID, $status, $notify, $comments);





    if ($notify == '1') {
         email_latest_status($oID);
    }
    $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
  }
  else {
    $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
  }
}