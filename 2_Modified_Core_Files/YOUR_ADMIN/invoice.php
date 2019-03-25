<?php
/*
 * @package admin
//////////////////////////////////////////////////////////////////////////
//  Based on Super Order 2.0                                        	//
//  By Frank Koehl - PM: BlindSide (original author)                	//
//                                                                  	//
//  Super Orders Updated by:						//
//  ~ JT of GTICustom							//
//  ~ C Jones Over the Hill Web Consulting (http://overthehillweb.com)	//
//  ~ Loose Chicken Software Development, david@loosechicken.com	//
//                                                      		//
//////////////////////////////////////////////////////////////////////////
//  DESCRIPTION: Replaces admin/invoice.php, adds amount paid &		//
//  balance due values based on super_order class calculations.  Also	//
//  includes the option to display a tax exemption number, configurable	//
//  from the admin.							//
//////////////////////////////////////////////////////////////////////////
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: invoice.php 19136 2011-07-18 16:56:18Z wilt $
*/

  require_once('includes/application_top.php');
  require_once(DIR_WS_CLASSES . 'currencies.php');
 // $currencies = new currencies();
  //require(DIR_WS_CLASSES . 'order.php');
  require_once(DIR_WS_CLASSES . 'super_order.php');

  
  require_once(DIR_WS_CLASSES . 'order.php');
// AJB 2012-05-31 - start (1)
// if(isset($_GET['oID']))
// $oID = zen_db_prepare_input($_GET['oID']);
if(isset($_GET['oID'])) {
	$oID = zen_db_prepare_input($_GET['oID']);
	$batched = false;
	$batch_item = 0;
} else {
	$batched = true;
}

// AJB 2012-05-31 - end (1)
  $order = new order($oID);
  $so = new super_order($oID);
  $currencies = new currencies();

  $display_tax = (TAX_ID_NUMBER == '' ? true : false);

  // Find any comments entered at checkout
  // and display on invoice if they exist
  // prepare order-status pulldown list
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
?>

<?php // AJB 2012-05-31 - start (2) // ?>
<?php if ($batched == false) {
	$page_title = HEADER_INVOICE . (int)$oID;
} else {
	$page_title = HEADER_INVOICES;
}
?>

<?php if (($batched == false) or ($batched == true and $batch_item == 1)) { ?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">

<style>
@media screen
{
div.form-separator {border-style:none none solid none;	border-bottom:thick dotted #000000;;}
}
@media print
{
div.form-separator {display: none;}
}
</style>

<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" type="text/javascript"><!--
function couponpopupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<?php } ?>
<?php // AJB 2012-05-31 - end (2) // ?>
<?php
    $prev_oID = $oID - 1;
    $next_oID = $oID + 1;

    $prev_button = '            <a href ="' . zen_href_link(FILENAME_SUPER_INVOICE, 'oID=' . $prev_oID) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';

    $check_for_next = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$next_oID . "'");
    if (zen_not_null($check_for_next->fields['orders_id'])) {
      $next_button = '            <a href ="' . zen_href_link(FILENAME_SUPER_INVOICE, 'oID=' . $next_oID) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';
    }
    else {
      $next_button = '            <a href ="' . zen_href_link(FILENAME_SUPER_ORDERS) . '">' . zen_draw_separator('pixel_trans.gif', '50', '30') . '</a>';
    }
?>

<?php // AJB 2012-05-31 - start (3) // ?>
<?php if (($batched == true) and ($batch_item > 1) and (($batch_item % $forms_per_page) == 0)) { ?>
<div style="page-break-before:always"><span style="display: none;">&nbsp;</span></div>
<?php } ?>
<div class="form-separator"></div>
<?php // AJB 2012-05-31 - end (3) // ?>
<!-- body_text //-->

<div>
	<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td colspan="3"><table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td class="pageHeading" valign="top" ><?php 
	echo nl2br(STORE_NAME_ADDRESS);
          if (!$display_tax) echo '<br><br>' . HEADER_TAX_ID . TAX_ID_NUMBER;
        ?><br>
