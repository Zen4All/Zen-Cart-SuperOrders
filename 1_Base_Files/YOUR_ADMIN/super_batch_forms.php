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
//                                                        //
//  Powered by Zen-Cart (www.zen-cart.com)                  //
//  Portions Copyright (c) 2005 The Zen-Cart Team           //
//                                                          //
//  Released under the GNU General Public License           //
//  available at www.zen-cart.com/license/2_0.txt           //
//  or see "license.txt" in the downloaded zip              //
//////////////////////////////////////////////////////////////////////////
//  DESCRIPTION:   Print invoices, packingslips, and labels en masse. //
//  Also includes support for PDF packingslips. Order search can be //
//  customized based on available filters (date range, current status,  //
//  customer, and product)            //
//////////////////////////////////////////////////////////////////////////
// $Id: super_batch_forms.php v 2010-10-24 $
*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'order.php');

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

    /* BEGIN modification updated action processing to include pdf forms */
    if (in_array($_GET['action'], array('batch_forms', 'merged_packingslips', 'merged_packingslips_master_list'))) {
       $selected_oids = zen_db_prepare_input($_POST['batch_order_numbers']);

        $merge_selected_oids = ($_POST['merge_order_numbers'] == 'true');
    }
    if ($_GET['action'] == 'batch_forms') {
   $target_file = zen_db_prepare_input($_POST['target_file']);
        $num_copies = zen_db_prepare_input($_POST['num_copies']);

        batch_forms($target_file, $selected_oids, $num_copies);
    }
    else if ($_GET['action'] == 'merged_packingslips') {
        lcsd_merged_packingslips($selected_oids, $merge_selected_oids);
    }
    else if($_GET['action'] == 'merged_packingslips_master_list') {
        lcsd_merged_packingslips_master_list($selected_oids, $merge_selected_oids);
    }
    /* END modification */
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
  <?php
  /* BEGIN addition added javascript for forms */
  ?>


        function GenerateBatchForms(action) {
          var batch_form = document.forms["batch_print"];

          if (action == "invoicepages") {
            batch_form.action = 'super_batch_pages.php';
          }
          else {
            //batch_form.action = (batch_form.action).split('?')[0] + '?action=' + action;
            batch_form.action = 'super_batch_forms.php?action=' + action;
          }
          batch_form.submit();
        }

        function checkByParent(aId) {
          var collection = document.getElementById(aId).getElementsByTagName('INPUT');
          for (var x=0; x<collection.length; x++) {
            if (collection[x].type.toUpperCase()=='CHECKBOX') {
              if (collection[x].checked == true) {
                collection[x].checked = false;
              } else {
                collection[x].checked = true;
              }
            }
          }
        }

<?php /* END addition */ ?>
      </script>
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
  // -->
</script>
</head>
<body onLoad="init()">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<script language="javascript">
var StartDate = new ctlSpiffyCalendarBox("StartDate", "order_search", "start_date", "btnDate1", "<?php echo (($_GET['start_date'] == '') ? '' : $_GET['start_date']); ?>", scBTNMODE_CUSTOMBLUE);
var EndDate = new ctlSpiffyCalendarBox("EndDate", "order_search", "end_date", "btnDate2", "<?php echo (($_GET['end_date'] == '') ? '' : $_GET['end_date']); ?>", scBTNMODE_CUSTOMBLUE);
</script>
<table border="0" width="100%" cellspacing="2" cellpadding="2">

    <tr>