</td>
        
        <td valign="top"><table border="0" cellpadding="0" cellspacing="2">
          <tr>
            <td class="invoiceHeading" align="left" valign="top"><?php echo HEADER_PHONE; ?></td>
            <td class="invoiceHeading" align="left" valign="top"><?php echo STORE_PHONE; ?></td>
          </tr>
          <tr>
            <td class="invoiceHeading" align="left" valign="top"><?php echo HEADER_FAX; ?></td>
            <td class="invoiceHeading" align="left" valign="top"><?php echo STORE_FAX; ?></td>
          </tr>
          <tr>
            <td class="invoiceHeading" align="left" valign="bottom"><?php echo $prev_button; ?></td>
            <td class="invoiceHeading" align="right" valign="bottom"><?php echo $next_button; ?></td>
          </tr>
        </table></td>
        <td class="pageHeading" align="right"><?php 
	echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '<br>';
          echo HEADER_INVOICE . (int)$oID;
        ?></td>
      </tr>
    </table></td>
  </tr>
  
  <tr>
        <td colspan="6"><?php echo zen_draw_separator(); ?></td>
      </tr>
  
  <tr>
    <td><table border="0" cellpadding="2" cellspacing="0" width="100%">
      

<?php
      $order_check = $db->Execute("select cc_cvv, customers_name, customers_company, customers_street_address,
                                    customers_suburb, customers_city, customers_postcode,
                                    customers_state, customers_country, customers_telephone,
                                    customers_email_address, customers_address_format_id, delivery_name,
                                    delivery_company, delivery_street_address, delivery_suburb,
                                    delivery_city, delivery_postcode, delivery_state, delivery_country,
                                    delivery_address_format_id, billing_name, billing_company,
                                    billing_street_address, billing_suburb, billing_city, billing_postcode,
                                    billing_state, billing_country, billing_address_format_id,
                                    payment_method, cc_type, cc_owner, cc_number, cc_expires, currency,
                                    currency_value, date_purchased, orders_status, last_modified
                             from " . TABLE_ORDERS . "
                             where orders_id = '" . (int)$oID . "'");
  $show_customer = 'false';
  if ($order_check->fields['billing_name'] != $order_check->fields['delivery_name']) {
    $show_customer = 'true';
  }
  if ($order_check->fields['billing_street_address'] != $order_check->fields['delivery_street_address']) {
    $show_customer = 'true';
  }
  if ($show_customer == 'true') {
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '23', '1'); ?></td>
        <td valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%">
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>        
          <tr>
            <td class="main"><strong><?php echo ENTRY_BILL_TO; ?></strong></td>
          </tr>
          <tr>
		  
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
		  <?php }?>
          <tr>
            <td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->billing, 1, '', '<br>'); ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo $order->customer['telephone']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
          </tr>
        </table></td>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '60', '1'); ?></td>
        <td valign="top"><table border="0" cellpadding="2" cellspacing="0" width="100%">
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo ENTRY_SHIP_TO; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
          </tr>
        </table></td>
<?php
        if ($so->purchase_order) {
?>
        <td align="right" valign="top"><table border="0" cellpadding="2" cellspacing="0">
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo ENTRY_PO_INFO; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main" align="left"><?php echo HEADER_PO_NUMBER; ?></td>
            <td class="main" align="right"><?php echo $so->purchase_order[0]['number']; ?></td>
          </tr>
          <tr>
            <td class="main" align="left"><?php echo HEADER_PO_INVOICE_DATE; ?></td>
            <td class="main" align="right"><?php echo zen_date_short($so->purchase_order[0]['posted']); ?></td>
          </tr>
          <tr>
            <td class="main" align="left"><?php echo HEADER_PO_TERMS; ?></td>
            <td class="main" align="right"><?php echo HEADER_PO_TERMS_LENGTH; ?></td>
          </tr>
        </table></td>
<?php } ?>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
        <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
        <td class="main"><?php echo $order->info['payment_method']; ?></td>
      </tr>
      <tr>
        <td class="main" colspan="2"><strong> </strong></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td><table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" colspan="2" width="25%"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent" width="25%"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
        <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_TAX; ?></td>
        <?php if ($display_tax) { ?>
        <td class="dataTableHeadingContent" align="right" width="10%"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right" width="10%"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right" width="10%"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right" width="10%"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
        <?php } else { ?>
        <td class="dataTableHeadingContent" align="right" width="20%"><?php echo TABLE_HEADING_PRICE_NO_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right" width="20%"><?php echo TABLE_HEADING_TOTAL_NO_TAX; ?></td>
        <?php } ?>
      </tr>
<?php
    $decimals = $currencies->get_decimal_places($order->info['currency']);
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true')
      {
        $priceIncTax = $currencies->format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']),$decimals) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
      } else 
      {
        $priceIncTax = $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
      }
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (($k = sizeof($order->products[$i]['attributes'])) > 0)) {
        for ($j = 0; $j < $k; $j++) {
          echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') echo TEXT_INFO_ATTRIBUTE_FREE;
          echo '</i></small></nobr>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n";
      echo '        <td class="dataTableContent" valign="top">';
      if ($display_tax) {
        echo zen_display_tax_value($order->products[$i]['tax']) . '%';
      }
      else {
        echo ENTRY_NO_TAX;
      }
      echo '</td>' . "\n" ;
    if ($display_tax) {
      echo '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n";
      echo '      </tr>' . "\n";
    }
    else {
      echo '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '      </tr>' . "\n";
    }
    }
?>
      <tr>
        <td colspan="8" align="right"><table border="0" cellpadding="2" cellspacing="0">

<!-- BOF added to get currency type and value for totals (modified for ZenCart 1.5.3 and beyond by retched) -->
                <?php 
                $dbc= $db->Execute("select currency, currency_value from " . TABLE_ORDERS . " where orders_id ='" . $_GET['oID'] . "'");
                $cu = $dbc->fields['currency'];
                $cv = $dbc->fields['currency_value'];
                ?>
<!-- EOF added to get currency type and value for totals (modified for ZenCart 1.5.3 and beyond by retched) -->

<?php
  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
  $display_title = $order->totals[$i]['title'];

    echo '          <tr>' . "\n" .
         '            <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $display_title . '</td>' . "\n" .
         '            <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
    $order_total = $order->totals[$i]['text'];
  }

    echo '          <tr>' . "\n" .
         '            <td align="right" class="ot-tax-TextPrint">&nbsp;</td>' . "\n" .
         '            <td align="right" class="printMain">&nbsp;</td>' . "\n" .
         '          </tr>' . "\n";

    echo '          <tr>' . "\n" .
         '            <td align="right" class="ot-tax-TextPrint"><strong>'. ENTRY_AMOUNT_APPLIED_CUST .' (' . $cu.')'. '</strong></td>' . "\n" .
         '            <td align="right" class="printMain"><strong>' . $currencies->format($so->amount_applied, true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
         '          </tr>' . "\n";

    echo '          <tr>' . "\n" .
         '            <td align="right" class="ot-tax-TextPrint"><strong>'. ENTRY_BALANCE_DUE_CUST .' (' . $cu.')'. '</strong></td>' . "\n" .
         '            <td align="right" class="printMain"><strong>' . $currencies->format($so->balance_due, true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
         '          </tr>' . "\n";
?>
        </table></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
  </tr>
<?php if (ORDER_COMMENTS_INVOICE > 0) { ?>
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="smallText" align="left"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
          </tr>
<?php
    $orders_history = $db->Execute("select orders_status_id, date_added, customer_notified, comments
                                    from " . TABLE_ORDERS_STATUS_HISTORY . "
                                    where orders_id = '" . zen_db_input($oID) . "' and customer_notified >= 0
                                    order by date_added");

    if ($orders_history->RecordCount() > 0) {
      $count_comments=0;
      while (!$orders_history->EOF) {
        $count_comments++;
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="left" valign="top">' . zen_datetime_short($orders_history->fields['date_added']) . '</td>' . "\n";
        echo '            <td class="smallText" align="left" valign="top">' . $orders_status_array[$orders_history->fields['orders_status_id']] . '</td>' . "\n";
        echo '            <td class="smallText" align="left" valign="top">' . ($orders_history->fields['comments'] == '' ? TEXT_NONE : nl2br(zen_db_output($orders_history->fields['comments']))) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
        $orders_history->MoveNext();
        if (ORDER_COMMENTS_INVOICE == 1 && $count_comments >= 1) {
         break;
        }
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
<?php } // order comments ?>
</table>
</div>


<!-- body_text_eof //-->

<br>
<?php // AJB 2012-05-31 - start (4) // ?>
<?php if (($batched == false) or (($batched == true) and ($batch_item == $number_of_orders))) { ?>
</body>
</html>
<?php } ?>
<?php // AJB 2012-05-31 - end (4) // ?>
<?php require_once(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