<!-- search -->
<td width="100%" valign="top">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td>
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2" class="pageHeading"><?php echo
              HEADING_TITLE . '&nbsp;&nbsp;' .
              '<INPUT class="normal_button button" TYPE="BUTTON" VALUE="' . BOX_CUSTOMERS_SUPER_BATCH_STATUS . '" ONCLICK="window.location.href=\'' . zen_href_link(FILENAME_SUPER_BATCH_STATUS, '') . '\'">' .
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
<?php echo zen_draw_form('order_search', FILENAME_SUPER_BATCH_FORMS, '', 'get', '', true); ?>
                <tr>
                  <td valign="top">
                  <table border="0" cellspacing="3" cellpadding="0">
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
                  <td valign="top">
                  <table border="0" cellspacing="3" cellpadding="0">
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
                  <td valign="top">
                  <table border="0" cellspacing="3" cellpadding="0">
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
    // if you want to start above order 1, uncomment this block
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
    /* BEGIN modification updated form to include pdf forms */

    /* show forms based on configuration preference */
    /*  0 - standard form
        1 - pdf form
        2 - both forms
    */
    echo zen_draw_form('batch_print', FILENAME_SUPER_BATCH_FORMS, 'action=batch_forms', 'post', 'target="_blank"');
    if(in_array(LCSD_PRINTING_MENU, array(0,2))){
?>
          <tr>
            <td>
              <table border="0" cellspacing="2" cellpadding="0">
                <tr>
                  <td class="main"><?php echo HEADING_SELECT_FORM; ?></td>
                  <td class="main"><?php echo zen_draw_separator('pixel_trans.gif', 5, 1); ?></td>
                  <td class="main"><?php echo HEADING_NUM_COPIES; ?></td>
                </tr>
                <tr>
                  <td class="main" colspan="2"><?php echo zen_draw_radio_field('target_file', FILENAME_SUPER_INVOICE . '.php',true) . SELECT_INVOICE; ?></td>

                  <td class="main">
                  <select name="num_copies" size="1">
                  <option>1</option>
                  <option>2</option>
                  <option>3</option>
                  <option>4</option>
                  <option>5</option>
                  <option>6</option>
                  <option>7</option>
                  <option>8</option>
                  <option>9</option>
                  <option>10</option>
                  </select></td>
                </tr>
                <tr>
                  <td class="main" colspan="3"><?php echo zen_draw_radio_field('target_file', FILENAME_SUPER_PACKINGSLIP . '.php') . SELECT_PACKINGSLIP; ?></td>
                </tr>
                <tr>
                  <td class="main" colspan="3"><?php echo zen_draw_radio_field('target_file', FILENAME_SUPER_SHIPPING_LABEL . '.php') . SELECT_SHIPPING_LABEL; ?></td>
                </tr>
                <tr>
            <td class="main" colspan="3" align="right"><input class="normal_button button" type="button" value="<?php echo BUTTON_SUBMIT_PRINT; ?>" onClick="GenerateBatchForms('invoicepages')"></td>
                </tr>
            </table></td>
          </tr>
<?php
    }
    if(LCSD_PRINTING_MENU == 2){
?>
      <tr>
        <td colspan="2"><?php echo zen_draw_separator(); ?></td>
      </tr>
<?php
    }
    if(in_array(LCSD_PRINTING_MENU, array(1,2))){
?>
    <tr>
        <td>
            <div style="float: left;">
                <div><b><?php echo PACKING_SLIPS_PDF_FORMS; ?></b></div>
                <div style="padding-top: 4px;"><input class="normal_button button" type="button" value="Print" onClick="GenerateBatchForms('merged_packingslips_master_list')"> <?php echo PACKING_SLIPS_MASTER_LIST; ?> </div>
                <div style="padding-top: 4px;"><input class="normal_button button" type="button" value="Print" onClick="GenerateBatchForms('merged_packingslips')"> <?php echo PACKING_SLIPS_SELECTED_ORDERS; ?> </div>
            </div>
            <div style="margin-left: 40px; position: relative; float: left; width: 400px;">
                <div><b><?php echo PACKING_SLIPS_PRINT_OPTIONS; ?></b></div>
                <div style="border: 1px solid #888888; margin-top: 5px; padding-bottom: 5px; "><input type="checkbox" name="merge_order_numbers" value="true" checked="checked"> <?php echo PACKING_SLIPS_MERGE_CUSTOMERS; ?> </div>
            </div>
            <div style="clear: both"></div>
        </td>
    </tr>
<?php
    }
?>
    <tr>
        <td colspan="2"><?php echo zen_draw_separator(); ?></td>
    </tr>
<?php /* END modification */ ?>
      <tr>
            <td>
  <table border="0" width="100%" cellspacing="2" cellpadding="0">
                <tr>
                  <td class="main" valign="bottom"><?php
      echo TEXT_TOTAL_ORDERS . '<strong>' . $orders->RecordCount() . '</strong>' . '&nbsp;&nbsp;';
          echo '<INPUT class="normal_button button" TYPE="BUTTON" VALUE="' . BUTTON_CHECK_ALL . '" ONCLICK="checkByParent(\'ordersList\');">';
            ?></td>
                  <td class="smallText" align="right" valign="bottom"><?php echo zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS) . '&nbsp;' . ICON_ORDER_DETAILS; ?></td>
                </tr>
            </table></td>
          </tr>
          <tr>
        <td><table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td valign="top">
          <div id="ordersList">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" align="left" colspan="2">&nbsp;&nbsp;<?php echo TABLE_HEADING_ORDERS_ID; ?></td>
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

                        <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_details.gif', ICON_ORDER_DETAILS) . '</a>&nbsp'; ?></td>
                      </tr>


<?php
      $orders->MoveNext();
    }
  }  // END if ($orders->RecordCount() > 0)
?>
                </form>
                  </table>
          </div>
          </td>
                </tr>
                <tr>
                  <td><?php echo zen_draw_separator('pixel_trans.gif', 1, 10); ?></td>
                </tr>
            </table></td>
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
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
<?php }

